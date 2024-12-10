<?php include __DIR__ . '/../Layout/header.php'; use App\Helpers\SessionManager; use App\Model\CategoryModel as Categoria; ?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h2><?= isset($producto) ? 'Editar Producto' : 'Crear Producto' ?></h2>
        </div>
        <div class="card-body">
            <?php 
            // Recuperar errores y datos previos de la sesión
            $errores = SessionManager::get('errores') ?: [];
            $datos = SessionManager::get('datos_producto') ?: ($producto ?? []);
            
            // Limpiar datos de sesión
            // SessionManager::delete('errores');
            // SessionManager::delete('datos_producto');
            ?>

            <?php if (!empty($errores)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errores as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form id="productoForm" method="POST" action="<?= isset($producto) ? '/productos/editar/' . $producto['id'] : '/productos/crear' ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nombre" class="form-label">Nombre del Producto *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" 
                               value="<?= htmlspecialchars($datos['nombre'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="categoria_id" class="form-label">Categoría *</label>
                        <select class="form-control" id="categoria_id" name="categoria_id" required>
                            <option value="">Seleccione una categoría</option>
                            <?php $categorias = new Categoria(); foreach ($categorias->obtenerTodos() as $categoria): ?>
                                <option value="<?= $categoria['id'] ?>"
                                    <?= (isset($datos['categoria_id']) && $datos['categoria_id'] == $categoria['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoria['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= htmlspecialchars($datos['descripcion'] ?? '') ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="precio_compra" class="form-label">Precio de Compra *</label>
                        <input type="number" step="0.01" class="form-control" id="precio_compra" name="precio_compra" 
                               value="<?= $datos['precio_compra'] ?? '' ?>" required min="0">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="precio_venta" class="form-label">Precio de Venta *</label>
                        <input type="number" step="0.01" class="form-control" id="precio_venta" name="precio_venta" 
                               value="<?= $datos['precio_venta'] ?? '' ?>" required min="0">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="stock" class="form-label">Stock Actual *</label>
                        <input type="number" class="form-control" id="stock" name="stock" 
                               value="<?= $datos['stock'] ?? '' ?>" required min="0">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="stock_minimo" class="form-label">Stock Mínimo *</label>
                    <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" 
                           value="<?= $datos['stock_minimo'] ?? '' ?>" required min="0">
                </div>

                <div class="d-flex justify-content-between">
                    <a href="http://testphp.local/dashboard" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <?= isset($producto) ? 'Actualizar Producto' : 'Crear Producto' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../Layout/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('productoForm');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function(e) {
        let valid = true;
        const campos = [
            'nombre', 'categoria_id', 'precio_compra', 
            'precio_venta', 'stock', 'stock_minimo'
        ];

        // Limpiar errores previos
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        campos.forEach(campo => {
            const input = document.getElementById(campo);
            
            // Validaciones básicas
            if (input.hasAttribute('required') && input.value.trim() === '') {
                input.classList.add('is-invalid');
                valid = false;
            }

            // Validación de números positivos para campos numéricos
            if (['precio_compra', 'precio_venta', 'stock', 'stock_minimo'].includes(campo)) {
                const valor = parseFloat(input.value);
                if (isNaN(valor) || valor < 0) {
                    input.classList.add('is-invalid');
                    valid = false;
                }
            }
        });

        // Validación de precios: precio de venta debe ser mayor que precio de compra
        const precioCompra = parseFloat(document.getElementById('precio_compra').value);
        const precioVenta = parseFloat(document.getElementById('precio_venta').value);
        
        if (precioVenta <= precioCompra) {
            document.getElementById('precio_venta').classList.add('is-invalid');
            valid = false;
            alert('El precio de venta debe ser mayor que el precio de compra');
        }

        // Prevenir envío del formulario si hay errores
        if (!valid) {
            e.preventDefault();
            alert('Por favor, corrija los errores en el formulario.');
        }
    });
});
</script>