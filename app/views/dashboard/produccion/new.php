<?php
/* MODAL "ABRIR PRODUCCIÓN" — se incluye desde index.php.

   ============ DATOS DE PRUEBA (reemplazar por datos del backend) ============ */
$sucursalActual = 'Tatu Central';
$encargados = [                        // usuarios de la sucursal
  ['id_usuario' => 1, 'nombre' => 'Carlos Mendoza'],
  ['id_usuario' => 2, 'nombre' => 'María Rojas'],
  ['id_usuario' => 3, 'nombre' => 'Luis Camacho'],
];
$ultimoCierre = '20/11/2026 · Tarde';  // null si no existe un cierre previo

$h = (int)date('H');
$turnoActual = $h < 14 ? 'Mañana' : ($h < 19 ? 'Tarde' : 'Noche');
/* ============ FIN DATOS DE PRUEBA ============ */
?>
<div class="modal fade" id="modalNuevaProduccion" tabindex="-1" aria-labelledby="tituloNuevaProduccion" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form id="form-nueva-produccion" class="modal-content border-radius-xl" action="<?= URL ?>/produccion/save" method="POST" autocomplete="off">
      <div class="modal-header border-0 pb-0 px-4 pt-4">
        <div>
          <h5 class="modal-title font-weight-bolder text-dark mb-0" id="tituloNuevaProduccion">Abrir Producción</h5>
          <p class="text-xs text-secondary mb-0">Indique los datos iniciales; luego registrará la producción desde la edición.</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body px-4">
        <div class="row g-3">
          <div class="col-12 col-sm-6">
            <label class="form-label text-xs text-secondary font-weight-bold text-uppercase mb-1">Fecha</label>
            <input type="date" name="fecha" class="form-control border px-2" value="<?= date('Y-m-d'); ?>" required>
          </div>
          <div class="col-12 col-sm-6">
            <label class="form-label text-xs text-secondary font-weight-bold text-uppercase mb-1">Turno</label>
            <select name="turno" class="form-select border px-2" required>
              <?php foreach (['Mañana', 'Tarde', 'Noche'] as $t): ?>
                <option value="<?= $t; ?>" <?= $t === $turnoActual ? 'selected' : ''; ?>><?= $t; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label text-xs text-secondary font-weight-bold text-uppercase mb-1">Sucursal</label>
            <input type="text" class="form-control border px-2 bg-light" value="<?= htmlspecialchars($sucursalActual); ?>" readonly>
          </div>
          <div class="col-12">
            <label class="form-label text-xs text-secondary font-weight-bold text-uppercase mb-1">Encargado</label>
            <select name="id_encargado" class="form-select border px-2" required>
              <option value="" selected disabled>Seleccione al encargado</option>
              <?php foreach ($encargados as $e): ?>
                <option value="<?= (int)$e['id_usuario']; ?>"><?= htmlspecialchars($e['nombre']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <div class="d-flex align-items-start gap-2 p-3 bg-light border-radius-md">
              <i class="material-symbols-rounded text-sm text-primary">info</i>
              <span class="text-xs text-secondary">
                <?php if ($ultimoCierre): ?>
                  La entrada de cada producto se cargará de la existencia del último cierre (<?= htmlspecialchars($ultimoCierre); ?>).
                <?php else: ?>
                  No hay un cierre previo: la entrada empieza en 0 y el encargado podrá corregirla en la edición.
                <?php endif; ?>
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer border-0 pt-0 px-4 pb-4 d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary mb-0 border-radius-md px-4" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn bg-gradient-primary text-white mb-0 border-radius-md px-4 shadow-sm d-inline-flex align-items-center gap-2">
          <i class="material-symbols-rounded text-sm">play_arrow</i>
          <span class="font-weight-bold">Abrir y registrar</span>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
(function () {
  const DEMO = true;   // true = no envía nada al servidor; con backend ponerlo en false
  const BASE = <?= json_encode(URL) ?>;

  document.getElementById('form-nueva-produccion').addEventListener('submit', function (e) {
    if (!DEMO) return;   // con backend: POST normal a /produccion/save y redirigir a edit/{id}
    e.preventDefault();
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalNuevaProduccion')).hide();
    window.location.href = BASE + '/produccion/edit';
  });
})();
</script>