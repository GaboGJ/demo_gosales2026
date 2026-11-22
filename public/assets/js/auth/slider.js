/* =========================================================
   GoSales - Deslizamiento login / registro
   Expone window.GSAuth.verLogin() y window.GSAuth.verRegistro()
   Cargar ANTES de registro.js. Si la página no es el login, no hace nada.
   ========================================================= */
(function () {
  'use strict';

  var contenedor = document.querySelector('.contenedor_login-deslizable');
  if (!contenedor) return;

  var MOVIL = 850;
  var formLogin      = document.querySelector('.formulario_login');
  var formRegistro   = document.querySelector('.formulario_registro');
  var cajaLogin      = document.querySelector('.caja_trasera-login');
  var cajaRegistro   = document.querySelector('.caja_trasera-registro');

  function esEscritorio() {
    return window.innerWidth > MOVIL;
  }

  function ajustarAncho() {
    if (esEscritorio()) {
      cajaLogin.style.display = 'block';
      cajaRegistro.style.display = 'block';
    } else {
      cajaRegistro.style.display = 'block';
      cajaRegistro.style.opacity = '1';
      cajaLogin.style.display = 'none';
      formLogin.style.display = 'flex';
      formRegistro.style.display = 'none';
      contenedor.style.left = '0px';
    }
  }

  function verLogin() {
    formRegistro.style.display = 'none';
    formLogin.style.display = 'flex';
    contenedor.style.left = '0px';

    if (esEscritorio()) {
      cajaRegistro.style.opacity = '1';
      cajaLogin.style.opacity = '0';
    } else {
      cajaRegistro.style.display = 'block';
      cajaLogin.style.display = 'none';
    }
  }

  function verRegistro() {
    formRegistro.style.display = 'flex';
    formLogin.style.display = 'none';

    if (esEscritorio()) {
      contenedor.style.left = '50%';
      cajaRegistro.style.opacity = '0';
      cajaLogin.style.opacity = '1';
    } else {
      contenedor.style.left = '0px';
      cajaRegistro.style.display = 'none';
      cajaLogin.style.display = 'block';
      cajaLogin.style.opacity = '1';
    }
  }

  document.getElementById('btn_modo-login').addEventListener('click', verLogin);
  document.getElementById('btn_modo-registro').addEventListener('click', verRegistro);
  window.addEventListener('resize', ajustarAncho);
  ajustarAncho();

  window.GSAuth = window.GSAuth || {};
  window.GSAuth.verLogin = verLogin;
  window.GSAuth.verRegistro = verRegistro;
})();