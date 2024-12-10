<?php
use App\Helpers\SessionManager;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Iniciar Sesión</title>
</head>
<body>
    <h1>Iniciar Sesión</h1>

    <?php if (SessionManager::get('error')): ?>
        <div class="error">
            <p><?= htmlspecialchars(SessionManager::get('error')) ?></p>
        </div>
    <?php  endif; ?>

    <div class="container">
    <div class="row">

    </div>
    <form action="/login" method="POST" class="row g-3" >
        <label for="email" class="form-label" >Correo Electrónico:</label>
        <input class="form-control"  type="email" id="email" name="email" required>

        <label for="password" class="form-label" >Contraseña:</label>
        <input class="form-control" type="password" id="password" name="password" required>

        <button type="submit">Iniciar Sesión</button>
    </form>

    </div>
</body>
</html>
