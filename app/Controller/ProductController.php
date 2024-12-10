<?php
namespace App\Controller;

use App\Model\ProductModel as Producto;
use App\Model\CategoryModel as Categoria;
use App\Helpers\Validador;
use App\Helpers\SessionManager;

class ProductController {
    private $productoModel;
    private $categoriaModel;

    public function __construct() {
        $this->productoModel = new Producto();
        $this->categoriaModel = new Categoria();
    }

    public function index() {
        SessionManager::iniciar();

        // Validar permisos
        if (!SessionManager::tieneRol(['admin', 'supervisor'])) {
            SessionManager::set('error', 'No tiene permisos para acceder');
            header('Location: /dashboard');
            exit();
        }

        $filtros = [
            'categoria_id' => $_GET['categoria_id'] ?? null,
            'nombre' => $_GET['nombre'] ?? null
        ];
        

        $data['productos'] = $this->productoModel->obtenerTodos($filtros);
        $data['categorias'] = $this->categoriaModel->obtenerTodos();
        view('dashboard', $data);

        // include __DIR__ . '/../views/productos/listar.php';
    }

    public function crear() {
        SessionManager::iniciar();

        // Validar permisos
        if (!SessionManager::tieneRol(['admin'])) {
            SessionManager::set('error', 'No tiene permisos para crear productos');
            header('Location: /productos');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errores = [];

            // Validaciones
            $nombre = Validador::limpiarInput($_POST['nombre']);
            $descripcion = Validador::limpiarInput($_POST['descripcion']);
            $categoria_id = Validador::limpiarInput($_POST['categoria_id']);
            $precio_compra = Validador::limpiarInput($_POST['precio_compra']);
            $precio_venta = Validador::limpiarInput($_POST['precio_venta']);
            $stock = Validador::limpiarInput($_POST['stock']);
            $stock_minimo = Validador::limpiarInput($_POST['stock_minimo']);

            // Validaciones de campos
            if (empty($nombre)) {
                $errores[] = "El nombre del producto es obligatorio";
            }

            if (!Validador::validarNumeroPositivo($precio_compra)) {
                $errores[] = "Precio de compra inválido";
            }

            if (!Validador::validarNumeroPositivo($precio_venta)) {
                $errores[] = "Precio de venta inválido";
            }

            if (!Validador::validarNumeroPositivo($stock)) {
                $errores[] = "Stock inválido";
            }

            if (!Validador::validarNumeroPositivo($stock_minimo)) {
                $errores[] = "Stock mínimo inválido";
            }

            if (empty($errores)) {
                try {
                    if ($this->productoModel->crear(
                        $nombre, $descripcion, $categoria_id, 
                        $precio_compra, $precio_venta, $stock, $stock_minimo
                    )) {
                        SessionManager::set('mensaje', 'Producto creado exitosamente');
                        header('Location: /dashboard');
                        exit();
                    }
                } catch (\PDOException $e) {
                    $errores[] = "Error al crear producto: " . $e->getMessage();
                }
            }

            // Mantener datos en caso de error
            SessionManager::set('errores', $errores);
            SessionManager::set('datos_producto', $_POST);
            header('Location: /productos/crear');
            exit();
        }

        $categorias = $this->categoriaModel->obtenerTodos();
        view('Product/Product');
        // include __DIR__ . '/../views/productos/crear.php';
    }

    public function editar($id) {
        SessionManager::iniciar();

        // Validar permisos
        if (!SessionManager::tieneRol(['admin'])) {
            SessionManager::set('error', 'No tiene permisos para editar productos');
            header('Location: /productos');
            exit();
        }

        $producto = $this->productoModel->obtenerPorId($id);

        if (!$producto) {
            SessionManager::set('error', 'Producto no encontrado');
            header('Location: /productos');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errores = [];

            // Validaciones similares a crear()
            $datos = [
                'nombre' => Validador::limpiarInput($_POST['nombre']),
                'descripcion' => Validador::limpiarInput($_POST['descripcion']),
                'categoria_id' => Validador::limpiarInput($_POST['categoria_id']),
                'precio_compra' => Validador::limpiarInput($_POST['precio_compra']),
                'precio_venta' => Validador::limpiarInput($_POST['precio_venta']),
                'stock_minimo' => Validador::limpiarInput($_POST['stock_minimo'])
            ];

            // Validaciones de campos
            // ... (similar a crear())

            if (empty($errores)) {
                try {
                    if ($this->productoModel->actualizar($id, $datos)) {
                        SessionManager::set('mensaje', 'Producto actualizado exitosamente');
                        header('Location: /productos');
                        exit();
                    }
                } catch (\PDOException $e) {
                    $errores[] = "Error al actualizar producto: " . $e->getMessage();
                }
            }

            SessionManager::set('errores', $errores);
            header('Location: /productos/editar/' . $id);
            exit();
        }

        $categorias = $this->categoriaModel->obtenerTodos();
        include __DIR__ . '/../views/productos/editar.php';
    }

    public function eliminar($id) {
        SessionManager::iniciar();

        // Validar permisos
        if (!SessionManager::tieneRol(['admin'])) {
            SessionManager::set('error', 'No tiene permisos para eliminar productos');
            header('Location: /productos');
            exit();
        }

        try {
            if ($this->productoModel->eliminar($id)) {
                SessionManager::set('mensaje', 'Producto eliminado exitosamente');
            } else {
                SessionManager::set('error', 'No se pudo eliminar el producto');
            }
        } catch (\PDOException $e) {
            SessionManager::set('error', 'Error al eliminar producto: ' . $e->getMessage());
        }

        header('Location: /productos');
        exit();
    }
}