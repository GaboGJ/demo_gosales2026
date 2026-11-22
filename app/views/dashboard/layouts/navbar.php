<!-- CONTENIDO PRINCIPAL: aquí se ABRE <main> y el contenedor de la vista (los cierra footer.php) -->
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg d-flex flex-column">

  <!-- Navbar superior -->
  <nav class="navbar navbar-main navbar-expand-lg px-0 mx-3 shadow-none border-radius-xl" id="navbarBlur" data-scroll="true">
    <div class="container-fluid py-1 px-3">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
          <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="<?= URL ?>/dashboard">GoSales</a></li>
          <li class="breadcrumb-item text-sm text-dark active" aria-current="page"><?= htmlspecialchars($tituloPagina ?? 'Panel Principal') ?></li>
        </ol>
      </nav>

      <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
        <div class="ms-md-auto pe-md-3 d-flex align-items-center">
          <div class="input-group input-group-outline bg-white border-radius-md shadow-sm">
            <span class="input-group-text border-0 ps-3">
              <i class="material-symbols-rounded text-primary text-sm">search</i>
            </span>
            <input type="text" class="form-control border-0 ps-2" placeholder="Buscar producto, mesa o pedido...">
          </div>
        </div>

        <ul class="navbar-nav d-flex align-items-center justify-content-end">
          <!-- Botón del sidebar (móvil) -->
          <li class="nav-item d-xl-none ps-3 d-flex align-items-center me-2">
            <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
              <div class="sidenav-toggler-inner">
                <i class="sidenav-toggler-line"></i>
                <i class="sidenav-toggler-line"></i>
                <i class="sidenav-toggler-line"></i>
              </div>
            </a>
          </li>

          <!-- Configurador -->
          <li class="nav-item px-2 d-flex align-items-center">
            <a href="javascript:;" class="nav-link text-body p-0">
              <i class="material-symbols-rounded fixed-plugin-button-nav">settings</i>
            </a>
          </li>

          <!-- Notificaciones -->
          <li class="nav-item dropdown px-2 d-flex align-items-center">
            <a href="javascript:;" class="nav-link text-body p-0" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="material-symbols-rounded">notifications</i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end px-2 py-3 me-sm-n4" aria-labelledby="dropdownMenuButton">
              <li class="mb-2">
                <a class="dropdown-item border-radius-md" href="javascript:;">
                  <div class="d-flex py-1">
                    <div class="my-auto me-3">
                      <span class="material-symbols-rounded text-primary">soup_kitchen</span>
                    </div>
                    <div class="d-flex flex-column justify-content-center">
                      <h6 class="text-sm font-weight-normal mb-1">
                        <span class="font-weight-bold">Pedido #1042</span> listo en cocina
                      </h6>
                      <p class="text-xs text-secondary mb-0">Hace 10 min</p>
                    </div>
                  </div>
                </a>
              </li>
            </ul>
          </li>

          <!-- Usuario -->
          <li class="nav-item dropdown ps-2 d-flex align-items-center">
            <a href="javascript:;" class="nav-link text-body p-0" id="dropdownUserButton" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="material-symbols-rounded fs-4">account_circle</i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end px-2 py-3 me-sm-n4" aria-labelledby="dropdownUserButton">
              <li class="mb-1">
                <a class="dropdown-item border-radius-md d-flex align-items-center" href="javascript:;">
                  <i class="material-symbols-rounded text-sm me-2 text-dark">person</i>
                  <span class="text-sm font-weight-normal">Mi Perfil</span>
                </a>
              </li>
              <hr class="horizontal dark my-2">
              <li>
                <a class="dropdown-item border-radius-md text-danger d-flex align-items-center" href="<?= URL ?>/auth/logout">
                  <i class="material-symbols-rounded text-sm me-2 text-danger">logout</i>
                  <span class="text-sm font-weight-bold">Cerrar Sesión</span>
                </a>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <!-- Fin navbar -->

  <!-- Contenedor de la vista (se cierra en footer.php) -->
  <div class="container-fluid py-2 d-flex flex-column flex-grow-1">