<?php
require 'auth.php';
require 'config.php';

$mensaje = '';
$tipo_mensaje = '';

// Procesar actualizacion si se envio el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
    $tabla = $_POST['tabla'] ?? '';

    try {
        switch ($tabla) {
            case 'Pais':
                $stmt = $conn->prepare("UPDATE Pais SET nombre = ?, continente = ? WHERE id_pais = ?");
                $stmt->bind_param("ssi", $_POST['nombre'], $_POST['continente'], $_POST['id']);
                $stmt->execute();
                $mensaje = "País actualizado correctamente.";
                $tipo_mensaje = 'success';
                break;

            case 'Universidad':
                $stmt = $conn->prepare("UPDATE Universidad SET nombre = ?, estado = ? WHERE id_universidad = ?");
                $stmt->bind_param("ssi", $_POST['nombre'], $_POST['estado'], $_POST['id']);
                $stmt->execute();
                $mensaje = "Universidad actualizada correctamente.";
                $tipo_mensaje = 'success';
                break;

            case 'Equipo':
                $stmt = $conn->prepare("UPDATE Equipo SET abreviacion = ?, nombre_completo = ?, ciudad = ?, conferencia = ? WHERE id_equipo = ?");
                $stmt->bind_param("ssssi", $_POST['abreviacion'], $_POST['nombre_completo'], $_POST['ciudad'], $_POST['conferencia'], $_POST['id']);
                $stmt->execute();
                $mensaje = "Equipo actualizado correctamente.";
                $tipo_mensaje = 'success';
                break;

            case 'Temporada':
                $stmt = $conn->prepare("UPDATE Temporada SET etiqueta = ?, anio_inicio = ?, anio_fin = ? WHERE id_temporada = ?");
                $stmt->bind_param("siii", $_POST['etiqueta'], $_POST['anio_inicio'], $_POST['anio_fin'], $_POST['id']);
                $stmt->execute();
                $mensaje = "Temporada actualizada correctamente.";
                $tipo_mensaje = 'success';
                break;

            case 'Jugador':
                $id_universidad = !empty($_POST['id_universidad']) ? $_POST['id_universidad'] : null;
                $stmt = $conn->prepare("UPDATE Jugador SET nombre = ?, id_pais = ?, id_universidad = ? WHERE id_jugador = ?");
                $stmt->bind_param("siii", $_POST['nombre'], $_POST['id_pais'], $id_universidad, $_POST['id']);
                $stmt->execute();
                $mensaje = "Jugador actualizado correctamente.";
                $tipo_mensaje = 'success';
                break;

            default:
                throw new Exception("Tabla no válida.");
        }
    } catch (Exception $e) {
        $mensaje = "Error: " . htmlspecialchars($e->getMessage());
        $tipo_mensaje = 'danger';
    }
}

// Determinar que tabla y registro mostrar
$tabla_actual = $_GET['tabla'] ?? '';
$id_actual = $_GET['id'] ?? '';

// Si se selecciono un registro, traer sus datos actuales
$datos = null;
if ($tabla_actual && $id_actual) {
    $pk = [
        'Pais' => 'id_pais',
        'Universidad' => 'id_universidad',
        'Equipo' => 'id_equipo',
        'Temporada' => 'id_temporada',
        'Jugador' => 'id_jugador'
    ];
    if (isset($pk[$tabla_actual])) {
        $stmt = $conn->prepare("SELECT * FROM $tabla_actual WHERE {$pk[$tabla_actual]} = ?");
        $stmt->bind_param("i", $id_actual);
        $stmt->execute();
        $datos = $stmt->get_result()->fetch_assoc();
    }
}

// Cargar listas para los selectores (mostramos solo unos pocos por tabla para no saturar)
$listas = [];
$listas['Pais'] = $conn->query("SELECT id_pais AS id, nombre FROM Pais ORDER BY nombre");
$listas['Universidad'] = $conn->query("SELECT id_universidad AS id, nombre FROM Universidad ORDER BY nombre");
$listas['Equipo'] = $conn->query("SELECT id_equipo AS id, nombre_completo AS nombre FROM Equipo ORDER BY nombre_completo");
$listas['Temporada'] = $conn->query("SELECT id_temporada AS id, etiqueta AS nombre FROM Temporada ORDER BY etiqueta");
$listas['Jugador'] = $conn->query("SELECT id_jugador AS id, nombre FROM Jugador ORDER BY nombre LIMIT 200");

// Para el form de Jugador necesitamos paises y universidades para dropdowns
$paises = $conn->query("SELECT id_pais, nombre FROM Pais ORDER BY nombre");
$universidades = $conn->query("SELECT id_universidad, nombre FROM Universidad ORDER BY nombre");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Modificar - NBA Database</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-nba { background-color: #17408B; }
        .navbar-nba .navbar-brand, .navbar-nba .nav-link { color: white; }
        .navbar-nba .nav-link:hover { color: #C9082A; }
        .btn-nba { background-color: #C9082A; color: white; border: none; }
        .btn-nba:hover { background-color: #a00622; color: white; }
        .form-header { background-color: #17408B; color: white; padding: 12px; border-radius: 6px 6px 0 0; }
        footer { background-color: #17408B; color: white; padding: 20px 0; margin-top: 50px; }
    </style>
</head>
<body>

<!-- Barra de navegacion -->
<nav class="navbar navbar-expand-lg navbar-nba">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">🏀 NBA Database</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="queries.php">Consultas</a></li>
                <li class="nav-item"><a class="nav-link" href="visualizar.php">Visualizar</a></li>
                <li class="nav-item"><a class="nav-link" href="insertar.php">Insertar</a></li>
                <li class="nav-item"><a class="nav-link" href="modificar.php">Modificar</a></li>
                <li class="nav-item"><a class="nav-link" href="graficas.php">Gráficas</a></li>
                <?php if (isset($_SESSION['usuario'])): ?>
                    <li class="nav-item"><span class="nav-link">👤 <?= htmlspecialchars($_SESSION['usuario']) ?></span></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Cerrar sesión</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Iniciar sesión</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5">
    <h1 class="mb-4">Modificar Datos</h1>
    <p class="lead">Selecciona una tabla y el registro a modificar.</p>

    <?php if ($mensaje): ?>
        <div class="alert alert-<?= $tipo_mensaje ?>"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <!-- Selector de tabla y registro -->
    <div class="card shadow-sm mb-4">
        <div class="form-header"><h4 class="mb-0">Paso 1: Seleccionar registro</h4></div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Tabla</label>
                    <select name="tabla" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Selecciona --</option>
                        <?php foreach (['Pais', 'Universidad', 'Equipo', 'Temporada', 'Jugador'] as $t): ?>
                            <option value="<?= $t ?>" <?= ($t === $tabla_actual) ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($tabla_actual && isset($listas[$tabla_actual])): ?>
                    <div class="col-md-6">
                        <label class="form-label">Registro</label>
                        <select name="id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Selecciona un registro --</option>
                            <?php while ($r = $listas[$tabla_actual]->fetch_assoc()): ?>
                                <option value="<?= $r['id'] ?>" <?= ($r['id'] == $id_actual) ? 'selected' : '' ?>>
                                    [<?= $r['id'] ?>] <?= htmlspecialchars($r['nombre']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <?php if ($tabla_actual === 'Jugador'): ?>
                            <small class="text-muted">Mostrando los primeros 200 jugadores ordenados alfabéticamente.</small>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Formulario de edicion segun la tabla -->
    <?php if ($datos): ?>
        <div class="card shadow-sm mb-4">
            <div class="form-header"><h4 class="mb-0">Paso 2: Editar <?= $tabla_actual ?> (ID: <?= $id_actual ?>)</h4></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="accion" value="actualizar">
                    <input type="hidden" name="tabla" value="<?= $tabla_actual ?>">
                    <input type="hidden" name="id" value="<?= $id_actual ?>">

                    <?php if ($tabla_actual === 'Pais'): ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($datos['nombre']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Continente</label>
                                <select name="continente" class="form-select">
                                    <?php foreach (['America', 'Europa', 'Asia', 'Africa', 'Oceania', 'Otro'] as $c): ?>
                                        <option value="<?= $c ?>" <?= ($c === $datos['continente']) ? 'selected' : '' ?>><?= $c ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                    <?php elseif ($tabla_actual === 'Universidad'): ?>
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($datos['nombre']) ?>" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Estado</label>
                                <input type="text" name="estado" class="form-control" value="<?= htmlspecialchars($datos['estado'] ?? '') ?>">
                            </div>
                        </div>

                    <?php elseif ($tabla_actual === 'Equipo'): ?>
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">Abreviación</label>
                                <input type="text" name="abreviacion" class="form-control" value="<?= htmlspecialchars($datos['abreviacion']) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Nombre completo</label>
                                <input type="text" name="nombre_completo" class="form-control" value="<?= htmlspecialchars($datos['nombre_completo'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Ciudad</label>
                                <input type="text" name="ciudad" class="form-control" value="<?= htmlspecialchars($datos['ciudad'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Conferencia</label>
                                <select name="conferencia" class="form-select">
                                    <?php foreach (['Este', 'Oeste'] as $c): ?>
                                        <option value="<?= $c ?>" <?= ($c === $datos['conferencia']) ? 'selected' : '' ?>><?= $c ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                    <?php elseif ($tabla_actual === 'Temporada'): ?>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Etiqueta</label>
                                <input type="text" name="etiqueta" class="form-control" value="<?= htmlspecialchars($datos['etiqueta']) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Año inicio</label>
                                <input type="number" name="anio_inicio" class="form-control" value="<?= $datos['anio_inicio'] ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Año fin</label>
                                <input type="number" name="anio_fin" class="form-control" value="<?= $datos['anio_fin'] ?>" required>
                            </div>
                        </div>

                    <?php elseif ($tabla_actual === 'Jugador'): ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($datos['nombre']) ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">País</label>
                                <select name="id_pais" class="form-select" required>
                                    <?php while ($p = $paises->fetch_assoc()): ?>
                                        <option value="<?= $p['id_pais'] ?>" <?= ($p['id_pais'] == $datos['id_pais']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p['nombre']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Universidad</label>
                                <select name="id_universidad" class="form-select">
                                    <option value="">-- Ninguna --</option>
                                    <?php while ($u = $universidades->fetch_assoc()): ?>
                                        <option value="<?= $u['id_universidad'] ?>" <?= ($u['id_universidad'] == ($datos['id_universidad'] ?? 0)) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($u['nombre']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-nba mt-3">Guardar cambios</button>
                </form>
            </div>
        </div>
    <?php elseif ($tabla_actual && $id_actual): ?>
        <div class="alert alert-warning">No se encontró el registro seleccionado.</div>
    <?php endif; ?>

</div>

<footer class="text-center">
    <p class="mb-0">Proyecto Final COMP4018 - Luis Gonzalez -2026</p>
</footer>

<?php $conn->close(); ?>
</body>
</html>