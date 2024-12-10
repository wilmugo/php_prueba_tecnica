<?php
namespace App\Controller;

use App\Model\ProductModel as Producto;
use App\Model\UserModel as Ususario;
use App\Helpers\Validador;
use App\Helpers\SessionManager;
use App\Database\Database;

class InventarioController {
    private $conexion;
    private $productoModel;

    public function __construct() {
        $this->conexion = Database::conectar();
        $this->productoModel = new Producto();
    }

    public function registrarEntrada() {
        SessionManager::iniciar();

        // Validar permisos
        if (!SessionManager::tieneRol(['admin', 'empleado'])) {
            SessionManager::set('error', 'No tiene permisos para registrar entradas');
            header('Location: /dashboard');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errores = [];

            // Validaciones
            $producto_id = Validador::limpiarInput($_POST['producto_id']);
            $cantidad = Validador::limpiarInput($_POST['cantidad']);
            $descripcion = Validador::limpiarInput($_POST['descripcion']);
            $usuario_id = SessionManager::get('usuario_id');

            // Validación de cantidad
            if (!Validador::validarNumeroPositivo($cantidad)) {
                $errores[] = "Cantidad inválida";
            }

            $producto = $this->productoModel->obtenerPorId($producto_id);
            if (!$producto) {
                $errores[] = "Producto no encontrado";
            }

            if (empty($errores)) {
                try {
                    $this->conexion->beginTransaction();

                    // Actualizar stock del producto
                    $this->productoModel->actualizarStock($producto_id, $cantidad, 'entrada');

                    // Registrar movimiento
                    $stmt = $this->conexion->prepare(
                        "INSERT INTO movimientos 
                        (producto_id, cantidad, tipo, usuario_id, descripcion) 
                        VALUES (:producto_id, :cantidad, 'entrada', :usuario_id, :descripcion)"
                    );

                    $stmt->execute([
                        ':producto_id' => $producto_id,
                        ':cantidad' => $cantidad,
                        ':usuario_id' => $usuario_id,
                        ':descripcion' => $descripcion
                    ]);

                    $this->conexion->commit();

                    SessionManager::set('mensaje', 'Entrada de inventario registrada exitosamente');
                    header('Location: /inventario/movimientos');
                    exit();

                } catch (\PDOException $e) {
                    $this->conexion->rollBack();
                    $errores[] = "Error al registrar entrada: " . $e->getMessage();
                }
            }

            SessionManager::set('errores', $errores);
            header('Location: /inventario/entrada');
            exit();
        }

        $productos = $this->productoModel->obtenerTodos();
        include __DIR__ . '/../views/inventario/entrada.php';
    }

    public function registrarSalida() {
        SessionManager::iniciar();

        // Validar permisos
        if (!SessionManager::tieneRol(['admin', 'empleado'])) {
            SessionManager::set('error', 'No tiene permisos para registrar salidas');
            header('Location: /dashboard');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errores = [];

            // Validaciones
            $producto_id = Validador::limpiarInput($_POST['producto_id']);
            $cantidad = Validador::limpiarInput($_POST['cantidad']);
            $descripcion = Validador::limpiarInput($_POST['descripcion']);
            $usuario_id = SessionManager::get('usuario_id');

            // Validación de cantidad
            if (!Validador::validarNumeroPositivo($cantidad)) {
                $errores[] = "Cantidad inválida";
            }

            $producto = $this->productoModel->obtenerPorId($producto_id);
            if (!$producto) {
                $errores[] = "Producto no encontrado";
            }

            // Verificar stock disponible
            if ($producto['stock'] < $cantidad) {
                $errores[] = "Stock insuficiente. Stock actual: {$producto['stock']}";
            }

            if (empty($errores)) {
                try {
                    $this->conexion->beginTransaction();

                    // Actualizar stock del producto
                    $this->productoModel->actualizarStock($producto_id, $cantidad, 'salida');

                    // Registrar movimiento
                    $stmt = $this->conexion->prepare(
                        "INSERT INTO movimientos 
                        (producto_id, cantidad, tipo, usuario_id, descripcion) 
                        VALUES (:producto_id, :cantidad, 'salida', :usuario_id, :descripcion)"
                    );

                    $stmt->execute([
                        ':producto_id' => $producto_id,
                        ':cantidad' => $cantidad,
                        ':usuario_id' => $usuario_id,
                        ':descripcion' => $descripcion
                    ]);

                    $this->conexion->commit();

                    SessionManager::set('mensaje', 'Salida de inventario registrada exitosamente');
                    header('Location: /inventario/movimientos');
                    exit();

                } catch (\PDOException $e) {
                    $this->conexion->rollBack();
                    $errores[] = "Error al registrar salida: " . $e->getMessage();
                }
            }

            SessionManager::set('errores', $errores);
            header('Location: /inventario/salida');
            exit();
        }

        $productos = $this->productoModel->obtenerTodos();
        include __DIR__ . '/../views/inventario/salida.php';
    }

    public function listarMovimientos() {
        SessionManager::iniciar();

        // Validar permisos
        if (!SessionManager::tieneRol(['admin', 'supervisor'])) {
            SessionManager::set('error', 'No tiene permisos para ver movimientos');
            header('Location: /dashboard');
            exit();
        }

        // Filtros
        $filtros = [
            'tipo' => $_GET['tipo'] ?? null,
            'fecha_inicio' => $_GET['fecha_inicio'] ?? null,
            'fecha_fin' => $_GET['fecha_fin'] ?? null
        ];

        // Consulta de movimientos con filtros
        $query = "SELECT m.*, p.nombre AS producto_nombre, u.nombre AS usuario_nombre 
                  FROM movimientos m
                  JOIN productos p ON m.producto_id = p.id
                  JOIN usuarios u ON m.usuario_id = u.id
                  WHERE 1=1";

        $parametros = [];

        if (!empty($filtros['tipo'])) {
            $query .= " AND m.tipo = :tipo";
            $parametros[':tipo'] = $filtros['tipo'];
        }

        if (!empty($filtros['fecha_inicio'])) {
            $query .= " AND m.fecha >= :fecha_inicio";
            $parametros[':fecha_inicio'] = $filtros['fecha_inicio'];
        }

        if (!empty($filtros['fecha_fin'])) {
            $query .= " AND m.fecha <= :fecha_fin";
            $parametros[':fecha_fin'] = $filtros['fecha_fin'];
        }

        $query .= " ORDER BY m.fecha DESC";

        $stmt = $this->conexion->prepare($query);
        $stmt->execute($parametros);
        $movimientos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        include __DIR__ . '/../View/inventario/movimientos.php';
    }
}