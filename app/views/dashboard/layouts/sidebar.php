<?php
  /* Menú lateral GoSales. Para agregar una opción basta con sumar una línea al arreglo.
     Formato de cada ítem: [clave ($menuActivo), texto, icono Material Symbols, ruta] */
  $menuGoSales = [
    ['titulo' => null, 'items' => [
      ['dashboard',   'Panel Principal',     'dashboard',            'dashboard'],
    ]],
    ['titulo' => 'Ventas y Operaciones', 'items' => [
      ['pos',         'Punto de Venta',      'point_of_sale',        'pos'],
      //['mesas',       'Mesas',               'table_restaurant',     'mesas'],
      ['pedidos',     'Pedidos',             'receipt_long',         'pedidos'],
      ['cocina',      'Cocina',              'soup_kitchen',         'cocina'],
      ['cajas',       'Control de Cajas',    'payments',             'cajas'],
      ['clientes',    'Clientes',            'groups',               'clientes'],
    ]],
    ['titulo' => 'Menú e Inventario', 'items' => [
      ['productos',   'Productos / Menú',    'restaurant_menu',      'productos'],
      ['categorias',  'Categorías',          'category',             'categorias'],
      ['recetas',     'Recetas',             'menu_book',            'recetas'],
      ['produccion',  'Producción',          'blender',              'produccion'],
      ['inventario',  'Inventario',          'inventory_2',          'inventario'],
      //['proveedores', 'Proveedores',         'local_shipping',       'proveedores'],
     // ['compras',     'Compras',             'shopping_cart',        'compras'],
    ]],
    ['titulo' => 'Administración', 'items' => [
      ['sucursales',  'Sucursales',          'storefront',           'sucursales'],
      ['usuarios',    'Usuarios',            'group',                'usuarios'],
      ['roles',       'Roles y Permisos',    'admin_panel_settings', 'roles'],
    ]],
    ['titulo' => 'Reportes', 'items' => [
      ['reportes',    'Reportes de Ventas',  'analytics',            'reportes'],
    ]],
  ];
?>
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2 bg-white my-2" id="sidenav-main">
  <div class="sidenav-header">
    <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
    <a class="navbar-brand px-4 py-3 m-0 d-flex align-items-center" href="<?= URL ?>/dashboard">
      <span class="material-symbols-rounded text-primary fs-3 me-2">point_of_sale</span>
      <span class="ms-1 font-weight-bold text-dark text-sm"><span class="text-primary">Go</span>Sales!</span>
    </a>
  </div>
  <hr class="horizontal dark mt-0 mb-2">

  <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
    <ul class="navbar-nav">
      <?php foreach ($menuGoSales as $seccion): ?>
        <?php if ($seccion['titulo']): ?>
        <li class="nav-item mt-3">
          <h6 class="ps-4 ms-2 text-uppercase text-xs text-dark font-weight-bolder opacity-5"><?= $seccion['titulo'] ?></h6>
        </li>
        <?php endif; ?>

        <?php foreach ($seccion['items'] as [$clave, $texto, $icono, $ruta]): ?>
        <li class="nav-item">
          <a class="nav-link <?= (($menuActivo ?? '') === $clave) ? 'active bg-gradient-primary text-white' : 'text-dark' ?>" href="<?= URL ?>/<?= $ruta ?>">
            <i class="material-symbols-rounded opacity-5"><?= $icono ?></i>
            <span class="nav-link-text ms-1"><?= $texto ?></span>
          </a>
        </li>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </ul>
  </div>

  <div class="sidenav-footer position-absolute w-100 bottom-0">
    <div class="mx-3">
      <a class="btn btn-outline-primary mt-2 w-100" href="javascript:;">
        <i class="material-symbols-rounded text-sm me-1 align-middle">menu_book</i> Guía
      </a>
      <a class="btn bg-gradient-primary w-100" href="javascript:;">
        <i class="material-symbols-rounded text-sm me-1 align-middle">support_agent</i> Soporte
      </a>
    </div>
  </div>
</aside>