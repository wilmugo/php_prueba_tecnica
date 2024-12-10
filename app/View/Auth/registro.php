<?php
use App\Helpers\SessionManager;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
            background-color: #f9f9f9;
        }

            h1 {
                text-align: center;
                color: #333;
            }

            form {
                max-width: 60%;
                margin: 0 auto;
                padding: 20px;
                background: #fff;
                border: 1px solid #ccc;
                border-radius: 8px;
            }

            form label {
                display: block;
                margin-bottom: 5px;
                color: #555;
            }

            form input, form select, form button {
                width: 96%;
                margin: 15px;
                padding: 10px;
                border: 1px solid #ccc;
                border-radius: 4px;
            }

            form button {
                background-color: #007BFF;
                color: #fff;
                border: none;
                cursor: pointer;
            }

            form button:hover {
                background-color: #0056b3;
            }

            .errores, .error {
                max-width: 400px;
                margin: 10px auto;
                padding: 10px;
                background-color: #f8d7da;
                border: 1px solid #f5c6cb;
                border-radius: 8px;
                color: #721c24;
            }
        
    </style>
</head>
<body>
    <h1>Registro de Usuario</h1>

    <?php if (SessionManager::get('errores')): ?>
        <div class="errores">
            <ul>
                <?php foreach (SessionManager::get('errores') as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php SessionManager::get('errores'); endif; ?>

    <form id="registroForm" action="/registro" method="POST">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required>

        <label for="email">Correo Electrónico:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required minlength="8">

        <label for="confirmar_password">Confirmar Contraseña:</label>
        <input type="password" id="confirmar_password" name="confirmar_password" required>

        <label for="rol">Rol:</label>
        <select id="rol" name="rol" required>
            <option value="empleado">Empleado</option>
            <option value="admin">Administrador</option>
            <option value="supervisor">Supervisor</option>
        </select>

        <button type="submit">Registrarse</button>
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const registroForm = document.getElementById('registroForm');

            registroForm.addEventListener('submit', (e) => {
                const password = document.getElementById('password').value;
                const confirmarPassword = document.getElementById('confirmar_password').value;

                if (password !== confirmarPassword) {
                    e.preventDefault();
                    alert('Las contraseñas no coinciden');
                }
            });
        });

    </script>
</body>
</html>
