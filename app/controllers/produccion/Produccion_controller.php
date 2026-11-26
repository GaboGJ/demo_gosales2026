<?php

class Produccion_controller {

    private function vista($vista, array $datos) {
        $viewPath   = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'produccion';
        extract($datos);

        require_once $viewPath . 'layouts/header.php';
        require_once $viewPath . 'layouts/sidebar.php';
        require_once $viewPath . 'layouts/navbar.php';

        if (file_exists($viewPath . $vista . '.php')) {
            require_once $viewPath . $vista . '.php';
        }

        require_once $viewPath . 'layouts/footer.php';
        require_once $viewPath . 'layouts/script.php';
    }

    /** Listado. Incluye los modales new.php (abrir producción) y show.php (planilla). */
    public function index() {
        $this->vista('produccion/index', []);
    }

    /** "Nueva producción" es un modal dentro del listado: se abre el listado con el modal desplegado. */
    public function new() {
        header('Location: ' . URL . '/produccion?nueva=1');
        exit;
    }

    /** Registro de producción y existencia del turno. */
    public function edit() {
        $this->vista('produccion/edit', []);
    }

    /** La planilla es un modal dentro del listado: /produccion/show/5 abre el listado con la planilla #5. */
    public function show($id = null) {
        $id = $id !== null ? (int)$id : (int)($_GET['id'] ?? 0);
        header('Location: ' . URL . '/produccion' . ($id > 0 ? '?ver=' . $id : ''));
        exit;
    }

    /** Imprime una o varias planillas seleccionadas desde el listado. */
    public function imprimir() {
        $idsRaw = trim($_GET['ids'] ?? ($_GET['id'] ?? ''));
        $ids    = array_values(array_unique(array_filter(array_map('intval', explode(',', $idsRaw)))));
        $tipo   = strtolower(trim($_GET['tipo'] ?? 'completo'));
        if (!in_array($tipo, ['completo', 'resumen', 'vacio'], true)) {
            $tipo = 'completo';
        }

        if (empty($ids)) {
            die('Error: Debe seleccionar al menos una producción para imprimir.');
        }

        // DEMO: reemplazar por consulta a tu modelo buscando por $ids
        $itemsDemo = [
            ['producto' => 'Masa Familiar',    'medida' => 'u',  'entrada' => 26,    'producido' => 30,   'existencia' => 22],
            ['producto' => 'Masa Mediana',     'medida' => 'u',  'entrada' => 44,    'producido' => 40,   'existencia' => null],
            ['producto' => 'Queso Muzzarella', 'medida' => 'g',  'entrada' => 33012, 'producido' => 0,    'existencia' => null],
            ['producto' => 'Queso Cheddar',    'medida' => 'g',  'entrada' => 5132,  'producido' => 0,    'existencia' => null],
            ['producto' => 'Calabresa',        'medida' => 'g',  'entrada' => 2594,  'producido' => 0,    'existencia' => null],
            ['producto' => 'Peperoni',         'medida' => 'g',  'entrada' => 2524,  'producido' => 0,    'existencia' => null],
            ['producto' => 'Tocino',           'medida' => 'g',  'entrada' => 750,   'producido' => 1000, 'existencia' => null],
            ['producto' => 'Jamón',            'medida' => 'g',  'entrada' => 2798,  'producido' => 0,    'existencia' => null],
            ['producto' => 'Harina',           'medida' => 'kg', 'entrada' => 0,     'producido' => 21,   'existencia' => null],
            ['producto' => 'Carne',            'medida' => 'g',  'entrada' => 6355,  'producido' => 0,    'existencia' => null],
            ['producto' => 'Pollo',            'medida' => 'g',  'entrada' => 2000,  'producido' => 0,    'existencia' => null],
        ];

        $catalogoDemo = [
            10 => ['id_produccion' => 10, 'codigo' => '00010', 'fecha' => '26/11/2026', 'turno' => 'Mañana', 'sucursal' => 'Tatu Central', 'responsable' => 'Carlos Mendoza',  'estado' => 'En proceso',  'items' => $itemsDemo],
            9  => ['id_produccion' => 9,  'codigo' => '00009', 'fecha' => '25/11/2026', 'turno' => 'Tarde',  'sucursal' => 'Tatu Central', 'responsable' => 'María Rojas',     'estado' => 'Completada',  'items' => $itemsDemo],
            8  => ['id_produccion' => 8,  'codigo' => '00008', 'fecha' => '25/11/2026', 'turno' => 'Mañana', 'sucursal' => 'Tatu Central', 'responsable' => 'Gabriel Guayhua', 'estado' => 'Completada',  'items' => $itemsDemo],
            7  => ['id_produccion' => 7,  'codigo' => '00007', 'fecha' => '24/11/2026', 'turno' => 'Noche',  'sucursal' => 'Tatu Central', 'responsable' => 'Luis Camacho',    'estado' => 'Anulada',     'items' => $itemsDemo],
            6  => ['id_produccion' => 6,  'codigo' => '00006', 'fecha' => '21/11/2026', 'turno' => 'Mañana', 'sucursal' => 'Tatu Central', 'responsable' => 'Carlos Mendoza',  'estado' => 'En proceso',  'items' => $itemsDemo],
            5  => ['id_produccion' => 5,  'codigo' => '00005', 'fecha' => '18/11/2026', 'turno' => 'Noche',  'sucursal' => 'Tatu Central', 'responsable' => 'Luis Camacho',    'estado' => 'Anulada',     'items' => $itemsDemo],
            4  => ['id_produccion' => 4,  'codigo' => '00004', 'fecha' => '17/11/2026', 'turno' => 'Tarde',  'sucursal' => 'Tatu Central', 'responsable' => 'María Rojas',     'estado' => 'Completada',  'items' => $itemsDemo],
            3  => ['id_produccion' => 3,  'codigo' => '00003', 'fecha' => '16/11/2026', 'turno' => 'Mañana', 'sucursal' => 'Tatu Central', 'responsable' => 'Gabriel Guayhua', 'estado' => 'Completada',  'items' => $itemsDemo],
            2  => ['id_produccion' => 2,  'codigo' => '00002', 'fecha' => '20/11/2026', 'turno' => 'Tarde',  'sucursal' => 'Tatu Central', 'responsable' => 'María Rojas',     'estado' => 'Completada',  'items' => $itemsDemo],
            1  => ['id_produccion' => 1,  'codigo' => '00001', 'fecha' => '20/11/2026', 'turno' => 'Mañana', 'sucursal' => 'Tatu Central', 'responsable' => 'Carlos Mendoza',  'estado' => 'Completada',  'items' => $itemsDemo],
        ];

        $produccionesLista = [];
        foreach ($ids as $id) {
            if (isset($catalogoDemo[$id])) {
                $produccionesLista[] = $catalogoDemo[$id];
            } else {
                $produccionesLista[] = [
                    'id_produccion' => $id,
                    'codigo'        => str_pad($id, 5, '0', STR_PAD_LEFT),
                    'fecha'         => '21/11/2026',
                    'turno'         => 'Mañana',
                    'sucursal'      => 'Tatu Central',
                    'responsable'   => 'Carlos Mendoza',
                    'estado'        => 'En proceso',
                    'items'         => $itemsDemo,
                ];
            }
        }

        require_once __DIR__ . '/../../views/dashboard/produccion/print.php';
        exit;
    }
}