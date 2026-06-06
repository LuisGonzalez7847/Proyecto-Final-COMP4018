<?php
require 'config.php';

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if (strlen($username) < 3) {
        $mensaje = "El username debe tener al menos 3 caracteres.";
        $tipo_mensaje = 'danger';
    } elseif (strlen($password) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres.";
        $tipo_mensaje = 'danger';
    } elseif ($password !== $confirmar) {
        $mensaje = "Las contraseñas no coinciden.";
        $tipo_mensaje = 'danger';
    } else {
        // Encriptar la contrasena antes de guardarla
        $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $conn->prepare("INSERT INTO Usuario (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hash);
            $stmt->execute();
            $mensaje = "Cuenta creada exitosamente. Ya puedes iniciar sesion.";
            $tipo_mensaje = 'success';
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate')) {
                $mensaje = "Ese nombre de usuario ya esta tomado.";
            } else {
                $mensaje = "Error: " . htmlspecialchars($e->getMessage());
            }
            $tipo_mensaje = 'danger';
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrarse - NBA Database</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #17408B 0%, #C9082A 100%); min-height: 100vh; }
        .card-login { max-width: 450px; margin: 80px auto; }
        .btn-nba { background-color: #C9082A; color: white; border: none; }
        .btn-nba:hover { background-color: #a00622; color: white; }
        .header-login { background-color: #17408B; color: white; padding: 20px; text-align: center; border-radius: 6px 6px 0 0; }
    </style>
</head>
<body>

<div class="container">
    <div class="card shadow card-login">
        <div class="header-login">
            <h2 class="mb-0">🏀 Crear Cuenta</h2>
        </div>
        <div class="card-body p-4">

            <?php if ($mensaje): ?>
                <div class="alert alert-<?= $tipo_mensaje ?>"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nombre de usuario</label>
                    <input type="text" name="username" class="form-control" required minlength="3" maxlength="50">
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required minlength="6">
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirmar contraseña</label>
                    <input type="password" name="confirmar" class="form-control" required minlength="6">
                </div>
                <button type="submit" class="btn btn-nba w-100">Registrarse</button>
            </form>

            <hr>
            <p class="text-center mb-0">
                ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
            </p>
        </div>
    </div>
</div>

</body>
</html>