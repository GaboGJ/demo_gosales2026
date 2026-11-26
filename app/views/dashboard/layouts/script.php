<!-- Core JS -->
<script src="<?= URL ?>/public/assets/js/core/popper.min.js"></script>
<script src="<?= URL ?>/public/assets/js/core/bootstrap.min.js"></script>
<script src="<?= URL ?>/public/assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="<?= URL ?>/public/assets/js/plugins/smooth-scrollbar.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

   <!-- JQUERY & DATATABLES SCRIPTS -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="<?= URL ?>/public/assets/js/plugins/jquery.dataTables.min.js"></script>
  <script src="<?= URL ?>/public/assets/js/vendor/dataTables.bootstrap5.js"></script>
  <script src="<?= URL ?>/public/assets/js/vendor/dataTables.responsive.min.js"></script>

  <!-- HELPER GLOBAL DE DATATABLES -->
<script>
    function inicializarDataTable(tableId, customOptions = {}) {
        var tableElement = $(tableId);
        if (tableElement.length === 0) return;

        if ($.fn.DataTable.isDataTable(tableElement)) {
            tableElement.DataTable().destroy();
        }
        
        var defaultOptions = {
            dom: '<"row align-items-center px-3 py-2 gy-2"<"col-12 col-md-6 text-center text-md-start"l><"col-12 col-md-6 text-center text-md-end"f>>' +
                 '<"row"<"col-12"tr>>' +
                 '<"row align-items-center p-3 gy-2"<"col-12 col-md-5 text-center text-md-start"i><"col-12 col-md-7 d-flex justify-content-center justify-content-md-end"p>>',
            buttons: [],
            responsive: {
                details: {
                    type: 'inline',
                    target: 0,
                    renderer: function (api, rowIdx, columns) {
                        var data = $.map(columns, function (col) {
                            return col.hidden ?
                                '<div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light gap-2">' +
                                    '<span class="text-secondary text-xxs font-weight-bolder text-uppercase">' + col.title + ':</span>' +
                                    '<span class="text-xs font-weight-bold text-dark text-wrap ms-auto">' + col.data + '</span>' +
                                '</div>' : '';
                        }).join('');

                        return data ? $('<div class="card card-body bg-gray-100 shadow-none p-2 my-2 border-radius-md w-100 overflow-hidden"/>').append(data) : false;
                    }
                }
            },
            autoWidth: false,
            pageLength: 5,
            lengthMenu: [
                [5, 10, 25, 50, -1],
                ['5', '10', '25', '50', 'Todos']
            ],
            language: {
                lengthMenu: "Mostrar _MENU_ registros", 
                emptyTable: "No hay registros disponibles",
                info: "<span class='text-xs font-weight-bold text-secondary'>Mostrando _START_ a _END_ de _TOTAL_ Registros</span>",
                infoEmpty: "<span class='text-xs font-weight-bold text-secondary'>Mostrando 0 a 0 de 0 Registros</span>",
                infoFiltered: "(Filtrado de _MAX_ total Registros)",
                search: "",
                zeroRecords: "Sin resultados encontrados",
                paginate: {
                    first: "«",
                    last: "»",
                    next: "›",
                    previous: "‹"
                }
            },
            initComplete: function() {
                var placeholderText = customOptions.placeholder || 'Buscar...'; 
                var dt = this.api(); 
                var tableIdSelector = '#' + dt.table().node().id;

                var lengthSelectDiv = $(tableIdSelector + '_length');
                var lengthSelect = lengthSelectDiv.find('select');
                
                lengthSelect.removeClass('form-control form-control-sm')
                            .addClass('form-select form-select-sm border-radius-md mx-2 d-inline-block')
                            .css({ 'width': 'auto', 'font-size': '0.75rem' });

                lengthSelectDiv.find('label').addClass('text-xs font-weight-bold text-secondary mb-0 d-inline-flex align-items-center');

                var filterContainer = $(tableIdSelector + '_filter'); 
                var dtLabel = filterContainer.find('label');
                var input = dtLabel.find('input[type="search"]');   

                input.removeClass('form-control-sm')
                     .addClass('form-control form-control-sm border border-radius-md px-2')
                     .attr('placeholder', placeholderText)
                     .css({
                         'display': 'inline-block',
                         'width': '100%',
                         'max-width': '220px'
                     });

                input.detach();
                dtLabel.empty().append(input);

                var paginateContainer = $(tableIdSelector + '_paginate');
                paginateContainer.find('.pagination').addClass('pagination-primary pagination-sm mb-0');

                dt.on('draw', function() {
                  paginateContainer.find('.page-item.active .page-link').addClass('text-white');
                });
                paginateContainer.find('.page-item.active .page-link').addClass('text-white');
            }
        };

        var finalOptions = $.extend(true, {}, defaultOptions, customOptions);
        return tableElement.DataTable(finalOptions);
    }
</script>


<!-- INICIALIZACIÓN DE TABLAS POR VISTA (cada una solo actúa si existe en la página) -->
<script>
$(document).ready(function () {
    console.log('jQuery:', typeof $, '| DataTable:', typeof $.fn.DataTable);
    try {
        inicializarDataTable('#datatable-produccion', { ordering: false, placeholder: 'Buscar produccion...' });
        console.log('DataTable de producción inicializado');
    } catch (e) {
        console.error('Fallo al inicializar:', e);
    }

    try {
        inicializarDataTable('#datatable-edicion', { ordering: false, placeholder: 'Buscar edición...' });
        console.log('DataTable de edición inicializado');
    } catch (e) {
        console.error('Fallo al inicializar:', e);
    }

    $('#modalPlanilla').on('shown.bs.modal', function () {
        try {
            var dtShow = inicializarDataTable('#datatable-show', {
                ordering: false,
                pageLength: 5,
                placeholder: 'Buscar producto...'
            });
            if (dtShow && dtShow.columns) {
                dtShow.columns.adjust();
                if (dtShow.responsive) dtShow.responsive.recalc();
            }
            console.log('DataTable de show inicializado');
        } catch (e) {
            console.error('Fallo al inicializar show:', e);
        }
    });
});
</script>

<script>
  // Scrollbar suave del sidebar (solo Windows)
  var win = navigator.platform.indexOf('Win') > -1;
  if (win && document.querySelector('#sidenav-scrollbar')) {
    Scrollbar.init(document.querySelector('#sidenav-scrollbar'), { damping: '0.5' });
  }
</script>

<!-- Control del template: sidebar, configurador, modo oscuro, etc. -->
<script src="<?= URL ?>/public/assets/js/material-dashboard.min.js?v=3.2.0"></script>


</body>

</html>