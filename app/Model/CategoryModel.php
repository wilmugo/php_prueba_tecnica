<?php
namespace App\Model;

use App\Database\Database;
use PDO;

class CategoryModel {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::conectar();
    }

    public function crear($nombre, $descripcion) {
        $stmt = $this->conexion->prepare(
            "INSERT INTO categorias (nombre, descripcion) 
             VALUES (:nombre, :descripcion)"
        );
        
        return $stmt->execute([
            ':nombre' => $nombre,
            ':descripcion' => $descripcion
        ]);
    }

    public function obtenerTodos() {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM categorias WHERE activo = 1"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM categorias WHERE id = :id AND activo = 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar($id, $datos) {
        $campos = [];
        $parametros = [':id' => $id];

        $camposPermitidos = ['nombre', 'descripcion'];

        foreach ($camposPermitidos as $campo) {
            if (isset($datos[$campo])) {
                $campos[] = "$campo = :$campo";
                $parametros[":$campo"] = $datos[$campo];
            }
        }

        if (empty($campos)) {
            return false;
        }

        $query = "UPDATE categorias SET " . implode(', ', $campos) . " WHERE id = :id";
        $stmt = $this->conexion->prepare($query);
        return $stmt->execute($parametros);
    }

    public function eliminar($id) {
        $stmt = $this->conexion->prepare(
            "UPDATE categorias SET activo = 0 WHERE id = :id"
        );
        return $stmt->execute([':id' => $id]);
    }

    public function obtenerCategoriasConProductos() {
        $stmt = $this->conexion->prepare(
            "SELECT 
                c.id, 
                c.nombre, 
                COUNT(p.id) as total_productos,
                SUM(p.stock) as stock_total
             FROM categorias c
             LEFT JOIN productos p ON c.id = p.categoria_id
             WHERE c.activo = 1
             GROUP BY c.id, c.nombre
             ORDER BY total_productos DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}