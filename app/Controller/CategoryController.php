<?php
namespace App\Controller;

use App\Model\CategoryModel as Categoria;
use App\Helpers\Validador;
use App\Helpers\SessionManager;

class CategoriaController {
    private $categoriaModel;

    public function __construct() {
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

        $categorias = $this->categoriaModel->obtenerCategoriasConProductos();
        include __DIR__ . '/../views/categorias/listar.php';
    }

    public function crear() {
        SessionManager::iniciar();

        // Validar permisos
        if (!SessionManager::tieneRol(['admin'])) {
            SessionManager::set('error', 'No tiene permisos para crear categorías');
            header('Location: /categorias');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errores = [];

            // Validaciones
            $nombre = Validador::limpiarInput($_POST['nombre']);
            $descripcion = Validador::limpiarInput($_POST['descripcion']);

            // Validación de nombre
            if (empty($nombre)) {
                $errores[] = "El nombre de la categoría es obligatorio";
            }

            if (strlen($nombre) > 100) {
                $errores[] = "El nombre no puede exceder 100 caracteres";
            }

            if (empty($errores)) {
                try {
                    if ($this->categoriaModel->crear($nombre, $descripcion)) {
                        SessionManager::set('mensaje', 'Categoría creada exitosamente');
                        header('Location: /categorias');
                        exit();
                    }
                } catch (\PDOException $e) {
                    $errores[] = "Error al crear categoría: " . $e->getMessage();
                }
            }

            // Mantener datos en caso de error
            SessionManager::set('errores', $errores);
            SessionManager::set('datos_categoria', $_POST);
            header('Location: /categorias/crear');
            exit();
        }

        include __DIR__ . '/../views/categorias/crear.php';
    }

    public function editar($id) {
        SessionManager::iniciar();

        // Validar permisos
        if (!SessionManager::tieneRol(['admin'])) {
            SessionManager::set('error', 'No tiene permisos para editar categorías');
            header('Location: /categorias');
            exit();
        }

        $categoria = $this->categoriaModel->obtenerPorId($id);

        if (!$categoria) {
            SessionManager::set('error', 'Categoría no encontrada');
            header('Location: /categorias');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errores = [];

            // Validaciones
            $datos = [
                'nombre' => Validador::limpiarInput($_POST['nombre']),
                'descripcion' => Validador::limpiarInput($_POST['descripcion'])
            ];

            // Validación de nombre
            if (empty($datos['nombre'])) {
                $errores[] = "El nombre de la categoría es obligatorio";
            }

            if (strlen($datos['nombre']) > 100) {
                $errores[] = "El nombre no puede exceder 100 caracteres";
            }

            if (empty($errores)) {
                try {
                    if ($this->categoriaModel->actualizar($id, $datos)) {
                        SessionManager::set('mensaje', 'Categoría actualizada exitosamente');
                        header('Location: /categorias');
                        exit();
                    }
                } catch (\PDOException $e) {
                    $errores[] = "Error al actualizar categoría: " . $e->getMessage();
                }
            }

            SessionManager::set('errores', $errores);
            header('Location: /categorias/editar/' . $id);
            exit();
        }

        include __DIR__ . '/../views/categorias/editar.php';
    }

    public function eliminar($id) {
        SessionManager::iniciar();

        // Validar permisos
        if (!SessionManager::tieneRol(['admin'])) {
            SessionManager::set('error', 'No tiene permisos para eliminar categorías');
            header('Location: /categorias');
            exit();
        }

        try {
            if ($this->categoriaModel->eliminar($id)) {
                SessionManager::set('mensaje', 'Categoría eliminada exitosamente');
            } else {
                SessionManager::set('error', 'No se pudo eliminar la categoría');
            }
        } catch (\PDOException $e) {
            SessionManager::set('error', 'Error al eliminar categoría: ' . $e->getMessage());
        }

        header('Location: /categorias');
        exit();
    }
}