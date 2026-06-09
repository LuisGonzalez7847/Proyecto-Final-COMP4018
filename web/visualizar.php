<?php
session_start();
require 'config.php';

// Lista de tablas disponibles para mostrar
$tablas_disponibles = [
    'Pais', 'Universidad', 'Equipo', 'Temporada',
    'Jugador', 'JugadorDrafteado', 'JugadorNoDrafteado',
    'EstadisticasTemporada', 'EstadisticasAvanzadas'
];

// Tabla seleccionada (por defecto Jugador)
$tabla = $_GET['tabla'] ?? 'Jugador';
if (!in_array($tabla, $tablas_disponibles)) {
    $tabla = 'Jugador';
}

// Busqueda opcional
$busqueda = trim($_GET['busqueda'] ?? '');

// Paginacion
$por_pagina = 50;
$pagina = max(1, (int)($_GET['pagina'] ?? 1));
$offset = ($pagina - 1) * $por_pagina;

// Armar el WHERE si hay busqueda - solo aplica a Jugador
$where = '';
if ($busqueda !== '' && $tabla === 'Jugador') {
    $busqueda_esc = $conn->real_escape_string($busqueda);
    $where = "WHERE nombre LIKE '%$busqueda_esc%'";
}

// Contar total de filas para paginacion
$res_count = $conn->query("SELECT COUNT(*) AS total FROM $tabla $where");
$total_filas = $res_count->fetch_assoc()['total'];
$total_paginas = max(1, (int)ceil($total_filas / $por_pagina));

// Traer los datos de la pagina actual
$sql = "SELECT * FROM $tabla $where LIMIT $por_pagina OFFSET $offset";
$resultado = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Visualizar - NBA Database</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-nba { background-color: #17408B; }
        .navbar-nba .navbar-brand, .navbar-nba .nav-link { color: white; }
        .navbar-nba .nav-link:hover { color: #C9082A; }
        .btn-nba { background-color: #C9082A; color: white; border: none; }
        .btn-nba:hover { background-color: #a00622; color: white; }
        .page-link { color: #17408B; }
        .page-item.active .page-link { background-color: #17408B; border-color: #17408B; }
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
    <h1 class="mb-4">Visualizar Tablas</h1>
    <p class="lead">Selecciona una tabla para ver su contenido. Los resultados se muestran en páginas de <?= $por_pagina ?> filas.</p>

    <!-- Selector de tabla y busqueda -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Tabla</label>
                    <select name="tabla" class="form-select" onchange="this.form.submit()">
                        <?php foreach ($tablas_disponibles as $t): ?>
                            <option value="<?= $t ?>" <?= ($t === $tabla) ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($tabla === 'Jugador'): ?>
                <div class="col-md-5">
                    <label class="form-label fw-bold">Buscar jugador por nombre</label>
                    <input type="text" name="busqueda" class="form-control" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Ej: LeBron">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-nba w-100">Buscar</button>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Info de resultados -->
    <p>Mostrando <strong><?= number_format($total_filas) ?></strong> filas en la tabla <strong><?= $tabla ?></strong>
       (página <?= $pagina ?> de <?= $total_paginas ?>).</p>

    <!-- Tabla de datos -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <?php if ($resultado && $resultado->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <?php while ($campo = $resultado->fetch_field()): ?>
                                    <th><?= htmlspecialchars($campo->name) ?></th>
                                <?php endwhile; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($fila = $resultado->fetch_assoc()): ?>
                                <tr>
                                    <?php foreach ($fila as $valor): ?>
                                        <td><?= htmlspecialchars($valor ?? 'NULL') ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">No hay resultados para mostrar.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Paginacion -->
    <?php if ($total_paginas > 1): ?>
        <nav>
            <ul class="pagination justify-content-center">
                <?php
                // Botones de paginacion (mostrar siempre primero, ultimo, y +/- 2 alrededor del actual)
                $params_base = "tabla=" . urlencode($tabla);
                if ($busqueda !== '') $params_base .= "&busqueda=" . urlencode($busqueda);

                $prev = max(1, $pagina - 1);
                $next = min($total_paginas, $pagina + 1);
                ?>
                <li class="page-item <?= ($pagina <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= $params_base ?>&pagina=<?= $prev ?>">Anterior</a>
                </li>

                <?php
                // Mostrar paginas alrededor de la actual
                $inicio = max(1, $pagina - 2);
                $fin = min($total_paginas, $pagina + 2);

                if ($inicio > 1) {
                    echo "<li class='page-item'><a class='page-link' href='?$params_base&pagina=1'>1</a></li>";
                    if ($inicio > 2) echo "<li class='page-item disabled'><span class='page-link'>...</span></li>";
                }

                for ($i = $inicio; $i <= $fin; $i++) {
                    $activa = ($i === $pagina) ? 'active' : '';
                    echo "<li class='page-item $activa'><a class='page-link' href='?$params_base&pagina=$i'>$i</a></li>";
                }

                if ($fin < $total_paginas) {
                    if ($fin < $total_paginas - 1) echo "<li class='page-item disabled'><span class='page-link'>...</span></li>";
                    echo "<li class='page-item'><a class='page-link' href='?$params_base&pagina=$total_paginas'>$total_paginas</a></li>";
                }
                ?>

                <li class="page-item <?= ($pagina >= $total_paginas) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= $params_base ?>&pagina=<?= $next ?>">Siguiente</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>

</div>

<footer class="text-center">
    <p class="mb-0">Proyecto Final COMP4018 - Luis Gonzalez - 2026</p>
</footer>

<?php $conn->close(); ?>
</body>
</html>