<!--
=========================================================
* Material Dashboard 3 - v3.2.0
=========================================================

* Product Page: https://www.creative-tim.com/product/material-dashboard
* Copyright 2024 Creative Tim (https://www.creative-tim.com)
* Licensed under MIT (https://www.creative-tim.com/license)
* Coded by Creative Tim

=========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
-->
<?php
  /* =====================================================================
     LAYOUT APP (panel interno de GoSales)
     Orden de inclusión (ver layout.php):
       header -> sidebar -> navbar (abre <main>) -> VISTA -> footer (cierra <main>)
       -> configurator -> script (cierra </body></html>)

     Variables opcionales que define el controlador / la vista:
       $tituloPagina  Título del breadcrumb y de la pestaña   (def. 'Panel Principal')
       $menuActivo    Clave del ítem activo del sidebar       (ej. 'dashboard')
       $pageStyles    CSS extra de la vista, relativos a public/assets/   (ej. ['css/x.css'])
       $pageScripts   JS extra de la vista, relativos a public/assets/    (ej. ['js/x.js'])
     ===================================================================== */
  $tituloPagina = $tituloPagina ?? 'Panel Principal';
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="assets/img/favicon.png">
  <title><?= htmlspecialchars($tituloPagina) ?> | GoSales</title>

  <!-- Fuentes e iconos -->
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <link href="<?= URL ?>/public/assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="<?= URL ?>/public/assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />

  
    <!-- DataTables CSS -->
  <link href="<?= URL ?>/public/assets/css/vendor/dataTables.bootstrap5.css" rel="stylesheet" type="text/css" />
  <link href="<?= URL ?>/public/assets/css/vendor/responsive.bootstrap5.css" rel="stylesheet" type="text/css" />
  

  <!-- CSS Material Dashboard 3 -->
  <link id="pagestyle" href="<?= URL ?>/public/assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />

</head>

<body class="g-sidenav-show bg-gray-100">