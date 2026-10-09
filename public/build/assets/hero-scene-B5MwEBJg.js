var e=1,t=.2,n=.12,r=Math.PI/180,i=1400,a=`#version 300 es
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
}`,o=`#version 300 es
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

        float e = roundRect((p - 0.5) * 2.0, ${e.toFixed(1)}, ${t.toFixed(2)});
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
}`,s=(e,t=0,n=1)=>Math.min(n,Math.max(t,e)),c=e=>1-(1-e)**3,l=e=>e*e*(3-2*e),u=(e,t,n)=>e.map((e,r)=>e+(t[r]-e)*n);function d(){let e=[];[[.8,.8],[-.8,.8],[-.8,-.8],[.8,-.8]].forEach(([n,r],i)=>{for(let a=0;a<=8;a++){let o=(i+a/8)*Math.PI/2;e.push([n+Math.cos(o)*t,r+Math.sin(o)*t,Math.cos(o),Math.sin(o)])}});let r=[],i=n/2,a=(e,t)=>[e/2+.5,t/2+.5];for(let t=0;t<e.length;t++){let n=e[t],o=e[(t+1)%e.length];r.push(0,i,0,0,1,0,.5,.5,1),r.push(n[0],i,n[1],0,1,0,...a(n[0],n[1]),1),r.push(o[0],i,o[1],0,1,0,...a(o[0],o[1]),1),r.push(n[0],i,n[1],n[2],0,n[3],0,1,0),r.push(n[0],-.06,n[1],n[2],0,n[3],0,0,0),r.push(o[0],-.06,o[1],o[2],0,o[3],0,0,0),r.push(n[0],i,n[1],n[2],0,n[3],0,1,0),r.push(o[0],-.06,o[1],o[2],0,o[3],0,0,0),r.push(o[0],i,o[1],o[2],0,o[3],0,1,0)}return[[-1,-1,0,0],[1,-1,1,0],[1,1,1,1],[-1,-1,0,0],[1,1,1,1],[-1,1,0,1]].forEach(([e,t,n,i])=>r.push(e,0,t,0,1,0,n,i,2)),{data:new Float32Array(r),plate:e.length*9,shadow:6}}function f(e,t,n,r){let i=1/Math.tan(e/2),a=1/(n-r);return new Float32Array([i/t,0,0,0,0,i,0,0,0,0,(r+n)*a,-1,0,0,2*r*n*a,0])}function p(e){let[t,n,r]=e,i=Math.hypot(t,n,r);t/=i,n/=i,r/=i;let a=r,o=-t;i=Math.hypot(a,o),a/=i,o/=i;let s=n*o,c=r*a-t*o,l=-n*a;return new Float32Array([a,s,t,0,0,c,n,0,o,l,r,0,-(a*e[0]+o*e[2]),-(s*e[0]+c*e[1]+l*e[2]),-(t*e[0]+n*e[1]+r*e[2]),1])}function m(e,t){let n=new Float32Array(16);for(let r=0;r<4;r++)for(let i=0;i<4;i++)n[r*4+i]=e[i]*t[r*4]+e[4+i]*t[r*4+1]+e[8+i]*t[r*4+2]+e[12+i]*t[r*4+3];return n}function h(e,t,n){let r=Math.cos(n)*e,i=Math.sin(n)*e;return new Float32Array([r,0,-i,0,0,e,0,0,i,0,r,0,0,t,0,1])}function g(e,t,n,r){return[e[0]*t+e[4]*n+e[8]*r+e[12],e[1]*t+e[5]*n+e[9]*r+e[13],e[2]*t+e[6]*n+e[10]*r+e[14],e[3]*t+e[7]*n+e[11]*r+e[15]]}function _(e,t,n){let r=e.createShader(t);return e.shaderSource(r,n),e.compileShader(r),e.getShaderParameter(r,e.COMPILE_STATUS)?r:null}function v(e){let t=_(e,e.VERTEX_SHADER,a),n=_(e,e.FRAGMENT_SHADER,o);if(!t||!n)return null;let r=e.createProgram();return e.attachShader(r,t),e.attachShader(r,n),e.linkProgram(r),e.getProgramParameter(r,e.LINK_STATUS)?r:null}function y(){let e=document.createElement(`canvas`);e.width=e.height=1;let t=e.getContext(`2d`,{willReadFrequently:!0});return(e,n)=>{if(!t||!e)return n;t.clearRect(0,0,1,1),t.fillStyle=`rgb(${n.map(e=>Math.round(e*255)).join(` `)})`,t.fillStyle=e,t.fillRect(0,0,1,1);let[r,i,a]=t.getImageData(0,0,1,1).data;return[r/255,i/255,a/255]}}function b(t){let a=t.querySelector(`canvas`),o=a?.parentElement,_=t.closest(`[data-hero]`),b=_?.querySelector(`[data-hero-pin]`),x=_?.querySelector(`[data-hero-cue]`),S=[...t.querySelectorAll(`[data-layer]`)].sort((e,t)=>e.dataset.layer-t.dataset.layer),C=a?.getContext(`webgl2`,{antialias:!0,alpha:!0,premultipliedAlpha:!0,powerPreference:`low-power`}),w=C?.getExtension(`WEBGL_debug_renderer_info`),T=C?String(C.getParameter(w?w.UNMASKED_RENDERER_WEBGL:C.RENDERER)):``,ee=/swiftshader|llvmpipe|softpipe|software/i.test(T),E=C&&!ee?v(C):null;if(!E){C?.getExtension(`WEBGL_lose_context`)?.loseContext(),t.classList.add(`is-static`);return}let D=d();C.bindVertexArray(C.createVertexArray()),C.bindBuffer(C.ARRAY_BUFFER,C.createBuffer()),C.bufferData(C.ARRAY_BUFFER,D.data,C.STATIC_DRAW),[[`aPos`,3,0],[`aNormal`,3,3],[`aUv`,2,6],[`aFace`,1,8]].forEach(([e,t,n])=>{let r=C.getAttribLocation(E,e);C.enableVertexAttribArray(r),C.vertexAttribPointer(r,t,C.FLOAT,!1,36,n*4)}),C.useProgram(E);let O={};[`uModel`,`uViewProj`,`uShift`,`uTop`,`uSide`,`uInk`,`uRim`,`uAccent`,`uShade`,`uLight`,`uLayer`,`uAlpha`,`uShadow`].forEach(e=>O[e]=C.getUniformLocation(E,e));let k=[-.5,.82,.3],A=Math.hypot(...k);C.uniform3f(O.uLight,k[0]/A,k[1]/A,k[2]/A),C.enable(C.DEPTH_TEST),C.enable(C.BLEND),C.blendFunc(C.ONE,C.ONE_MINUS_SRC_ALPHA),C.clearColor(0,0,0,0);let j=window.matchMedia(`(prefers-reduced-motion: reduce)`),te=window.matchMedia(`(pointer: fine)`),ne=window.matchMedia(`(prefers-color-scheme: dark)`),M=document.documentElement.dir===`rtl`,re=y(),N=0,P=0,F=!1,I=0,L=!0,R=0,z=0,B=0,V=0,H=!1,U={x:0,y:0,tx:0,ty:0},W=null,G=[];function K(){let e=getComputedStyle(document.documentElement),t=(t,n)=>re(e.getPropertyValue(t).trim(),n),n=t(`--bg`,[.97,.96,.95]),r=t(`--surface`,[1,1,1]),i=t(`--surface-2`,[.94,.93,.9]),a=t(`--border-strong`,[.79,.77,.73]),o=t(`--text`,[.07,.07,.09]),s=t(`--accent`,[.91,.33,.16]);W=.2126*n[0]+.7152*n[1]+.0722*n[2]<.4?{top:u(i,a,.3),side:u(r,i,.6),ink:u(i,o,.3),rim:u(a,o,.35),accent:s,shade:[0,0,0],shadow:.7}:{top:r,side:u(i,a,.3),ink:a,rim:u(a,o,.12),accent:s,shade:o,shadow:.2}}function q(){if(j.matches){R=1;return}if(F){let e=_.getBoundingClientRect();R=s((I-e.top)/Math.max(e.height-b.offsetHeight,1))}else{let e=o.getBoundingClientRect(),t=window.innerHeight;R=s((t*.75-(e.top+e.height/2))/(t*.45))}}function J(){let e=a.getBoundingClientRect(),t=Math.min(window.devicePixelRatio||1,2);N=e.width,P=e.height,a.width=Math.max(1,Math.round(N*t)),a.height=Math.max(1,Math.round(P*t)),C.viewport(0,0,a.width,a.height),F=!!b&&getComputedStyle(b).position===`sticky`,I=F&&parseFloat(getComputedStyle(b).top)||0,S.forEach((e,t)=>G[t]=e.offsetWidth),q(),Q()}function Y(e){if(!N||!P||H)return;let a=!j.matches,o=a?s((e-B)/i):1,u=l(s((z-.04)/.7)),d=(-45+30*u+U.x*10)*r,g=N/P,_=N<440,v=Math.min(1,g)*(_?.8:1),y=(M?1:-1)*(_?.2:.16),b=(30-4*u+U.y*5)*r,S=6.6+1.8*u,w=m(f(30*r,g,.1,50),p([0,Math.sin(b)*S,Math.cos(b)*S])),T=.22+.6*u;C.clear(C.COLOR_BUFFER_BIT|C.DEPTH_BUFFER_BIT),C.uniformMatrix4fv(O.uViewProj,!1,w),C.uniform1f(O.uShift,y),C.uniform3fv(O.uTop,W.top),C.uniform3fv(O.uSide,W.side),C.uniform3fv(O.uInk,W.ink),C.uniform3fv(O.uRim,W.rim),C.uniform3fv(O.uAccent,W.accent),C.uniform3fv(O.uShade,W.shade),C.depthMask(!1),C.uniformMatrix4fv(O.uModel,!1,h(v*1.35*(1+.3*u),(-1.5*T-n/2-.05)*v,d)),C.uniform1f(O.uShadow,W.shadow*(1-.45*u)*c(o)),C.uniform1f(O.uAlpha,1),C.drawArrays(C.TRIANGLES,D.plate,D.shadow),C.depthMask(!0),C.uniform1f(O.uShadow,0);for(let e=0;e<4;e++){let t=a?c(s((o*i-e*140)/800)):1,n=h(v,((e-1.5)*T+(1-t)*1.4)*v,d+(e-1.5)*6*r*u);C.uniformMatrix4fv(O.uModel,!1,n),C.uniform1f(O.uLayer,e),C.uniform1f(O.uAlpha,t),C.drawArrays(C.TRIANGLES,0,D.plate),X(e,n,w,y,t,u)}x&&(x.style.opacity=String(1-s(z*5))),t.classList.contains(`is-3d`)||t.classList.add(`is-3d`)}function X(t,r,i,a,o,c){let l=S[t];if(!l)return;let u=(e,t)=>{let o=g(i,...g(r,e,n/2,t).slice(0,3));return[((o[0]/o[3]+a)*.5+.5)*N,(.5-o[1]/o[3]*.5)*P]},d=M?1/0:-1/0;for(let[t,n]of[[e,e],[-1,e],[-1,-1],[e,-1]]){let e=u(t*.9,n*.9)[0];d=M?Math.min(d,e):Math.max(d,e)}let f=u(0,0)[1],p=s((c-.3-(3-t)*.1)/.18)*o,m=M?Math.max(d-14,G[t]+2):Math.min(d+14,N-G[t]-2);l.style.transform=`translate3d(${m.toFixed(1)}px, ${f.toFixed(1)}px, 0) translate(${M?`-100%`:`0`}, -50%)`,l.style.opacity=p.toFixed(3)}function Z(e,t,n,r){return e[t]+=(n-e[t])*r,Math.abs(n-e[t])<5e-4&&(e[t]=n),e[t]!==n}function ie(e){V=0,B||=e;let t=j.matches?1:.12,n={progress:z},r=[Z(n,`progress`,R,t),Z(U,`x`,U.tx,t),Z(U,`y`,U.ty,t)].some(Boolean);z=n.progress,Y(e);let i=!j.matches&&e-B<1600;L&&(r||i)&&Q()}function Q(){!V&&!H&&(V=requestAnimationFrame(ie))}K(),J(),new ResizeObserver(J).observe(a),new IntersectionObserver(([e])=>{L=e.isIntersecting,L&&Q()}).observe(_??t),_?.addEventListener(`pointermove`,e=>{if(j.matches||!te.matches||e.pointerType!==`mouse`)return;let t=_.getBoundingClientRect();U.tx=s((e.clientX-t.left)/t.width,0,1)*2-1,U.ty=s((e.clientY-t.top)/Math.min(t.height,window.innerHeight),0,1)*2-1,Q()}),_?.addEventListener(`pointerleave`,()=>{U.tx=0,U.ty=0,Q()}),window.addEventListener(`scroll`,()=>{q(),Q()},{passive:!0});let $=()=>{K(),Q()};new MutationObserver($).observe(document.documentElement,{attributes:!0,attributeFilter:[`data-theme`]}),ne.addEventListener(`change`,$),j.addEventListener(`change`,J),a.addEventListener(`webglcontextlost`,e=>{e.preventDefault(),H=!0,t.classList.remove(`is-3d`),t.classList.add(`is-static`),S.forEach(e=>e.removeAttribute(`style`))})}export{b as mount};