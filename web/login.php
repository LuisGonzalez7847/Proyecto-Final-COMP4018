<?php
session_start();
require 'config.php';

// Si ya esta logueado, redirigir a inicio
if (isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id_usuario, password FROM Usuario WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        if (password_verify($password, $usuario['password'])) {
            // Login exitoso
            $_SESSION['usuario'] = $username;
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            header("Location: index.php");
            exit();
        }
    }
    $mensaje = "Usuario o contraseña incorrectos.";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - NBA Database</title>
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
            <h2 class="mb-0">🏀 Iniciar Sesión</h2>
        </div>
        <div class="card-body p-4">

            <?php if ($mensaje): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nombre de usuario</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-nba w-100">Iniciar sesión</button>
            </form>

            <hr>
            <p class="text-center mb-0">
                ¿No tienes cuenta? <a href="register.php">Regístrate</a>
            </p>
        </div>
    </div>
</div>

</body>
</html>