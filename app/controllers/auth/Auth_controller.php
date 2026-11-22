<?php
require_once __DIR__ . '/../../models/auth/Auth_model.php';

class Auth_controller {

    private $authModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->authModel = new Auth_model();
    }

    /* ============================ VISTA ============================ */

    public function index() {
        if (!empty($_SESSION['id_usuario'])) {
            header('Location: ' . rtrim(URL, '/') . '/dashboard');
            exit;
        }

        $viewsPath = __DIR__ . '/../../views/web/';

        require_once $viewsPath . 'layouts/header.php';
        require_once $viewsPath . 'layouts/navbar.php';
        require_once $viewsPath . 'auth/index.php';
        require_once $viewsPath . 'layouts/footer.php';
        require_once $viewsPath . 'layouts/script.php';
    }

}