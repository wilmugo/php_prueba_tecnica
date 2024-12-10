<?php
namespace App\Model;

use App\Database\Database;
use PDO;

class ProductModel {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::conectar();
    }

    public function crear($nombre, $descripcion, $categoria_id, $precio_compra, $precio_venta, $stock, $stock_minimo) {
        $stmt = $this->conexion->prepare(
            "INSERT INTO productos 
            (nombre, descripcion, categoria_id, precio_compra, precio_venta, stock, stock_minimo) 
            VALUES (:nombre, :descripcion, :categoria_id, :precio_compra, :precio_venta, :stock, :stock_minimo)"
        );
        
        return $stmt->execute([
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':categoria_id' => $categoria_id,
            ':precio_compra' => $precio_compra,
            ':precio_venta' => $precio_venta,
            ':stock' => $stock,
            ':stock_minimo' => $stock_minimo
        ]);
    }

    public function obtenerTodos($filtros = []) {
        $query = "SELECT p.*, c.nombre AS categoria_nombre 
                  FROM productos p
                  LEFT JOIN categorias c ON p.categoria_id = c.id
                  WHERE p.activo = 1";
        
        $parametros = [];

        if (!empty($filtros['categoria_id'])) {
            $query .= " AND p.categoria_id = :categoria_id";
            $parametros[':categoria_id'] = $filtros['categoria_id'];
        }

        if (!empty($filtros['nombre'])) {
            $query .= " AND p.nombre LIKE :nombre";
            $parametros[':nombre'] = "%{$filtros['nombre']}%";
        }

        $stmt = $this->conexion->prepare($query);
        $stmt->execute($parametros);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM productos WHERE id = :id AND activo = 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar($id, $datos) {
        $campos = [];
        $parametros = [':id' => $id];

        $camposPermitidos = [
            'nombre', 'descripcion', 'categoria_id', 
            'precio_compra', 'precio_venta', 'stock_minimo'
        ];

        foreach ($camposPermitidos as $campo) {
            if (isset($datos[$campo])) {
                $campos[] = "$campo = :$campo";
                $parametros[":$campo"] = $datos[$campo];
            }
        }

        if (empty($campos)) {
            return false;
        }

        $query = "UPDATE productos SET " . implode(', ', $campos) . " WHERE id = :id";
        $stmt = $this->conexion->prepare($query);
        return $stmt->execute($parametros);
    }

    public function eliminar($id) {
        $stmt = $this->conexion->prepare(
            "UPDATE productos SET activo = 0 WHERE id = :id"
        );
        return $stmt->execute([':id' => $id]);
    }

    public function actualizarStock($producto_id, $cantidad, $tipo) {
        $operador = $tipo === 'entrada' ? '+' : '-';
        $stmt = $this->conexion->prepare(
            "UPDATE productos SET stock = stock $operador :cantidad WHERE id = :producto_id"
        );
        return $stmt->execute([
            ':cantidad' => $cantidad,
            ':producto_id' => $producto_id
        ]);
    }

    public function obtenerProductosBajoStock() {
        $stmt = $this->conexion->prepare(
            "SELECT p.*, c.nombre AS categoria_nombre 
             FROM productos p
             LEFT JOIN categorias c ON p.categoria_id = c.id
             WHERE p.stock <= p.stock_minimo AND p.activo = 1
             ORDER BY p.stock ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerProductosMasVendidos($limite = 10) {
        $stmt = $this->conexion->prepare(
            "SELECT p.id, p.nombre, 
                    SUM(m.cantidad) AS total_vendido, 
                    c.nombre AS categoria_nombre
             FROM productos p
             LEFT JOIN movimientos m ON p.id = m.producto_id
             LEFT JOIN categorias c ON p.categoria_id = c.id
             WHERE m.tipo = 'salida' AND p.activo = 1
             GROUP BY p.id, p.nombre, c.nombre
             ORDER BY total_vendido DESC
             LIMIT :limite"
        );
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}