<?php
namespace App\Helpers;

class Validador {
    public static function limpiarInput($dato) {
        return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
    }

    public static function validarEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validarNumeroPositivo($numero) {
        return filter_var($numero, FILTER_VALIDATE_FLOAT) !== false && $numero > 0;
    }

    public static function generarTokenCSRF() {
        return bin2hex(random_bytes(32));
    }

    public static function validarTokenCSRF($token) {
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
}
?>