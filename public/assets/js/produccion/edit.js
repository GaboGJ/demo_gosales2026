/* ==================================================================
   edit.js — Registro de producción (vista edit.php)

   Requiere: Bootstrap 5 (modal) y lector_balanza.js.

   La tabla la dibuja y pagina el DataTable del layout, que puede volver a crear las filas al
   buscar o cambiar de página. Por eso los datos viven en memoria (el "modelo") y las filas solo
   los muestran: nada se guarda en el DOM de la tabla y, al enviar, los campos se arman desde el modelo.

   BALANZA: todo se maneja en GRAMOS (peso, taper y neto). Sin selector de unidad.
   Si el producto se mide en kg (ej. Harina), el neto en gramos se convierte solo a kg al sumarlo.

   Configuración (la define edit.php):
     window.PRODUCCION_EDIT = { demo, base, idProduccion, fotoObligatoria, items: [...] }

   Índice
     1. Configuración y modelo
     2. Utilidades y unidades
     3. Vista de cada fila
     4. Cálculo, progreso y entrada
     5. Fotos de balanza (lista y suma)
     6. Cámara: captura AUTOMÁTICA y lectura de la balanza (en gramos)
     7. Modal de fotos sumadas
     8. Eventos del formulario
     9. Guardado y envío
   ================================================================== */
(function () {
  'use strict';

  /* ---------- 1. Configuración y modelo ---------- */
  const CFG = Object.assign({ demo: true, base: '', idProduccion: 0, fotoObligatoria: true, items: [] }, window.PRODUCCION_EDIT);

  const form         = document.getElementById('form-edit');
  if (!form) return;
  const bloqueMotivo = document.getElementById('bloque-motivo');
  const motivo       = document.getElementById('motivo_ajuste');
  const camposEnvio  = document.getElementById('campos-envio');

  const texto = (v) => (v === null || v === undefined ? '' : String(v));

  // Un registro por producto. Los campos numéricos se guardan como texto, tal cual los escribe la persona.
  // fotos[tipo] = [{ archivo, url, texto (etiqueta "303 g"), valor (en la unidad del producto),
  //                  neto (g), unidad ('g'), taper (g) }]
  const modelo = {};
  const lista = CFG.items.map((it, idx) => {
    const m = {
      id: String(it.id), idx, nombre: it.nombre, medida: it.medida, producido: Number(it.producido) || 0,
      entrada: texto(it.entrada), entradaOriginal: texto(it.entrada), entradaAbierta: false,
      agregar: '',
      existencia: texto(it.existencia), existenciaOriginal: texto(it.existencia),
      fotos: { produccion: [], existencia: [] }
    };
    modelo[m.id] = m;
    return m;
  });

  /* ---------- 2. Utilidades y unidades ---------- */
  const num   = (v) => parseFloat(texto(v).replace(',', '.')) || 0;
  const fmt   = (n) => Number(n).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const campoDe    = (tipo) => (tipo === 'produccion' ? 'agregar' : 'existencia');
  const nombreTipo = (tipo) => (tipo === 'produccion' ? 'Producción' : 'Existencia');
  const filaVisible = (id) => form.querySelector('tr[data-id="' + id + '"]');   // null si la fila no está en la página actual

  // Zonas de pantalla donde se pinta un producto: su fila y, si el DataTable la tiene desplegada, su fila hija.
  function ambitos(id) {
    const tr = filaVisible(id);
    if (!tr) return [];
    const sig = tr.nextElementSibling;
    return sig && sig.classList.contains('child') ? [tr, sig] : [tr];
  }

  // La balanza siempre se lee en GRAMOS; aquí solo se convierte a la unidad del producto (g o kg).
  const UNIDADES = { g: 1, kg: 1000, lb: 453.59237, oz: 28.349523 };
  const redondear = (v, medida) => { const k = medida === 'kg' ? 1000 : 100; return Math.round(v * k) / k; };

  /** Convierte un peso (en la unidad indicada) a la unidad del producto. */
  function aUnidadProducto(valor, unidadBal, medida) {
    const gramos = valor * (UNIDADES[unidadBal] || 1);
    if (medida === 'g')  return Math.round(gramos);
    if (UNIDADES[medida]) return Math.round(gramos / UNIDADES[medida] * 1000) / 1000;
    return valor;                                   // unidad desconocida: se toma el número tal cual
  }

  function aviso(txt, tipo) {
    const a = document.getElementById('aviso-demo');
    a.className = 'alert alert-' + (tipo || 'success') + ' text-white';
    a.querySelector('span').textContent = txt;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  /* ---------- 3. Vista de cada fila (el modelo se pinta en la fila; nunca al revés) ---------- */
  function salidaDe(m) {
    return m.existencia === '' ? null : num(m.entrada) + m.producido + num(m.agregar) - num(m.existencia);
  }

  function pintarCalculo(zona, m) {
    const salida  = zona.querySelector('.out-salida');
    const avisoEl = zona.querySelector('.out-aviso');
    if (!salida || !avisoEl) return;
    const calc = salidaDe(m);
    const negativo = calc !== null && calc < 0;
    salida.textContent = calc === null ? '—' : calc.toLocaleString('es-BO', { maximumFractionDigits: 2 });
    salida.classList.toggle('text-danger', negativo);
    salida.classList.toggle('text-primary', !negativo);
    avisoEl.classList.toggle('d-none', !negativo);
    zona.classList.toggle('fila-ajustada', num(m.entrada) !== num(m.entradaOriginal));   // entrada modificada
  }

  /** Fotos sumadas: miniaturas juntas (las últimas 3, "+N" si hay más) y el peso de la última foto. Al tocar se abre el detalle. */
  function pintarFotos(zona, m, tipo) {
    const cont = zona.querySelector('[data-fotos="' + tipo + '"]');
    if (!cont) return;
    cont.textContent = '';
    const fotos = m.fotos[tipo];
    if (!fotos.length) return;

    const MAX = 3;
    const ultima = fotos[fotos.length - 1];
    const ocultas = fotos.length - MAX;

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'foto-strip';
    btn.dataset.verFotos = '1'; btn.dataset.id = m.id; btn.dataset.tipo = tipo;
    btn.title = fotos.length + (fotos.length === 1 ? ' foto: ' : ' fotos: ') + fotos.map((f) => f.texto).join(' + ') + ' (tocar para ver)';

    const grupo = document.createElement('span');
    grupo.className = 'foto-grupo';
    if (ocultas > 0) {
      const mas = document.createElement('span');
      mas.className = 'foto-mas';
      mas.textContent = '+' + ocultas;
      grupo.appendChild(mas);
    }
    fotos.slice(-MAX).forEach((f) => {
      const img = document.createElement('img');
      img.src = f.url; img.alt = 'Foto de la balanza'; img.className = 'foto-av';
      grupo.appendChild(img);
    });

    const peso = document.createElement('span');
    peso.className = 'foto-peso';
    peso.textContent = ultima.texto;

    btn.append(grupo, peso);
    cont.appendChild(btn);
  }

  function pintarFila(zona, m) {
    if (!m) return;
    const poner = (sel, v) => { const i = zona.querySelector(sel); if (i && i.value !== v) i.value = v; };
    poner('.inp-entrada', m.entrada);
    poner('.inp-agregar', m.agregar);
    poner('.inp-existencia', m.existencia);

    const candado = zona.querySelector('.btn-lock');
    const inpEntrada = zona.querySelector('.inp-entrada');
    if (inpEntrada) inpEntrada.readOnly = !m.entradaAbierta;
    if (candado) {
      candado.querySelector('i').textContent = m.entradaAbierta ? 'lock_open' : 'lock';
      candado.classList.toggle('abierto', m.entradaAbierta);
      if (!candado.disabled) candado.title = m.entradaAbierta ? 'Bloquear entrada' : 'Desbloquear entrada (pide motivo)';
    }

    const acum = zona.querySelector('.txt-producido');
    if (acum) acum.textContent = fmt(m.producido);
    pintarCalculo(zona, m);
    pintarFotos(zona, m, 'produccion');
    pintarFotos(zona, m, 'existencia');
  }

  /** Actualiza progreso y avisos, y repinta el producto si está en pantalla. */
  function refrescar(m) {
    ambitos(m.id).forEach((z) => pintarFila(z, m));
    progreso(); revisarAjustes();
  }

  const repintarVisibles = () => form.querySelectorAll('tr[data-id]').forEach((tr) => {
    ambitos(tr.dataset.id).forEach((z) => pintarFila(z, modelo[tr.dataset.id]));
  });

  // Si el DataTable vuelve a crear las filas (buscar, paginar, ordenar, desplegar con el "+"), se vuelven a pintar desde el modelo.
  let repintando = false;
  new MutationObserver((cambios) => {
    const hayFilas = cambios.some((c) => Array.prototype.some.call(c.addedNodes, (n) =>
      n.nodeType === 1 && (n.matches('tr[data-id], tr.child') || n.querySelector('tr[data-id], tr.child'))));
    if (!hayFilas || repintando) return;
    repintando = true;
    requestAnimationFrame(() => { repintando = false; repintarVisibles(); });
  }).observe(form, { childList: true, subtree: true });

  /* ---------- 4. Cálculo, progreso y entrada ---------- */
  function progreso() {
    const contados = lista.filter((m) => m.existencia !== '').length;
    document.getElementById('lbl-progreso').textContent = contados + ' / ' + lista.length;
    document.getElementById('barra-progreso').style.width = (lista.length ? contados / lista.length * 100 : 0) + '%';
    return contados;
  }

  function revisarAjustes() {
    const hay = lista.some((m) => num(m.entrada) !== num(m.entradaOriginal));
    bloqueMotivo.classList.toggle('d-none', !hay);
    motivo.required = hay;
  }

  function alternarEntrada(m) {
    if (!m.entradaAbierta) {
      m.entradaAbierta = true;
      refrescar(m);
      const zonas = ambitos(m.id);
      const inp = zonas.length ? zonas[zonas.length - 1].querySelector('.inp-entrada') : null;
      if (inp) { inp.focus(); inp.select(); }
    } else {
      m.entrada = m.entradaOriginal;
      m.entradaAbierta = false;
      refrescar(m);
    }
  }

  /* ---------- 5. Fotos de balanza (lista y suma) ----------
     Cada foto leída suma su peso al campo y queda en la lista del producto (se ve y se quita desde el modal de fotos).
     Producción: suma sobre lo escrito. Existencia: la primera foto reemplaza el conteo anterior y las siguientes se suman. */
  function sumarFoto(m, tipo, foto) {
    const campo = campoDe(tipo);
    const base = (tipo === 'existencia' && !m.fotos[tipo].length) ? 0 : num(m[campo]);
    m.fotos[tipo].push(foto);
    m[campo] = String(redondear(base + foto.valor, m.medida));
    refrescar(m);
  }

  function quitarFoto(m, tipo, i) {
    const campo = campoDe(tipo);
    const [foto] = m.fotos[tipo].splice(i, 1);
    if (!foto) return;
    const resto = redondear(num(m[campo]) - foto.valor, m.medida);
    if (m.fotos[tipo].length)       m[campo] = String(Math.max(0, resto));
    else if (tipo === 'existencia') m[campo] = m.existenciaOriginal;
    else                            m[campo] = resto > 0 ? String(resto) : '';
    refrescar(m);
  }

  /* ---------- 6. Cámara: captura AUTOMÁTICA y lectura de la balanza (GRAMOS) ----------
     Al abrir, la cámara queda en vivo y el sistema busca el visor de la balanza cuadro a cuadro.
     Cuando lee el MISMO número varias veces seguidas (peso estable y mayor que 0) toma la foto solo.
     No hay botón "Capturar". Alternativas: "Galería / archivo", o escribir el peso a mano (eso congela
     el cuadro actual como foto de respaldo). Los campos de peso y taper (en gramos) están SIEMPRE visibles. */
  const el = {
    modal:    document.getElementById('modalCamara'),
    sub:      document.getElementById('camSubtitulo'),
    video:    document.getElementById('camVideo'),
    preview:  document.getElementById('camPreview'),
    canvas:   document.getElementById('camCanvas'),
    archivo:  document.getElementById('camArchivo'),
    error:    document.getElementById('camError'),
    lectura:  document.getElementById('camLectura'),
    peso:     document.getElementById('camPeso'),
    taper:    document.getElementById('camTaper'),
    neto:     document.getElementById('camNeto'),
    subir:    document.getElementById('camSubir'),
    repetir:  document.getElementById('camRepetir'),
    otra:     document.getElementById('camOtra'),
    usar:     document.getElementById('camUsar')
  };
  // Si el modal de edit.php no es el nuevo, faltarán IDs: se avisa en vez de romper todo el script
  const faltantes = Object.keys(el).filter((k) => !el[k]).map((k) => k);
  if (faltantes.length) console.error('[produccion/edit.js] Faltan elementos en #modalCamara (use el edit.php actualizado):', faltantes);
  const ESTABLE_N = 2;          // lecturas iguales seguidas para tomar la foto
  const TICK_MS   = 250;        // cada cuánto se analiza un cuadro de la cámara

  let camCtx = null;      // { m, tipo }
  let stream = null;      // cámara en vivo
  let blobFoto = null;    // foto tomada
  let modo = 'vivo';      // 'vivo' | 'previa'
  let lecturaId = 0;      // identifica el modo/lectura en curso (descarta resultados viejos)
  let leyendo = false;    // leyendo una foto ya tomada
  let autoTimer = null, autoBusy = false, estable = { texto: null, n: 0 };

  function detenerCamara() {
    detenerAuto();
    if (stream) { stream.getTracks().forEach((t) => t.stop()); stream = null; }
  }
  function detenerAuto() { if (autoTimer) { clearInterval(autoTimer); autoTimer = null; } autoBusy = false; }

  function mostrarError(txt) {
    el.error.textContent = txt || '';
    el.error.classList.toggle('d-none', !txt);
  }
/** Estado bajo el video (una línea con icono): tipo = 'info' | 'ok' | 'aviso' */
function mensaje(txt, tipo) {
  const t = { info: ['text-secondary', 'info'], ok: ['text-success', 'check_circle'], aviso: ['text-warning', 'warning'] }[tipo || 'info'];
  el.lectura.className = 'd-flex align-items-start gap-1 text-xs font-weight-bold mt-2 ' + t[0];
  el.lectura.innerHTML = '<i class="material-symbols-rounded text-sm">' + t[1] + '</i><span></span>';
  el.lectura.lastChild.textContent = txt || '';
  el.lectura.classList.toggle('d-none', !txt);
}

  function subtitulo() {
    const n = camCtx.m.fotos[camCtx.tipo].length;
    el.sub.textContent = camCtx.m.nombre + ' · ' + nombreTipo(camCtx.tipo) +
      (n ? ' · ' + n + (n === 1 ? ' foto sumada' : ' fotos sumadas') : '');
  }

  /** Todo en GRAMOS: peso, taper y neto son números enteros. */
  function calcularNeto() {
    const peso = Math.round(num(el.peso.value)), taper = Math.round(num(el.taper.value));
    return { peso, taper, neto: peso - taper };
  }

  /** Actualiza el resumen "Neto" y habilita "Sumar" solo si hay foto y un peso neto válido. */
  function actualizarSumar() {
    const { peso, taper, neto } = calcularNeto();
    let ok = false, txt = '';
    if (el.peso.value.trim() === '' || peso <= 0) {
        txt = 'Escriba el peso en gramos de la balanza.';
    } else if (neto <= 0) {
        txt = 'El taper (' + taper + ' g) debe ser menor al peso.';
    } else {
        txt = 'Neto: ' + neto.toLocaleString('es-BO') + ' g' + (taper > 0 ? ' (' + peso + ' − ' + taper + ')' : '') +
        (camCtx && camCtx.m.medida === 'kg' ? ' → ' + aUnidadProducto(neto, 'g', 'kg').toLocaleString('es-BO', { maximumFractionDigits: 3 }) + ' kg' : '');
        ok = true;
    }
    el.neto.textContent = txt;
    el.neto.classList.toggle('text-danger', !ok && neto <= 0 && peso > 0);
    const puedo = ok && !!blobFoto && !leyendo;
    el.otra.disabled = !puedo; el.usar.disabled = !puedo;
    return ok;
  }

  /** Sin cámara en vivo (p. ej. HTTP en el teléfono): se destaca "Galería / archivo". */
  function soloArchivo(on) {
    el.subir.classList.toggle('btn-outline-secondary', !on);
    el.subir.classList.toggle('bg-gradient-primary', on);
    el.subir.classList.toggle('text-white', on);
  }

  function modoVivo() {
    blobFoto = null; modo = 'vivo'; leyendo = false;
    lecturaId++;
    estable = { texto: null, n: 0 };
    el.video.classList.remove('d-none');
    el.preview.classList.add('d-none');
    el.repetir.classList.add('d-none');
    el.otra.classList.add('d-none'); el.usar.classList.add('d-none');
    el.peso.value = '';
    actualizarSumar();
  }

  function modoPrevia(blob, lectura, manual) {
    blobFoto = blob; modo = 'previa';
    lecturaId++;
    detenerAuto();
    el.preview.src = window.URL.createObjectURL(blob);
    el.video.classList.add('d-none');
    el.preview.classList.remove('d-none');
    el.repetir.classList.remove('d-none');
    el.otra.classList.remove('d-none'); el.usar.classList.remove('d-none');
    if (lectura) aplicarLectura(lectura);
    else if (manual) mensaje('Foto tomada. Verifique que el peso escrito', 'info');
    else leerPesoDeFoto(blob);
    actualizarSumar();
  }

  function aplicarLectura(r, dudosa) {
    el.peso.value = r.texto;     // r.texto ya viene en gramos
    if (dudosa) mensaje('Lectura dudosa: ' + r.texto + ' g (' + r.confianza + '%). Verifique y corrija.', 'aviso');
    else mensaje('Peso leído: ' + r.texto + ' g · ' + r.confianza + '%.', 'ok');
  }

  /** Lee el peso de una foto ya tomada. Si falla, los campos siguen habilitados para escribirlo a mano. */
  function leerPesoDeFoto(blob) {
    const id = ++lecturaId;
    leyendo = true;
    mensaje('Leyendo balanza...', 'info');
    actualizarSumar();

    const fin = () => { if (id === lecturaId) { leyendo = false; actualizarSumar(); } };
    LectorBalanza.leerPeso(blob).then((r) => {
      if (id !== lecturaId || !camCtx) return;
      if (r.ok) aplicarLectura(r);
      else if (r.texto) aplicarLectura(r, true);
      else mensaje('No pude leer. Escriba el peso que ve en la foto.', 'aviso');
      fin();
    }).catch(() => {
      if (id !== lecturaId) return;
      mensaje('No pude leer. Escriba el peso que ve en la foto..', 'aviso');
      fin();
    });
  }

  /* --- Captura automática --- */
  function dibujar(fuente, w, h) {
    const max = 1280, k = Math.min(1, max / Math.max(w, h));
    el.canvas.width = Math.round(w * k);
    el.canvas.height = Math.round(h * k);
    el.canvas.getContext('2d').drawImage(fuente, 0, 0, el.canvas.width, el.canvas.height);
  }
  const canvasABlob = () => new Promise((res) => el.canvas.toBlob(res, 'image/jpeg', 0.8));

  function iniciarAuto() {
    detenerAuto();
    if (window.LectorBalanza) LectorBalanza.reiniciar();   // olvida el visor anterior
    estable = { texto: null, n: 0 };
    autoTimer = setInterval(tickAuto, TICK_MS);
  }

  async function tickAuto() {
    if (autoBusy || modo !== 'vivo' || !stream || !el.video.videoWidth) return;
    autoBusy = true;
    const id = lecturaId;
    try {
      const r = await LectorBalanza.leerPeso(el.video, { vivo: true });
      if (id !== lecturaId || modo !== 'vivo' || !stream) return;     // cambió el modo mientras se leía
      if (r.ok && r.confianza >= 60 && r.valor > 0) {
        if (r.texto === estable.texto) estable.n++; else { estable = { texto: r.texto, n: 1 }; }
        if (estable.n >= ESTABLE_N) {
          dibujar(el.video, el.video.videoWidth, el.video.videoHeight);
          const blob = await canvasABlob();
          if (id !== lecturaId || modo !== 'vivo') return;
          detenerCamara();
          modoPrevia(blob, r);
        } else {
          mensaje('Detectado ' + r.texto + ' g . mantenga quieta (' + estable.n + '/' + ESTABLE_N + ')', 'info');
        }
      } else {
        estable = { texto: null, n: 0 };
        mensaje(r.sinVisor ? 'Buscando el visor... enfóquelo de frente y con buena luz.'
          : 'Visor detectado, esperando un peso estable...', 'info');
      }
    } catch (e) { /* un cuadro fallido no detiene la búsqueda */ }
    finally { autoBusy = false; }
  }

  async function iniciarVideo() {
    modoVivo();
    mostrarError('');
    soloArchivo(false);
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      soloArchivo(true);
      mensaje('', 'info');
      mostrarError('La cámara no está disponible. Use "Galería / archivo" para elegir una ya tomada.');
      return;
    }
    try {
      stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false });
      el.video.srcObject = stream;
      mensaje('Enfoque el visor: la foto se toma sola al leer un peso estable.', 'info');
      iniciarAuto();
    } catch (e) {
      soloArchivo(true);
      mensaje('', 'info');
      mostrarError('No se pudo abrir la cámara. Use "Galería / archivo".');
    }
  }

  function abrirCamara(m, tipo) {
    if (faltantes.length) { alert('El modal de la cámara en edit.php está desactualizado. Falta: ' + faltantes.join(', ') + '. Use el edit.php actualizado.'); return; }
    if (!window.bootstrap) { alert('Bootstrap no está cargado.'); return; }
    if (!window.LectorBalanza) { alert('No se cargó lector_balanza.js (revise la ruta en edit.php).'); return; }
    camCtx = { m, tipo };
    subtitulo();
    el.taper.value = '0';
    bootstrap.Modal.getOrCreateInstance(el.modal).show();
    iniciarVideo();
  }

  /** DEMO: la foto queda solo en el navegador. BACKEND: sube el blob a /produccion/foto y devuelve { archivo, url }. */
  async function guardarFoto(blob, m, tipo, dat) {
    if (CFG.demo) return { archivo: 'foto-demo-' + Date.now() + '.jpg', url: window.URL.createObjectURL(blob) };
    const fd = new FormData();
    fd.append('foto', blob, 'foto.jpg');
    fd.append('id_produccion', CFG.idProduccion);
    fd.append('id_producto', m.id);
    fd.append('tipo', tipo);
    fd.append('peso', dat.peso);        // gramos
    fd.append('taper', dat.taper);      // gramos
    fd.append('unidad', dat.unidad);    // siempre 'g'
    fd.append('neto', dat.neto);        // gramos
    const r = await fetch(CFG.base + '/produccion/foto', { method: 'POST', body: fd, credentials: 'same-origin' });
    const j = await r.json();
    if (!j.ok) throw new Error(j.msg || 'No se pudo subir la foto.');
    return j;
  }

  /** Suma el peso neto al campo. Con seguir = true deja el modal abierto para otra foto (otro recipiente). */
  async function confirmarFoto(seguir) {
    if (!blobFoto || !camCtx || !actualizarSumar()) return;
    const { m, tipo } = camCtx;
    const { peso, taper, neto } = calcularNeto();
    const unidad = 'g';
    el.otra.disabled = true; el.usar.disabled = true;
    try {
      const guardada = await guardarFoto(blobFoto, m, tipo, { peso, taper, neto, unidad });
      sumarFoto(m, tipo, {
        archivo: guardada.archivo,
        url: guardada.url,
        texto: neto.toLocaleString('es-BO') + ' g',
        valor: aUnidadProducto(neto, 'g', m.medida),   // Harina (kg) se convierte sola; los demás quedan en g
        neto, unidad, taper
      });
      if (seguir) { subtitulo(); iniciarVideo(); }     // el taper se conserva: suele ser el mismo recipiente
      else bootstrap.Modal.getOrCreateInstance(el.modal).hide();
    } catch (e) {
      mostrarError(e.message || 'No se pudo guardar la foto.');
      actualizarSumar();
    }
  }

  if (!faltantes.length) {
  /* Campos de peso / taper en gramos (siempre visibles): solo dígitos */
  el.peso.addEventListener('input', async () => {
    el.peso.value = el.peso.value.replace(/[^0-9]/g, '');
    // Escribir el peso estando en vivo congela el cuadro actual como foto de respaldo
    if (modo === 'vivo' && stream && el.video.videoWidth && el.peso.value !== '') {
      const valor = el.peso.value;
      dibujar(el.video, el.video.videoWidth, el.video.videoHeight);
      const blob = await canvasABlob();
      if (modo !== 'vivo') return;
      detenerCamara();
      modoPrevia(blob, null, true);
      el.peso.value = valor;
    }
    actualizarSumar();
  });
  el.taper.addEventListener('input', () => { el.taper.value = el.taper.value.replace(/[^0-9]/g, ''); actualizarSumar(); });

  el.repetir.addEventListener('click', () => { detenerCamara(); iniciarVideo(); });

  // Galería / archivos (sin "capture": en el celular deja elegir galería, archivos o cámara)
  el.subir.addEventListener('click', () => el.archivo.click());
  el.archivo.addEventListener('change', (ev) => {
    const f = ev.target.files[0];
    ev.target.value = '';
    if (!f) return;
    const img = new Image();
    img.onload = async () => {
      dibujar(img, img.naturalWidth, img.naturalHeight);
      detenerCamara();
      mostrarError('');
      modoPrevia(await canvasABlob());
    };
    img.onerror = () => mostrarError('No se pudo leer esa imagen. Pruebe con otra foto (JPG o PNG).');
    img.src = window.URL.createObjectURL(f);
  });

  el.usar.addEventListener('click', () => confirmarFoto(false));
  el.otra.addEventListener('click', () => confirmarFoto(true));
  el.modal.addEventListener('hidden.bs.modal', () => { detenerCamara(); modoVivo(); mostrarError(''); mensaje('', 'info'); camCtx = null; });
  }

  /* ---------- 7. Modal de fotos sumadas (ver todas y quitar alguna) ---------- */
  const fo = {
    modal: document.getElementById('modalFotos'),
    sub:   document.getElementById('fotosSubtitulo'),
    grid:  document.getElementById('fotosGrid'),
    total: document.getElementById('fotosTotal')
  };
  let fotosCtx = null;    // { m, tipo }

  function pintarModalFotos() {
    const { m, tipo } = fotosCtx;
    const fotos = m.fotos[tipo];
    fo.sub.textContent = m.nombre + ' · ' + nombreTipo(tipo);
    fo.grid.textContent = '';
    fotos.forEach((f, i) => {
      const item = document.createElement('div');
      item.className = 'foto-item';

      const ver = document.createElement('a');
      ver.href = f.url; ver.target = '_blank'; ver.rel = 'noopener'; ver.title = 'Ver foto completa';
      const img = document.createElement('img');
      img.src = f.url; img.alt = 'Foto de la balanza';
      ver.appendChild(img);

      const quitar = document.createElement('button');
      quitar.type = 'button'; quitar.className = 'foto-item-quitar';
      quitar.title = 'Quitar esta foto (resta su peso)'; quitar.dataset.quitarFoto = i;
      quitar.innerHTML = '<i class="material-symbols-rounded">close</i>';

      const peso = document.createElement('span');
      peso.className = 'foto-item-peso';
      peso.textContent = f.texto + (f.taper > 0 ? ' (taper ' + f.taper + ' g)' : '');

      item.append(ver, quitar, peso);
      fo.grid.appendChild(item);
    });
    const suma = fotos.reduce((a, f) => a + (f.valor || 0), 0);
    fo.total.textContent = suma.toLocaleString('es-BO', { minimumFractionDigits: 0, maximumFractionDigits: 3 }) + ' ' + m.medida;
  }

  function abrirFotos(m, tipo) {
    if (!m.fotos[tipo].length) return;
    fotosCtx = { m, tipo };
    pintarModalFotos();
    bootstrap.Modal.getOrCreateInstance(fo.modal).show();
  }

  fo.grid.addEventListener('click', (e) => {
    const b = e.target.closest('[data-quitar-foto]');
    if (!b || !fotosCtx) return;
    quitarFoto(fotosCtx.m, fotosCtx.tipo, Number(b.dataset.quitarFoto));
    if (fotosCtx.m.fotos[fotosCtx.tipo].length) pintarModalFotos();
    else bootstrap.Modal.getOrCreateInstance(fo.modal).hide();
  });
  fo.modal.addEventListener('hidden.bs.modal', () => { fotosCtx = null; });

  /* ---------- 8. Eventos del formulario (delegados en el formulario: no dependen de las filas que dibuje el DataTable) ---------- */
  form.addEventListener('input', (e) => {
    const inp = e.target.closest('.inp-entrada, .inp-agregar, .inp-existencia');
    const m = inp && modelo[inp.dataset.id];
    if (!m) return;
    inp.value = inp.value.replace(/[^0-9.,]/g, '');                       // solo cifras y separador decimal
    m[inp.classList.contains('inp-entrada') ? 'entrada' : inp.classList.contains('inp-agregar') ? 'agregar' : 'existencia'] = inp.value;
    ambitos(m.id).forEach((z) => pintarCalculo(z, m));                    // la fila y su fila desplegada ("+")
    progreso(); revisarAjustes();
  });

  form.addEventListener('click', (e) => {
    const strip = e.target.closest('[data-ver-fotos]');
    if (strip) {
      const mf = modelo[strip.dataset.id];
      if (mf) abrirFotos(mf, strip.dataset.tipo);
      return;
    }
    const btn = e.target.closest('.btn-lock, .btn-balanza');
    const m = btn && !btn.disabled && modelo[btn.dataset.id];
    if (!m) return;
    if (btn.classList.contains('btn-balanza')) abrirCamara(m, btn.dataset.tipo);
    else alternarEntrada(m);
  });

  // Enter dentro de un campo (incluido el buscador del DataTable) no debe enviar el formulario
  form.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && e.target.tagName === 'INPUT') e.preventDefault();
  });

  repintarVisibles();
  progreso();

  /* ---------- 9. Guardado y envío ---------- */
  // Los campos del formulario se arman desde el modelo: así viajan todos los productos, aunque el
  // DataTable solo muestre una página.
  function construirCampos() {
    camposEnvio.textContent = '';
    const agregar = (nombre, valor) => {
      const i = document.createElement('input');
      i.type = 'hidden'; i.name = nombre; i.value = valor;
      camposEnvio.appendChild(i);
    };
    lista.forEach((m) => {
      const p = 'items[' + m.idx + ']';
      agregar(p + '[id_producto]', m.id);
      agregar(p + '[entrada]', num(m.entrada));
      agregar(p + '[agregar]', m.agregar === '' ? '' : num(m.agregar));
      agregar(p + '[existencia]', m.existencia === '' ? '' : num(m.existencia));
      ['produccion', 'existencia'].forEach((tipo) => m.fotos[tipo].forEach((f) => {
        agregar(p + '[fotos_' + tipo + '][]', f.archivo);     // archivo de cada foto
        agregar(p + '[pesos_' + tipo + '][]', f.neto);        // peso neto en GRAMOS
        agregar(p + '[unidades_' + tipo + '][]', f.unidad);   // siempre 'g'
        agregar(p + '[tapers_' + tipo + '][]', f.taper);      // taper descontado en GRAMOS
      }));
    });
  }

  // DEMO: aplica el guardado en pantalla (suma lo agregado al acumulado y limpia los campos).
  function aplicarGuardadoDemo() {
    lista.forEach((m) => {
      const ag = num(m.agregar);
      if (ag > 0) m.producido = Math.round((m.producido + ag) * 100) / 100;
      m.agregar = '';
      m.fotos.produccion.length = 0;
      m.fotos.existencia.length = 0;
      m.existenciaOriginal = m.existencia;
      m.entradaOriginal = m.entrada;
      m.entradaAbierta = false;
    });
    motivo.value = '';
    repintarVisibles();
    progreso(); revisarAjustes();
  }

  // Productos por peso con producción o existencia nueva sin foto de balanza
  function productosSinFoto() {
    const faltan = [];
    lista.forEach((m) => {
      if (m.medida === 'u') return;   // las unidades se cuentan a mano: no tienen foto de balanza
      if (num(m.agregar) > 0 && !m.fotos.produccion.length) faltan.push(m.nombre + ' (producción)');
      if (m.existencia !== '' && m.existencia !== m.existenciaOriginal && !m.fotos.existencia.length) faltan.push(m.nombre + ' (existencia)');
    });
    return faltan;
  }

  form.addEventListener('submit', function (e) {
    const accion = e.submitter ? e.submitter.value : 'guardar';

    if (motivo.required && motivo.value.trim() === '') {
      e.preventDefault(); alert('Indique el motivo del ajuste de la entrada.'); motivo.focus(); return;
    }

    if (CFG.fotoObligatoria) {
      const sinFoto = productosSinFoto();
      if (sinFoto.length) {
        e.preventDefault();
        alert('Falta la foto de la balanza en:\n- ' + sinFoto.join('\n- '));
        return;
      }
    }

    if (accion === 'cerrar') {
      const faltan = lista.length - progreso();
      if (faltan > 0) {
        e.preventDefault();
        alert('No se puede cerrar el turno: faltan ' + faltan + ' producto(s) por contar su existencia.');
        return;
      }
      if (lista.some((m) => salidaDe(m) < 0) && !confirm('Hay productos cuya existencia supera entrada + producción. ¿Cerrar el turno de todos modos?')) {
        e.preventDefault(); return;
      }
      if (!confirm('¿Cerrar el turno? Luego ya no podrá editar esta planilla.')) { e.preventDefault(); return; }
    }

    if (CFG.demo) {
      e.preventDefault();
      if (accion === 'cerrar') {
        window.location.href = CFG.base + '/produccion';
      } else {
        aplicarGuardadoDemo();
        aviso('Avance guardado (demo: no se envió al servidor).');
      }
      return;
    }

    construirCampos();   // con backend: continúa el envío normal del formulario
  });
})();