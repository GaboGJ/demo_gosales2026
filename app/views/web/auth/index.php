
<!-- Login / Registro GoSales (deslizante con cajas traseras) -->
<!-- Estilos: public/assets/css/auth/auth.css | Comportamiento: public/assets/js/auth/slider.js y registro.js -->
<main class="main-content mt-6 mt-md-8 d-flex align-items-center py-3">
    <div class="container my-auto">
      <div class="contenedor_todo">

        <!-- ===== CAJAS TRASERAS ===== -->
        <div class="caja_trasera bg-gradient-primary shadow-primary">
          <!-- Visible cuando se muestra el registro -->
          <div class="caja_trasera-login">
            <span class="mask bg-gradient-primary opacity-7"></span>
            <div class="caja-contenido">
              <h3 class="text-white font-weight-bolder mb-2">¡Sirve rápido y vende más con GoSales!</h3>
              <p class="text-xs text-white opacity-9 mb-4">La solución POS más intuitiva para la gestión de mesas, pedidos y facturación en tu local.</p>
              <button id="btn_modo-login" type="button" class="btn btn-outline-white border-radius-lg px-4 text-capitalize mb-0">Iniciar Sesión</button>
            </div>
          </div>

          <!-- Visible cuando se muestra el login -->
          <div class="caja_trasera-registro">
            <span class="mask bg-gradient-primary opacity-7"></span>
            <div class="caja-contenido">
              <h3 class="text-white font-weight-bolder mb-2">¿Aún no gestionas tus ventas con GoSales?</h3>
              <p class="text-xs text-white opacity-9 mb-4">Configura tu Punto de Venta (POS) y comienza a vender en tu restaurante en 3 simples pasos.</p>
              <button id="btn_modo-registro" type="button" class="btn btn-outline-white border-radius-lg px-4 text-capitalize mb-0">Crear Cuenta</button>
            </div>
          </div>
        </div>

        <!-- ===== FORMULARIOS DESLIZANTES ===== -->
        <div class="contenedor_login-deslizable">

          <!-- LOGIN -->
          <div class="card-auth-container shadow-primary formulario_login">
            <div class="d-flex align-items-center mb-2">
              <div class="icon icon-shape icon-sm bg-gradient-primary text-center border-radius-md me-2 d-flex align-items-center justify-content-center">
                <span class="material-symbols-rounded text-white text-sm">lock</span>
              </div>
              <h4 class="font-weight-bolder text-dark mb-0">Bienvenido a <span class="text-primary">Go</span>Sales!</h4>
            </div>
            <p class="text-xs text-secondary mb-4">Ingresa con tus credenciales para iniciar la gestión de tu restaurante.</p>

            <form id="loginForm" action="" method="POST" role="form" autocomplete="off">
              <div class="mb-3">
                <label class="label-custom" for="login_email">Correo Electrónico</label>
                <input type="email" class="input-custom" id="login_email" name="email" placeholder="correo@ejemplo.com" required>
              </div>

              <div class="mb-3">
                <label class="label-custom" for="login_password">Contraseña</label>
                <input type="password" class="input-custom" id="login_password" name="password_user" placeholder="••••••••" required>
              </div>

              <div class="form-check form-switch mb-4">
                <input class="form-check-input" type="checkbox" id="rememberMe" name="remember_me">
                <label class="form-check-label text-xs text-secondary" for="rememberMe">Recordarme</label>
              </div>

              <button type="submit" id="btnLogin" class="btn bg-gradient-primary border-radius-lg w-100 font-weight-bold text-capitalize py-2 mb-0 text-white">
                Iniciar Sesión <span class="material-symbols-rounded text-sm ms-1 align-middle">arrow_forward</span>
              </button>
            </form>
          </div>

          <!-- REGISTRO (3 pasos) -->
          <div class="card-auth-container shadow-primary formulario_registro" style="display: none;">
            <h4 class="font-weight-bolder text-dark mb-1">Registro en <span class="text-primary">Go</span>Sales!</h4>
            <p class="text-xs text-secondary mb-3">Completa la información para configurar tu entorno de ventas.</p>

            <div class="border-bottom pb-2 mb-3">
              <div class="d-flex justify-content-between align-items-center text-center">
                <div class="step-indicator flex-fill" id="indicator-registro-1">
                  <button type="button" class="btn btn-icon-only btn-rounded bg-gradient-primary text-white mb-0 btn-sm shadow-none" data-step-go="1">
                    <span class="material-symbols-rounded text-xs text-white align-middle">person</span>
                  </button>
                  <span class="d-block text-xxs font-weight-bold text-dark">1. Personal</span>
                </div>

                <div class="border-top border-2 flex-fill opacity-3"></div>

                <div class="step-indicator flex-fill" id="indicator-registro-2">
                  <button type="button" class="btn btn-icon-only btn-rounded bg-gray-200 text-secondary mb-0 btn-sm shadow-none" data-step-go="2">
                    <span class="material-symbols-rounded text-xs align-middle">admin_panel_settings</span>
                  </button>
                  <span class="d-block text-xxs font-weight-bold text-secondary">2. Cuenta</span>
                </div>

                <div class="border-top border-2 flex-fill opacity-3"></div>

                <div class="step-indicator flex-fill" id="indicator-registro-3">
                  <button type="button" class="btn btn-icon-only btn-rounded bg-gray-200 text-secondary mb-0 btn-sm shadow-none" data-step-go="3">
                    <span class="material-symbols-rounded text-xs align-middle">storefront</span>
                  </button>
                  <span class="d-block text-xxs font-weight-bold text-secondary">3. Negocio</span>
                </div>
              </div>
            </div>

            <!-- novalidate: se valida por paso en JS -->
            <form id="register_form" action="" method="POST" role="form" novalidate autocomplete="off">

              <!-- Paso 1: Personal -->
              <div class="step-content-registro" id="step-registro-1">
                <div class="row g-2">
                  <div class="col-12 col-sm-6">
                    <label class="label-custom">Nombre *</label>
                    <input type="text" class="input-custom" name="nombre_persona" maxlength="50" placeholder="Ej. Juan" required>
                  </div>
                  <div class="col-12 col-sm-6">
                    <label class="label-custom">Apellido *</label>
                    <input type="text" class="input-custom" name="apellido_persona" maxlength="50" placeholder="Ej. Pérez" required>
                  </div>
                  <div class="col-12 col-sm-6">
                    <label class="label-custom">Carnet de Identidad *</label>
                    <input type="text" class="input-custom" name="carnet_persona" maxlength="20" placeholder="Documento de Identidad" required>
                  </div>
                  <div class="col-12 col-sm-6">
                    <label class="label-custom">Teléfono / Celular *</label>
                    <input type="tel" class="input-custom" name="telefono_persona" maxlength="50" placeholder="Ej. 70012345" required>
                  </div>
                </div>
              </div>

              <!-- Paso 2: Cuenta -->
              <div class="step-content-registro d-none" id="step-registro-2">
                <div class="row g-2">
                  <div class="col-12">
                    <label class="label-custom">Correo Electrónico (Usuario) *</label>
                    <input type="email" class="input-custom" name="email_usuario" maxlength="100" placeholder="juan@ejemplo.com" required>
                  </div>
                  <div class="col-12 col-sm-6">
                    <label class="label-custom">Contraseña *</label>
                    <input type="password" class="input-custom" id="password" name="password" placeholder="••••••••" required>
                  </div>
                  <div class="col-12 col-sm-6">
                    <label class="label-custom">Confirmar Contraseña *</label>
                    <input type="password" class="input-custom" id="confirm_password" name="confirm_password" placeholder="Repita contraseña" required>
                  </div>
                </div>
              </div>

              <!-- Paso 3: Negocio -->
              <div class="step-content-registro d-none" id="step-registro-3">
                <div class="row g-2">
                  <div class="col-12">
                    <label class="label-custom">Nombre de tu Empresa / Restaurante *</label>
                    <input type="text" class="input-custom" name="nombre_empresa" maxlength="100" placeholder="Ej. Restaurante Sabor Latino S.R.L." required>
                  </div>
                  <div class="col-12">
                    <label class="label-custom">Nombre de tu primera Sucursal *</label>
                    <input type="text" class="input-custom" name="nombre_sucursal" maxlength="100" placeholder="Ej. Sucursal Central" required>
                    <small class="text-xxs text-muted">Se creará como tu ubicación principal de ventas.</small>
                  </div>
                  <div class="col-12 mt-2">
                    <div class="form-check p-0 ms-1">
                      <input class="form-check-input" type="checkbox" id="terminos" required>
                      <label class="form-check-label text-xs text-secondary ms-1" for="terminos">
                        Acepto los <a href="javascript:;" class="text-primary font-weight-bolder">Términos y Condiciones</a>
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <div class="border-top pt-3 mt-3">
                <div class="d-flex justify-content-between align-items-center step-footer-registro" id="footer-registro-1">
                  <div></div>
                  <button type="button" class="btn btn-xs bg-gradient-primary mb-0 border-radius-md px-3 font-weight-bold text-capitalize text-white" data-step-go="2">
                    Siguiente <i class="fas fa-arrow-right text-xxs ms-1"></i>
                  </button>
                </div>

                <div class="d-flex justify-content-between align-items-center step-footer-registro d-none" id="footer-registro-2">
                  <button type="button" class="btn btn-xs bg-gradient-secondary mb-0 border-radius-md px-3 font-weight-bold text-capitalize" data-step-show="1">
                    <i class="fas fa-arrow-left text-xxs me-1"></i> Atrás
                  </button>
                  <button type="button" class="btn btn-xs bg-gradient-primary mb-0 border-radius-md px-3 font-weight-bold text-capitalize text-white" data-step-go="3">
                    Siguiente <i class="fas fa-arrow-right text-xxs ms-1"></i>
                  </button>
                </div>

                <div class="d-flex justify-content-between align-items-center step-footer-registro d-none" id="footer-registro-3">
                  <button type="button" class="btn btn-xs bg-gradient-secondary mb-0 border-radius-md px-3 font-weight-bold text-capitalize" data-step-show="2">
                    <i class="fas fa-arrow-left text-xxs me-1"></i> Atrás
                  </button>
                  <button type="submit" id="btnRegistro" class="btn btn-xs bg-gradient-primary mb-0 border-radius-md px-3 font-weight-bold text-capitalize text-white">
                    Crear Cuenta y Empresa <i class="fas fa-paper-plane ms-1 text-xxs"></i>
                  </button>
                </div>
              </div>
            </form>
          </div>

        </div>
      </div>
    </div>
  </main>