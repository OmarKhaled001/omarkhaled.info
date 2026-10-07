// About page: a portrait drawn with words. The server publishes only a small grayscale light map of
// the photo (App\Support\Design\Portrait); words are packed where the face is lit — bigger, bolder
// and brighter in the highlights. Motion: the portrait assembles from the brightest areas, words keep
// swapping, a light band sweeps across now and then, the pointer acts as a spotlight and the title
// drifts with scroll. Everything stops while off screen; reduced motion gets the finished still.

const DEG = Math.PI / 180;
const SWAP_EVERY_MS = 110;
const SWEEP_EVERY_MS = 6500;
const SWEEP_MS = 1900;
const FLASH_MS = 450;

const rand = (min, max) => min + Math.random() * (max - min);
const pick = (list) => list[Math.floor(Math.random() * list.length)];
const clamp = (v, min = 0, max = 1) => Math.min(max, Math.max(min, v));

function loadImage(src) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.decoding = 'async';
        img.onload = () => resolve(img);
        img.onerror = reject;
        img.src = src;
    });
}

function layer(width, height) {
    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    return canvas;
}

export async function mount(figure) {
    const canvas = figure.querySelector('canvas');
    const section = figure.closest('[data-portrait-section]');
    const drifting = section ? [...section.querySelectorAll('[data-drift]')] : [];
    const words = JSON.parse(figure.dataset.words || '[]');
    const accentWords = new Set(JSON.parse(figure.dataset.accent || '[]'));
    if (!canvas || !words.length) return;

    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const style = getComputedStyle(figure);
    const family = style.fontFamily;
    const ink = style.color;
    const accent = getComputedStyle(document.documentElement).getPropertyValue('--accent').trim() || '#E8542A';

    const [img] = await Promise.all([
        loadImage(figure.dataset.src),
        document.fonts?.load(`700 32px ${family}`, words.join(' ')).catch(() => null),
    ]);

    // Light map → luminance array (0..1).
    const probe = layer(img.naturalWidth, img.naturalHeight).getContext('2d', { willReadFrequently: true });
    probe.drawImage(img, 0, 0);
    const pixels = probe.getImageData(0, 0, img.naturalWidth, img.naturalHeight).data;
    const lum = new Float32Array(img.naturalWidth * img.naturalHeight);
    for (let i = 0; i < lum.length; i++) lum[i] = pixels[i * 4] / 255;

    // Word widths at 100px, measured once per weight.
    const measure = canvas.getContext('2d');
    const widths = { 500: {}, 700: {} };
    for (const weight of [500, 700]) {
        measure.font = `${weight} 100px ${family}`;
        for (const word of words) widths[weight][word] = measure.measureText(word).width;
    }

    const ctx = canvas.getContext('2d');
    let W = 0;
    let H = 0;
    let base = null;
    let glow = null;
    let mask = null;
    let shade = null;
    let placed = [];
    let pending = [];
    let candidates = [];
    let grid = null;
    let cols = 0;
    let cell = 1;
    let minSize = 0;
    let maxSize = 0;
    let lumAt = () => 0;

    let visible = false;
    let frame = 0;
    let dirty = true;
    let lastSwap = 0;
    let nextSweep = 0;
    const flashes = [];
    const pointer = { x: 0, y: 0, active: false };

    function setup() {
        const rect = canvas.getBoundingClientRect();
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        W = Math.max(1, Math.round(rect.width * dpr));
        H = Math.max(1, Math.round(rect.height * dpr));
        canvas.width = W;
        canvas.height = H;
        base = layer(W, H);
        glow = layer(W, H);
        mask = layer(W, H);

        // "cover" mapping from canvas pixels to the light map.
        const iw = img.naturalWidth;
        const ih = img.naturalHeight;
        const scale = Math.max(W / iw, H / ih);
        const ox = (W - iw * scale) / 2;
        const oy = (H - ih * scale) / 2;
        lumAt = (x, y) => {
            const ix = Math.floor((x - ox) / scale);
            const iy = Math.floor((y - oy) / scale);
            return ix < 0 || iy < 0 || ix >= iw || iy >= ih ? 0 : lum[iy * iw + ix];
        };

        // Shading mask: the light map as alpha, smoothly upscaled. Words act as a screen and take
        // their brightness from the photo pixel by pixel, which is what makes the features readable.
        const small = layer(iw, ih);
        const sctx = small.getContext('2d');
        const alpha = sctx.createImageData(iw, ih);
        for (let i = 0; i < lum.length; i++) {
            alpha.data[i * 4] = alpha.data[i * 4 + 1] = alpha.data[i * 4 + 2] = 255;
            alpha.data[i * 4 + 3] = Math.round((0.07 + 0.93 * lum[i] ** 0.9) * 255);
        }
        sctx.putImageData(alpha, 0, 0);
        shade = layer(W, H);
        const shctx = shade.getContext('2d');
        shctx.imageSmoothingQuality = 'high';
        shctx.drawImage(small, ox, oy, iw * scale, ih * scale);

        minSize = Math.max(5 * dpr, W * 0.0095);
        maxSize = W * 0.05;
        cell = Math.max(2, Math.round(minSize / 3));
        cols = Math.ceil(W / cell);
        grid = new Uint8Array(cols * Math.ceil(H / cell));

        // Jittered sample points, brightest first: highlights assemble before the shadows.
        candidates = [];
        const step = minSize * 0.7;
        for (let y = step / 2; y < H; y += step) {
            for (let x = step / 2; x < W; x += step) {
                const cx = x + rand(-step, step) * 0.4;
                const cy = y + rand(-step, step) * 0.4;
                const b = lumAt(cx, cy);
                if (b > 0.07) candidates.push({ x: cx, y: cy, b });
            }
        }
        candidates.sort((a, b) => b.b - a.b);
        placed = [];
        pending = [];
        dirty = true;
    }

    function cellsFree(x0, y0, w, h, mark) {
        const c0 = Math.floor(x0 / cell);
        const r0 = Math.floor(y0 / cell);
        const c1 = Math.floor((x0 + w) / cell);
        const r1 = Math.floor((y0 + h) / cell);
        for (let r = r0; r <= r1; r++) {
            for (let c = c0; c <= c1; c++) {
                if (mark) grid[r * cols + c] = 1;
                else if (grid[r * cols + c]) return false;
            }
        }
        return true;
    }

    function tryPlace({ x, y, b }) {
        let size = minSize + (maxSize - minSize) * b ** 2 * rand(0.6, 1);
        for (let attempt = 0; attempt < 4 && size >= minSize; attempt++, size *= 0.72) {
            const text = pick(words);
            const weight = size > minSize * 2.2 ? 700 : 500;
            const r = Math.random();
            const rot = r < 0.13 ? 90 : r < 0.26 ? -90 : r < 0.3 ? 180 : 0;
            const len = (widths[weight][text] * size) / 100;
            const thick = size * 0.8;
            const vertical = rot === 90 || rot === -90;
            const bw = vertical ? thick : len;
            const bh = vertical ? len : thick;
            const x0 = x - bw / 2;
            const y0 = y - bh / 2;
            if (x0 < 0 || y0 < 0 || x0 + bw >= W || y0 + bh >= H) continue;
            // Keep words inside the lit silhouette.
            if (Math.min(lumAt(x0, y0), lumAt(x0 + bw, y0), lumAt(x0, y0 + bh), lumAt(x0 + bw, y0 + bh)) < 0.03) continue;
            if (!cellsFree(x0, y0, bw, bh, false)) continue;
            cellsFree(x0, y0, bw, bh, true);
            return { text, size, weight, rot, x, y, bw, bh, b, len, alpha: 0.6 + 0.4 * b };
        }
        return null;
    }

    function paint(target, word, alpha, color) {
        const c = target.getContext('2d');
        c.save();
        c.translate(word.x, word.y);
        if (word.rot) c.rotate(word.rot * DEG);
        c.globalAlpha = alpha;
        c.fillStyle = color;
        c.font = `${word.weight} ${word.size}px ${family}`;
        c.textAlign = 'center';
        c.textBaseline = 'middle';
        c.fillText(word.text, 0, 0);
        c.restore();
    }

    function paintWord(word, flash = false) {
        const color = accentWords.has(word.text) ? accent : ink;
        paint(base, word, flash ? 1 : word.alpha, flash ? '#ffffff' : color);
        paint(glow, word, 1, accentWords.has(word.text) ? accent : '#ffffff');
    }

    function clearWord(word) {
        const pad = Math.max(1, word.size * 0.08);
        for (const target of [base, glow]) {
            target.getContext('2d').clearRect(word.x - word.bw / 2 - pad, word.y - word.bh / 2 - pad, word.bw + pad * 2, word.bh + pad * 2);
        }
    }

    /** Layout and painting are time-boxed per frame, so even slow phones never get a long task. */
    function build() {
        const start = performance.now();
        while (candidates.length && performance.now() - start < 6) {
            const word = tryPlace(candidates.shift());
            if (word) {
                placed.push(word);
                pending.push(word);
            }
        }
        const batch = reduced ? pending.length : Math.max(18, Math.ceil(pending.length / 6));
        for (const word of pending.splice(0, batch)) paintWord(word);
        dirty = true;
    }

    function swap(now) {
        const pool = placed.filter((w) => w.b > 0.25);
        for (let i = 0; i < 5 && pool.length; i++) {
            const word = pick(pool);
            const fits = words.filter((t) => {
                const len = (widths[word.weight][t] * word.size) / 100;
                return t !== word.text && len <= word.len * 1.02 && len >= word.len * 0.5;
            });
            if (!fits.length) continue;
            clearWord(word);
            word.text = pick(fits);
            word.len = (widths[word.weight][word.text] * word.size) / 100;
            paintWord(word, true);
            flashes.push({ word, until: now + FLASH_MS });
        }
        for (let i = flashes.length - 1; i >= 0; i--) {
            if (flashes[i].until <= now) {
                clearWord(flashes[i].word);
                paintWord(flashes[i].word);
                flashes.splice(i, 1);
            }
        }
        dirty = true;
    }

    function present(now) {
        ctx.clearRect(0, 0, W, H);
        ctx.drawImage(base, 0, 0);

        let gradient = null;
        if (pointer.active) {
            gradient = ctx.createRadialGradient(pointer.x, pointer.y, 0, pointer.x, pointer.y, W * 0.2);
            gradient.addColorStop(0, 'rgba(0,0,0,1)');
            gradient.addColorStop(1, 'rgba(0,0,0,0)');
        } else if (now >= nextSweep && now < nextSweep + SWEEP_MS) {
            const p = -0.25 + ((now - nextSweep) / SWEEP_MS) * 1.5;
            gradient = ctx.createLinearGradient(0, 0, W, H);
            for (const [offset, alpha] of [[p - 0.14, 0], [p, 0.9], [p + 0.14, 0]]) {
                if (offset >= 0 && offset <= 1) gradient.addColorStop(offset, `rgba(0,0,0,${alpha})`);
            }
        }

        if (gradient) {
            const m = mask.getContext('2d');
            m.globalCompositeOperation = 'source-over';
            m.clearRect(0, 0, W, H);
            m.drawImage(glow, 0, 0);
            m.globalCompositeOperation = 'destination-in';
            m.fillStyle = gradient;
            m.fillRect(0, 0, W, H);
            ctx.drawImage(mask, 0, 0);
        }

        ctx.globalCompositeOperation = 'destination-in';
        ctx.drawImage(shade, 0, 0);
        ctx.globalCompositeOperation = 'source-over';
    }

    function drift() {
        if (!section || reduced) return;
        const r = section.getBoundingClientRect();
        const progress = clamp((window.innerHeight - r.top) / (window.innerHeight + r.height)) - 0.5;
        drifting.forEach((el) => {
            el.style.transform = `translate3d(${(Number(el.dataset.drift) * progress * 9).toFixed(2)}vw, 0, 0)`;
        });
    }

    function tick(now) {
        frame = 0;
        if (candidates.length || pending.length) build();
        else if (!reduced) {
            if (now - lastSwap > SWAP_EVERY_MS) {
                lastSwap = now;
                swap(now);
            }
            if (now > nextSweep + SWEEP_MS) nextSweep = now + SWEEP_EVERY_MS * rand(0.7, 1.2);
            if (pointer.active || (now >= nextSweep && now < nextSweep + SWEEP_MS)) dirty = true;
        }
        if (dirty) {
            present(now);
            dirty = false;
        }
        if (visible && (!reduced || candidates.length || pending.length)) request();
    }

    function request() {
        if (!frame) frame = requestAnimationFrame(tick);
    }

    setup();
    nextSweep = performance.now() + 2200;
    figure.classList.add('is-ready');

    new IntersectionObserver(([entry]) => {
        visible = entry.isIntersecting;
        if (visible) request();
    }).observe(figure);

    let resizeTimer = 0;
    let lastWidth = canvas.getBoundingClientRect().width;
    new ResizeObserver(() => {
        const width = canvas.getBoundingClientRect().width;
        if (Math.abs(width - lastWidth) < 2) return;
        lastWidth = width;
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            setup();
            request();
        }, 200);
    }).observe(canvas);

    if (!reduced) {
        canvas.addEventListener('pointermove', (event) => {
            if (event.pointerType !== 'mouse') return;
            const rect = canvas.getBoundingClientRect();
            pointer.x = ((event.clientX - rect.left) / rect.width) * W;
            pointer.y = ((event.clientY - rect.top) / rect.height) * H;
            pointer.active = true;
            dirty = true;
            request();
        });
        canvas.addEventListener('pointerleave', () => {
            pointer.active = false;
            dirty = true;
            request();
        });
        window.addEventListener('scroll', drift, { passive: true });
        drift();
    }
}
