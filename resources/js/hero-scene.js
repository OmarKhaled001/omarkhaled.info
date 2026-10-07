// Hero 3D scene: a Laravel platform as four stacked plates (database → Laravel core → Filament admin →
// interface), drawn with raw WebGL2 — no 3D library, so it stays a few KB — and pulled apart by scroll.
// app.js imports this lazily after the page has loaded, so the headline stays the LCP element and
// nothing here blocks the main thread during load. Colours come from the CSS tokens, so the model
// follows light/dark mode and the accent colour from Site Settings.

const S = 1; // plate half-size
const R = 0.2; // corner radius
const T = 0.12; // plate thickness
const SEG = 8; // segments per rounded corner
const LAYERS = 4;
const STRIDE = 9; // position(3) normal(3) uv(2) face(1)
const DEG = Math.PI / 180;
const INTRO_MS = 1400;

const VERTEX = `#version 300 es
in vec3 aPos;
in vec3 aNormal;
in vec2 aUv;
in float aFace;
uniform mat4 uModel;
uniform mat4 uViewProj;
uniform float uShift;
out vec3 vNormal;
out vec2 vUv;
out float vFace;
void main() {
    vNormal = mat3(uModel) * aNormal;
    vUv = aUv;
    vFace = aFace;
    gl_Position = uViewProj * uModel * vec4(aPos, 1.0);
    gl_Position.x += uShift * gl_Position.w;
}`;

// Top faces carry a printed "plate" for each layer, drawn procedurally from the UVs.
const FRAGMENT = `#version 300 es
precision highp float;
in vec3 vNormal;
in vec2 vUv;
in float vFace;
uniform vec3 uTop;
uniform vec3 uSide;
uniform vec3 uInk;
uniform vec3 uRim;
uniform vec3 uAccent;
uniform vec3 uShade;
uniform vec3 uLight;
uniform float uLayer;
uniform float uAlpha;
uniform float uShadow;
out vec4 outColor;

float aa(float d) { float w = fwidth(d); return 1.0 - smoothstep(-w, w, d); }
float box(vec2 p, vec4 r) {
    vec2 c = (r.xy + r.zw) * 0.5;
    vec2 d = abs(p - c) - (r.zw - r.xy) * 0.5;
    return aa(max(d.x, d.y));
}
float frame(vec2 p, vec4 r, float t) { return box(p, r) - box(p, r + vec4(t, t, -t, -t)); }
float disc(vec2 p, vec2 c, float r) { return aa(length(p - c) - r); }
float roundRect(vec2 p, float b, float r) {
    vec2 q = abs(p) - b + r;
    return length(max(q, 0.0)) + min(max(q.x, q.y), 0.0) - r;
}
float hash(float n) { return fract(sin(n * 12.9898) * 43758.5453); }

void main() {
    if (vFace > 1.5) {
        float d = length(vUv - 0.5) * 2.0;
        float a = (1.0 - smoothstep(0.1, 1.0, d)) * uShadow;
        outColor = vec4(uShade * a, a);
        return;
    }

    float light = max(dot(normalize(vNormal), uLight), 0.0);
    vec3 col;

    if (vFace > 0.5) {
        vec2 p = vUv;
        float ink = 0.0;
        float acc = 0.0;

        float e = roundRect((p - 0.5) * 2.0, ${S.toFixed(1)}, ${R.toFixed(2)});
        float we = fwidth(e);
        ink += (1.0 - smoothstep(0.0, we * 1.5, abs(e + 0.1) - 0.004)) * 0.6;
        float rim = 1.0 - smoothstep(0.0, we * 2.0, -e - 0.014);

        // Repeated shapes use cell lookups (floor) rather than loops: one evaluation per pixel and a
        // small shader that compiles fast even on software rasterisers.
        if (uLayer > 2.5) {
            // Interface: nav bar, hero block, copy lines, three cards.
            ink += box(p, vec4(0.16, 0.16, 0.84, 0.205)) * 0.7;
            acc += box(p, vec4(0.16, 0.27, 0.58, 0.5));
            ink += box(p, vec4(0.64, 0.29, 0.84, 0.32));
            ink += box(p, vec4(0.64, 0.36, 0.8, 0.39));
            ink += box(p, vec4(0.64, 0.43, 0.76, 0.46));
            float x = 0.16 + clamp(floor((p.x - 0.16) / 0.233), 0.0, 2.0) * 0.233;
            ink += frame(p, vec4(x, 0.58, x + 0.205, 0.84), 0.012);
            ink += box(p, vec4(x + 0.03, 0.62, x + 0.13, 0.645)) * 0.8;
        } else if (uLayer > 1.5) {
            // Filament admin: sidebar menu, stat cards, table.
            ink += box(p, vec4(0.16, 0.16, 0.3, 0.84)) * 0.35;
            float mi = clamp(floor((p.y - 0.22) / 0.08), 0.0, 4.0);
            float menu = box(p, vec4(0.19, 0.22 + mi * 0.08, 0.27, 0.245 + mi * 0.08));
            if (mi == 1.0) acc += menu; else ink += menu * 0.8;
            float sx = 0.36 + clamp(floor((p.x - 0.36) / 0.165), 0.0, 2.0) * 0.165;
            ink += frame(p, vec4(sx, 0.16, sx + 0.15, 0.32), 0.012);
            ink += box(p, vec4(0.36, 0.4, 0.84, 0.45)) * 0.45;
            float ri = clamp(floor((p.y - 0.5) / 0.07), 0.0, 4.0);
            float ry = 0.5 + ri * 0.07;
            ink += box(p, vec4(0.36, ry + 0.035, 0.84, ry + 0.043)) * 0.6;
            ink += box(p, vec4(0.38, ry + 0.005, 0.48 + hash(ri) * 0.25, ry + 0.022)) * 0.8;
        } else if (uLayer > 0.5) {
            // Laravel core: indented lines of code, one highlighted.
            float li = clamp(floor((p.y - 0.18) / 0.085), 0.0, 7.0);
            float ly = 0.18 + li * 0.085;
            float lx = 0.17 + mod(li, 3.0) * 0.05;
            float line = box(p, vec4(lx, ly, min(lx + 0.18 + hash(li + 3.0) * 0.42, 0.84), ly + 0.032));
            if (li == 3.0) acc += line; else ink += line * 0.85;
        } else {
            // Database: a grid of records, one row highlighted.
            vec2 cell = clamp(floor((p - 0.14) / 0.12), 0.0, 5.0);
            float dot = disc(p, 0.2 + cell * 0.12, 0.03);
            if (cell.y == 2.0) acc += dot; else ink += dot * 0.8;
        }

        col = mix(uTop, uInk, clamp(ink, 0.0, 1.0));
        col = mix(col, uAccent, clamp(acc, 0.0, 1.0));
        col = mix(col, uRim, rim);
        col *= 0.94 + 0.06 * light;
    } else {
        float h = vUv.y; // 0 = bottom edge, 1 = top edge
        col = uSide * (0.66 + 0.34 * light);
        col = mix(col, uRim, smoothstep(0.7, 1.0, h) * 0.5);
        if (uLayer > 2.5) {
            col = mix(col, uAccent, 1.0 - smoothstep(0.0, fwidth(h) * 1.5, abs(h - 0.42) - 0.12));
        }
    }

    outColor = vec4(col * uAlpha, uAlpha);
}`;

const clamp = (v, min = 0, max = 1) => Math.min(max, Math.max(min, v));
const easeOut = (t) => 1 - (1 - t) ** 3;
const smooth = (t) => t * t * (3 - 2 * t);
const mix = (a, b, t) => a.map((v, i) => v + (b[i] - v) * t);

// --- Geometry -------------------------------------------------------------------------------

function plateGeometry() {
    const ring = [];
    const centers = [
        [S - R, S - R],
        [-(S - R), S - R],
        [-(S - R), -(S - R)],
        [S - R, -(S - R)],
    ];
    centers.forEach(([cx, cz], corner) => {
        for (let i = 0; i <= SEG; i++) {
            const a = ((corner + i / SEG) * Math.PI) / 2;
            ring.push([cx + Math.cos(a) * R, cz + Math.sin(a) * R, Math.cos(a), Math.sin(a)]);
        }
    });

    const v = [];
    const top = T / 2;
    const uv = (x, z) => [x / (2 * S) + 0.5, z / (2 * S) + 0.5];

    for (let i = 0; i < ring.length; i++) {
        const a = ring[i];
        const b = ring[(i + 1) % ring.length];
        // Top face (fan from the centre).
        v.push(0, top, 0, 0, 1, 0, 0.5, 0.5, 1);
        v.push(a[0], top, a[1], 0, 1, 0, ...uv(a[0], a[1]), 1);
        v.push(b[0], top, b[1], 0, 1, 0, ...uv(b[0], b[1]), 1);
        // Side wall with smooth normals around the rounded corners.
        v.push(a[0], top, a[1], a[2], 0, a[3], 0, 1, 0);
        v.push(a[0], -top, a[1], a[2], 0, a[3], 0, 0, 0);
        v.push(b[0], -top, b[1], b[2], 0, b[3], 0, 0, 0);
        v.push(a[0], top, a[1], a[2], 0, a[3], 0, 1, 0);
        v.push(b[0], -top, b[1], b[2], 0, b[3], 0, 0, 0);
        v.push(b[0], top, b[1], b[2], 0, b[3], 0, 1, 0);
    }

    // Soft contact shadow under the stack.
    const shadow = [
        [-1, -1, 0, 0],
        [1, -1, 1, 0],
        [1, 1, 1, 1],
        [-1, -1, 0, 0],
        [1, 1, 1, 1],
        [-1, 1, 0, 1],
    ];
    shadow.forEach(([x, z, su, sv]) => v.push(x, 0, z, 0, 1, 0, su, sv, 2));

    return { data: new Float32Array(v), plate: ring.length * 9, shadow: 6 };
}

// --- Matrices (column-major) ----------------------------------------------------------------

function perspective(fovy, aspect, near, far) {
    const f = 1 / Math.tan(fovy / 2);
    const nf = 1 / (near - far);
    return new Float32Array([f / aspect, 0, 0, 0, 0, f, 0, 0, 0, 0, (far + near) * nf, -1, 0, 0, 2 * far * near * nf, 0]);
}

/** View matrix for a camera at `eye` looking at the origin, with +Y up. */
function lookAt(eye) {
    let [zx, zy, zz] = eye;
    let l = Math.hypot(zx, zy, zz);
    zx /= l;
    zy /= l;
    zz /= l;
    let xx = zz;
    let xz = -zx;
    l = Math.hypot(xx, xz);
    xx /= l;
    xz /= l;
    const yx = zy * xz;
    const yy = zz * xx - zx * xz;
    const yz = -zy * xx;
    return new Float32Array([
        xx, yx, zx, 0,
        0, yy, zy, 0,
        xz, yz, zz, 0,
        -(xx * eye[0] + xz * eye[2]),
        -(yx * eye[0] + yy * eye[1] + yz * eye[2]),
        -(zx * eye[0] + zy * eye[1] + zz * eye[2]),
        1,
    ]);
}

function multiply(a, b) {
    const o = new Float32Array(16);
    for (let c = 0; c < 4; c++) {
        for (let r = 0; r < 4; r++) {
            o[c * 4 + r] = a[r] * b[c * 4] + a[4 + r] * b[c * 4 + 1] + a[8 + r] * b[c * 4 + 2] + a[12 + r] * b[c * 4 + 3];
        }
    }
    return o;
}

/** Uniform scale k, rotation about Y, then translation up by y. */
function model(k, y, yaw) {
    const c = Math.cos(yaw) * k;
    const s = Math.sin(yaw) * k;
    return new Float32Array([c, 0, -s, 0, 0, k, 0, 0, s, 0, c, 0, 0, y, 0, 1]);
}

function apply(m, x, y, z) {
    return [
        m[0] * x + m[4] * y + m[8] * z + m[12],
        m[1] * x + m[5] * y + m[9] * z + m[13],
        m[2] * x + m[6] * y + m[10] * z + m[14],
        m[3] * x + m[7] * y + m[11] * z + m[15],
    ];
}

// --- WebGL plumbing -------------------------------------------------------------------------

function compile(gl, type, source) {
    const shader = gl.createShader(type);
    gl.shaderSource(shader, source);
    gl.compileShader(shader);
    return gl.getShaderParameter(shader, gl.COMPILE_STATUS) ? shader : null;
}

function createProgram(gl) {
    const vs = compile(gl, gl.VERTEX_SHADER, VERTEX);
    const fs = compile(gl, gl.FRAGMENT_SHADER, FRAGMENT);
    if (!vs || !fs) return null;
    const program = gl.createProgram();
    gl.attachShader(program, vs);
    gl.attachShader(program, fs);
    gl.linkProgram(program);
    return gl.getProgramParameter(program, gl.LINK_STATUS) ? program : null;
}

/** Any CSS colour (hex, rgb, oklch…) → [r, g, b] in 0..1, via a 1×1 2D canvas. */
function colorReader() {
    const canvas = document.createElement('canvas');
    canvas.width = canvas.height = 1;
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    return (value, fallback) => {
        if (!ctx || !value) return fallback;
        ctx.clearRect(0, 0, 1, 1);
        ctx.fillStyle = `rgb(${fallback.map((c) => Math.round(c * 255)).join(' ')})`;
        ctx.fillStyle = value;
        ctx.fillRect(0, 0, 1, 1);
        const [r, g, b] = ctx.getImageData(0, 0, 1, 1).data;
        return [r / 255, g / 255, b / 255];
    };
}

// --- Scene ----------------------------------------------------------------------------------

export function mount(figure) {
    const canvas = figure.querySelector('canvas');
    const stage = canvas?.parentElement;
    const section = figure.closest('[data-hero]');
    const pin = section?.querySelector('[data-hero-pin]');
    const cue = section?.querySelector('[data-hero-cue]');
    const labels = [...figure.querySelectorAll('[data-layer]')].sort((a, b) => a.dataset.layer - b.dataset.layer);

    const gl = canvas?.getContext('webgl2', { antialias: true, alpha: true, premultipliedAlpha: true, powerPreference: 'low-power' });

    // Software rasterisers (SwiftShader, llvmpipe: VMs, blocklisted GPUs, headless audits) composite
    // WebGL by reading every frame back on the main thread (~100 ms each on a mid-range phone CPU),
    // so they get the CSS-only stack instead.
    const debugInfo = gl?.getExtension('WEBGL_debug_renderer_info');
    const renderer = gl ? String(gl.getParameter(debugInfo ? debugInfo.UNMASKED_RENDERER_WEBGL : gl.RENDERER)) : '';
    const software = /swiftshader|llvmpipe|softpipe|software/i.test(renderer);
    const program = gl && !software ? createProgram(gl) : null;
    if (!program) {
        gl?.getExtension('WEBGL_lose_context')?.loseContext();
        figure.classList.add('is-static');
        return;
    }

    const geometry = plateGeometry();
    gl.bindVertexArray(gl.createVertexArray());
    gl.bindBuffer(gl.ARRAY_BUFFER, gl.createBuffer());
    gl.bufferData(gl.ARRAY_BUFFER, geometry.data, gl.STATIC_DRAW);
    [
        ['aPos', 3, 0],
        ['aNormal', 3, 3],
        ['aUv', 2, 6],
        ['aFace', 1, 8],
    ].forEach(([name, size, offset]) => {
        const loc = gl.getAttribLocation(program, name);
        gl.enableVertexAttribArray(loc);
        gl.vertexAttribPointer(loc, size, gl.FLOAT, false, STRIDE * 4, offset * 4);
    });

    gl.useProgram(program);
    const u = {};
    ['uModel', 'uViewProj', 'uShift', 'uTop', 'uSide', 'uInk', 'uRim', 'uAccent', 'uShade', 'uLight', 'uLayer', 'uAlpha', 'uShadow'].forEach(
        (name) => (u[name] = gl.getUniformLocation(program, name)),
    );
    const light = [-0.5, 0.82, 0.3];
    const ll = Math.hypot(...light);
    gl.uniform3f(u.uLight, light[0] / ll, light[1] / ll, light[2] / ll);
    gl.enable(gl.DEPTH_TEST);
    gl.enable(gl.BLEND);
    gl.blendFunc(gl.ONE, gl.ONE_MINUS_SRC_ALPHA);
    gl.clearColor(0, 0, 0, 0);

    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    const finePointer = window.matchMedia('(pointer: fine)');
    const darkScheme = window.matchMedia('(prefers-color-scheme: dark)');
    const rtl = document.documentElement.dir === 'rtl';
    const readColor = colorReader();

    let width = 0;
    let height = 0;
    let pinned = false;
    let pinTop = 0;
    let visible = true;
    let target = 0;
    let progress = 0;
    let start = 0;
    let frame = 0;
    let lost = false;
    const tilt = { x: 0, y: 0, tx: 0, ty: 0 };
    let colors = null;
    const labelWidths = [];

    function readColors() {
        const css = getComputedStyle(document.documentElement);
        const get = (name, fallback) => readColor(css.getPropertyValue(name).trim(), fallback);
        const bg = get('--bg', [0.97, 0.96, 0.95]);
        const surface = get('--surface', [1, 1, 1]);
        const surface2 = get('--surface-2', [0.94, 0.93, 0.9]);
        const strong = get('--border-strong', [0.79, 0.77, 0.73]);
        const text = get('--text', [0.07, 0.07, 0.09]);
        const accent = get('--accent', [0.91, 0.33, 0.16]);
        const dark = 0.2126 * bg[0] + 0.7152 * bg[1] + 0.0722 * bg[2] < 0.4;

        colors = dark
            ? {
                  top: mix(surface2, strong, 0.3),
                  side: mix(surface, surface2, 0.6),
                  ink: mix(surface2, text, 0.3),
                  rim: mix(strong, text, 0.35),
                  accent,
                  shade: [0, 0, 0],
                  shadow: 0.7,
              }
            : {
                  top: surface,
                  side: mix(surface2, strong, 0.3),
                  ink: strong,
                  rim: mix(strong, text, 0.12),
                  accent,
                  shade: text,
                  shadow: 0.2,
              };
    }

    /** 0 = stacked, 1 = fully exploded. Pinned: progress through the pin; otherwise: through the viewport. */
    function readScroll() {
        if (reduced.matches) {
            target = 1;
            return;
        }
        if (pinned) {
            const r = section.getBoundingClientRect();
            target = clamp((pinTop - r.top) / Math.max(r.height - pin.offsetHeight, 1));
        } else {
            // Not pinned: follow the stage's centre as it travels up the viewport.
            const r = stage.getBoundingClientRect();
            const vh = window.innerHeight;
            target = clamp((vh * 0.75 - (r.top + r.height / 2)) / (vh * 0.45));
        }
    }

    function resize() {
        const rect = canvas.getBoundingClientRect();
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        width = rect.width;
        height = rect.height;
        canvas.width = Math.max(1, Math.round(width * dpr));
        canvas.height = Math.max(1, Math.round(height * dpr));
        gl.viewport(0, 0, canvas.width, canvas.height);
        pinned = !!pin && getComputedStyle(pin).position === 'sticky';
        pinTop = pinned ? parseFloat(getComputedStyle(pin).top) || 0 : 0;
        labels.forEach((label, i) => (labelWidths[i] = label.offsetWidth));
        readScroll();
        request();
    }

    function draw(now) {
        if (!width || !height || lost) return;
        const motion = !reduced.matches;
        const intro = motion ? clamp((now - start) / INTRO_MS) : 1;
        const e = smooth(clamp((progress - 0.04) / 0.7));
        const yaw = (-45 + 30 * e + tilt.x * 10) * DEG;

        const aspect = width / height;
        const narrow = width < 440;
        const k = Math.min(1, aspect) * (narrow ? 0.8 : 1);
        const shift = (rtl ? 1 : -1) * (narrow ? 0.2 : 0.16);
        const pitch = (30 - 4 * e + tilt.y * 5) * DEG;
        const dist = 6.6 + 1.8 * e; // closer while stacked, pulls back as the layers separate
        const viewProj = multiply(perspective(30 * DEG, aspect, 0.1, 50), lookAt([0, Math.sin(pitch) * dist, Math.cos(pitch) * dist]));
        const step = 0.22 + 0.6 * e;

        gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);
        gl.uniformMatrix4fv(u.uViewProj, false, viewProj);
        gl.uniform1f(u.uShift, shift);
        gl.uniform3fv(u.uTop, colors.top);
        gl.uniform3fv(u.uSide, colors.side);
        gl.uniform3fv(u.uInk, colors.ink);
        gl.uniform3fv(u.uRim, colors.rim);
        gl.uniform3fv(u.uAccent, colors.accent);
        gl.uniform3fv(u.uShade, colors.shade);

        // Shadow first, without writing depth, so the plates always draw over it.
        gl.depthMask(false);
        gl.uniformMatrix4fv(u.uModel, false, model(k * 1.35 * (1 + 0.3 * e), (-1.5 * step - T / 2 - 0.05) * k, yaw));
        gl.uniform1f(u.uShadow, colors.shadow * (1 - 0.45 * e) * easeOut(intro));
        gl.uniform1f(u.uAlpha, 1);
        gl.drawArrays(gl.TRIANGLES, geometry.plate, geometry.shadow);
        gl.depthMask(true);
        gl.uniform1f(u.uShadow, 0);

        for (let i = 0; i < LAYERS; i++) {
            const drop = motion ? easeOut(clamp((intro * INTRO_MS - i * 140) / 800)) : 1;
            const y = ((i - 1.5) * step + (1 - drop) * 1.4) * k;
            const m = model(k, y, yaw + (i - 1.5) * 6 * DEG * e);
            gl.uniformMatrix4fv(u.uModel, false, m);
            gl.uniform1f(u.uLayer, i);
            gl.uniform1f(u.uAlpha, drop);
            gl.drawArrays(gl.TRIANGLES, 0, geometry.plate);

            placeLabel(i, m, viewProj, shift, drop, e);
        }

        if (cue) cue.style.opacity = String(1 - clamp(progress * 5));
        if (!figure.classList.contains('is-3d')) figure.classList.add('is-3d');
    }

    /**
     * Places a layer's label just past the plate's outermost corner on the free side, level with the
     * plate's centre (so labels keep the plates' even spacing whichever corner is outermost).
     */
    function placeLabel(i, m, viewProj, shift, drop, e) {
        const label = labels[i];
        if (!label) return;
        const project = (x, z) => {
            const clip = apply(viewProj, ...apply(m, x, T / 2, z).slice(0, 3));
            return [((clip[0] / clip[3] + shift) * 0.5 + 0.5) * width, (0.5 - (clip[1] / clip[3]) * 0.5) * height];
        };
        let edge = rtl ? Infinity : -Infinity;
        for (const [x, z] of [[S, S], [-S, S], [-S, -S], [S, -S]]) {
            const sx = project(x * 0.9, z * 0.9)[0];
            edge = rtl ? Math.min(edge, sx) : Math.max(edge, sx);
        }
        const y = project(0, 0)[1];
        const reveal = clamp((e - 0.3 - (LAYERS - 1 - i) * 0.1) / 0.18) * drop;
        const x = rtl ? Math.max(edge - 14, labelWidths[i] + 2) : Math.min(edge + 14, width - labelWidths[i] - 2);
        label.style.transform = `translate3d(${x.toFixed(1)}px, ${y.toFixed(1)}px, 0) translate(${rtl ? '-100%' : '0'}, -50%)`;
        label.style.opacity = reveal.toFixed(3);
    }

    /** Eases `obj[key]` towards `goal`; returns true while still moving. */
    function approach(obj, key, goal, rate) {
        obj[key] += (goal - obj[key]) * rate;
        if (Math.abs(goal - obj[key]) < 0.0005) obj[key] = goal;
        return obj[key] !== goal;
    }

    // Frames are drawn only while something changes (intro, scroll easing, pointer tilt), never idly.
    function tick(now) {
        frame = 0;
        if (!start) start = now;
        const rate = reduced.matches ? 1 : 0.12;
        const state = { progress };
        const moving = [approach(state, 'progress', target, rate), approach(tilt, 'x', tilt.tx, rate), approach(tilt, 'y', tilt.ty, rate)].some(Boolean);
        progress = state.progress;
        draw(now);
        const introRunning = !reduced.matches && now - start < INTRO_MS + 200;
        if (visible && (moving || introRunning)) request();
    }

    function request() {
        if (!frame && !lost) frame = requestAnimationFrame(tick);
    }

    readColors();
    resize();
    new ResizeObserver(resize).observe(canvas);
    new IntersectionObserver(([entry]) => {
        visible = entry.isIntersecting;
        if (visible) request();
    }).observe(section ?? figure);
    // Desktop: the model leans gently towards the pointer.
    section?.addEventListener('pointermove', (event) => {
        if (reduced.matches || !finePointer.matches || event.pointerType !== 'mouse') return;
        const r = section.getBoundingClientRect();
        tilt.tx = clamp((event.clientX - r.left) / r.width, 0, 1) * 2 - 1;
        tilt.ty = clamp((event.clientY - r.top) / Math.min(r.height, window.innerHeight), 0, 1) * 2 - 1;
        request();
    });
    section?.addEventListener('pointerleave', () => {
        tilt.tx = 0;
        tilt.ty = 0;
        request();
    });
    window.addEventListener(
        'scroll',
        () => {
            readScroll();
            request();
        },
        { passive: true },
    );

    const recolor = () => {
        readColors();
        request();
    };
    new MutationObserver(recolor).observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
    darkScheme.addEventListener('change', recolor);
    reduced.addEventListener('change', resize);

    canvas.addEventListener('webglcontextlost', (event) => {
        event.preventDefault();
        lost = true;
        figure.classList.remove('is-3d');
        figure.classList.add('is-static');
        labels.forEach((label) => label.removeAttribute('style'));
    });
}
