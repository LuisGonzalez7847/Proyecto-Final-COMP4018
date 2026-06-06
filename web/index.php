<?php
session_start();
require 'config.php';

// Contar registros totales para mostrar en el dashboard
$totales = [];
$tablas = ['Jugador', 'EstadisticasTemporada', 'Equipo', 'Temporada', 'Pais', 'Universidad'];
foreach ($tablas as $t) {
    $res = $conn->query("SELECT COUNT(*) AS c FROM $t");
    $totales[$t] = $res->fetch_assoc()['c'];
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Proyecto NBA - COMP4018</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-nba { background-color: #17408B; }
        .navbar-nba .navbar-brand, .navbar-nba .nav-link { color: white; }
        .navbar-nba .nav-link:hover { color: #C9082A; }
        .hero { background: linear-gradient(135deg, #17408B 0%, #C9082A 100%); color: white; padding: 60px 0; }
        .card-stat { border-left: 5px solid #C9082A; }
        .diagrama { max-width: 100%; border: 2px solid #ddd; border-radius: 8px; padding: 10px; background: white; }
        .table-info-header { background-color: #17408B; color: white; }
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

<!-- Hero -->
<div class="hero">
    <div class="container text-center">
        <h1 class="display-4 fw-bold">Base de Datos de la NBA</h1>
        <p class="lead">Proyecto Final COMP4018 - Bases de Datos</p>
        <p>Datos históricos de jugadores desde 1996-97 hasta 2022-23</p>
    </div>
</div>

<div class="container my-5">

    <!-- Tarjetas con estadisticas generales -->
    <div class="row g-3 mb-5">
        <div class="col-md-4">
            <div class="card card-stat shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Jugadores</h6>
                    <h3><?= number_format($totales['Jugador']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stat shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Estadísticas por temporada</h6>
                    <h3><?= number_format($totales['EstadisticasTemporada']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stat shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Equipos</h6>
                    <h3><?= $totales['Equipo'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stat shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Temporadas</h6>
                    <h3><?= $totales['Temporada'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stat shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Países</h6>
                    <h3><?= $totales['Pais'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stat shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Universidades</h6>
                    <h3><?= $totales['Universidad'] ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Descripcion del proyecto -->
    <div class="card shadow-sm mb-5">
        <div class="card-body">
            <h2 class="mb-3">Sobre el Proyecto</h2>
            <p>
                Este proyecto consiste en una base de datos relacional construida a partir del dataset
                público <em>NBA Players Data</em> de Kaggle, que contiene estadísticas físicas y de juego de
                jugadores de la NBA temporada por temporada desde 1996-97 hasta 2022-23.
            </p>
            <p>
                El diseño cuenta con <strong>9 entidades</strong>, <strong>7 relaciones</strong>, e incluye una
                jerarquía de especialización con herencia <strong>total y disjunta</strong> entre jugadores
                drafteados y no drafteados. Las tablas están normalizadas hasta <strong>3NF / BCNF</strong>.
            </p>
        </div>
    </div>

    <!-- Diagrama E/R -->
    <div class="card shadow-sm mb-5">
        <div class="card-body">
            <h2 class="mb-3">Diagrama Entidad-Relación</h2>
            <p>
                Notación Chen: rectángulos = entidades, rombos = relaciones, doble borde = entidad asociativa,
                círculo "d" = herencia disjunta.
            </p>
            <div class="text-center">
                <img src="diagrama_er.png" alt="Diagrama E/R" class="diagrama">
            </div>
        </div>
    </div>

    <!-- Modelo relacional -->
    <div class="card shadow-sm mb-5">
        <div class="card-body">
            <h2 class="mb-3">Modelo Relacional</h2>
            <p>Las 9 tablas con sus llaves primarias (PK) y foráneas (FK):</p>
            <pre style="background: #f4f4f4; padding: 15px; border-radius: 6px;">
Pais(<u>id_pais</u>, nombre, continente)

Universidad(<u>id_universidad</u>, nombre, estado)

Equipo(<u>id_equipo</u>, abreviacion, nombre_completo, ciudad, conferencia)

Temporada(<u>id_temporada</u>, etiqueta, anio_inicio, anio_fin)

Jugador(<u>id_jugador</u>, nombre, id_pais [FK], id_universidad [FK])

JugadorDrafteado(<u>id_jugador</u> [FK], anio_draft, ronda, numero_pick, id_equipo_draft [FK])

JugadorNoDrafteado(<u>id_jugador</u> [FK], anio_ingreso_liga)

EstadisticasTemporada(<u>id_jugador, id_temporada</u> [FK], id_equipo [FK],
                       edad, altura, peso, gp, pts, reb, ast, net_rating)

EstadisticasAvanzadas(<u>id_jugador, id_temporada</u> [FK→EstadisticasTemporada],
                       oreb_pct, dreb_pct, usg_pct, ts_pct, ast_pct)
            </pre>
        </div>
    </div>

    <!-- Descripcion de atributos -->
    <div class="card shadow-sm mb-5">
        <div class="card-body">
            <h2 class="mb-3">Descripción de Atributos</h2>

            <h5 class="table-info-header p-2 mt-3">Jugador</h5>
            <table class="table table-bordered table-sm">
                <thead><tr><th>Atributo</th><th>Tipo</th><th>Descripción</th></tr></thead>
                <tbody>
                    <tr><td>id_jugador</td><td>INT</td><td>Identificador único del jugador (PK)</td></tr>
                    <tr><td>nombre</td><td>VARCHAR(100)</td><td>Nombre completo del jugador</td></tr>
                    <tr><td>id_pais</td><td>INT</td><td>País de origen (FK → Pais)</td></tr>
                    <tr><td>id_universidad</td><td>INT</td><td>Universidad de origen, puede ser NULL (FK → Universidad)</td></tr>
                </tbody>
            </table>

            <h5 class="table-info-header p-2 mt-3">JugadorDrafteado / JugadorNoDrafteado</h5>
            <table class="table table-bordered table-sm">
                <thead><tr><th>Atributo</th><th>Tipo</th><th>Descripción</th></tr></thead>
                <tbody>
                    <tr><td>anio_draft</td><td>INT</td><td>Año en que fue seleccionado en el draft</td></tr>
                    <tr><td>ronda</td><td>INT</td><td>Ronda del draft (1 o 2)</td></tr>
                    <tr><td>numero_pick</td><td>INT</td><td>Posición en que fue escogido</td></tr>
                    <tr><td>id_equipo_draft</td><td>INT</td><td>Equipo que lo drafteó (FK → Equipo)</td></tr>
                    <tr><td>anio_ingreso_liga</td><td>INT</td><td>Año de ingreso (solo para no drafteados)</td></tr>
                </tbody>
            </table>

            <h5 class="table-info-header p-2 mt-3">EstadisticasTemporada</h5>
            <table class="table table-bordered table-sm">
                <thead><tr><th>Atributo</th><th>Tipo</th><th>Descripción</th></tr></thead>
                <tbody>
                    <tr><td>edad</td><td>INT</td><td>Edad del jugador en esa temporada</td></tr>
                    <tr><td>altura</td><td>DECIMAL</td><td>Altura en cm</td></tr>
                    <tr><td>peso</td><td>DECIMAL</td><td>Peso en kg</td></tr>
                    <tr><td>gp</td><td>INT</td><td>Partidos jugados (games played)</td></tr>
                    <tr><td>pts</td><td>DECIMAL</td><td>Puntos por juego</td></tr>
                    <tr><td>reb</td><td>DECIMAL</td><td>Rebotes por juego</td></tr>
                    <tr><td>ast</td><td>DECIMAL</td><td>Asistencias por juego</td></tr>
                    <tr><td>net_rating</td><td>DECIMAL</td><td>Net rating del jugador</td></tr>
                </tbody>
            </table>

            <h5 class="table-info-header p-2 mt-3">EstadisticasAvanzadas</h5>
            <table class="table table-bordered table-sm">
                <thead><tr><th>Atributo</th><th>Tipo</th><th>Descripción</th></tr></thead>
                <tbody>
                    <tr><td>oreb_pct</td><td>DECIMAL</td><td>Porcentaje de rebotes ofensivos</td></tr>
                    <tr><td>dreb_pct</td><td>DECIMAL</td><td>Porcentaje de rebotes defensivos</td></tr>
                    <tr><td>usg_pct</td><td>DECIMAL</td><td>Porcentaje de uso del jugador</td></tr>
                    <tr><td>ts_pct</td><td>DECIMAL</td><td>True Shooting Percentage</td></tr>
                    <tr><td>ast_pct</td><td>DECIMAL</td><td>Porcentaje de asistencias</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<footer class="text-center">
    <p class="mb-0">Proyecto Final COMP4018 - Luis Gonzalez - 2025-2026</p>
</footer>

</body>
</html>