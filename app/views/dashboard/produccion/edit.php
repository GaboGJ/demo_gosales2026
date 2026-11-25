<?php
// require_once __DIR__ . '/detail.php';  // <- cuando tengas el backend, descomenta y borra el bloque de prueba

/* ============ DATOS DE PRUEBA (reemplazar por datos del backend) ============
   'entrada'    = existencia del último cierre.
   'producido'  = suma de lo ya registrado en esta producción (cada guardado de "Producción (+)" se suma).
   'existencia' = null -> aún sin contar. */
$esEncargado = true;   // true = el usuario es el encargado y puede desbloquear la entrada; probar con false
$produccion = [
  'id_produccion' => 6,
  'fecha_produccion' => '2026-11-21 08:00:00',
  'turno' => 'Mañana',
  'sucursal' => 'Tatu Central',
  'responsable' => 'Gabriel Guayhua',
  'estado' => 'En proceso',
];
$items = [
  ['id_producto' => 1,  'producto' => 'Masa Familiar',    'medida' => 'u',  'entrada' => 26,    'producido' => 30,   'existencia' => 22],
  ['id_producto' => 2,  'producto' => 'Masa Mediana',     'medida' => 'u',  'entrada' => 44,    'producido' => 40,   'existencia' => null],
  ['id_producto' => 3,  'producto' => 'Queso Muzzarella', 'medida' => 'g',  'entrada' => 33012, 'producido' => 0,    'existencia' => null],
  ['id_producto' => 4,  'producto' => 'Queso Cheddar',    'medida' => 'g',  'entrada' => 5132,  'producido' => 0,    'existencia' => null],
  ['id_producto' => 5,  'producto' => 'Calabresa',        'medida' => 'g',  'entrada' => 2594,  'producido' => 0,    'existencia' => null],
  ['id_producto' => 6,  'producto' => 'Peperoni',         'medida' => 'g',  'entrada' => 2524,  'producido' => 0,    'existencia' => null],
  ['id_producto' => 7,  'producto' => 'Tocino',           'medida' => 'g',  'entrada' => 750,   'producido' => 1000, 'existencia' => null],
  ['id_producto' => 8,  'producto' => 'Jamón',            'medida' => 'g',  'entrada' => 2798,  'producido' => 0,    'existencia' => null],
  ['id_producto' => 9,  'producto' => 'Harina',           'medida' => 'kg', 'entrada' => 0,     'producido' => 21,   'existencia' => null],
  ['id_producto' => 10, 'producto' => 'Carne',            'medida' => 'g',  'entrada' => 6355,  'producido' => 0,    'existencia' => null],
  ['id_producto' => 11, 'producto' => 'Pollo',            'medida' => 'g',  'entrada' => 2000,  'producido' => 0,    'existencia' => null],
];
/* ============ FIN DATOS DE PRUEBA ============ */

$idProd = (int)$produccion['id_produccion'];
$total  = count($items);

/* ---------- Piezas reutilizables de la vista ---------- */

// Datos de la cabecera: [icono, texto]
$meta = [
  ['calendar_today', date('d/m/Y', strtotime($produccion['fecha_produccion']))],
  ['schedule',       'Turno ' . $produccion['turno']],
  ['storefront',     $produccion['sucursal']],
  ['person',         $produccion['responsable']],
];

// Estado inicial que usa edit.js (los datos viven en memoria; la tabla solo los muestra)
$estadoInicial = [];
foreach ($items as $i => $it) {
  $estadoInicial[] = [
    'id' => (int)$it['id_producto'], 'nombre' => $it['producto'], 'medida' => $it['medida'],
    'entrada' => $it['entrada'], 'producido' => $it['producido'], 'existencia' => $it['existencia'],
  ];
}

// Campo de cantidad (Producción o Existencia): [ input | unidad | cámara ] y debajo, en una sola línea,
// las fotos sumadas (miniaturas juntas + último peso leído). Las unidades ('u') se cuentan a mano: sin cámara.
$campoPeso = function (int $idp, string $tipo, string $medida, $valor, string $placeholder) {
  $clase = $tipo === 'produccion' ? 'inp-agregar' : 'inp-existencia';
  ?>
  <div class="campo">
    <input type="text" inputmode="decimal" autocomplete="off" data-id="<?= $idp; ?>"
           class="campo-input <?= $clase; ?>" value="<?= $valor; ?>" placeholder="<?= $placeholder; ?>">
    <?php if ($medida !== 'u'): ?>
      <button type="button" class="campo-btn campo-btn-foto btn-balanza" data-id="<?= $idp; ?>" data-tipo="<?= $tipo; ?>"
              title="Tomar foto de la balanza (lee el peso y lo suma)" aria-label="Tomar foto de la balanza">
        <i class="material-symbols-rounded">photo_camera</i>
      </button>
    <?php endif; ?>
  </div>
  <div class="mt-1" data-fotos="<?= $tipo; ?>"></div>
  <?php
};
?>
<style>
  /* ===== Campos (entrada / producción / existencia) ===== */
  #datatable-edicion .campo { display: flex; align-items: stretch; height: 40px; background: #fff; border: 1px solid #d2d6da;
    border-radius: .5rem; overflow: hidden; transition: border-color .15s, box-shadow .15s; }
  #datatable-edicion .campo:focus-within { border-color: #e91e63; box-shadow: 0 0 0 .15rem rgba(233,30,99,.15); }
  #datatable-edicion .campo-input { flex: 1 1 auto; min-width: 0; width: 100%; border: 0; outline: 0; box-shadow: none; background: transparent;
    padding: 0 .5rem; text-align: center; font-size: .875rem; font-weight: 600; color: #344767; }
  #datatable-edicion .campo-input::placeholder { font-weight: 400; color: #adb5bd; }
  #datatable-edicion .campo-input[readonly] { background: #f8f9fa; color: #67748e; }
  #datatable-edicion .campo-unidad { display: flex; align-items: center; padding: 0 .6rem; background: #f8f9fa; border-left: 1px solid #e9ecef;
    font-size: .72rem; font-weight: 700; color: #67748e; }
  #datatable-edicion .campo-btn { flex: 0 0 42px; display: flex; align-items: center; justify-content: center; margin: 0; padding: 0;
    border: 0; border-left: 1px solid #e9ecef; cursor: pointer; transition: background .15s; }
  #datatable-edicion .campo-btn i { font-size: 1.15rem; line-height: 1; }
  #datatable-edicion .campo-btn-foto { background: linear-gradient(195deg, #ec407a, #d81b60); color: #fff; border-left: 0; }
  #datatable-edicion .campo-btn-foto:hover { filter: brightness(1.08); }
  #datatable-edicion .campo-btn-lock { background: #f8f9fa; color: #67748e; }
  #datatable-edicion .campo-btn-lock:hover:not(:disabled) { background: #e9ecef; }
  #datatable-edicion .campo-btn-lock.abierto { background: #fb8c00; color: #fff; }
  #datatable-edicion .campo-btn-lock:disabled { cursor: not-allowed; opacity: .5; }

  /* ===== Fotos tomadas: miniaturas juntas + último peso (una sola línea) ===== */
  #datatable-edicion .foto-strip { display: inline-flex; align-items: center; gap: .5rem; max-width: 100%; margin: 0; padding: 2px 10px 2px 2px;
    background: #fff; border: 1px solid #e9ecef; border-radius: 999px; cursor: pointer; }
  #datatable-edicion .foto-strip:hover { border-color: #e91e63; }
  #datatable-edicion .foto-grupo { display: inline-flex; align-items: center; }
  #datatable-edicion .foto-grupo > * + * { margin-left: -8px; }
  #datatable-edicion .foto-av, #datatable-edicion .foto-mas { width: 26px; height: 26px; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 0 1px #e9ecef; }
  #datatable-edicion .foto-av { object-fit: cover; display: block; }
  #datatable-edicion .foto-mas { display: inline-flex; align-items: center; justify-content: center; background: #344767; color: #fff; font-size: .6rem; font-weight: 700; }
  #datatable-edicion .foto-peso { font-size: .75rem; font-weight: 700; color: #344767; white-space: nowrap; }

  /* ===== Tabla: comportamiento normal del DataTable responsive (con el "+" para desplegar) ===== */
  #datatable-edicion td { padding-top: .85rem; padding-bottom: .85rem; vertical-align: top; }
  #datatable-edicion tbody tr { border-bottom: 1px solid #f0f2f5; }
  #datatable-edicion .celda-campo { width: 100%; max-width: 230px; margin: 0 auto; }
  #datatable-edicion .col-salida { min-width: 110px; }
  #datatable-edicion tr.fila-ajustada > td { background: #fff8e1; }

  /* Fila desplegada (la que muestra el "+") */
  #datatable-edicion tr.child .celda-campo { max-width: none; min-width: 180px; margin: 0; }
  #datatable-edicion tr.child .salida-wrap { padding-top: 0 !important; }
  #datatable-edicion tr.child ul.dtr-details { display: block; width: 100%; }
</style>

<style>
  /* ===== Cabecera y encabezado del registro: compactos en pantallas chicas ===== */
  @media (max-width: 767.98px) {
    .prod-edit { padding-left: .5rem !important; padding-right: .5rem !important; padding-top: .5rem !important; }
    .prod-edit .cab-card .card-body { padding: .75rem !important; }
    .prod-edit .cab-titulo { font-size: 1rem; }
    .prod-edit .cab-meta { column-gap: .75rem !important; row-gap: .15rem !important; font-size: .7rem !important; }
    .prod-edit .cab-meta .material-symbols-rounded { font-size: .95rem !important; }
    .prod-edit .reg-card .card-header { padding: .75rem .75rem 0 !important; }
    .prod-edit .reg-card .card-header h6 { font-size: .9rem; margin-bottom: .15rem !important; }
    .prod-edit .reg-card .card-header p { font-size: .7rem !important; line-height: 1.3; }
    .prod-edit .reg-card .card-header .reg-progreso { margin-top: .5rem !important; }
    .prod-edit .reg-card .card-footer { padding: .75rem !important; }
    .prod-edit .reg-card .card-footer .btn { padding-top: .55rem; padding-bottom: .55rem; }
    .prod-edit .cab-card, .prod-edit .reg-card { margin-bottom: .6rem !important; }
  }
</style>
<div class="container-fluid prod-edit pt-3 pb-5 flex-grow-1">

  <!-- Aviso (en modo demo muestra lo que haría el servidor) -->
  <div id="aviso-demo" class="alert text-white d-none" role="alert"><span class="text-sm font-weight-bold"></span></div>

  <!-- CABECERA -->
  <div class="card cab-card shadow-xs border-radius-xl mb-3">
    <div class="card-body p-3 p-md-4">
      <div class="d-flex align-items-center flex-wrap gap-2 mb-1 mb-md-2">
        <h5 class="cab-titulo font-weight-bolder text-dark mb-0">Producción #<?= str_pad($idProd, 5, '0', STR_PAD_LEFT); ?></h5>
        <span class="badge badge-sm bg-gradient-warning"><?= htmlspecialchars($produccion['estado']); ?></span>
      </div>
      <div class="cab-meta d-flex flex-wrap gap-2 gap-md-3 text-xs text-secondary">
        <?php foreach ($meta as [$icono, $texto]): ?>
          <span class="d-inline-flex align-items-center gap-1">
            <i class="material-symbols-rounded text-sm"><?= $icono; ?></i><?= htmlspecialchars($texto); ?>
          </span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- REGISTRO DEL TURNO -->
  <form id="form-edit" action="<?= URL ?>/produccion/update/<?= $idProd; ?>" method="POST" autocomplete="off">
    <input type="hidden" name="id_produccion" value="<?= $idProd; ?>">
    <div id="campos-envio" class="d-none"></div>

    <div class="card reg-card shadow-xs border-radius-xl overflow-hidden mb-4">

      <div class="card-header p-3 p-md-4 pb-0">
        <h6 class="font-weight-bolder text-dark mb-1">Registro del turno</h6>
        <p class="text-xs text-secondary mb-0 d-md-none">
          <b>Producción</b> se suma al acumulado; <b>Existencia</b> es el conteo actual. Una foto de balanza por recipiente.
        </p>
        <p class="text-xs text-secondary mb-0 d-none d-md-block">
          <b>Producción</b>: lo que acaba de producir; se suma al acumulado.
          <b>Existencia</b>: conteo o peso actual.
          Con la balanza, cada foto suma su peso al campo: tome una por recipiente.
        </p>
        <?php if (!$esEncargado): ?>
          <p class="text-xxs text-warning font-weight-bold mb-0 mt-1 d-flex align-items-center gap-1">
            <i class="material-symbols-rounded text-sm">lock</i>
            La entrada solo puede corregirla el encargado (<?= htmlspecialchars($produccion['responsable']); ?>).
          </p>
        <?php endif; ?>
        <div class="reg-progreso d-flex align-items-center gap-2 gap-md-3 mt-3">
          <span class="text-xxs font-weight-bold text-secondary text-nowrap">Existencia contada</span>
          <div class="progress flex-grow-1">
            <div id="barra-progreso" class="progress-bar bg-gradient-primary" role="progressbar"></div>
          </div>
          <span id="lbl-progreso" class="text-xxs font-weight-bold text-secondary text-nowrap">0 / <?= $total; ?></span>
        </div>
      </div>

      <div class="table-responsive p-0">
        <table id="datatable-edicion" class="table align-items-center mb-0">
          <thead>
            <!-- Producto siempre visible (class="all"). Las demás columnas se ocultan según data-priority
                 (1 = la última en ocultarse) y se ven al tocar el "+" de la fila. -->
            <tr>
              <th class="all text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Producto</th>
              <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Entrada</th>
              <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Producción (+)</th>
              <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Existencia</th>
              <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center pe-3 pe-md-4">Salida / Consumo</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $it):
              $idp  = (int)$it['id_producto'];
              $med  = htmlspecialchars($it['medida']);
              $exis = $it['existencia'] !== null ? $it['existencia'] : '';
            ?>
              <tr data-id="<?= $idp; ?>">

                <!-- Producto -->
                <td class="celda-producto" data-label="Producto">
                  <div class="d-flex align-items-center gap-2 pt-1">
                    <div class="avatar avatar-sm bg-gradient-primary border-radius-md d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                      <i class="material-symbols-rounded text-sm">inventory_2</i>
                    </div>
                    <div class="d-flex flex-column">
                      <span class="text-sm font-weight-bold text-dark"><?= htmlspecialchars($it['producto']); ?></span>
                      <span class="text-xxs text-secondary">Unidad: <?= $med; ?></span>
                    </div>
                  </div>
                </td>

                <!-- Entrada (bloqueada; solo el encargado puede desbloquearla) -->
                <td data-label="Entrada">
                  <div class="celda-campo">
                    <div class="campo">
                      <input type="text" inputmode="decimal" autocomplete="off" data-id="<?= $idp; ?>"
                             class="campo-input inp-entrada" value="<?= $it['entrada']; ?>" readonly>
                      <button type="button" class="campo-btn campo-btn-lock btn-lock" data-id="<?= $idp; ?>" <?= $esEncargado ? '' : 'disabled'; ?>
                              title="<?= $esEncargado ? 'Desbloquear entrada (pide motivo)' : 'Solo el encargado puede corregir la entrada'; ?>"
                              aria-label="Desbloquear entrada">
                        <i class="material-symbols-rounded">lock</i>
                      </button>
                    </div>
                  </div>
                </td>

                <!-- Producción a agregar -->
                <td data-label="Producción (+)">
                  <div class="celda-campo">
                    <?php $campoPeso($idp, 'produccion', $it['medida'], '', '0'); ?>
                  </div>
                </td>

                <!-- Existencia -->
                <td data-label="Existencia">
                  <div class="celda-campo">
                    <?php $campoPeso($idp, 'existencia', $it['medida'], $exis, 'Sin contar'); ?>
                  </div>
                </td>

                <!-- Salida / consumo -->
                <td class="text-center pe-3 pe-md-4 col-salida" data-label="Salida / Consumo">
                  <div class="salida-wrap pt-2">
                    <div>
                      <span class="text-lg font-weight-bolder text-primary out-salida">—</span>
                      <span class="text-xxs text-secondary"><?= $med; ?></span>
                    </div>
                    <div class="text-xxs text-danger font-weight-bold out-aviso d-none">La existencia supera entrada + producción</div>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- MOTIVO DE AJUSTE (aparece si el encargado modifica alguna entrada) -->
      <div id="bloque-motivo" class="px-3 px-md-4 pt-3 d-none">
        <div class="alert alert-warning text-white mb-0" role="alert">
          <div class="d-flex align-items-center gap-2 mb-2">
            <i class="material-symbols-rounded">warning</i>
            <span class="font-weight-bold text-sm">Se modificó al menos una entrada respecto al cierre anterior.</span>
          </div>
          <textarea name="motivo_ajuste" id="motivo_ajuste" rows="2" class="form-control bg-white border px-2"
                    placeholder="Motivo del ajuste (compra, merma, error de conteo, primer registro...)"></textarea>
        </div>
      </div>

      <div class="card-footer p-3 p-md-4 d-flex flex-column flex-sm-row justify-content-end gap-2 border-top">
        <button type="submit" name="accion" value="guardar" class="btn btn-outline-primary mb-0 px-4 d-inline-flex align-items-center gap-2 justify-content-center">
          <i class="material-symbols-rounded text-sm">save</i>Guardar avance
        </button>
        <button type="submit" name="accion" value="cerrar" class="btn bg-gradient-primary text-white mb-0 px-4 d-inline-flex align-items-center gap-2 justify-content-center">
          <i class="material-symbols-rounded text-sm">task_alt</i>Cerrar turno
        </button>
      </div>

    </div>
  </form>
</div>

<!-- ===== Reemplaza COMPLETO el bloque <div class="modal ..." id="modalCamara"> de edit.php ===== -->
<!-- IDs usados por edit.js: camSubtitulo, camVideo, camPreview, camCanvas, camArchivo, camError, camLectura,
     camPeso, camUnidad, camTaper, camNeto, camSubir, camRepetir, camOtra, camUsar. NO existe camCapturar. -->
<div class="modal fade" id="modalCamara" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-0">Foto de la balanza</h5>
          <p class="text-sm text-secondary mb-0" id="camSubtitulo"></p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <div class="ratio ratio-4x3 bg-dark rounded overflow-hidden">
          <video id="camVideo" autoplay playsinline muted></video>
          <img id="camPreview" class="d-none" alt="Foto de la balanza" style="object-fit:contain">
        </div>
        <canvas id="camCanvas" class="d-none"></canvas>
        <input type="file" id="camArchivo" accept="image/*" class="d-none">

        <div id="camLectura" class="text-sm font-weight-bold mt-2 d-none"></div>
        <div id="camError" class="alert alert-danger text-white text-sm py-2 mt-2 d-none" role="alert"></div>

        <!-- Siempre visibles: sirven aunque la lectura automática falle -->
        <div class="row g-2 mt-1">
          <div class="col-7">
            <div class="input-group input-group-outline is-filled">
              <label class="form-label">Peso de la balanza</label>
              <input type="text" id="camPeso" class="form-control" inputmode="decimal" autocomplete="off">
            </div>
          </div>
          <div class="col-5">
            <select id="camUnidad" class="form-select ps-2" aria-label="Unidad de la balanza">
              <option value="g">g</option>
              <option value="kg">kg</option>
              <option value="lb">lb</option>
              <option value="oz">oz</option>
            </select>
          </div>
          <div class="col-12">
            <div class="input-group input-group-outline is-filled">
              <label class="form-label">Descontar taper (misma unidad)</label>
              <input type="text" id="camTaper" class="form-control" inputmode="decimal" value="0" autocomplete="off">
            </div>
          </div>
        </div>
        <p id="camNeto" class="text-sm font-weight-bold mt-2 mb-0"></p>
      </div>

      <div class="modal-footer flex-wrap gap-2 justify-content-between">
        <div class="d-flex gap-2">
          <button type="button" id="camSubir" class="btn btn-sm btn-outline-secondary mb-0">
            <i class="material-symbols-rounded align-middle">photo_library</i> Galería / archivo
          </button>
          <button type="button" id="camRepetir" class="btn btn-sm btn-outline-secondary mb-0 d-none">
            <i class="material-symbols-rounded align-middle">refresh</i> Repetir
          </button>
        </div>
        <div class="d-flex gap-2">
          <button type="button" id="camOtra" class="btn btn-sm btn-outline-primary mb-0 d-none" disabled>Sumar y otra</button>
          <button type="button" id="camUsar" class="btn btn-sm bg-gradient-primary mb-0 d-none" disabled>Sumar</button>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- MODAL: FOTOS SUMADAS (se abre al tocar las miniaturas; aquí se ven todas y se puede quitar alguna) -->
<style>
  #modalFotos .foto-item { position: relative; width: 104px; text-align: center; }
  #modalFotos .foto-item img { width: 104px; height: 104px; object-fit: cover; display: block; border-radius: .75rem; border: 1px solid #e9ecef; }
  #modalFotos .foto-item .foto-item-peso { display: block; margin-top: .35rem; font-size: .8rem; font-weight: 700; color: #344767; }
  #modalFotos .foto-item-quitar { position: absolute; top: 4px; right: 4px; width: 24px; height: 24px; margin: 0; padding: 0; border: 0; border-radius: 50%;
    background: rgba(52,71,103,.85); color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; }
  #modalFotos .foto-item-quitar:hover { background: #f5365c; }
  #modalFotos .foto-item-quitar i { font-size: 1rem; line-height: 1; }
</style>
<div class="modal fade" id="modalFotos" tabindex="-1" aria-labelledby="tituloFotos" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-radius-xl">
      <div class="modal-header border-0 pb-0 px-3 px-md-4 pt-3 pt-md-4">
        <div>
          <h5 class="modal-title font-weight-bolder text-dark mb-0" id="tituloFotos">Fotos de la balanza</h5>
          <p class="text-xs text-secondary mb-0" id="fotosSubtitulo"></p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body px-3 px-md-4">
        <div id="fotosGrid" class="d-flex flex-wrap gap-3"></div>
      </div>
      <div class="modal-footer border-0 pt-0 px-3 px-md-4 pb-3 pb-md-4 d-flex justify-content-between align-items-center">
        <span class="text-sm text-secondary">Total sumado: <b id="fotosTotal" class="text-dark"></b></span>
        <button type="button" class="btn btn-outline-secondary mb-0 px-3" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script src="<?= URL ?>/public/assets/js/produccion/lector_balanza.js"></script>
<script>
  window.PRODUCCION_EDIT = {
    demo: true,                       // true = no envía nada al servidor; con backend ponerlo en false
    base: <?= json_encode(URL) ?>,
    idProduccion: <?= $idProd; ?>,
    fotoObligatoria: true,            // productos por peso: exige foto de balanza al registrar producción o una existencia nueva/cambiada
    items: <?= json_encode($estadoInicial, JSON_UNESCAPED_UNICODE) ?>
  };
</script>
<script src="<?= URL ?>/public/assets/js/produccion/edit.js"></script>