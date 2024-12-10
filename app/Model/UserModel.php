<?php
namespace App\Model;

use App\Database\Database;
use PDO;

class UserModel {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::conectar();
    }

    public function registrar($nombre, $email, $password, $rol) {
        $hash_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->conexion->prepare(
            "INSERT INTO usuarios (nombre, email, password, rol) 
             VALUES (:nombre, :email, :password, :rol)"
        );
        
        return $stmt->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':password' => $hash_password,
            ':rol' => $rol
        ]);
    }

    public function autenticar($email, $password) {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM usuarios WHERE email = :email AND activo = 1"
        );
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($password, $usuario['password'])) {
            return $usuario;
        }
        return false;
    }

    public function obtenerPorId($id) {
        $stmt = $this->conexion->prepare(
            "SELECT id, nombre, email, rol FROM usuarios WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>