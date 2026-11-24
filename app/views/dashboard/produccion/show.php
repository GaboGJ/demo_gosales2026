<?php
/* MODAL "PLANILLA" — se incluye desde index.php y edit.php.
   Define: verDetalleProduccion(id) e imprimirProduccion(id).
   imprimirProduccion imprime directo (sin abrir el modal): Producción arriba y Existencia abajo.
   Si la página que lo incluye define $enEdicion = true, oculta el botón "Continuar registro".

   ============ DATOS DE PRUEBA (reemplazar por datos del backend; mismo formato) ============ */
$catalogo = [
  ['Masa Familiar', 'u'], ['Masa Mediana', 'u'], ['Queso Muzzarella', 'g'], ['Queso Cheddar', 'g'],
  ['Calabresa', 'g'], ['Peperoni', 'g'], ['Tocino', 'g'], ['Jamón', 'g'], ['Harina', 'kg'], ['Carne', 'g'], ['Pollo', 'g'],
];

// Arma una planilla a partir de arrays paralelos al catálogo (existencia null = aún sin contar).
$armar = function (array $cab, array $ent, array $pro, array $exi, array $hist = null) use ($catalogo) {
  $items = [];
  $contados = 0;
  $auto = [];
  foreach ($catalogo as $i => $c) {
    $contado = $exi[$i] !== null;
    if ($contado) $contados++;
    $items[] = [
      'producto' => $c[0], 'medida' => $c[1], 'entrada' => $ent[$i], 'producido' => $pro[$i],
      'existencia' => $exi[$i], 'salida' => $contado ? $ent[$i] + $pro[$i] - $exi[$i] : null, 'foto_url' => null,
    ];
    if ($pro[$i] > 0) $auto[] = ['hora' => $cab['hora'], 'producto' => $c[0], 'cantidad' => $pro[$i], 'medida' => $c[1], 'foto_url' => null];
  }
  return [
    'produccion' => [
      'id' => $cab['id'], 'codigo' => str_pad($cab['id'], 5, '0', STR_PAD_LEFT), 'fecha' => $cab['fecha'], 'turno' => $cab['turno'],
      'sucursal' => 'Tatu Central', 'responsable' => $cab['responsable'], 'estado' => $cab['estado'],
      'contados' => $contados, 'total' => count($catalogo),
    ],
    'items' => $items,
    'historial' => $hist ?? $auto,
  ];
};

//          Masa F, Masa M, Muzz,  Cheddar, Calab, Peper, Tocino, Jamón, Harina, Carne, Pollo
$planillas = [
  1 => $armar(['id' => 1, 'fecha' => '20/11/2026', 'turno' => 'Mañana', 'responsable' => 'Carlos Mendoza', 'estado' => 'Completada', 'hora' => '08:15'],
        [26, 55, 24158, 6584, 652, 1218, 0, 966, 0, 6100, 2992],
        [28, 43, 17616, 0, 7000, 0, 2000, 3058, 21, 0, 0],
        [20, 39, 19498, 3562, 2732, 508, 900, 308, 0, 4390, 2326]),
  2 => $armar(['id' => 2, 'fecha' => '20/11/2026', 'turno' => 'Tarde', 'responsable' => 'María Rojas', 'estado' => 'Completada', 'hora' => '15:30'],
        [20, 39, 19498, 3562, 2732, 508, 900, 308, 0, 4390, 2326],
        [53, 60, 38066, 2955, 3349, 3818, 1000, 3040, 0, 2445, 0],
        [26, 44, 33012, 5132, 2594, 2524, 750, 2798, 0, 6355, 2000]),
  3 => $armar(['id' => 3, 'fecha' => '19/11/2026', 'turno' => 'Mañana', 'responsable' => 'Carlos Mendoza', 'estado' => 'Completada', 'hora' => '08:40'],
        [30, 50, 15000, 4000, 2000, 900, 600, 500, 5, 4000, 2500],
        [53, 60, 0, 2955, 0, 0, 0, 0, 0, 0, 0],
        [22, 38, 11200, 5800, 1400, 610, 420, 380, 3, 3100, 1900]),
  4 => $armar(['id' => 4, 'fecha' => '19/11/2026', 'turno' => 'Tarde', 'responsable' => 'María Rojas', 'estado' => 'Completada', 'hora' => '14:10'],
        [22, 38, 11200, 5800, 1400, 610, 420, 380, 3, 3100, 1900],
        [40, 45, 38066, 0, 3349, 3818, 1000, 3040, 0, 2445, 0],
        [18, 30, 40500, 5100, 4200, 3900, 1100, 2900, 2, 4800, 1500]),
  5 => $armar(['id' => 5, 'fecha' => '18/11/2026', 'turno' => 'Noche', 'responsable' => 'Luis Camacho', 'estado' => 'Anulada', 'hora' => '20:05'],
        [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        [0, 0, 0, 0, 0, 0, 1000, 0, 0, 0, 0],
        [null, null, null, null, null, null, null, null, null, null, null]),
  // Misma producción que se ve en edit.php (en proceso)
  6 => $armar(['id' => 6, 'fecha' => '21/11/2026', 'turno' => 'Mañana', 'responsable' => 'Carlos Mendoza', 'estado' => 'En proceso', 'hora' => '08:20'],
        [26, 44, 33012, 5132, 2594, 2524, 750, 2798, 0, 6355, 2000],
        [30, 40, 0, 0, 0, 0, 1000, 0, 21, 0, 0],
        [22, null, null, null, null, null, null, null, null, null, null],
        [
          ['hora' => '08:20', 'producto' => 'Masa Familiar', 'cantidad' => 20,   'medida' => 'u',  'foto_url' => null],
          ['hora' => '08:20', 'producto' => 'Masa Mediana',  'cantidad' => 25,   'medida' => 'u',  'foto_url' => null],
          ['hora' => '09:45', 'producto' => 'Masa Familiar', 'cantidad' => 10,   'medida' => 'u',  'foto_url' => null],
          ['hora' => '09:45', 'producto' => 'Masa Mediana',  'cantidad' => 15,   'medida' => 'u',  'foto_url' => null],
          ['hora' => '10:10', 'producto' => 'Tocino',        'cantidad' => 1000, 'medida' => 'g',  'foto_url' => null],
          ['hora' => '10:10', 'producto' => 'Harina',        'cantidad' => 21,   'medida' => 'kg', 'foto_url' => null],
        ]),
];
/* ============ FIN DATOS DE PRUEBA ============ */

$enEdicion = $enEdicion ?? false;
?>
<style>
  /* ===== Hoja de impresión (se arma en #areaImpresion, hijo directo de <body>) ===== */
  #areaImpresion { display: none; }
  @media print {
    body.imprimiendo-planilla > *:not(#areaImpresion) { display: none !important; }
    body.imprimiendo-planilla #areaImpresion { display: block !important; padding: 8px; color: #000; }
  }
  #areaImpresion .pi-cab { text-align: center; margin-bottom: 10px; }
  #areaImpresion .pi-cab h2 { margin: 0; font-size: 18px; text-transform: uppercase; }
  #areaImpresion .pi-datos { display: flex; justify-content: space-between; font-size: 12px; border-top: 1px solid #444; border-bottom: 1px solid #444; padding: 6px 0; margin-bottom: 4px; }
  #areaImpresion .pi-titulo { font-size: 14px; font-weight: 700; text-transform: uppercase; margin: 14px 0 6px; }
  #areaImpresion .pi-seccion { page-break-inside: avoid; }
  #areaImpresion .pi-tabla { width: 100%; border-collapse: collapse; font-size: 12px; }
  #areaImpresion .pi-tabla th, #areaImpresion .pi-tabla td { border: 1px solid #444; padding: 4px 8px; }
  #areaImpresion .pi-tabla th { background: #eee; text-transform: uppercase; font-size: 11px; }
  #areaImpresion .pi-tabla td.n { text-align: right; }
  #areaImpresion .pi-firma { margin: 60px auto 0; width: 260px; border-top: 1px solid #000; text-align: center; padding-top: 4px; font-size: 12px; text-transform: uppercase; }
</style>

<div class="modal fade" id="modalPlanilla" tabindex="-1" aria-labelledby="tituloPlanilla" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-radius-xl">

      <div class="modal-header border-0 pb-0 px-4 pt-4">
        <div>
          <h5 class="modal-title font-weight-bolder text-dark mb-0" id="tituloPlanilla">Planilla de Producción</h5>
          <p class="text-xs text-secondary mb-0">Vista de la planilla tal como va el registro del turno.</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body px-4" id="planillaImprimible">
        <div id="planillaContenido" class="text-center text-sm text-secondary py-5">Cargando planilla...</div>
      </div>

      <div class="modal-footer border-0 pt-0 px-4 pb-4 d-flex flex-wrap gap-2">
        <a href="#" id="planillaEditar" class="btn bg-gradient-primary text-white mb-0 border-radius-md px-3 d-none align-items-center gap-2">
          <i class="material-symbols-rounded text-sm">edit_note</i><span class="font-weight-bold">Continuar registro</span>
        </a>
        <button type="button" class="btn btn-outline-primary mb-0 border-radius-md px-3 d-inline-flex align-items-center gap-2" onclick="imprimirProduccion()">
          <i class="material-symbols-rounded text-sm">print</i><span class="font-weight-bold">Imprimir</span>
        </button>
        <button type="button" class="btn btn-outline-secondary mb-0 border-radius-md px-3" data-bs-dismiss="modal">Cerrar</button>
      </div>

    </div>
  </div>
</div>

<script>
(function () {
  const BASE = <?= json_encode(URL) ?>;
  const EN_EDICION = <?= json_encode((bool)$enEdicion) ?>;
  const PLANILLAS = <?= json_encode($planillas, JSON_UNESCAPED_UNICODE) ?>;   // BACKEND: reemplazar por fetch a la planilla
  let actual = null;   // id de la planilla que se está viendo en el modal

  const fmt = (n) => Number(n).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const esc = (s) => String(s == null ? '' : s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const TURNO  = { 'Mañana': ['bg-gradient-info', 'wb_twilight'], 'Tarde': ['bg-gradient-warning', 'light_mode'], 'Noche': ['bg-gradient-dark', 'dark_mode'] };
  const ESTADO = { 'En proceso': 'bg-gradient-warning', 'Completada': 'bg-gradient-success', 'Anulada': 'bg-gradient-danger' };
  const TH = 'text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 border-bottom border-light';

  const enlaceFoto = (url) => url
    ? '<a href="' + esc(url) + '" target="_blank" rel="noopener" class="text-primary ms-1" title="Ver foto de respaldo"><i class="material-symbols-rounded text-sm align-middle">photo_camera</i></a>'
    : '';

  /* ---------- Vista del modal ---------- */
  function render(d) {
    const p = d.produccion;
    const t = TURNO[p.turno] || TURNO['Mañana'];
    const estadoClase = ESTADO[p.estado] || 'bg-gradient-warning';
    const pct = p.total ? Math.round(p.contados / p.total * 100) : 0;

    const filas = d.items.map((it) => {
      const contado = it.existencia !== null;
      const neg = contado && it.salida < 0;
      return '<tr>' +
        '<td class="py-2 pe-3 align-middle"><span class="text-sm font-weight-bold text-dark text-uppercase">' + esc(it.producto) + '</span> <span class="text-xxs text-secondary">(' + esc(it.medida) + ')</span></td>' +
        '<td class="py-2 px-3 align-middle text-center text-sm">' + fmt(it.entrada) + '</td>' +
        '<td class="py-2 px-3 align-middle text-center text-sm">' + fmt(it.producido) + '</td>' +
        '<td class="py-2 px-3 align-middle text-center text-sm font-weight-bold text-dark">' + (contado ? fmt(it.existencia) + enlaceFoto(it.foto_url) : '—') + '</td>' +
        '<td class="py-2 ps-3 align-middle text-center">' +
          (contado
            ? '<span class="text-sm font-weight-bolder ' + (neg ? 'text-danger' : 'text-primary') + '">' + fmt(it.salida) + '</span>' +
              (neg ? '<div class="text-xxs text-danger font-weight-bold">Existencia mayor a lo disponible</div>' : '')
            : '<span class="text-xxs text-secondary font-weight-bold">Pendiente</span>') +
        '</td></tr>';
    }).join('');

    const hist = d.historial.length
      ? d.historial.map((h) =>
          '<tr><td class="py-2 pe-3 text-sm text-secondary font-weight-bold">' + esc(h.hora) + '</td>' +
          '<td class="py-2 px-3 text-sm text-dark font-weight-bold">' + esc(h.producto) + '</td>' +
          '<td class="py-2 px-3 text-end text-sm text-primary font-weight-bolder">+' + fmt(h.cantidad) + ' <span class="text-xxs text-secondary">' + esc(h.medida) + '</span></td>' +
          '<td class="py-2 ps-3 text-center">' + (h.foto_url ? enlaceFoto(h.foto_url) : '<span class="text-xxs text-secondary">—</span>') + '</td></tr>').join('')
      : '<tr><td colspan="4" class="py-4 text-center text-xs text-secondary">Aún no hay producción registrada.</td></tr>';

    return '' +
      '<div class="border-radius-lg overflow-hidden border">' +
        '<div class="p-3 bg-gradient-primary text-center"><h5 class="font-weight-bolder text-white text-uppercase mb-0">' + esc(p.sucursal) + '</h5></div>' +
        '<div class="px-3 py-3 border-bottom"><div class="row g-3">' +
          '<div class="col-6 col-md-3"><span class="text-xxs text-secondary font-weight-bold text-uppercase d-block">Planilla</span><span class="text-sm font-weight-bolder text-dark">#' + esc(p.codigo) + '</span> <span class="badge badge-sm ' + estadoClase + ' border-radius-pill px-2 py-1 ms-1">' + esc(p.estado) + '</span></div>' +
          '<div class="col-6 col-md-2"><span class="text-xxs text-secondary font-weight-bold text-uppercase d-block">Fecha</span><span class="text-sm font-weight-bolder text-dark">' + esc(p.fecha) + '</span></div>' +
          '<div class="col-6 col-md-3"><span class="text-xxs text-secondary font-weight-bold text-uppercase d-block mb-1">Turno</span><span class="badge badge-sm ' + t[0] + ' border-radius-pill px-3 py-1 font-weight-bold d-inline-flex align-items-center gap-1"><i class="material-symbols-rounded text-sm">' + t[1] + '</i>' + esc(p.turno) + '</span></div>' +
          '<div class="col-6 col-md-4"><span class="text-xxs text-secondary font-weight-bold text-uppercase d-block">Encargado</span><span class="text-sm font-weight-bolder text-primary">' + esc(p.responsable) + '</span></div>' +
        '</div>' +
        '<div class="mt-3"><div class="d-flex justify-content-between text-xxs font-weight-bold text-secondary text-uppercase mb-1"><span>Existencia contada</span><span>' + p.contados + ' / ' + p.total + '</span></div>' +
        '<div class="progress" style="height:8px"><div class="progress-bar bg-gradient-primary" role="progressbar" style="width:' + pct + '%"></div></div></div></div>' +
        '<div class="table-responsive px-3 pt-2"><table class="table table-borderless align-items-center mb-0 w-100"><thead><tr>' +
          '<th class="' + TH + '">Productos</th><th class="text-center ' + TH + ' px-3">Entrada</th><th class="text-center ' + TH + ' px-3">Producción</th><th class="text-center ' + TH + ' px-3">Existencia</th><th class="text-center ' + TH + ' ps-3">Salida / Consumo</th>' +
        '</tr></thead><tbody>' + filas + '</tbody></table></div>' +
        '<p class="text-xxs text-secondary px-3 mt-2 mb-3">Salida / consumo = entrada + producción − existencia.</p>' +
      '</div>' +
      '<div class="mt-4"><h6 class="font-weight-bolder text-dark mb-0">Historial de producción registrada</h6>' +
        '<p class="text-xs text-secondary mb-2">Cada guardado de producción queda registrado con su hora y su foto de respaldo.</p>' +
        '<div class="table-responsive"><table class="table table-borderless align-items-center mb-0 w-100"><thead><tr>' +
          '<th class="' + TH + '">Hora</th><th class="' + TH + ' px-3">Producto</th><th class="text-end ' + TH + ' px-3">Cantidad</th><th class="text-center ' + TH + ' ps-3">Foto</th>' +
        '</tr></thead><tbody>' + hist + '</tbody></table></div></div>';
  }

  /* ---------- Hoja de impresión: Producción arriba, Existencia abajo ---------- */
  function renderImpresion(d) {
    const p = d.produccion;
    const filasProd = d.items.map((it) =>
      '<tr><td>' + esc(it.producto) + '</td><td>' + esc(it.medida) + '</td>' +
      '<td class="n">' + fmt(it.entrada) + '</td><td class="n">' + fmt(it.producido) + '</td>' +
      '<td class="n"><b>' + fmt(Number(it.entrada) + Number(it.producido)) + '</b></td></tr>').join('');
    const filasExis = d.items.map((it) => {
      const contado = it.existencia !== null;
      return '<tr><td>' + esc(it.producto) + '</td><td>' + esc(it.medida) + '</td>' +
        '<td class="n"><b>' + (contado ? fmt(it.existencia) : '&nbsp;') + '</b></td>' +
        '<td class="n">' + (contado ? fmt(it.salida) : '&nbsp;') + '</td></tr>';
    }).join('');

    return '' +
      '<div class="pi-cab"><h2>' + esc(p.sucursal) + '</h2></div>' +
      '<div class="pi-datos"><span><b>Planilla:</b> #' + esc(p.codigo) + '</span><span><b>Fecha:</b> ' + esc(p.fecha) +
      '</span><span><b>Turno:</b> ' + esc(p.turno) + '</span><span><b>Encargado:</b> ' + esc(p.responsable) + '</span></div>' +
      '<div class="pi-seccion"><div class="pi-titulo">Producción</div>' +
        '<table class="pi-tabla"><thead><tr><th>Producto</th><th>Unid.</th><th>Entrada</th><th>Producción</th><th>Total</th></tr></thead>' +
        '<tbody>' + filasProd + '</tbody></table></div>' +
      '<div class="pi-seccion"><div class="pi-titulo">Existencia</div>' +
        '<table class="pi-tabla"><thead><tr><th>Producto</th><th>Unid.</th><th>Existencia</th><th>Salida / Consumo</th></tr></thead>' +
        '<tbody>' + filasExis + '</tbody></table>' +
        '<div style="font-size:11px;margin-top:4px">Salida / consumo = entrada + producción − existencia.</div></div>' +
      '<div class="pi-firma">Firma del encargado</div>';
  }

  /* ---------- API pública ---------- */
  window.verDetalleProduccion = function (id) {
    actual = id;
    const contenido = document.getElementById('planillaContenido');
    const btnEditar = document.getElementById('planillaEditar');
    btnEditar.classList.add('d-none');
    btnEditar.classList.remove('d-inline-flex');

    const d = PLANILLAS[id];
    if (!d) {
      contenido.className = 'text-center text-sm text-danger py-5';
      contenido.textContent = 'No se encontró la planilla #' + id + '.';
    } else {
      contenido.className = '';
      contenido.innerHTML = render(d);
      if (!EN_EDICION && d.produccion.estado === 'En proceso') {
        btnEditar.href = BASE + '/produccion/edit';
        btnEditar.classList.remove('d-none');
        btnEditar.classList.add('d-inline-flex');
      }
    }
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalPlanilla')).show();
  };

  // Imprime sin abrir el modal: arma la hoja en #areaImpresion y oculta el resto de la página al imprimir.
  window.imprimirProduccion = function (id) {
    const d = PLANILLAS[id || actual];
    if (!d) return;
    let area = document.getElementById('areaImpresion');
    if (!area) { area = document.createElement('div'); area.id = 'areaImpresion'; document.body.appendChild(area); }
    area.innerHTML = renderImpresion(d);
    document.body.classList.add('imprimiendo-planilla');
    const limpiar = () => { document.body.classList.remove('imprimiendo-planilla'); window.removeEventListener('afterprint', limpiar); };
    window.addEventListener('afterprint', limpiar);
    window.print();
  };
})();
</script>