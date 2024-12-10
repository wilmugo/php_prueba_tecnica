<?php
namespace App\Helpers;

class SessionManager {
    public static function iniciar() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function iniciarSesion($usuario) {
        self::iniciar();
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_rol'] = $usuario['rol'];
        $_SESSION['csrf_token'] = Validador::generarTokenCSRF();
    }

    public static function cerrarSesion() {
        self::iniciar();
        session_unset();
        session_destroy();
    }

    public static function set($clave, $valor) {
        self::iniciar();
        $_SESSION[$clave] = $valor;
    }

    public static function get($clave, $porDefecto = null) {
        self::iniciar();
        return $_SESSION[$clave] ?? $porDefecto;
    }

    public static function usuarioAutenticado() {
        self::iniciar();
        return isset($_SESSION['usuario_id']);
    }

    public static function tieneRol($rolesPermitidos) {
        self::iniciar();
        return in_array($_SESSION['usuario_rol'] ?? '', $rolesPermitidos);
    }
}
?>