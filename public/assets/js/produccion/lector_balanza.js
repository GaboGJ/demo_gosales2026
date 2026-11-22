/* ==================================================================
   lector_balanza.js — Lee el PESO de una balanza Trillium (LCD verde
   de 7 segmentos) a partir de una foto. 100% en el navegador, sin
   librerías ni servidor.

   Uso:   const r = await LectorBalanza.leerPeso(blobOCanvas);
          r = { ok, valor (kg), texto, confianza (0-100), motivo }

   Cómo funciona (en corto):
   1. Busca el visor verde de la balanza en la foto (el de más a la izquierda = PESO).
   2. Quita reflejos: divide cada píxel por la luz de su vecindad.
   3. Endereza la inclinación de los dígitos (letra itálica del LCD).
   4. Separa los dígitos y mira qué segmentos están encendidos (a-g).
   5. Arma el número; el punto decimal se detecta (si no, asume 3 decimales).
   ================================================================== */
(function (root) {
  'use strict';

  const CFG = {
    LADO_MAX: 1600,     // la foto se reduce a este lado máximo
    ALTO_VISOR: 240,    // el visor se normaliza a este alto (px)
    MAX_KG: 41,         // balanza de 40 kg: más que esto es lectura errónea
    DEC_DEFECTO: 3,     // decimales si no se detecta el punto (e=1 g → 3 decimales en kg)
    CONF_MIN: 55,       // bajo este % se considera lectura dudosa
  };

  /* Segmentos: a=arriba, b=arriba-der, c=abajo-der, d=abajo, e=abajo-izq, f=arriba-izq, g=medio */
  const PATRONES = {
    0: 'abcdef', 1: 'bc', 2: 'abdeg', 3: 'abcdg', 4: 'bcfg',
    5: 'acdfg', 6: 'acdefg', 7: 'abc', 8: 'abcdefg', 9: 'abcdfg',
  };
  /* Rectángulos de cada segmento como fracción de la celda del dígito [x0,x1,y0,y1] */
  const RECT = {
    a: [0.22, 0.78, 0.00, 0.15], g: [0.12, 0.88, 0.44, 0.56], d: [0.22, 0.78, 0.85, 1.00],
    f: [0.00, 0.30, 0.10, 0.47], b: [0.70, 1.00, 0.10, 0.47],
    e: [0.00, 0.30, 0.53, 0.90], c: [0.70, 1.00, 0.53, 0.90],
  };

  /* ---------------- utilidades de imagen ---------------- */
  const clamp = (v, a, b) => (v < a ? a : v > b ? b : v);

  function filtroMax(src, w, h, r) {            // máximo local (separable)
    const tmp = new Float32Array(w * h), out = new Float32Array(w * h);
    for (let y = 0; y < h; y++) for (let x = 0; x < w; x++) {
      let m = 0; const a = Math.max(0, x - r), b = Math.min(w - 1, x + r);
      for (let i = a; i <= b; i++) { const v = src[y * w + i]; if (v > m) m = v; }
      tmp[y * w + x] = m;
    }
    for (let y = 0; y < h; y++) for (let x = 0; x < w; x++) {
      let m = 0; const a = Math.max(0, y - r), b = Math.min(h - 1, y + r);
      for (let j = a; j <= b; j++) { const v = tmp[j * w + x]; if (v > m) m = v; }
      out[y * w + x] = m;
    }
    return out;
  }
  function desenfoque(src, w, h, r) {           // promedio local (separable)
    const tmp = new Float32Array(w * h), out = new Float32Array(w * h);
    for (let y = 0; y < h; y++) for (let x = 0; x < w; x++) {
      let s = 0, n = 0; const a = Math.max(0, x - r), b = Math.min(w - 1, x + r);
      for (let i = a; i <= b; i++) { s += src[y * w + i]; n++; }
      tmp[y * w + x] = s / n;
    }
    for (let y = 0; y < h; y++) for (let x = 0; x < w; x++) {
      let s = 0, n = 0; const a = Math.max(0, y - r), b = Math.min(h - 1, y + r);
      for (let j = a; j <= b; j++) { s += tmp[j * w + x]; n++; }
      out[y * w + x] = s / n;
    }
    return out;
  }

  /** Componentes conexas (4 vecinos) de una máscara binaria. */
  function componentes(bin, w, h) {
    const lab = new Int32Array(w * h), comps = [], pila = [];
    let n = 0;
    for (let i = 0; i < w * h; i++) {
      if (!bin[i] || lab[i]) continue;
      n++; lab[i] = n; pila.push(i);
      let x0 = w, y0 = h, x1 = 0, y1 = 0, area = 0;
      while (pila.length) {
        const p = pila.pop(), x = p % w, y = (p / w) | 0;
        area++; if (x < x0) x0 = x; if (x > x1) x1 = x; if (y < y0) y0 = y; if (y > y1) y1 = y;
        if (x > 0 && bin[p - 1] && !lab[p - 1]) { lab[p - 1] = n; pila.push(p - 1); }
        if (x < w - 1 && bin[p + 1] && !lab[p + 1]) { lab[p + 1] = n; pila.push(p + 1); }
        if (y > 0 && bin[p - w] && !lab[p - w]) { lab[p - w] = n; pila.push(p - w); }
        if (y < h - 1 && bin[p + w] && !lab[p + w]) { lab[p + w] = n; pila.push(p + w); }
      }
      comps.push({ id: n, x0, y0, x1, y1, area, w: x1 - x0 + 1, h: y1 - y0 + 1 });
    }
    return { lab, comps };
  }

  /* ---------------- 1) Buscar el visor verde de PESO ---------------- */
  function buscarVisorPeso(rgba, W, H) {
    const B = 4, bw = Math.floor(W / B), bh = Math.floor(H / B);
    const m = new Uint8Array(bw * bh);
    for (let by = 0; by < bh; by++) for (let bx = 0; bx < bw; bx++) {
      let c = 0;
      for (let j = 0; j < B; j++) for (let i = 0; i < B; i++) {
        const p = ((by * B + j) * W + (bx * B + i)) * 4, r = rgba[p], g = rgba[p + 1], b = rgba[p + 2];
        if (g > 120 && g - r > 25 && g - b > 25) c++;
      }
      m[by * bw + bx] = c >= 8 ? 1 : 0;
    }
    const d = new Uint8Array(bw * bh);                       // dilatar 1 bloque: une huecos
    for (let y = 0; y < bh; y++) for (let x = 0; x < bw; x++) {
      if (!m[y * bw + x]) continue;
      for (let j = -1; j <= 1; j++) for (let i = -1; i <= 1; i++) {
        const xx = x + i, yy = y + j; if (xx >= 0 && xx < bw && yy >= 0 && yy < bh) d[yy * bw + xx] = 1;
      }
    }
    const { comps } = componentes(d, bw, bh);
    if (!comps.length) return null;
    const maxA = Math.max(...comps.map((c) => c.area));
    // Visores = bloques grandes y apaisados; el PESO es el de más a la izquierda
    const vis = comps.filter((c) => c.area >= maxA * 0.3 && c.w >= c.h * 1.3 && c.area > 40)
      .sort((a, b) => a.x0 - b.x0);
    if (!vis.length) return null;
    const c = vis[0];
    return { x: (c.x0 + 1) * B, y: (c.y0 + 1) * B, w: (c.w - 2) * B, h: (c.h - 2) * B };
  }

  /* ---------------- 2) Analizar el visor ---------------- */
  function leerVisor(rgba, W, H, box, ang) {
    // Recorte con margen (saca el marco negro del visor)
    const mx = Math.round(box.w * 0.04), my = Math.round(box.h * 0.07);
    const sx = box.x + mx, sy = box.y + my, sw = box.w - 2 * mx, sh = box.h - 2 * my;
    const hs = CFG.ALTO_VISOR, k = hs / sh, ws = Math.round(sw * k);

    // Canal VERDE escalado (el fondo del LCD es verde brillante; los dígitos, oscuros)
    const g = new Float32Array(ws * hs);
    // 'ang' = giro (grados) para corregir fotos tomadas chuecas: se muestrea el visor ya enderezado
    const cx = sx + sw / 2, cy = sy + sh / 2, ca = Math.cos(ang * Math.PI / 180), sa = Math.sin(ang * Math.PI / 180);
    for (let y = 0; y < hs; y++) {
      for (let x = 0; x < ws; x++) {
        const u = (x + 0.5) / k - sw / 2, v = (y + 0.5) / k - sh / 2;
        const fx = clamp(cx + u * ca - v * sa - 0.5, 0, W - 1), fy = clamp(cy + u * sa + v * ca - 0.5, 0, H - 1);
        const x0 = Math.floor(fx), x1 = Math.min(W - 1, x0 + 1), tx = fx - x0;
        const y0 = Math.floor(fy), y1 = Math.min(H - 1, y0 + 1), ty = fy - y0;
        const v00 = rgba[(y0 * W + x0) * 4 + 1], v10 = rgba[(y0 * W + x1) * 4 + 1];
        const v01 = rgba[(y1 * W + x0) * 4 + 1], v11 = rgba[(y1 * W + x1) * 4 + 1];
        g[y * ws + x] = (v00 * (1 - tx) + v10 * tx) * (1 - ty) + (v01 * (1 - tx) + v11 * tx) * ty;
      }
    }

    // Quitar reflejos: oscuridad = 1 - pixel / luz_local  (la luz local = máximo de la vecindad, suavizado)
    let bg = filtroMax(g, ws, hs, Math.round(hs * 0.056));
    bg = desenfoque(desenfoque(bg, ws, hs, Math.round(hs * 0.04)), ws, hs, Math.round(hs * 0.04));
    const osc = new Float32Array(ws * hs), tinta = new Uint8Array(ws * hs);
    for (let i = 0; i < osc.length; i++) { osc[i] = clamp(1 - g[i] / Math.max(bg[i], 1), 0, 1); tinta[i] = osc[i] > 0.12 ? 1 : 0; }

    // Componentes: segmentos (grandes), puntos (chicos) y basura (marco, flechitas)
    const { lab, comps } = componentes(tinta, ws, hs);
    const tocaBorde = (c) => c.x0 === 0 || c.y0 === 0 || c.x1 === ws - 1 || c.y1 === hs - 1;
    const segs = comps.filter((c) => !tocaBorde(c) &&   // lo que toca el borde es marco o flechitas, no dígitos
      (c.h >= hs * 0.18 || c.w >= hs * 0.13) && c.area >= hs * hs * 0.004);
    if (!segs.length) return { ok: false, motivo: 'No se ven dígitos en el visor.' };
    const yTop = Math.min(...segs.map((c) => c.y0)), yBot = Math.max(...segs.map((c) => c.y1)), Hd = yBot - yTop + 1;
    if (Hd < hs * 0.35) return { ok: false, motivo: 'Dígitos demasiado pequeños. Acerca la cámara.' };
    const idSeg = new Set(segs.map((c) => c.id));
    const puntos = comps.filter((c) => !idSeg.has(c.id) && !tocaBorde(c) && c.w >= Hd * 0.04 && c.w <= Hd * 0.14 && c.h >= Hd * 0.04 &&
      c.h <= Hd * 0.14 && c.w / c.h > 0.65 && c.w / c.h < 1.5 && (c.y0 + c.y1) / 2 > yTop + Hd * 0.78);

    // Inclinación (cursiva del LCD): probar ángulos y quedarse con el que hace las columnas más nítidas
    const yMid = (yTop + yBot) / 2, pts = [];
    for (let y = yTop; y <= yBot; y++) for (let x = 0; x < ws; x++) if (tinta[y * ws + x] && idSeg.has(lab[y * ws + x])) pts.push(x, y);
    let mejorS = 0, mejorScore = -1;
    for (let s = -0.1; s <= 0.4001; s += 0.02) {
      const hist = new Int32Array(ws * 2 + 400), off = 200 + ws / 2 | 0;
      for (let i = 0; i < pts.length; i += 2) hist[Math.round(pts[i] + s * (pts[i + 1] - yMid)) + 200]++;
      let sc = 0; for (let i = 0; i < hist.length; i++) sc += hist[i] * hist[i];
      if (sc > mejorScore) { mejorScore = sc; mejorS = s; }
    }
    const s = mejorS, off = Math.ceil(Math.abs(s) * hs / 2) + 2, w2 = ws + 2 * off;
    const D = new Float32Array(w2 * hs), T = new Uint8Array(w2 * hs);   // oscuridad y tinta "enderezadas"
    for (let y = 0; y < hs; y++) for (let x2 = 0; x2 < w2; x2++) {
      const x = Math.round(x2 - off - s * (y - yMid));
      if (x >= 0 && x < ws) {
        D[y * w2 + x2] = osc[y * ws + x];
        if (y >= yTop && y <= yBot && tinta[y * ws + x] && idSeg.has(lab[y * ws + x])) T[y * w2 + x2] = 1;
      }
    }

    // Separar dígitos por columnas con tinta
    const col = new Int32Array(w2);
    for (let y = yTop; y <= yBot; y++) for (let x = 0; x < w2; x++) if (T[y * w2 + x]) col[x]++;
    let cl = [], ini = -1;
    for (let x = 0; x <= w2; x++) {
      if (x < w2 && col[x] > 0) { if (ini < 0) ini = x; } else if (ini >= 0) { cl.push({ x0: ini, x1: x - 1 }); ini = -1; }
    }
    const huecoMin = Hd * 0.06;                                   // unir trozos casi pegados
    cl = cl.reduce((a, c) => { const u = a[a.length - 1]; if (u && c.x0 - u.x1 < huecoMin) u.x1 = c.x1; else a.push({ ...c }); return a; }, []);
    if (!cl.length) return { ok: false, motivo: 'No se pudieron separar los dígitos.' };
    cl.forEach((c) => { c.w = c.x1 - c.x0 + 1; });

    // Rejilla de celdas: ancho de dígito (Wd), paso entre dígitos (P) y borde izquierdo (L0)
    const anchos = cl.filter((c) => c.w >= Hd * 0.4).map((c) => c.w).sort((a, b) => a - b);
    const Wd = anchos.length ? anchos[anchos.length >> 1] : Hd * 0.52;
    const anchas = cl.filter((c) => c.w >= Hd * 0.4);
    let P = Hd * 0.68;
    if (anchas.length >= 2) {
      const pasos = [];
      for (let i = 1; i < anchas.length; i++) {
        const dx = anchas[i].x0 - anchas[i - 1].x0, n = Math.max(1, Math.round(dx / (Hd * 0.68))); pasos.push(dx / n);
      }
      P = pasos.reduce((a, b) => a + b, 0) / pasos.length;
    }
    const ref0 = anchas.length ? anchas[0].x0 : cl[0].x1 - Wd;
    const idx = (c) => (c.w >= Hd * 0.4 ? Math.round((c.x0 - ref0) / P) : Math.round((c.x1 - (ref0 + Wd)) / P));
    const idxs = cl.map(idx);
    let L0 = 0, nL = 0;                                           // refinar borde izquierdo con las celdas anchas
    cl.forEach((c, i) => { if (c.w >= Hd * 0.4) { L0 += c.x0 - idxs[i] * P; nL++; } });
    L0 = nL ? L0 / nL : ref0;
    const i0 = Math.min(...idxs), i1 = Math.max(...idxs);
    if (i1 - i0 > 7) return { ok: false, motivo: 'Lectura confusa: demasiados dígitos.' };

    // Leer cada celda: oscuridad media de cada segmento
    const celdas = [];
    for (let i = i0; i <= i1; i++) {
      const L = L0 + i * P, v = {};
      for (const sg in RECT) {
        const [a, b, c, d] = RECT[sg];
        const xa = Math.round(L + a * Wd), xb = Math.max(xa + 1, Math.round(L + b * Wd));
        const ya = Math.round(yTop + c * Hd), yb = Math.max(ya + 1, Math.round(yTop + d * Hd));
        let sum = 0, n = 0;
        for (let y = ya; y < yb; y++) for (let x = xa; x < xb; x++) if (x >= 0 && x < w2 && y >= 0 && y < hs) { sum += D[y * w2 + x]; n++; }
        v[sg] = n ? sum / n : 0;
      }
      const orden = Object.values(v).sort((p, q) => q - p);
      celdas.push({ i, v, ref: (orden[0] + orden[1]) / 2 });
    }
    const refGlobal = Math.max(...celdas.map((c) => c.ref));
    if (refGlobal < 0.06) return { ok: false, motivo: 'Dígitos muy tenues. Mejora la luz.' };

    let digitos = '', confs = [];
    for (const c of celdas) {
      const ref = Math.max(c.ref, refGlobal * 0.5);
      const f = {}; for (const sg in c.v) f[sg] = Math.min(1, c.v[sg] / ref);
      const err = Object.keys(PATRONES).map((dg) => {
        // Un reflejo ESCONDE segmentos pero casi nunca los inventa: si el patrón pide un segmento y no se ve,
        // cuesta menos que ver un segmento que el patrón no tiene. La barra del medio (g) es la más tenue.
        let e = 0;
        for (const sg in RECT) {
          if (PATRONES[dg].includes(sg)) e += (1 - f[sg]) * (sg === 'g' ? 0.5 : 1);
          else e += f[sg];
        }
        return { dg, e };
      }).sort((p, q) => p.e - q.e);
      digitos += err[0].dg;
      // confianza del dígito: qué tan claro ganó el mejor patrón frente al segundo
      confs.push(clamp((err[1].e - err[0].e) / 0.8, 0, 1) * 0.6 + clamp(1 - err[0].e / 2.0, 0, 1) * 0.4);
    }

    // Punto decimal: un puntito abajo, entre dos celdas
    let dec = CFG.DEC_DEFECTO, puntoVisto = false;
    const cand = puntos.map((p) => {
      const cx = (p.x0 + p.x1) / 2, cy = (p.y0 + p.y1) / 2;
      return { area: p.area, i: Math.floor((cx + s * (cy - yMid) + off - L0) / P) };
    }).filter((p) => p.i >= i0 && p.i < i1).sort((p, q) => q.area - p.area);
    if (cand.length) { dec = i1 - cand[0].i; puntoVisto = true; }
    if (dec >= digitos.length) dec = Math.max(0, digitos.length - 1);

    const texto = dec > 0 ? digitos.slice(0, digitos.length - dec) + '.' + digitos.slice(digitos.length - dec) : digitos;
    const confianza = Math.round(100 * (confs.reduce((a, b) => a + b, 0) / confs.length) * (puntoVisto ? 1 : 0.9));
    return { ok: true, texto, valor: parseFloat(texto), confianza, puntoVisto, digitos: digitos.length };
  }

  /* ---------------- API pública ---------------- */

  /** Núcleo puro (sin DOM): recibe píxeles RGBA. */
  function analizar(rgba, W, H) {
    const box = buscarVisorPeso(rgba, W, H);
    if (!box) return { ok: false, motivo: 'No encontré el visor verde de la balanza. Acércate y encuádralo completo.' };
    // Se lee el visor con varios giros y los intentos VOTAN: una lectura sola puede equivocarse
    // (un reflejo confunde un 9 con un 4), pero varios giros distintos coincidiendo es evidencia fuerte.
    const votos = {};
    let ultimo = null;
    for (const ang of [0, -3, 3, -6, 6, -9, 9]) {
      const t = leerVisor(rgba, W, H, box, ang);
      ultimo = ultimo || t;
      if (!t.ok || t.confianza < 40 || !(t.valor >= 0 && t.valor <= CFG.MAX_KG)) continue;
      const v = votos[t.texto] || (votos[t.texto] = { n: 0, suma: 0, r: t });
      v.n++; v.suma += t.confianza;
      if (v.n >= 3) break;                              // 3 coincidencias: suficiente
    }
    const ganador = Object.values(votos).sort((p, q) => q.n - p.n || q.suma - p.suma)[0];
    if (!ganador) return { ok: false, motivo: (ultimo && ultimo.motivo) || 'No pude leer el peso. Repite la foto con el visor bien encuadrado y sin reflejos.' };
    const factor = ganador.n >= 3 ? 1 : ganador.n === 2 ? 0.9 : 0.7;
    const r = { ...ganador.r, confianza: Math.round((ganador.suma / ganador.n) * factor), votos: ganador.n };

    if (!(r.valor >= 0 && r.valor <= CFG.MAX_KG)) return { ok: false, motivo: 'Lectura fuera de rango (' + r.texto + ').', texto: r.texto, valor: r.valor, confianza: r.confianza };
    if (r.confianza < CFG.CONF_MIN) return { ...r, ok: false, motivo: 'Lectura poco clara (' + r.confianza + '%). Repite la foto sin reflejos.' };
    return r;
  }

  /** Recibe un Blob, canvas o imagen y devuelve la lectura. */
  async function leerPeso(fuente) {
    let bmp = fuente;
    if (typeof Blob !== 'undefined' && fuente instanceof Blob) bmp = await createImageBitmap(fuente, { imageOrientation: 'from-image' });
    const w0 = bmp.width || bmp.naturalWidth, h0 = bmp.height || bmp.naturalHeight;
    const k = Math.min(1, CFG.LADO_MAX / Math.max(w0, h0));
    const c = document.createElement('canvas'); c.width = Math.round(w0 * k); c.height = Math.round(h0 * k);
    const ctx = c.getContext('2d', { willReadFrequently: true }); ctx.drawImage(bmp, 0, 0, c.width, c.height);
    const d = ctx.getImageData(0, 0, c.width, c.height);
    return analizar(d.data, d.width, d.height);
  }

  root.LectorBalanza = { leerPeso, analizar, CFG };
})(typeof window !== 'undefined' ? window : globalThis);