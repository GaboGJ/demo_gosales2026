/* Impresión de la planilla: Producción (producto + cantidad) y Existencia (producto + valor). */
(function () {
  const css = `#areaImpresion{display:none}
@media print{body.imprimiendo-planilla>*:not(#areaImpresion){display:none!important}
body.imprimiendo-planilla #areaImpresion{display:block!important;padding:8px;color:#000}}
#areaImpresion h3{margin:0 0 4px;font-size:16px;text-transform:uppercase}
#areaImpresion .pi-sub{font-size:12px;border-bottom:1px solid #444;padding-bottom:6px;margin-bottom:6px}
#areaImpresion .pi-t{font-size:14px;font-weight:700;text-transform:uppercase;margin:14px 0 6px}
#areaImpresion table{width:100%;border-collapse:collapse;font-size:12px;page-break-inside:avoid}
#areaImpresion th,#areaImpresion td{border:1px solid #444;padding:4px 8px}
#areaImpresion th{background:#eee;text-align:left;text-transform:uppercase;font-size:11px}
#areaImpresion td.n{text-align:right;width:30%}`;
  const st = document.createElement('style'); st.textContent = css; document.head.appendChild(st);

  const esc = (s) => String(s == null ? '' : s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const fmt = (n) => Number(n).toLocaleString('es-BO', { maximumFractionDigits: 2 });

  window.imprimirProduccion = function (id) {
    const d = (window.PLANILLAS_PRODUCCION || {})[id || window.planillaActual];
    if (!d) return;
    const p = d.produccion;
    const tabla = (titulo, col, filas) =>
      '<div class="pi-t">' + titulo + '</div><table><thead><tr><th>Producto</th><th>' + col + '</th></tr></thead><tbody>' + filas + '</tbody></table>';
    const prod = d.items.map((i) => '<tr><td>' + esc(i.producto) + '</td><td class="n">' + fmt(i.producido) + ' ' + esc(i.medida) + '</td></tr>').join('');
    const exis = d.items.map((i) => '<tr><td>' + esc(i.producto) + '</td><td class="n">' + (i.existencia === null ? '&nbsp;' : fmt(i.existencia) + ' ' + esc(i.medida)) + '</td></tr>').join('');

    let area = document.getElementById('areaImpresion');
    if (!area) { area = document.createElement('div'); area.id = 'areaImpresion'; document.body.appendChild(area); }
    area.innerHTML = '<h3>' + esc(p.sucursal) + '</h3><div class="pi-sub">Planilla #' + esc(p.codigo) + ' · ' + esc(p.fecha) + ' · ' + esc(p.turno) + '</div>' +
      tabla('Producción', 'Producción', prod) + tabla('Existencia', 'Existencia', exis);

    document.body.classList.add('imprimiendo-planilla');
    const limpiar = () => { document.body.classList.remove('imprimiendo-planilla'); window.removeEventListener('afterprint', limpiar); };
    window.addEventListener('afterprint', limpiar);
    window.print();
  };
})();