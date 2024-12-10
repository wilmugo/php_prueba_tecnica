<?php
use App\Helpers\SessionManager;
use App\Model\CategoryModel as Categoria;
use App\Model\ProductModel as Producto;
require_once __DIR__ . '/Layout/header.php';
?>



<div class="container mt-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2>Gestión de Productos</h2>
            <?php if (SessionManager::tieneRol(['admin'])): ?>
                <a href="http://testphp.local/productos/crear" class="btn btn-primary">Nuevo Producto</a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <!-- Filtro de productos -->
            <form method="get" action="/productos" class="mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <select name="categoria_id" class="form-control">
                            <option value="">Todas las Categorías</option>
                            <?php  foreach ($data['categorias'] as $categoria): ?>
                                <option value="<?= $categoria['id'] ?>" 
                                    <?= isset($_GET['categoria_id']) && $_GET['categoria_id'] == $categoria['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoria['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="nombre" class="form-control" placeholder="Buscar por nombre" 
                               value="<?= $_GET['nombre'] ?? '' ?>">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-secondary">Filtrar</button>
                        <a href="/productos" class="btn btn-outline-secondary">Limpiar</a>
                    </div>
                </div>
            </form>


            <!-- Tabla de productos -->
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Precio Compra</th>
                        <th>Precio Venta</th>
                        <th>Stock</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['productos'] as $producto): ?>
                        <tr>
                            <td><?= htmlspecialchars($producto['nombre']) ?></td>
                            <td><?= htmlspecialchars($producto['categoria_nombre']) ?></td>
                            <td>$<?= number_format($producto['precio_compra'], 2) ?></td>
                            <td>$<?= number_format($producto['precio_venta'], 2) ?></td>
                            <td><?= $producto['stock'] ?> (Mín. <?= $producto['stock_minimo'] ?>)</td>
                            <td>
                                <?php if (SessionManager::tieneRol(['admin'])): ?>
                                    <div class="btn-group" role="group">
                                        <a href="/productos/editar/<?= $producto['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                        <button class="btn btn-sm btn-danger" onclick="confirmarEliminar(<?= $producto['id'] ?>)">Eliminar</button>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<script>
function confirmarEliminar(id) {
    if (confirm('¿Está seguro de que desea eliminar este producto? Esta acción no se puede deshacer.')) {
        window.location.href = `/productos/eliminar/${id}`;
    }
}
</script>


<?php
// require_once __DIR__ . '/Layout/footer.php';
?>