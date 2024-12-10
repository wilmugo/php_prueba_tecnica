<?php
namespace App\Controller;

use App\Model\UserModel as Usuario;
use App\Helpers\SessionManager;
use App\Helpers\Validador;

class AuthController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
    }

    public function registro() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errores = [];

            $nombre = Validador::limpiarInput($_POST['nombre']);
            $email = Validador::limpiarInput($_POST['email']);
            $password = $_POST['password'];
            $confirmar_password = $_POST['confirmar_password'];
            $rol = Validador::limpiarInput($_POST['rol']);

            // Validaciones
            if (empty($nombre)) {
                $errores[] = "El nombre es obligatorio";
            }

            if (!Validador::validarEmail($email)) {
                $errores[] = "Email inválido";
            }

            if (empty($password) || strlen($password) < 8) {
                $errores[] = "La contraseña debe tener al menos 8 caracteres";
            }

            if ($password !== $confirmar_password) {
                $errores[] = "Las contraseñas no coinciden";
            }

            if (empty($errores)) {
                try {
                    if ($this->usuarioModel->registrar($nombre, $email, $password, $rol)) {
                        SessionManager::set('mensaje', 'Registro exitoso');
                        // header('Location: /login');
                        view('Auth/login');
                        exit();
                    }
                } catch (\PDOException $e) {
                    $errores[] = "Error en el registro: " . $e->getMessage();
                }
            }

            // Mostrar errores
            SessionManager::set('errores', $errores);
            // header('Location: /registro');
            view('Auth/registro');
            exit();
        }else {
            view('Auth/registro');
        }

    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = Validador::limpiarInput($_POST['email']);
            $password = $_POST['password'];

            $usuario = $this->usuarioModel->autenticar($email, $password);

            if ($usuario) {
                SessionManager::iniciarSesion($usuario);
                // header('Location: /dashboard');
                view('dashboard');
                exit();
            } else {
                SessionManager::set('error', 'Credenciales inválidas');
                // header('Location: /login');
                view('Auth/login');
                exit();
            }
        }
        else {
            view('Auth/login');
        }
    }

    public function logout() {
        SessionManager::cerrarSesion();
        // header('Location: /login');
        view('Auth/login');
        exit();
    }
}
?>