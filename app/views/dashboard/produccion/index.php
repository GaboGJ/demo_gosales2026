<?php
$producciones = [
  ['id' => 10, 'fecha' => '2026-11-26 08:10:00', 'sucursal' => 'Tatu Central', 'responsable' => 'Carlos Mendoza',  'estado' => 'En proceso',  'items' => ['Masa Familiar', 'Masa Mediana', 'Queso Muzzarella', 'Harina']],
  ['id' => 9,  'fecha' => '2026-11-25 16:45:00', 'sucursal' => 'Tatu Central', 'responsable' => 'María Rojas',     'estado' => 'Completada',  'items' => ['Masa Familiar', 'Masa Mediana', 'Queso Cheddar', 'Peperoni', 'Tocino']],
  ['id' => 8,  'fecha' => '2026-11-25 07:50:00', 'sucursal' => 'Tatu Central', 'responsable' => 'Gabriel Guayhua', 'estado' => 'Completada',  'items' => ['Masa Familiar', 'Masa Mediana', 'Queso Muzzarella', 'Calabresa', 'Jamón']],
  ['id' => 7,  'fecha' => '2026-11-24 20:15:00', 'sucursal' => 'Tatu Central', 'responsable' => 'Luis Camacho',    'estado' => 'Anulada',     'items' => ['Queso Muzzarella', 'Carne']],
  ['id' => 6,  'fecha' => '2026-11-21 08:00:00', 'sucursal' => 'Tatu Central', 'responsable' => 'Carlos Mendoza',  'estado' => 'En proceso',  'items' => ['Masa Familiar', 'Masa Mediana', 'Tocino', 'Harina']],
  ['id' => 5,  'fecha' => '2026-11-18 20:05:00', 'sucursal' => 'Tatu Central', 'responsable' => 'Luis Camacho',    'estado' => 'Anulada',     'items' => ['Tocino']],
  ['id' => 4,  'fecha' => '2026-11-17 15:20:00', 'sucursal' => 'Tatu Central', 'responsable' => 'María Rojas',     'estado' => 'Completada',  'items' => ['Masa Familiar', 'Queso Muzzarella', 'Pollo', 'Carne']],
  ['id' => 3,  'fecha' => '2026-11-16 08:30:00', 'sucursal' => 'Tatu Central', 'responsable' => 'Gabriel Guayhua', 'estado' => 'Completada',  'items' => ['Masa Familiar', 'Masa Mediana', 'Queso Cheddar', 'Jamón', 'Harina']],
  ['id' => 2,  'fecha' => '2026-11-20 15:30:00', 'sucursal' => 'Tatu Central', 'responsable' => 'María Rojas',     'estado' => 'Completada',  'items' => ['Masa Familiar', 'Masa Mediana', 'Queso Muzzarella', 'Queso Cheddar', 'Calabresa']],
  ['id' => 1,  'fecha' => '2026-11-20 08:15:00', 'sucursal' => 'Tatu Central', 'responsable' => 'Carlos Mendoza',  'estado' => 'Completada',  'items' => ['Masa Familiar', 'Masa Mediana', 'Queso Muzzarella', 'Calabresa']],
];

function turnoDemo($fecha) {
  $h = (int)date('H', strtotime($fecha));
  return $h < 14 ? 'Mañana' : ($h < 19 ? 'Tarde' : 'Noche');
}
?>
<style>
  .chk-tabla-prod {
    appearance: none;
    -webkit-appearance: none;
    width: 18px;
    height: 18px;
    border: 1.5px solid #cbd5e1;
    border-radius: 4px;
    background-color: #ffffff;
    background-position: center;
    background-repeat: no-repeat;
    background-size: 12px 12px;
    cursor: pointer;
    vertical-align: middle;
    margin: 0;
    transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
  }
  .chk-tabla-prod:hover {
    border-color: #e91e63;
  }
  .chk-tabla-prod:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(233, 30, 99, 0.15);
  }
  .chk-tabla-prod:checked {
    background-color: #e91e63;
    border-color: #e91e63;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='3.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='20 6 9 17 4 12'/%3E%3C/svg%3E");
  }
  .chk-tabla-prod:indeterminate {
    background-color: #e91e63;
    border-color: #e91e63;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='3.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cline x1='5' y1='12' x2='19' y2='12'/%3E%3C/svg%3E");
  }
</style>

<div class="container-fluid py-3 flex-grow-1">
  <div class="card border-0 shadow-sm border-radius-xl p-3 mb-3">
    <h4 class="font-weight-bolder text-dark mb-0">Gestión de Producción</h4>
    <p class="text-xs text-secondary mb-0">Filtra en la tabla (por ejemplo escribiendo "Mañana"), selecciona uno o varios registros e imprímelos.</p>
  </div>

  <div class="card border-0 shadow-sm border-radius-xl overflow-hidden">
    <div class="card-header bg-white p-3 d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2">
      <h6 class="font-weight-bolder text-dark mb-0">Listado de Producciones</h6>

      <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
        <button type="button" id="btnImprimirSeleccionados" class="btn btn-outline-primary mb-0 px-3 w-100 w-sm-auto d-none align-items-center justify-content-center gap-1" onclick="imprimirSeleccionados()">
          <i class="material-symbols-rounded text-sm">print</i>
          <span>Imprimir Seleccionados (<span id="contadorSeleccionados">0</span>)</span>
        </button>

        <button type="button" class="btn bg-gradient-primary text-white mb-0 px-3 w-100 w-sm-auto d-inline-flex align-items-center justify-content-center gap-1" data-bs-toggle="modal" data-bs-target="#modalNuevaProduccion">
          <i class="material-symbols-rounded text-sm">add</i>
          <span>Abrir Producción</span>
        </button>
      </div>
    </div>

    <div class="table-responsive px-2 px-sm-3 pb-3">
      <table id="datatable-produccion" class="table table-borderless align-items-center mb-0 w-100">
        <thead>
          <tr>
            <th class="text-center py-2" style="width: 58px;">
              <div class="d-inline-flex align-items-center justify-content-center gap-1">
                <input type="checkbox" class="chk-tabla-prod" id="checkTodosVisibles" title="Seleccionar todos los registros visibles">
              </div>
            </th>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-2">Nº / Fecha</th>
            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-2">Turno</th>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-2">Productos / Encargado</th>
            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-2">Estado</th>
            <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-2">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($producciones as $p):
            $turno = turnoDemo($p['fecha']);
            $estadoKey = strtolower($p['estado']);

            [$turnoClass, $turnoIcon] = match ($turno) {
              'Tarde' => ['bg-gradient-warning', 'light_mode'],
              'Noche' => ['bg-gradient-dark',    'dark_mode'],
              default => ['bg-gradient-info',    'wb_twilight'],
            };

            $badgeEstado = match ($estadoKey) {
              'completada' => 'bg-gradient-success',
              'anulada'    => 'bg-gradient-danger',
              default      => 'bg-gradient-warning',
            };

            $resumen = implode(', ', array_slice($p['items'], 0, 3)) . (count($p['items']) > 3 ? ' y ' . (count($p['items']) - 3) . ' más' : '');
          ?>
            <tr>
              <td class="align-middle py-2 text-center">
                <div class="d-inline-flex align-items-center justify-content-center gap-1 celda-sel-fila">
                  <input type="checkbox" class="chk-tabla-prod check-fila-produccion" value="<?= (int)$p['id']; ?>" aria-label="Seleccionar planilla #<?= str_pad((int)$p['id'], 5, '0', STR_PAD_LEFT); ?>">
                </div>
              </td>
              <td class="py-2" data-order="<?= strtotime($p['fecha']); ?>">
                <div class="d-flex align-items-center gap-2 gap-sm-3">
                  <div class="avatar avatar-md bg-gradient-primary border-radius-lg shadow-xs d-flex align-items-center justify-content-center flex-shrink-0 text-white">
                    <i class="material-symbols-rounded">skillet</i>
                  </div>
                  <div>
                    <h6 class="mb-0 text-sm font-weight-bold text-dark">#<?= str_pad((int)$p['id'], 5, '0', STR_PAD_LEFT); ?></h6>
                    <span class="text-xxs text-secondary font-weight-bold">
                      <?= date('d/m/Y', strtotime($p['fecha'])); ?> <span class="text-dark"><?= date('H:i', strtotime($p['fecha'])); ?></span>
                    </span>
                    <div class="text-xxs text-secondary"><?= htmlspecialchars($p['sucursal']); ?></div>
                  </div>
                </div>
              </td>
              <td class="align-middle py-2 text-center" data-search="<?= htmlspecialchars($turno); ?>">
                <span class="badge badge-sm <?= $turnoClass; ?> border-radius-pill px-3 py-1 font-weight-bold d-inline-flex align-items-center gap-1">
                  <i class="material-symbols-rounded text-sm"><?= $turnoIcon; ?></i><?= $turno; ?>
                </span>
              </td>
              <td class="align-middle py-2">
                <div class="text-xs font-weight-bold text-dark"><?= htmlspecialchars($resumen); ?></div>
                <div class="text-xxs text-secondary"><?= count($p['items']); ?> producto(s) · Encargado: <span class="text-primary"><?= htmlspecialchars($p['responsable']); ?></span></div>
              </td>
              <td class="align-middle py-2 text-center">
                <span class="badge badge-sm badge-estado <?= $badgeEstado; ?> border-radius-pill px-3 py-1"><?= htmlspecialchars($p['estado']); ?></span>
              </td>
              <td class="align-middle py-2 text-end">
                <div class="d-flex align-items-center justify-content-end gap-1">
                  <button type="button" class="btn btn-link text-primary p-2 mb-0" title="Ver Planilla" onclick="abrirShowProduccion(<?= (int)$p['id']; ?>)">
                    <i class="material-symbols-rounded text-sm">visibility</i>
                  </button>
                  <?php if ($estadoKey === 'en proceso'): ?>
                    <a href="<?= URL ?>/produccion/edit" class="btn btn-link text-primary p-2 mb-0 btn-editar" title="Registrar Producción">
                      <i class="material-symbols-rounded text-sm">edit_note</i>
                    </a>
                  <?php endif; ?>
                  <button type="button" class="btn btn-link text-primary p-2 mb-0" title="Imprimir Planilla" onclick="imprimirProduccion(<?= (int)$p['id']; ?>)">
                    <i class="material-symbols-rounded text-sm">print</i>
                  </button>
                  <?php if ($estadoKey !== 'anulada'): ?>
                    <button type="button" class="btn btn-link text-danger p-2 mb-0 btn-anular" title="Anular Producción" onclick="anularDemo(this)">
                      <i class="material-symbols-rounded text-sm">delete</i>
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php
require __DIR__ . '/new.php';
require __DIR__ . '/show.php';
?>

<!-- Modal Seleccionar Tipo de Impresión por Fila -->
<div class="modal fade" id="modalTipoImpresion" tabindex="-1" aria-labelledby="modalTipoImpresionLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-radius-xl shadow-lg">
      <div class="modal-header bg-gradient-primary text-white border-radius-top-xl py-3">
        <h6 class="modal-title font-weight-bold text-white mb-0 d-flex align-items-center gap-2" id="modalTipoImpresionLabel">
          <i class="material-symbols-rounded text-sm">print</i>
          <span>¿Cómo deseas imprimir la planilla <span id="codigoPlanillaImprimir">#00000</span>?</span>
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body p-4">
        <p class="text-xs text-secondary mb-3">Elige el formato de impresión para este registro:</p>

        <div class="d-grid gap-2">
          <button type="button" class="btn btn-outline-primary text-start d-flex align-items-start gap-3 p-3 mb-0" onclick="ejecutarImpresionConTipo('completo')">
            <i class="material-symbols-rounded mt-1">description</i>
            <div>
              <div class="font-weight-bold text-sm">Completo</div>
              <div class="text-xxs text-secondary">Imprime la planilla completa con todos los datos y cálculos actuales.</div>
            </div>
          </button>

          <button type="button" class="btn btn-outline-primary text-start d-flex align-items-start gap-3 p-3 mb-0" onclick="ejecutarImpresionConTipo('resumen')">
            <i class="material-symbols-rounded mt-1">summarize</i>
            <div>
              <div class="font-weight-bold text-sm">Resumen</div>
              <div class="text-xxs text-secondary">Arriba datos de producción registrados y abajo existencia con datos registrados.</div>
            </div>
          </button>

          <button type="button" class="btn btn-outline-primary text-start d-flex align-items-start gap-3 p-3 mb-0" onclick="ejecutarImpresionConTipo('vacio')">
            <i class="material-symbols-rounded mt-1">edit_off</i>
            <div>
              <div class="font-weight-bold text-sm">Vacío</div>
              <div class="text-xxs text-secondary">Igual que el resumen, pero sin los datos registrados para completar manualmente.</div>
            </div>
          </button>
        </div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-link text-secondary mb-0" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const BASE_URL = <?= json_encode(URL) ?>;
  const seleccionados = new Set();
  let idPendienteImpresion = null;
  let onFinishPendienteImpresion = null;

  function fijarOrdenMasYCheck() {
    document.querySelectorAll('#datatable-produccion tbody tr').forEach(tr => {
      if (tr.classList.contains('child')) return;
      const td = tr.querySelector('td:first-child');
      const wrap = td ? td.querySelector('.celda-sel-fila') : null;
      const chk = td ? td.querySelector('.check-fila-produccion') : null;
      const dtrBtn = td ? td.querySelector('.dtr-control') : null;

      if (!wrap) return;
      if (chk && chk.parentElement !== wrap) {
        wrap.appendChild(chk);
      }
      if (dtrBtn && dtrBtn.parentElement !== wrap) {
        wrap.appendChild(dtrBtn);
      }
    });
  }

  function lanzarImpresionIframe(url, onFinish) {
    const iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.right = '0';
    iframe.style.bottom = '0';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = '0';
    iframe.src = url;

    let finalizado = false;
    const terminar = () => {
      if (finalizado) return;
      finalizado = true;
      setTimeout(() => iframe.remove(), 300);
      if (typeof onFinish === 'function') onFinish();
    };

    iframe.onload = function () {
      try {
        const win = iframe.contentWindow;
        if (!win) return terminar();
        win.addEventListener('afterprint', terminar, { once: true });
        win.focus();
        win.print();
        setTimeout(terminar, 1500);
      } catch (e) {
        terminar();
      }
    };

    document.body.appendChild(iframe);
  }

  function actualizarBarraSeleccion() {
    const btn = document.getElementById('btnImprimirSeleccionados');
    const contador = document.getElementById('contadorSeleccionados');
    const checkTodos = document.getElementById('checkTodosVisibles');
    if (!btn || !contador) return;

    contador.textContent = String(seleccionados.size);
    if (seleccionados.size > 0) {
      btn.classList.remove('d-none');
      btn.classList.add('d-inline-flex');
    } else {
      btn.classList.add('d-none');
      btn.classList.remove('d-inline-flex');
    }

    if (checkTodos) {
      const checksVisibles = Array.from(document.querySelectorAll('#datatable-produccion tbody tr:not(.child) .check-fila-produccion'));
      if (checksVisibles.length === 0) {
        checkTodos.checked = false;
        checkTodos.indeterminate = false;
      } else {
        const marcadasVisibles = checksVisibles.filter(c => c.checked).length;
        checkTodos.checked = marcadasVisibles === checksVisibles.length;
        checkTodos.indeterminate = marcadasVisibles > 0 && marcadasVisibles < checksVisibles.length;
      }
    }
  }

  function sincronizarChecksEnPagina() {
    fijarOrdenMasYCheck();
    document.querySelectorAll('#datatable-produccion tbody tr:not(.child) .check-fila-produccion').forEach(cb => {
      cb.checked = seleccionados.has(cb.value);
    });
    actualizarBarraSeleccion();
  }

  window.imprimirProduccion = function (idProduccion, onFinish) {
    if (!idProduccion) return;
    idPendienteImpresion = idProduccion;
    onFinishPendienteImpresion = typeof onFinish === 'function' ? onFinish : null;

    const spanCodigo = document.getElementById('codigoPlanillaImprimir');
    if (spanCodigo) {
      spanCodigo.textContent = '#' + String(idProduccion).padStart(5, '0');
    }

    const modalEl = document.getElementById('modalTipoImpresion');
    if (modalEl && window.bootstrap) {
      bootstrap.Modal.getOrCreateInstance(modalEl).show();
    } else {
      window.ejecutarImpresionConTipo('completo');
    }
  };

  window.ejecutarImpresionConTipo = function (tipo) {
    if (!idPendienteImpresion) return;
    const id = idPendienteImpresion;
    const cb = onFinishPendienteImpresion;
    const tipoValido = ['completo', 'resumen', 'vacio'].includes(tipo) ? tipo : 'completo';

    const modalEl = document.getElementById('modalTipoImpresion');
    if (modalEl && window.bootstrap) {
      const inst = bootstrap.Modal.getInstance(modalEl);
      if (inst) inst.hide();
    }

    idPendienteImpresion = null;
    onFinishPendienteImpresion = null;

    const url = `${BASE_URL}/produccion/imprimir?ids=${encodeURIComponent(id)}&tipo=${encodeURIComponent(tipoValido)}`;
    lanzarImpresionIframe(url, cb);
  };

  window.imprimirSeleccionados = function () {
    const ids = Array.from(seleccionados);
    if (!ids.length) {
      alert('Selecciona al menos una fila de la tabla para imprimir.');
      return;
    }
    const url = `${BASE_URL}/produccion/imprimir?ids=${encodeURIComponent(ids.join(','))}`;
    lanzarImpresionIframe(url);
  };

  window.abrirShowProduccion = function (id) {
    const modalEl = document.getElementById('modalPlanilla');
    if (!modalEl || !window.bootstrap) return;
    const codigoEl = modalEl.querySelector('[data-codigo]');
    if (codigoEl) {
      const cod = String(id).padStart(5, '0');
      codigoEl.dataset.codigo = cod;
      const b = codigoEl.querySelector('b');
      if (b) b.textContent = '#' + cod;
    }
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  };

  window.anularDemo = function (btn) {
    if (!confirm('¿Anular esta producción?')) return;
    const tr = btn.closest('tr');
    const badge = tr.querySelector('.badge-estado');
    badge.className = 'badge badge-sm badge-estado bg-gradient-danger border-radius-pill px-3 py-1';
    badge.textContent = 'Anulada';
    tr.querySelectorAll('.btn-editar, .btn-anular').forEach((b) => b.remove());
  };

  window.addEventListener('load', function () {
    const ver = new URLSearchParams(location.search).get('ver');
    if (ver) window.abrirShowProduccion(parseInt(ver, 10));

    let dt = null;
    if (window.jQuery && jQuery.fn.DataTable) {
      if (jQuery.fn.DataTable.isDataTable('#datatable-produccion')) {
        dt = jQuery('#datatable-produccion').DataTable();
      } else {
        dt = jQuery('#datatable-produccion').DataTable({
          order: [[1, 'desc']],
          columnDefs: [{ orderable: false, targets: [0, 5] }],
          language: { search: 'Buscar:', paginate: { previous: 'Anterior', next: 'Siguiente' } }
        });
      }
      dt.on('draw responsive-display', function () {
        setTimeout(sincronizarChecksEnPagina, 0);
      });
    }

    document.addEventListener('click', function (e) {
      if (e.target && (e.target.classList.contains('check-fila-produccion') || e.target.id === 'checkTodosVisibles')) {
        e.stopPropagation();
      }
      if (e.target && e.target.closest('.dtr-control')) {
        setTimeout(sincronizarChecksEnPagina, 0);
      }
    }, true);

    document.addEventListener('change', function (e) {
      if (e.target && e.target.classList.contains('check-fila-produccion')) {
        e.stopPropagation();
        const val = e.target.value;
        if (e.target.checked) {
          seleccionados.add(val);
        } else {
          seleccionados.delete(val);
        }
        actualizarBarraSeleccion();
      }

      if (e.target && e.target.id === 'checkTodosVisibles') {
        e.stopPropagation();
        const marcar = e.target.checked;
        document.querySelectorAll('#datatable-produccion tbody tr:not(.child) .check-fila-produccion').forEach(cb => {
          cb.checked = marcar;
          if (marcar) {
            seleccionados.add(cb.value);
          } else {
            seleccionados.delete(cb.value);
          }
        });
        actualizarBarraSeleccion();
      }
    });

    sincronizarChecksEnPagina();
  });
})();
</script>