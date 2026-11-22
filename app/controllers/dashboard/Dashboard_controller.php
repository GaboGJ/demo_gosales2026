<?php

class Dashboard_controller {

    public function index() {
        $viewPath   = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'dashboard';

        
        require_once $viewPath . 'layouts/header.php';
        require_once $viewPath . 'layouts/sidebar.php';
        require_once $viewPath . 'layouts/navbar.php';
        require_once $viewPath . 'start/index.php';
        require_once $viewPath . 'layouts/footer.php';
        require_once $viewPath . 'layouts/script.php';
    }
}
?>