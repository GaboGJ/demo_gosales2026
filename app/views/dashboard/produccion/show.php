<div class="modal fade" id="modalPlanilla" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-radius-xl">

      <div class="modal-header border-0 pb-1 px-4 pt-4">
        <div>
          <h5 class="modal-title font-weight-bolder text-dark mb-0">Planilla de Producción</h5>
          <p class="text-xs text-secondary mb-0">Vista del registro del turno.</p>
        </div>
        <button type="button" class="btn btn-link text-dark p-0 mb-0 ms-auto" data-bs-dismiss="modal" aria-label="Cerrar">
          <i class="material-symbols-rounded">close</i>
        </button>
      </div>

      <div class="modal-body px-4 pt-2 pb-4" id="planillaContenido">
        <!-- Simulación de 1 sola planilla obtenida desde el backend -->
        <div class="border-radius-lg overflow-hidden border"
             data-id="6"
             data-codigo="00006"
             data-fecha="21/11/2026"
             data-turno="Mañana"
             data-sucursal="Tatu Central"
             data-responsable="Carlos Mendoza"
             data-estado="En proceso">

          <div class="p-3 bg-gradient-primary text-center">
            <h5 class="font-weight-bolder text-white text-uppercase mb-0">Tatu Central</h5>
          </div>

          <div class="p-3 border-bottom">
            <div class="row g-2 text-sm">
              <div class="col-6 col-md-3">
                <span class="text-xxs text-secondary text-uppercase d-block">Planilla</span>
                <b>#00006</b> <span class="badge badge-sm bg-gradient-warning ms-1">En proceso</span>
              </div>
              <div class="col-6 col-md-3">
                <span class="text-xxs text-secondary text-uppercase d-block">Fecha</span>
                <b>21/11/2026</b>
              </div>
              <div class="col-6 col-md-3">
                <span class="text-xxs text-secondary text-uppercase d-block">Turno</span>
                <b>Mañana</b>
              </div>
              <div class="col-6 col-md-3">
                <span class="text-xxs text-secondary text-uppercase d-block">Encargado</span>
                <b class="text-primary">Carlos Mendoza</b>
              </div>
            </div>

            <div class="mt-3">
              <div class="d-flex justify-content-between text-xxs font-weight-bold text-secondary text-uppercase mb-1">
                <span>Existencia contada</span>
                <span>1 / 11</span>
              </div>
              <div class="progress" style="height: 7px;">
                <div class="progress-bar bg-gradient-primary" style="width: 9%;"></div>
              </div>
            </div>
          </div>

<div class="table-responsive px-3 pt-2">
  <table id="datatable-show" class="table table-borderless align-items-center mb-1 w-100">
    <thead>
      <tr>
        <th class="all text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-2 border-bottom border-light">Producto</th>
        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-2 px-2 border-bottom border-light">Entrada</th>
        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-2 px-2 border-bottom border-light">Producción</th>
        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-2 px-2 border-bottom border-light">Existencia</th>
        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-2 ps-2 border-bottom border-light">Salida / Consumo</th>
      </tr>
    </thead>
              <tbody>
                <tr data-entrada="26" data-producido="30" data-existencia="22" data-salida="34">
                  <td class="py-2 pe-2"><span class="text-sm font-weight-bold text-dark text-uppercase">Masa Familiar</span> <span class="text-xxs text-secondary">(u)</span></td>
                  <td class="py-2 px-2 text-center text-sm">26,00</td>
                  <td class="py-2 px-2 text-center text-sm">30,00</td>
                  <td class="py-2 px-2 text-center text-sm font-weight-bold text-dark">22,00</td>
                  <td class="py-2 ps-2 text-center"><span class="text-sm font-weight-bolder text-primary">34,00</span></td>
                </tr>
                <tr data-entrada="44" data-producido="40" data-existencia="" data-salida="">
                  <td class="py-2 pe-2"><span class="text-sm font-weight-bold text-dark text-uppercase">Masa Mediana</span> <span class="text-xxs text-secondary">(u)</span></td>
                  <td class="py-2 px-2 text-center text-sm">44,00</td>
                  <td class="py-2 px-2 text-center text-sm">40,00</td>
                  <td class="py-2 px-2 text-center text-sm font-weight-bold text-dark">—</td>
                  <td class="py-2 ps-2 text-center"><span class="text-xxs text-secondary font-weight-bold">Pendiente</span></td>
                </tr>
                <tr data-entrada="33012" data-producido="0" data-existencia="" data-salida="">
                  <td class="py-2 pe-2"><span class="text-sm font-weight-bold text-dark text-uppercase">Queso Muzzarella</span> <span class="text-xxs text-secondary">(g)</span></td>
                  <td class="py-2 px-2 text-center text-sm">33.012,00</td>
                  <td class="py-2 px-2 text-center text-sm">0,00</td>
                  <td class="py-2 px-2 text-center text-sm font-weight-bold text-dark">—</td>
                  <td class="py-2 ps-2 text-center"><span class="text-xxs text-secondary font-weight-bold">Pendiente</span></td>
                </tr>
                <tr data-entrada="5132" data-producido="0" data-existencia="" data-salida="">
                  <td class="py-2 pe-2"><span class="text-sm font-weight-bold text-dark text-uppercase">Queso Cheddar</span> <span class="text-xxs text-secondary">(g)</span></td>
                  <td class="py-2 px-2 text-center text-sm">5.132,00</td>
                  <td class="py-2 px-2 text-center text-sm">0,00</td>
                  <td class="py-2 px-2 text-center text-sm font-weight-bold text-dark">—</td>
                  <td class="py-2 ps-2 text-center"><span class="text-xxs text-secondary font-weight-bold">Pendiente</span></td>
                </tr>
                <tr data-entrada="2594" data-producido="0" data-existencia="" data-salida="">
                  <td class="py-2 pe-2"><span class="text-sm font-weight-bold text-dark text-uppercase">Calabresa</span> <span class="text-xxs text-secondary">(g)</span></td>
                  <td class="py-2 px-2 text-center text-sm">2.594,00</td>
                  <td class="py-2 px-2 text-center text-sm">0,00</td>
                  <td class="py-2 px-2 text-center text-sm font-weight-bold text-dark">—</td>
                  <td class="py-2 ps-2 text-center"><span class="text-xxs text-secondary font-weight-bold">Pendiente</span></td>
                </tr>
                <tr data-entrada="2524" data-producido="0" data-existencia="" data-salida="">
                  <td class="py-2 pe-2"><span class="text-sm font-weight-bold text-dark text-uppercase">Peperoni</span> <span class="text-xxs text-secondary">(g)</span></td>
                  <td class="py-2 px-2 text-center text-sm">2.524,00</td>
                  <td class="py-2 px-2 text-center text-sm">0,00</td>
                  <td class="py-2 px-2 text-center text-sm font-weight-bold text-dark">—</td>
                  <td class="py-2 ps-2 text-center"><span class="text-xxs text-secondary font-weight-bold">Pendiente</span></td>
                </tr>
                <tr data-entrada="750" data-producido="1000" data-existencia="" data-salida="">
                  <td class="py-2 pe-2"><span class="text-sm font-weight-bold text-dark text-uppercase">Tocino</span> <span class="text-xxs text-secondary">(g)</span></td>
                  <td class="py-2 px-2 text-center text-sm">750,00</td>
                  <td class="py-2 px-2 text-center text-sm">1.000,00</td>
                  <td class="py-2 px-2 text-center text-sm font-weight-bold text-dark">—</td>
                  <td class="py-2 ps-2 text-center"><span class="text-xxs text-secondary font-weight-bold">Pendiente</span></td>
                </tr>
                <tr data-entrada="2798" data-producido="0" data-existencia="" data-salida="">
                  <td class="py-2 pe-2"><span class="text-sm font-weight-bold text-dark text-uppercase">Jamón</span> <span class="text-xxs text-secondary">(g)</span></td>
                  <td class="py-2 px-2 text-center text-sm">2.798,00</td>
                  <td class="py-2 px-2 text-center text-sm">0,00</td>
                  <td class="py-2 px-2 text-center text-sm font-weight-bold text-dark">—</td>
                  <td class="py-2 ps-2 text-center"><span class="text-xxs text-secondary font-weight-bold">Pendiente</span></td>
                </tr>
                <tr data-entrada="0" data-producido="21" data-existencia="" data-salida="">
                  <td class="py-2 pe-2"><span class="text-sm font-weight-bold text-dark text-uppercase">Harina</span> <span class="text-xxs text-secondary">(kg)</span></td>
                  <td class="py-2 px-2 text-center text-sm">0,00</td>
                  <td class="py-2 px-2 text-center text-sm">21,00</td>
                  <td class="py-2 px-2 text-center text-sm font-weight-bold text-dark">—</td>
                  <td class="py-2 ps-2 text-center"><span class="text-xxs text-secondary font-weight-bold">Pendiente</span></td>
                </tr>
                <tr data-entrada="6355" data-producido="0" data-existencia="" data-salida="">
                  <td class="py-2 pe-2"><span class="text-sm font-weight-bold text-dark text-uppercase">Carne</span> <span class="text-xxs text-secondary">(g)</span></td>
                  <td class="py-2 px-2 text-center text-sm">6.355,00</td>
                  <td class="py-2 px-2 text-center text-sm">0,00</td>
                  <td class="py-2 px-2 text-center text-sm font-weight-bold text-dark">—</td>
                  <td class="py-2 ps-2 text-center"><span class="text-xxs text-secondary font-weight-bold">Pendiente</span></td>
                </tr>
                <tr data-entrada="2000" data-producido="0" data-existencia="" data-salida="">
                  <td class="py-2 pe-2"><span class="text-sm font-weight-bold text-dark text-uppercase">Pollo</span> <span class="text-xxs text-secondary">(g)</span></td>
                  <td class="py-2 px-2 text-center text-sm">2.000,00</td>
                  <td class="py-2 px-2 text-center text-sm">0,00</td>
                  <td class="py-2 px-2 text-center text-sm font-weight-bold text-dark">—</td>
                  <td class="py-2 ps-2 text-center"><span class="text-xxs text-secondary font-weight-bold">Pendiente</span></td>
                </tr>
              </tbody>
            </table>
          </div>

          <p class="text-xxs text-secondary px-3 mb-2">Salida / consumo = entrada + producción − existencia.</p>
        </div>
      </div>

    </div>
  </div>
</div>