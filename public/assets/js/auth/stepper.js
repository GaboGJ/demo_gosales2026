/* =========================================================
   GoSales - Stepper del registro (solo front, SIN envío al backend)
   Pasos: 1 Personal / 2 Cuenta / 3 Negocio
   Si la página no es el login, no hace nada.

   Al terminar el paso 3 y pasar la validación NO se envía nada:
   se dispara el evento "gs:registro-listo" en el formulario con
   los datos en event.detail, por si luego quieres conectarlo.
   ========================================================= */
(function () {
  'use strict';

  var form = document.getElementById('register_form');
  if (!form) return;

  var TOTAL_PASOS = 3;

  // Aviso: usa SweetAlert2 si está cargado; si no, alert nativo
  function aviso(titulo, texto) {
    if (window.Swal) {
      var color = getComputedStyle(document.documentElement).getPropertyValue('--bs-primary').trim() || '#e91e63';
      Swal.fire({ icon: 'warning', title: titulo, text: texto, confirmButtonColor: color });
    } else {
      alert(titulo + ': ' + texto);
    }
  }

  /* ---------- Mostrar un paso (contenido, botones e indicadores) ---------- */
  function mostrarPaso(n) {
    document.querySelectorAll('.step-content-registro').forEach(function (el) { el.classList.add('d-none'); });
    document.getElementById('step-registro-' + n).classList.remove('d-none');

    document.querySelectorAll('.step-footer-registro').forEach(function (el) { el.classList.add('d-none'); });
    document.getElementById('footer-registro-' + n).classList.remove('d-none');

    for (var i = 1; i <= TOTAL_PASOS; i++) {
      var ind  = document.getElementById('indicator-registro-' + i);
      var btn  = ind.querySelector('button');
      var icon = btn.querySelector('span');
      var lbl  = ind.querySelector('span.d-block');
      var on   = i <= n;

      btn.classList.toggle('bg-gradient-primary', on);
      btn.classList.toggle('text-white', on);
      btn.classList.toggle('bg-gray-200', !on);
      btn.classList.toggle('text-secondary', !on);
      icon.classList.toggle('text-white', on);
      lbl.classList.toggle('text-dark', on);
      lbl.classList.toggle('text-secondary', !on);
    }
  }

  /* ---------- Validación de UN paso ---------- */
  function validarPaso(paso) {
    var inputs = document.querySelectorAll('#step-registro-' + paso + ' input');
    for (var i = 0; i < inputs.length; i++) {
      if (!inputs[i].checkValidity()) {
        mostrarPaso(paso);
        inputs[i].reportValidity();
        return false;
      }
    }
    // Paso 2: las contraseñas deben coincidir
    if (paso === 2 && form.password.value !== form.confirm_password.value) {
      mostrarPaso(2);
      aviso('Atención', 'Las contraseñas no coinciden');
      return false;
    }
    return true;
  }

  // Para avanzar o saltar a un paso exige que los anteriores estén completos
  function irAPaso(n) {
    for (var p = 1; p < n; p++) {
      if (!validarPaso(p)) return;
    }
    mostrarPaso(n);
  }

  /* ---------- Botones ---------- */
  // data-step-go="n"   -> avanza/salta validando los pasos previos
  // data-step-show="n" -> vuelve atrás sin validar
  document.querySelectorAll('[data-step-go]').forEach(function (el) {
    el.addEventListener('click', function () { irAPaso(parseInt(el.getAttribute('data-step-go'), 10)); });
  });
  document.querySelectorAll('[data-step-show]').forEach(function (el) {
    el.addEventListener('click', function () { mostrarPaso(parseInt(el.getAttribute('data-step-show'), 10)); });
  });

  /* ---------- Envío del formulario (sin backend) ---------- */
  form.addEventListener('submit', function (e) {
    e.preventDefault(); // no se envía nada al servidor

    for (var p = 1; p <= TOTAL_PASOS; p++) {
      if (!validarPaso(p)) return;
    }

    // Datos listos (todos los pasos válidos): se publican en un evento
    var datos = {};
    new FormData(form).forEach(function (valor, clave) { datos[clave] = valor; });
    form.dispatchEvent(new CustomEvent('gs:registro-listo', { bubbles: true, detail: datos }));
  });

  // API opcional para controlar el stepper desde otro script
  window.GSAuth = window.GSAuth || {};
  window.GSAuth.mostrarPaso = mostrarPaso;
  window.GSAuth.reiniciarStepper = function () { form.reset(); mostrarPaso(1); };
})();