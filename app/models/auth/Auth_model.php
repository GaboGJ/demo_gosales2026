<?php
/**
 * Auth_model
 */
class Auth_model {
   
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        // Cualquier error SQL lanza PDOException (no falla en silencio)
        if ($this->pdo) {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }
}