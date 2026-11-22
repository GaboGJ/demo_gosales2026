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
}
?>