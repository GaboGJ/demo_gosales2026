<?php
// require_once __DIR__ . '/detail.php';  // <- cuando tengas el backend, descomenta y borra el bloque de prueba

/* ============ DATOS DE PRUEBA (reemplazar por $producciones del detail.php) ============ */
$producciones = [
  [
    'id_produccion' => 6,
    'fecha_produccion' => '2026-11-21 08:00:00',
    'sucursal' => 'Tatu Central',
    'responsable' => 'Carlos Mendoza',
    'costo_produccion' => 0,
    'estado' => 'En proceso',
    'items' => [
      ['producto' => 'Masa Familiar', 'cantidad' => 30,   'medida' => 'u'],
      ['producto' => 'Masa Mediana',  'cantidad' => 40,   'medida' => 'u'],
      ['producto' => 'Tocino',        'cantidad' => 1000, 'medida' => 'g'],
      ['producto' => 'Harina',        'cantidad' => 21,   'medida' => 'kg'],
    ],
  ],
  [
    'id_produccion' => 2,
    'fecha_produccion' => '2026-11-20 15:30:00',
    'sucursal' => 'Tatu Central',
    'responsable' => 'María Rojas',
    'costo_produccion' => 835.00,
    'estado' => 'Completada',
    'items' => [
      ['producto' => 'Masa Familiar',    'cantidad' => 53,    'medida' => 'u'],
      ['producto' => 'Masa Mediana',     'cantidad' => 60,    'medida' => 'u'],
      ['producto' => 'Queso Muzzarella', 'cantidad' => 38066, 'medida' => 'g'],
      ['producto' => 'Queso Cheddar',    'cantidad' => 2955,  'medida' => 'g'],
      ['producto' => 'Calabresa',        'cantidad' => 3349,  'medida' => 'g'],
    ],
  ],
  [
    'id_produccion' => 1,
    'fecha_produccion' => '2026-11-20 08:15:00',
    'sucursal' => 'Tatu Central',
    'responsable' => 'Carlos Mendoza',
    'costo_produccion' => 412.50,
    'estado' => 'Completada',
    'items' => [
      ['producto' => 'Masa Familiar',    'cantidad' => 28,    'medida' => 'u'],
      ['producto' => 'Masa Mediana',     'cantidad' => 43,    'medida' => 'u'],
      ['producto' => 'Queso Muzzarella', 'cantidad' => 17616, 'medida' => 'g'],
      ['producto' => 'Calabresa',        'cantidad' => 7000,  'medida' => 'g'],
    ],
  ],
  [
    'id_produccion' => 4,
    'fecha_produccion' => '2026-11-19 14:10:00',
    'sucursal' => 'Tatu Central',
    'responsable' => 'María Rojas',
    'costo_produccion' => 1120.00,
    'estado' => 'Completada',
    'items' => [
      ['producto' => 'Masa Familiar',    'cantidad' => 40,    'medida' => 'u'],
      ['producto' => 'Queso Muzzarella', 'cantidad' => 38066, 'medida' => 'g'],
      ['producto' => 'Peperoni',         'cantidad' => 3818,  'medida' => 'g'],
      ['producto' => 'Jamón',            'cantidad' => 3040,  'medida' => 'g'],
      ['producto' => 'Carne',            'cantidad' => 2445,  'medida' => 'g'],
    ],
  ],
  [
    'id_produccion' => 3,
    'fecha_produccion' => '2026-11-19 08:40:00',
    'sucursal' => 'Tatu Central',
    'responsable' => 'Carlos Mendoza',
    'costo_produccion' => 590.25,
    'estado' => 'Completada',
    'items' => [
      ['producto' => 'Masa Familiar', 'cantidad' => 53,   'medida' => 'u'],
      ['producto' => 'Masa Mediana',  'cantidad' => 60,   'medida' => 'u'],
      ['producto' => 'Queso Cheddar', 'cantidad' => 2955, 'medida' => 'g'],
    ],
  ],
  [
    'id_produccion' => 5,
    'fecha_produccion' => '2026-11-18 20:05:00',
    'sucursal' => 'Tatu Central',
    'responsable' => 'Luis Camacho',
    'costo_produccion' => 210.00,
    'estado' => 'Anulada',
    'items' => [
      ['producto' => 'Tocino', 'cantidad' => 1000, 'medida' => 'g'],
    ],
  ],
];

// Turno derivado de la hora de fecha_produccion (la tabla `producciones` no tiene columna turno)
if (!function_exists('turnoProduccion')) {
  function turnoProduccion($fecha) {
    $h = (int)date('H', strtotime($fecha));
    if ($h < 14) return 'Mañana';
    if ($h < 19) return 'Tarde';
    return 'Noche';
  }
}
/* ============ FIN DATOS DE PRUEBA ============ */
?>
<!-- CONTENEDOR PRINCIPAL -->
<div class="container-fluid py-3 flex-grow-1">

  <!-- SECCIÓN SUPERIOR: TÍTULO Y DESCRIPCIÓN -->
  <div class="row mb-3">
    <div class="col-12">
      <div class="card border-0 shadow-sm border-radius-xl p-3 p-md-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
          <div>
            <h4 class="font-weight-bolder text-dark mb-1">Gestión de Producción</h4>
            <p class="text-xs text-secondary mb-0">Controla la producción diaria de masas, embutidos y quesos por fecha y turno.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- LISTADO DE PRODUCCIONES -->
  <div class="row">
    <div class="col-12">
      <div class="card border-0 shadow-sm border-radius-xl overflow-hidden mb-4">

        <div class="card-header bg-white p-3 p-md-4 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
          <div>
            <h6 class="font-weight-bolder text-dark mb-0">Listado de Producciones</h6>
            <p class="text-xs text-secondary mb-0">Abra una producción por turno, regístrela con el lápiz y consulte la planilla con el ojo.</p>
          </div>
          <button type="button" class="btn bg-gradient-primary text-white mb-0 border-radius-md px-3 shadow-sm d-inline-flex align-items-center gap-2 w-100 w-sm-auto justify-content-center" data-bs-toggle="modal" data-bs-target="#modalNuevaProduccion">
            <i class="material-symbols-rounded text-sm">add_box</i>
            <span class="font-weight-bold">Abrir Producción</span>
          </button>
        </div>

        <hr class="horizontal dark my-0 opacity-2">

        <div class="card-body px-0 pt-0 pb-2">
          <div class="table-responsive p-0">
            <table id="datatable-produccion" class="table table-borderless align-items-center mb-0 w-100">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 ps-4 ps-md-5 pe-4 border-top border-bottom border-light">Nº / Fecha</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Turno</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Productos / Responsable</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Costo</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 px-3 border-top border-bottom border-light">Estado</th>
                  <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3 pe-4 pe-md-5 ps-4 border-top border-bottom border-light">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($producciones)): ?>
                  <?php foreach ($producciones as $p):
                    $turno = turnoProduccion($p['fecha_produccion']);
                    $estadoKey = strtolower($p['estado']);

                    // Color del estado (semántico: aquí sí usamos otros colores)
                    $estadoClass = 'bg-gradient-warning';
                    if ($estadoKey === 'completada') {
                        $estadoClass = 'bg-gradient-success';
                    } elseif ($estadoKey === 'anulada') {
                        $estadoClass = 'bg-gradient-danger';
                    }

                    // Color e icono según turno
                    $turnoClass = 'bg-gradient-info';
                    $turnoIcon  = 'wb_twilight';
                    if ($turno === 'Tarde') {
                        $turnoClass = 'bg-gradient-warning';
                        $turnoIcon  = 'light_mode';
                    } elseif ($turno === 'Noche') {
                        $turnoClass = 'bg-gradient-dark';
                        $turnoIcon  = 'dark_mode';
                    }

                    // Resumen de productos: primeros 3 + "y N más"
                    $nombres = array_column($p['items'], 'producto');
                    $resumen = implode(', ', array_slice($nombres, 0, 3));
                    if (count($nombres) > 3) {
                        $resumen .= ' y ' . (count($nombres) - 3) . ' más';
                    }
                  ?>
                    <tr>
                      <td class="py-3 ps-4 ps-md-5 pe-4" data-order="<?= strtotime($p['fecha_produccion']); ?>">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-md bg-gradient-primary border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                            <i class="material-symbols-rounded">skillet</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm font-weight-bold text-dark">#<?= str_pad((int)$p['id_produccion'], 5, '0', STR_PAD_LEFT); ?></h6>
                            <span class="text-xxs text-secondary font-weight-bold">
                              <?= date('d/m/Y', strtotime($p['fecha_produccion'])); ?>
                              <span class="text-dark"><?= date('H:i', strtotime($p['fecha_produccion'])); ?></span>
                            </span>
                            <span class="text-xxs text-secondary"><?= htmlspecialchars($p['sucursal']); ?></span>
                          </div>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm <?= $turnoClass; ?> border-radius-pill px-3 py-1 font-weight-bold d-inline-flex align-items-center gap-1">
                          <i class="material-symbols-rounded text-sm"><?= $turnoIcon; ?></i><?= $turno; ?>
                        </span>
                      </td>
                      <td class="py-3 px-3 align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold text-dark"><?= htmlspecialchars($resumen); ?></span>
                          <span class="text-xxs text-secondary font-weight-bold">
                            <?= count($p['items']); ?> producto(s) ·
                            Encargado: <span class="text-primary"><?= htmlspecialchars($p['responsable']); ?></span>
                          </span>
                        </div>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="text-sm font-weight-bolder text-primary">Bs. <?= number_format($p['costo_produccion'], 2); ?></span>
                      </td>
                      <td class="align-middle text-center py-3 px-3">
                        <span class="badge badge-sm badge-estado <?= $estadoClass; ?> border-radius-pill px-3 py-1 font-weight-bold"><?= htmlspecialchars($p['estado']); ?></span>
                      </td>
                      <td class="align-middle text-end py-3 pe-4 pe-md-5 ps-4">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                          <button type="button" class="btn btn-link text-primary p-2 mb-0" title="Ver Planilla" onclick="verDetalleProduccion(<?= (int)$p['id_produccion']; ?>)">
                            <i class="material-symbols-rounded text-sm">visibility</i>
                          </button>
                          <?php if ($estadoKey === 'en proceso'): ?>
                          <a href="<?= URL ?>/produccion/edit" class="btn btn-link text-primary p-2 mb-0 btn-editar" title="Registrar Producción">
                            <i class="material-symbols-rounded text-sm">edit_note</i>
                          </a>
                          <?php endif; ?>
                          <button type="button" class="btn btn-link text-primary p-2 mb-0" title="Imprimir Planilla" onclick="imprimirProduccion(<?= (int)$p['id_produccion']; ?>)">
                            <i class="material-symbols-rounded text-sm">print</i>
                          </button>
                          <?php if ($estadoKey !== 'anulada'): ?>
                          <button type="button" class="btn btn-link text-danger p-2 mb-0 btn-anular" title="Anular Producción" onclick="anularProduccion(<?= (int)$p['id_produccion']; ?>, this)">
                            <i class="material-symbols-rounded text-sm">delete</i>
                          </button>
                          <?php endif; ?>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>

</div>

<?php
// Modales: cada uno vive en su archivo y trae sus propios datos de prueba
require __DIR__ . '/new.php';    // abrir producción
require __DIR__ . '/show.php';   // planilla
?>

<script>
(function () {
  // DEMO: anular solo cambia la fila en pantalla. Con backend: POST a /produccion/anular/{id} y recargar.
  window.anularProduccion = function (id, btn) {
    if (!confirm('¿Anular esta producción? Esta acción no se puede deshacer.')) return;
    const tr = btn.closest('tr');
    const badge = tr.querySelector('.badge-estado');
    badge.className = 'badge badge-sm badge-estado bg-gradient-danger border-radius-pill px-3 py-1 font-weight-bold';
    badge.textContent = 'Anulada';
    tr.querySelectorAll('.btn-editar, .btn-anular').forEach((b) => b.remove());
  };

  window.addEventListener('load', function () {
    const params = new URLSearchParams(location.search);

    // /produccion/new  -> abre el modal de nueva producción
    if (params.get('nueva') && window.bootstrap) {
      bootstrap.Modal.getOrCreateInstance(document.getElementById('modalNuevaProduccion')).show();
    }
    // /produccion/show/ID -> abre la planilla
    if (params.get('ver') && window.verDetalleProduccion) {
      window.verDetalleProduccion(parseInt(params.get('ver'), 10));
    }

    // DataTable del listado (si el layout ya lo inicializó, no se vuelve a crear)
    if (window.jQuery && jQuery.fn.DataTable && !jQuery.fn.DataTable.isDataTable('#datatable-produccion')) {
      jQuery('#datatable-produccion').DataTable({
        order: [[0, 'desc']],
        columnDefs: [{ orderable: false, targets: 5 }],
        language: {
          search: 'Buscar:', lengthMenu: 'Mostrar _MENU_', info: 'Mostrando _START_ a _END_ de _TOTAL_',
          infoEmpty: 'Sin registros', zeroRecords: 'No se encontraron resultados',
          paginate: { previous: 'Anterior', next: 'Siguiente' }
        }
      });
    }
  });
})();
</script>