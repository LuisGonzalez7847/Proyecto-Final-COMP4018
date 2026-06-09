<?php
session_start();
require 'config.php';

// Grafica 1: Top 10 anotadores
$top_anotadores = $conn->query("
    SELECT j.nombre, MAX(et.pts) AS max_pts
    FROM EstadisticasTemporada et
    JOIN Jugador j ON et.id_jugador = j.id_jugador
    GROUP BY j.id_jugador, j.nombre
    ORDER BY max_pts DESC
    LIMIT 10
");
$nombres_top = [];
$pts_top = [];
while ($r = $top_anotadores->fetch_assoc()) {
    $nombres_top[] = $r['nombre'];
    $pts_top[] = (float)$r['max_pts'];
}

// Grafica 2: Jugadores por continente
$por_continente = $conn->query("
    SELECT p.continente, COUNT(j.id_jugador) AS total
    FROM Jugador j
    JOIN Pais p ON j.id_pais = p.id_pais
    GROUP BY p.continente
    ORDER BY total DESC
");
$continentes = [];
$cont_totales = [];
while ($r = $por_continente->fetch_assoc()) {
    $continentes[] = $r['continente'] ?: 'Otro';
    $cont_totales[] = (int)$r['total'];
}

// Grafica 3: Drafteados vs No Drafteados
$drafteados_res = $conn->query("SELECT COUNT(*) AS c FROM JugadorDrafteado");
$nodrafteados_res = $conn->query("SELECT COUNT(*) AS c FROM JugadorNoDrafteado");
$num_drafteados = (int)$drafteados_res->fetch_assoc()['c'];
$num_no_drafteados = (int)$nodrafteados_res->fetch_assoc()['c'];

// Grafica 4: Promedio de puntos por temporada
$promedio_por_temporada = $conn->query("
    SELECT t.etiqueta, ROUND(AVG(et.pts), 2) AS pts_prom
    FROM EstadisticasTemporada et
    JOIN Temporada t ON et.id_temporada = t.id_temporada
    GROUP BY t.id_temporada, t.etiqueta, t.anio_inicio
    ORDER BY t.anio_inicio
");
$temporadas_lbl = [];
$pts_por_temp = [];
while ($r = $promedio_por_temporada->fetch_assoc()) {
    $temporadas_lbl[] = $r['etiqueta'];
    $pts_por_temp[] = (float)$r['pts_prom'];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gráficas - NBA Database</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .navbar-nba { background-color: #17408B; }
        .navbar-nba .navbar-brand, .navbar-nba .nav-link { color: white; }
        .navbar-nba .nav-link:hover { color: #C9082A; }
        .grafica-header { background-color: #17408B; color: white; padding: 12px; border-radius: 6px 6px 0 0; }
        .grafica-container { position: relative; height: 400px; padding: 15px; }
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
    <h1 class="mb-4">Gráficas Estadísticas</h1>
    <p class="lead">Visualización de los datos de la base de datos usando Chart.js.</p>

    <div class="row g-4">

        <!-- Grafica 1: Top 10 anotadores -->
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="grafica-header"><h5 class="mb-0">Top 10 Anotadores Históricos (PPG)</h5></div>
                <div class="grafica-container">
                    <canvas id="grafica1"></canvas>
                </div>
            </div>
        </div>

        <!-- Grafica 2: Por continente -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="grafica-header"><h5 class="mb-0">Jugadores por Continente</h5></div>
                <div class="grafica-container">
                    <canvas id="grafica2"></canvas>
                </div>
            </div>
        </div>

        <!-- Grafica 3: Drafteados vs No -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="grafica-header"><h5 class="mb-0">Drafteados vs No Drafteados</h5></div>
                <div class="grafica-container">
                    <canvas id="grafica3"></canvas>
                </div>
            </div>
        </div>

        <!-- Grafica 4: Por temporada -->
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="grafica-header"><h5 class="mb-0">Promedio de Puntos por Temporada (1996-2023)</h5></div>
                <div class="grafica-container">
                    <canvas id="grafica4"></canvas>
                </div>
            </div>
        </div>

    </div>
</div>

<footer class="text-center">
    <p class="mb-0">Proyecto Final COMP4018 - Luis Gonzalez - 2026</p>
</footer>

<script>
// Colores oficiales NBA
const azulNBA = '#17408B';
const rojoNBA = '#C9082A';

// Pasar datos de PHP a JavaScript
const datosTop = {
    labels: <?= json_encode($nombres_top) ?>,
    valores: <?= json_encode($pts_top) ?>
};

const datosContinente = {
    labels: <?= json_encode($continentes) ?>,
    valores: <?= json_encode($cont_totales) ?>
};

const datosDraft = {
    valores: [<?= $num_drafteados ?>, <?= $num_no_drafteados ?>]
};

const datosTemporadas = {
    labels: <?= json_encode($temporadas_lbl) ?>,
    valores: <?= json_encode($pts_por_temp) ?>
};

// Grafica 1: Barras horizontales
new Chart(document.getElementById('grafica1'), {
    type: 'bar',
    data: {
        labels: datosTop.labels,
        datasets: [{
            label: 'Puntos por juego (PPG)',
            data: datosTop.valores,
            backgroundColor: rojoNBA,
            borderColor: azulNBA,
            borderWidth: 1
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            x: { beginAtZero: true }
        }
    }
});

// Grafica 2: Pie chart
new Chart(document.getElementById('grafica2'), {
    type: 'pie',
    data: {
        labels: datosContinente.labels,
        datasets: [{
            data: datosContinente.valores,
            backgroundColor: ['#17408B', '#C9082A', '#FFA500', '#2E8B57', '#9370DB', '#888888']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Grafica 3: Donut
new Chart(document.getElementById('grafica3'), {
    type: 'doughnut',
    data: {
        labels: ['Drafteados', 'No Drafteados'],
        datasets: [{
            data: datosDraft.valores,
            backgroundColor: [azulNBA, rojoNBA]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Grafica 4: Linea temporal
new Chart(document.getElementById('grafica4'), {
    type: 'line',
    data: {
        labels: datosTemporadas.labels,
        datasets: [{
            label: 'Promedio PPG',
            data: datosTemporadas.valores,
            borderColor: azulNBA,
            backgroundColor: 'rgba(23, 64, 139, 0.1)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        }
    }
});
</script>

</body>
</html>