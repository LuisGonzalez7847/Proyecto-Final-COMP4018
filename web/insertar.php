<?php
require 'auth.php';
require 'config.php';

$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario si se envio uno
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tabla = $_POST['tabla'] ?? '';

    try {
        switch ($tabla) {
            case 'Pais':
                $stmt = $conn->prepare("INSERT INTO Pais (nombre, continente) VALUES (?, ?)");
                $stmt->bind_param("ss", $_POST['nombre'], $_POST['continente']);
                $stmt->execute();
                $mensaje = "País '" . htmlspecialchars($_POST['nombre']) . "' insertado correctamente (ID: " . $conn->insert_id . ").";
                $tipo_mensaje = 'success';
                break;

            case 'Universidad':
                $stmt = $conn->prepare("INSERT INTO Universidad (nombre, estado) VALUES (?, ?)");
                $stmt->bind_param("ss", $_POST['nombre'], $_POST['estado']);
                $stmt->execute();
                $mensaje = "Universidad insertada correctamente (ID: " . $conn->insert_id . ").";
                $tipo_mensaje = 'success';
                break;

            case 'Equipo':
                $stmt = $conn->prepare("INSERT INTO Equipo (abreviacion, nombre_completo, ciudad, conferencia) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $_POST['abreviacion'], $_POST['nombre_completo'], $_POST['ciudad'], $_POST['conferencia']);
                $stmt->execute();
                $mensaje = "Equipo insertado correctamente (ID: " . $conn->insert_id . ").";
                $tipo_mensaje = 'success';
                break;

            case 'Temporada':
                $stmt = $conn->prepare("INSERT INTO Temporada (etiqueta, anio_inicio, anio_fin) VALUES (?, ?, ?)");
                $stmt->bind_param("sii", $_POST['etiqueta'], $_POST['anio_inicio'], $_POST['anio_fin']);
                $stmt->execute();
                $mensaje = "Temporada insertada correctamente (ID: " . $conn->insert_id . ").";
                $tipo_mensaje = 'success';
                break;

            case 'Jugador':
                // Insertar primero en Jugador
                $id_universidad = !empty($_POST['id_universidad']) ? $_POST['id_universidad'] : null;
                $stmt = $conn->prepare("INSERT INTO Jugador (nombre, id_pais, id_universidad) VALUES (?, ?, ?)");
                $stmt->bind_param("sii", $_POST['nombre'], $_POST['id_pais'], $id_universidad);
                $stmt->execute();
                $nuevo_id = $conn->insert_id;

                // Insertar en el subtipo correspondiente
                if ($_POST['tipo_jugador'] === 'drafteado') {
                    $stmt2 = $conn->prepare("INSERT INTO JugadorDrafteado (id_jugador, anio_draft, ronda, numero_pick, id_equipo_draft) VALUES (?, ?, ?, ?, ?)");
                    $stmt2->bind_param("iiiii", $nuevo_id, $_POST['anio_draft'], $_POST['ronda'], $_POST['numero_pick'], $_POST['id_equipo_draft']);
                    $stmt2->execute();
                    $mensaje = "Jugador drafteado '" . htmlspecialchars($_POST['nombre']) . "' insertado correctamente (ID: $nuevo_id).";
                } else {
                    $stmt2 = $conn->prepare("INSERT INTO JugadorNoDrafteado (id_jugador, anio_ingreso_liga) VALUES (?, ?)");
                    $stmt2->bind_param("ii", $nuevo_id, $_POST['anio_ingreso_liga']);
                    $stmt2->execute();
                    $mensaje = "Jugador no drafteado '" . htmlspecialchars($_POST['nombre']) . "' insertado correctamente (ID: $nuevo_id).";
                }
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

// Cargar datos para los dropdowns del formulario de Jugador
$paises = $conn->query("SELECT id_pais, nombre FROM Pais ORDER BY nombre");
$universidades = $conn->query("SELECT id_universidad, nombre FROM Universidad ORDER BY nombre");
$equipos = $conn->query("SELECT id_equipo, nombre_completo FROM Equipo ORDER BY nombre_completo");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Insertar - NBA Database</title>
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
    <h1 class="mb-4">Insertar Datos</h1>
    <p class="lead">Añade nuevos registros a las tablas principales de la base de datos.</p>

    <?php if ($mensaje): ?>
        <div class="alert alert-<?= $tipo_mensaje ?>"><?= $mensaje ?></div>
    <?php endif; ?>

    <!-- Form Pais -->
    <div class="card shadow-sm mb-4">
        <div class="form-header"><h4 class="mb-0">Insertar País</h4></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="tabla" value="Pais">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre del país</label>
                        <input type="text" name="nombre" class="form-control" required maxlength="80">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Continente</label>
                        <select name="continente" class="form-select">
                            <option value="America">América</option>
                            <option value="Europa">Europa</option>
                            <option value="Asia">Asia</option>
                            <option value="Africa">África</option>
                            <option value="Oceania">Oceanía</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-nba mt-3">Insertar País</button>
            </form>
        </div>
    </div>

    <!-- Form Universidad -->
    <div class="card shadow-sm mb-4">
        <div class="form-header"><h4 class="mb-0">Insertar Universidad</h4></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="tabla" value="Universidad">
                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required maxlength="120">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Estado (opcional)</label>
                        <input type="text" name="estado" class="form-control" maxlength="60">
                    </div>
                </div>
                <button type="submit" class="btn btn-nba mt-3">Insertar Universidad</button>
            </form>
        </div>
    </div>

    <!-- Form Equipo -->
    <div class="card shadow-sm mb-4">
        <div class="form-header"><h4 class="mb-0">Insertar Equipo</h4></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="tabla" value="Equipo">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Abreviación</label>
                        <input type="text" name="abreviacion" class="form-control" required maxlength="5">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nombre completo</label>
                        <input type="text" name="nombre_completo" class="form-control" maxlength="80">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Ciudad</label>
                        <input type="text" name="ciudad" class="form-control" maxlength="60">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Conferencia</label>
                        <select name="conferencia" class="form-select">
                            <option value="Este">Este</option>
                            <option value="Oeste">Oeste</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-nba mt-3">Insertar Equipo</button>
            </form>
        </div>
    </div>

    <!-- Form Temporada -->
    <div class="card shadow-sm mb-4">
        <div class="form-header"><h4 class="mb-0">Insertar Temporada</h4></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="tabla" value="Temporada">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Etiqueta (ej: 2023-24)</label>
                        <input type="text" name="etiqueta" class="form-control" required maxlength="10">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Año inicio</label>
                        <input type="number" name="anio_inicio" class="form-control" required min="1900" max="2100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Año fin</label>
                        <input type="number" name="anio_fin" class="form-control" required min="1900" max="2100">
                    </div>
                </div>
                <button type="submit" class="btn btn-nba mt-3">Insertar Temporada</button>
            </form>
        </div>
    </div>

    <!-- Form Jugador (con jerarquia) -->
    <div class="card shadow-sm mb-4">
        <div class="form-header"><h4 class="mb-0">Insertar Jugador (con jerarquía)</h4></div>
        <div class="card-body">
            <form method="POST" id="form-jugador">
                <input type="hidden" name="tabla" value="Jugador">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre del jugador</label>
                        <input type="text" name="nombre" class="form-control" required maxlength="100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">País</label>
                        <select name="id_pais" class="form-select" required>
                            <?php while ($p = $paises->fetch_assoc()): ?>
                                <option value="<?= $p['id_pais'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Universidad (opcional)</label>
                        <select name="id_universidad" class="form-select">
                            <option value="">-- Ninguna --</option>
                            <?php while ($u = $universidades->fetch_assoc()): ?>
                                <option value="<?= $u['id_universidad'] ?>"><?= htmlspecialchars($u['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <hr class="my-4">
                <h5>Tipo de Jugador (jerarquía)</h5>

                <div class="row g-3">
                    <div class="col-md-12">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="tipo_jugador" id="tipo_d" value="drafteado" checked onchange="toggleCamposJugador()">
                            <label class="form-check-label" for="tipo_d">Drafteado</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="tipo_jugador" id="tipo_nd" value="no_drafteado" onchange="toggleCamposJugador()">
                            <label class="form-check-label" for="tipo_nd">No Drafteado</label>
                        </div>
                    </div>
                </div>

                <!-- Campos drafteado -->
                <div id="campos-drafteado" class="row g-3 mt-1">
                    <div class="col-md-3">
                        <label class="form-label">Año del draft</label>
                        <input type="number" name="anio_draft" class="form-control" min="1900" max="2100">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Ronda</label>
                        <input type="number" name="ronda" class="form-control" min="1" max="3">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Pick #</label>
                        <input type="number" name="numero_pick" class="form-control" min="1">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Equipo que lo drafteó</label>
                        <select name="id_equipo_draft" class="form-select">
                            <?php while ($e = $equipos->fetch_assoc()): ?>
                                <option value="<?= $e['id_equipo'] ?>"><?= htmlspecialchars($e['nombre_completo']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <!-- Campos no drafteado -->
                <div id="campos-no-drafteado" class="row g-3 mt-1" style="display:none;">
                    <div class="col-md-4">
                        <label class="form-label">Año de ingreso a la liga</label>
                        <input type="number" name="anio_ingreso_liga" class="form-control" min="1900" max="2100">
                    </div>
                </div>

                <button type="submit" class="btn btn-nba mt-3">Insertar Jugador</button>
            </form>
        </div>
    </div>

</div>

<footer class="text-center">
    <p class="mb-0">Proyecto Final COMP4018 - </p>
</footer>

<script>
// Mostrar u ocultar campos segun el tipo de jugador
function toggleCamposJugador() {
    const drafteado = document.getElementById('tipo_d').checked;
    document.getElementById('campos-drafteado').style.display = drafteado ? 'flex' : 'none';
    document.getElementById('campos-no-drafteado').style.display = drafteado ? 'none' : 'flex';
}
</script>

<?php $conn->close(); ?>
</body>
</html>