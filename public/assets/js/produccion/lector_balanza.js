/* ==================================================================
   lector_balanza.js — Lee el PESO que muestra el visor de una balanza
   a partir de una foto o de un cuadro de video. 100% en el navegador.

   Uso:   const r = await LectorBalanza.leerPeso(blob | canvas | video, { vivo: false });
          r = { ok, valor (GRAMOS), texto (gramos, ej. "18600"), lectura (lo que mostraba el visor),
                confianza (0-100), puntoVisto, motivo }

   TODO SE DEVUELVE EN GRAMOS:
     - Si el visor muestra el PUNTO decimal (18.600) -> está en kg -> 18600 g.
     - Si NO hay punto (303)                         -> ya son gramos -> 303 g.

   Tipos de visor que detecta automáticamente:
     - LCD verde con dígitos oscuros (Trillium y similares)
     - LED rojo / ámbar con dígitos encendidos (balanzas de mostrador)
   Otros visores: la lectura falla y la pantalla habilita el ingreso manual.

   Velocidad (cámara en vivo):
     - Recuerda dónde estaba el visor y de qué tipo era: el siguiente cuadro
       lo prueba primero y solo busca de nuevo si falla.
     - Lee a menor resolución, con pocos giros, y para en la primera lectura buena.

   Cómo funciona (en corto):
   1. Busca los posibles visores en la foto y los prueba de izquierda a derecha.
   2. Quita reflejos / normaliza la luz.
   3. Endereza la inclinación (letra itálica del LCD y foto chueca).
   4. Separa los dígitos y mira qué segmentos están encendidos (a-g).
   5. Arma el número y detecta el punto decimal.
   ================================================================== */
(function (root) {
  'use strict';

  const CFG = {
    LADO_MAX: 1600,         // foto estática: se reduce a este lado máximo
    LADO_VIVO: 720,         // cuadros de la cámara en vivo (más rápido)
    ALTO_VISOR: 240,        // foto estática: el visor se normaliza a este alto (px)
    ALTO_VISOR_VIVO: 140,   // cámara en vivo: alto más chico = mucho más rápido
    MAX_CELDAS: 8,          // más dígitos que esto = lectura errónea
    CONF_MIN: 55,           // bajo este % se considera lectura dudosa
    ANGULOS: [0, -3, 3, -6, 6, -9, 9, -13, 13, -18, 18, -24, 24],
    ANGULOS_VIVO: [0, -6, 6],
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

  /* Tipos de visor: cómo reconocer su color y cómo leer sus dígitos */
  const MODOS = {
    verde: { prueba: (r, g, b) => g > 110 && g - r > 20 && g - b > 20, dilatar: 1, canal: 1, luz: false },
    led:   { prueba: (r, g, b) => r > 150 && r - g > 60 && r - b > 60, dilatar: 2, canal: 0, luz: true },
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

  /* ---------------- 1) Buscar los posibles visores ---------------- */
  /** Devuelve una lista de candidatos (el visor verde primero, de izquierda a derecha).
      soloModo ('verde' | 'led' | null): si se indica, solo busca ese tipo de visor (más rápido). */
  function buscarVisores(rgba, W, H, soloModo) {
    const B = Math.max(2, Math.round(Math.max(W, H) / 400));
    const bw = Math.floor(W / B), bh = Math.floor(H / B);
    const minPix = Math.ceil(B * B * 0.4);
    const out = [];

    for (const nombre in MODOS) {
      if (soloModo && nombre !== soloModo) continue;
      const M = MODOS[nombre];
      const m = new Uint8Array(bw * bh);
      for (let by = 0; by < bh; by++) for (let bx = 0; bx < bw; bx++) {
        let c = 0;
        for (let j = 0; j < B; j++) for (let i = 0; i < B; i++) {
          const p = ((by * B + j) * W + (bx * B + i)) * 4;
          if (M.prueba(rgba[p], rgba[p + 1], rgba[p + 2])) c++;
        }
        m[by * bw + bx] = c >= minPix ? 1 : 0;
      }
      const R = M.dilatar, d = new Uint8Array(bw * bh);        // dilatar: une huecos entre dígitos
      for (let y = 0; y < bh; y++) for (let x = 0; x < bw; x++) {
        if (!m[y * bw + x]) continue;
        for (let j = -R; j <= R; j++) for (let i = -R; i <= R; i++) {
          const xx = x + i, yy = y + j; if (xx >= 0 && xx < bw && yy >= 0 && yy < bh) d[yy * bw + xx] = 1;
        }
      }
      const { comps } = componentes(d, bw, bh);
      const anchas = comps.filter((c) => c.w - 2 * R >= 3 && c.h - 2 * R >= 2 && c.w >= c.h * 1.1 &&
        c.area > 30 && c.area <= bw * bh * 0.2);
      if (!anchas.length) continue;
      const maxA = Math.max(...anchas.map((c) => c.area));
      anchas.filter((c) => c.area >= maxA * 0.15).sort((a, b) => a.x0 - b.x0).slice(0, nombre === 'verde' ? 4 : 2)
        .forEach((c) => out.push({
          x: (c.x0 + R) * B, y: (c.y0 + R) * B, w: (c.w - 2 * R) * B, h: (c.h - 2 * R) * B,
          modo: nombre, canal: M.canal, luz: M.luz,
        }));
    }
    return out;     // verde primero, luego LED; dentro de cada tipo, de izquierda a derecha
  }

  /* ---------------- 2) Analizar un visor ---------------- */
  function leerVisor(rgba, W, H, box, ang) {
    // Recorte: el LCD pierde un margen (marco negro); el LED gana uno (los dígitos son el recorte)
    const mx = Math.round(box.w * (box.luz ? -0.08 : 0.04)), my = Math.round(box.h * (box.luz ? -0.15 : 0.07));
    const sx = box.x + mx, sy = box.y + my, sw = box.w - 2 * mx, sh = box.h - 2 * my;
    if (sw < 20 || sh < 10) return { ok: false, motivo: 'Visor demasiado pequeño.' };
    const hs = box.alto || CFG.ALTO_VISOR, k = hs / sh, ws = Math.round(sw * k);
    if (ws < 40 || ws > 4000) return { ok: false, motivo: 'Visor con forma extraña.' };

    // Canal de color del visor escalado y girado 'ang' grados (corrige fotos chuecas)
    const g = new Float32Array(ws * hs);
    const cx = sx + sw / 2, cy = sy + sh / 2, ca = Math.cos(ang * Math.PI / 180), sa = Math.sin(ang * Math.PI / 180);
    const ch = box.canal;
    for (let y = 0; y < hs; y++) {
      for (let x = 0; x < ws; x++) {
        const u = (x + 0.5) / k - sw / 2, v = (y + 0.5) / k - sh / 2;
        const fx = clamp(cx + u * ca - v * sa - 0.5, 0, W - 1), fy = clamp(cy + u * sa + v * ca - 0.5, 0, H - 1);
        const x0 = Math.floor(fx), x1 = Math.min(W - 1, x0 + 1), tx = fx - x0;
        const y0 = Math.floor(fy), y1 = Math.min(H - 1, y0 + 1), ty = fy - y0;
        const v00 = rgba[(y0 * W + x0) * 4 + ch], v10 = rgba[(y0 * W + x1) * 4 + ch];
        const v01 = rgba[(y1 * W + x0) * 4 + ch], v11 = rgba[(y1 * W + x1) * 4 + ch];
        g[y * ws + x] = (v00 * (1 - tx) + v10 * tx) * (1 - ty) + (v01 * (1 - tx) + v11 * tx) * ty;
      }
    }

    const osc = new Float32Array(ws * hs), tinta = new Uint8Array(ws * hs);
    if (box.luz) {
      // LED: dígitos ENCENDIDOS sobre fondo oscuro. Se normaliza entre el fondo y los segmentos más brillantes.
      const muestra = []; for (let i = 0; i < g.length; i += 5) muestra.push(g[i]);
      muestra.sort((a, b) => a - b);
      const lo = muestra[Math.floor(muestra.length * 0.1)], hi = muestra[Math.floor(muestra.length * 0.995)];
      const rango = Math.max(hi - lo, 30);
      for (let i = 0; i < osc.length; i++) { osc[i] = clamp((g[i] - lo) / rango, 0, 1); tinta[i] = osc[i] > 0.5 ? 1 : 0; }
    } else {
      // LCD: dígitos OSCUROS sobre fondo brillante. Quita reflejos: oscuridad = 1 - pixel / luz_local
      let bg = filtroMax(g, ws, hs, Math.round(hs * 0.056));
      bg = desenfoque(desenfoque(bg, ws, hs, Math.round(hs * 0.04)), ws, hs, Math.round(hs * 0.04));
      for (let i = 0; i < osc.length; i++) { osc[i] = clamp(1 - g[i] / Math.max(bg[i], 1), 0, 1); tinta[i] = osc[i] > 0.12 ? 1 : 0; }
    }

    // Componentes: segmentos (grandes), puntos (chicos) y basura (marco, flechitas)
    const { lab, comps } = componentes(tinta, ws, hs);
    const tocaBorde = (c) => c.x0 === 0 || c.y0 === 0 || c.x1 === ws - 1 || c.y1 === hs - 1;
    const segs = comps.filter((c) => !tocaBorde(c) &&
      (c.h >= hs * 0.18 || c.w >= hs * 0.13) && c.area >= hs * hs * 0.004);
    if (!segs.length) return { ok: false, motivo: 'No se ven dígitos en el visor.' };
    const yTop = Math.min(...segs.map((c) => c.y0)), yBot = Math.max(...segs.map((c) => c.y1)), Hd = yBot - yTop + 1;
    if (Hd < hs * 0.35) return { ok: false, motivo: 'Dígitos demasiado pequeños. Acerque la cámara.' };
    const idSeg = new Set(segs.map((c) => c.id));
    const puntos = comps.filter((c) => !idSeg.has(c.id) && !tocaBorde(c) && c.w >= Hd * 0.04 && c.w <= Hd * 0.14 && c.h >= Hd * 0.04 &&
      c.h <= Hd * 0.14 && c.w / c.h > 0.65 && c.w / c.h < 1.5 && (c.y0 + c.y1) / 2 > yTop + Hd * 0.78);

    // Inclinación (cursiva del LCD): probar valores y quedarse con el que hace las columnas más nítidas
    const yMid = (yTop + yBot) / 2, pts = [];
    for (let y = yTop; y <= yBot; y++) for (let x = 0; x < ws; x++) if (tinta[y * ws + x] && idSeg.has(lab[y * ws + x])) pts.push(x, y);
    let mejorS = 0, mejorScore = -1;
    for (let s = -0.1; s <= 0.4001; s += 0.02) {
      const hist = new Int32Array(ws * 2 + 400);
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
    if (i1 - i0 + 1 > CFG.MAX_CELDAS) return { ok: false, motivo: 'Lectura confusa: demasiados dígitos.' };

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
    if (refGlobal < 0.06) return { ok: false, motivo: 'Dígitos muy tenues. Mejore la luz.' };

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
      confs.push(clamp((err[1].e - err[0].e) / 0.8, 0, 1) * 0.6 + clamp(1 - err[0].e / 2.0, 0, 1) * 0.4);
    }

    // Punto decimal: un puntito abajo, entre dos celdas. Si no se ve, el número es ENTERO (ya son gramos).
    let dec = 0, puntoVisto = false;
    const cand = puntos.map((p) => {
      const cx2 = (p.x0 + p.x1) / 2, cy2 = (p.y0 + p.y1) / 2;
      return { area: p.area, i: Math.floor((cx2 + s * (cy2 - yMid) + off - L0) / P) };
    }).filter((p) => p.i >= i0 && p.i < i1).sort((p, q) => q.area - p.area);
    if (cand.length) { dec = i1 - cand[0].i; puntoVisto = true; }
    if (dec >= digitos.length) dec = Math.max(0, digitos.length - 1);

    let texto = dec > 0 ? digitos.slice(0, digitos.length - dec) + '.' + digitos.slice(digitos.length - dec) : digitos;
    texto = texto.replace(/^0+(?=\d)/, '');                        // "0303" -> "303", "00.350" -> "0.350"
    const confianza = Math.round(100 * (confs.reduce((a, b) => a + b, 0) / confs.length) * (puntoVisto ? 1 : 0.9));
    return { ok: true, texto, valor: parseFloat(texto), confianza, puntoVisto, digitos: digitos.length };
  }

  /* ---------------- 3) Votación, memoria del visor y conversión a gramos ---------------- */
  let ultimoBox = null, ultimoModo = null;   // visor recordado (solo cámara en vivo)

  /** Convierte la lectura a GRAMOS: con punto visible el visor está en kg, sin punto ya son gramos. */
  function aGramos(r) {
    const g = r.puntoVisto ? Math.round(r.valor * 1000) : Math.round(r.valor);
    return { ...r, lectura: r.texto, valor: g, gramos: g, texto: String(g) };
  }

  /** Lee un visor con varios giros. En vivo se detiene en la primera lectura buena. */
  function votar(rgba, W, H, box, angulos, necesarios, vivo) {
    const votos = {};
    let motivo = null;
    for (const ang of angulos) {
      const t = leerVisor(rgba, W, H, box, ang);
      if (!t.ok) { motivo = motivo || t.motivo; continue; }
      if (t.confianza < 40 || !isFinite(t.valor) || t.valor < 0) continue;
      const v = votos[t.texto] || (votos[t.texto] = { n: 0, suma: 0, r: t });
      v.n++; v.suma += t.confianza;
      if (v.n >= necesarios || (vivo && t.confianza >= CFG.CONF_MIN)) break;
    }
    const gan = Object.values(votos).sort((p, q) => q.n - p.n || q.suma - p.suma)[0];
    if (!gan) return { r: null, motivo };
    const factor = vivo ? 1 : (gan.n >= 3 ? 1 : gan.n === 2 ? 0.9 : 0.7);
    return { r: { ...gan.r, confianza: Math.round((gan.suma / gan.n) * factor), votos: gan.n, modo: box.modo }, motivo };
  }

  function reiniciar() { ultimoBox = null; ultimoModo = null; }

  /* ---------------- API pública ---------------- */

  /** Núcleo puro (sin DOM): recibe píxeles RGBA. opts.vivo = true usa la ruta rápida. Devuelve GRAMOS. */
  function analizar(rgba, W, H, opts) {
    opts = opts || {};
    const vivo = !!opts.vivo;
    const angulos = vivo ? CFG.ANGULOS_VIVO : CFG.ANGULOS;
    const necesarios = vivo ? 1 : 3;
    const alto = vivo ? CFG.ALTO_VISOR_VIVO : CFG.ALTO_VISOR;

    // 1) En vivo: probar primero el visor del cuadro anterior (evita buscarlo de nuevo)
    if (vivo && ultimoBox && ultimoBox.W === W && ultimoBox.H === H) {
      const { r } = votar(rgba, W, H, ultimoBox.box, angulos, necesarios, true);
      if (r && r.confianza >= CFG.CONF_MIN) return aGramos(r);
      ultimoBox = null;
    }

    // 2) Buscar visores (en vivo, primero solo del tipo que funcionó la última vez)
    let cands = buscarVisores(rgba, W, H, vivo ? ultimoModo : null);
    if (!cands.length && vivo && ultimoModo) cands = buscarVisores(rgba, W, H, null);
    if (!cands.length) return { ok: false, sinVisor: true, motivo: 'No encontré el visor de la balanza.' };

    let primerMotivo = null, dudoso = null;
    for (const c of cands) {
      const box = { ...c, alto };
      const { r, motivo } = votar(rgba, W, H, box, angulos, necesarios, vivo);
      primerMotivo = primerMotivo || motivo;
      if (!r) continue;
      if (r.confianza >= CFG.CONF_MIN) {
        if (vivo) { ultimoBox = { box, W, H }; ultimoModo = box.modo; }
        return aGramos(r);
      }
      dudoso = dudoso || r;
    }
    if (dudoso) return { ...aGramos(dudoso), ok: false, motivo: 'Lectura poco clara (' + dudoso.confianza + '%).' };
    return { ok: false, motivo: primerMotivo || 'No pude leer el peso.' };
  }

  /** Recibe un Blob, canvas, imagen o <video> y devuelve la lectura en gramos. */
  async function leerPeso(fuente, opts) {
    opts = opts || {};
    let bmp = fuente, propio = false;
    if (typeof Blob !== 'undefined' && fuente instanceof Blob) {
      bmp = await createImageBitmap(fuente, { imageOrientation: 'from-image' });
      propio = true;
    }
    const w0 = bmp.videoWidth || bmp.width || bmp.naturalWidth, h0 = bmp.videoHeight || bmp.height || bmp.naturalHeight;
    if (!w0 || !h0) return { ok: false, motivo: 'Imagen vacía.' };
    const lado = opts.vivo ? CFG.LADO_VIVO : CFG.LADO_MAX;
    const k = Math.min(1, lado / Math.max(w0, h0));
    const c = document.createElement('canvas'); c.width = Math.round(w0 * k); c.height = Math.round(h0 * k);
    const ctx = c.getContext('2d', { willReadFrequently: true }); ctx.drawImage(bmp, 0, 0, c.width, c.height);
    if (propio && bmp.close) bmp.close();
    const d = ctx.getImageData(0, 0, c.width, c.height);
    return analizar(d.data, d.width, d.height, opts);
  }

  root.LectorBalanza = { leerPeso, analizar, reiniciar, CFG };
})(typeof window !== 'undefined' ? window : globalThis);