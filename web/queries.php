<?php
session_start();
require 'config.php';

// Definimos los 5 queries con su titulo, descripcion y SQL
$queries = [
    [
        'titulo' => 'Top 10 anotadores históricos',
        'descripcion' => 'Los 10 mejores promedios de puntos por juego en una temporada, mostrando jugador, equipo y país.',
        'tecnica' => 'JOIN de 5 tablas',
        'sql' => "SELECT
    j.nombre AS jugador,
    t.etiqueta AS temporada,
    e.nombre_completo AS equipo,
    p.nombre AS pais,
    et.pts AS puntos_por_juego
FROM EstadisticasTemporada et
JOIN Jugador j   ON et.id_jugador   = j.id_jugador
JOIN Temporada t ON et.id_temporada = t.id_temporada
JOIN Equipo e    ON et.id_equipo    = e.id_equipo
JOIN Pais p      ON j.id_pais       = p.id_pais
ORDER BY et.pts DESC
LIMIT 10;"
    ],
    [
        'titulo' => 'Países con más de 5 jugadores en la NBA',
        'descripcion' => 'Cuenta cuántos jugadores aporta cada país a la NBA, mostrando solo los que tienen más de 5.',
        'tecnica' => 'GROUP BY con HAVING',
        'sql' => "SELECT
    p.nombre AS pais,
    p.continente,
    COUNT(j.id_jugador) AS total_jugadores
FROM Jugador j
JOIN Pais p ON j.id_pais = p.id_pais
GROUP BY p.id_pais, p.nombre, p.continente
HAVING COUNT(j.id_jugador) > 5
ORDER BY total_jugadores DESC;"
    ],
    [
        'titulo' => 'Jugadores que superaron el promedio general de puntos',
        'descripcion' => 'Top 20 anotadores cuyos mejores valores de puntos superan el promedio general de la liga.',
        'tecnica' => 'Subconsulta en WHERE',
        'sql' => "SELECT DISTINCT
    j.nombre AS jugador,
    MAX(et.pts) AS max_pts
FROM EstadisticasTemporada et
JOIN Jugador j ON et.id_jugador = j.id_jugador
WHERE et.pts > (SELECT AVG(pts) FROM EstadisticasTemporada)
GROUP BY j.id_jugador, j.nombre
ORDER BY max_pts DESC
LIMIT 20;"
    ],
    [
        'titulo' => 'Top 5 universidades por promedio de puntos',
        'descripcion' => 'Universidades cuyos exalumnos tienen mejor rendimiento promedio en la NBA (con al menos 10 jugadores).',
        'tecnica' => 'CTE (WITH) + agregación',
        'sql' => "WITH puntos_por_jugador AS (
    SELECT
        j.id_universidad,
        j.id_jugador,
        AVG(et.pts) AS promedio_pts
    FROM Jugador j
    JOIN EstadisticasTemporada et ON j.id_jugador = et.id_jugador
    WHERE j.id_universidad IS NOT NULL
    GROUP BY j.id_universidad, j.id_jugador
)
SELECT
    u.nombre AS universidad,
    COUNT(ppj.id_jugador) AS jugadores_en_nba,
    ROUND(AVG(ppj.promedio_pts), 2) AS pts_promedio
FROM puntos_por_jugador ppj
JOIN Universidad u ON ppj.id_universidad = u.id_universidad
GROUP BY u.id_universidad, u.nombre
HAVING COUNT(ppj.id_jugador) >= 10
ORDER BY pts_promedio DESC
LIMIT 5;"
    ],
    [
        'titulo' => 'Comparación: Drafteados vs No Drafteados',
        'descripcion' => 'Compara el rendimiento promedio entre jugadores drafteados y no drafteados, aprovechando la jerarquía de herencia del modelo.',
        'tecnica' => 'UNION ALL aprovechando la herencia',
        'sql' => "SELECT
    'Drafteado' AS tipo,
    COUNT(DISTINCT j.id_jugador) AS total_jugadores,
    ROUND(AVG(et.pts), 2) AS promedio_pts,
    ROUND(AVG(et.reb), 2) AS promedio_reb,
    ROUND(AVG(et.ast), 2) AS promedio_ast
FROM Jugador j
JOIN JugadorDrafteado jd ON j.id_jugador = jd.id_jugador
JOIN EstadisticasTemporada et ON j.id_jugador = et.id_jugador

UNION ALL

SELECT
    'No Drafteado' AS tipo,
    COUNT(DISTINCT j.id_jugador) AS total_jugadores,
    ROUND(AVG(et.pts), 2) AS promedio_pts,
    ROUND(AVG(et.reb), 2) AS promedio_reb,
    ROUND(AVG(et.ast), 2) AS promedio_ast
FROM Jugador j
JOIN JugadorNoDrafteado jnd ON j.id_jugador = jnd.id_jugador
JOIN EstadisticasTemporada et ON j.id_jugador = et.id_jugador;"
    ],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consultas - NBA Database</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-nba { background-color: #17408B; }
        .navbar-nba .navbar-brand, .navbar-nba .nav-link { color: white; }
        .navbar-nba .nav-link:hover { color: #C9082A; }
        .query-header { background-color: #17408B; color: white; padding: 12px; border-radius: 6px 6px 0 0; }
        .sql-code {
            background-color: #2d2d2d; color: #f8f8f2; padding: 15px;
            border-radius: 6px; font-family: 'Consolas', monospace; font-size: 13px;
            overflow-x: auto; white-space: pre;
        }
        .tecnica-badge { background-color: #C9082A; color: white; }
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
    <h1 class="mb-4">Consultas SQL</h1>
    <p class="lead">Cinco consultas que demuestran las capacidades del modelo: JOIN multi-tabla, agregación con HAVING, subconsultas, CTE y uso de la jerarquía de herencia.</p>

    <?php foreach ($queries as $i => $q): ?>
        <div class="card shadow-sm mb-4">
            <div class="query-header">
                <h4 class="mb-0">Query <?= $i + 1 ?>: <?= htmlspecialchars($q['titulo']) ?></h4>
            </div>
            <div class="card-body">
                <p><?= htmlspecialchars($q['descripcion']) ?></p>
                <span class="badge tecnica-badge mb-3"><?= htmlspecialchars($q['tecnica']) ?></span>

                <h6 class="mt-3">Código SQL:</h6>
                <pre class="sql-code"><?= htmlspecialchars($q['sql']) ?></pre>

                <h6 class="mt-3">Resultados:</h6>
                <?php
                $resultado = $conn->query($q['sql']);
                if ($resultado && $resultado->num_rows > 0) {
                    echo '<div class="table-responsive"><table class="table table-striped table-hover table-bordered">';
                    // Encabezados
                    echo '<thead class="table-dark"><tr>';
                    while ($campo = $resultado->fetch_field()) {
                        echo '<th>' . htmlspecialchars($campo->name) . '</th>';
                    }
                    echo '</tr></thead><tbody>';
                    // Filas
                    while ($fila = $resultado->fetch_assoc()) {
                        echo '<tr>';
                        foreach ($fila as $valor) {
                            echo '<td>' . htmlspecialchars($valor ?? 'NULL') . '</td>';
                        }
                        echo '</tr>';
                    }
                    echo '</tbody></table></div>';
                    echo '<small class="text-muted">' . $resultado->num_rows . ' filas devueltas</small>';
                } else {
                    echo '<div class="alert alert-warning">Sin resultados o error en el query.</div>';
                }
                ?>
            </div>
        </div>
    <?php endforeach; ?>

</div>

<footer class="text-center">
    <p class="mb-0">Proyecto Final COMP4018 - Luis Gonzalez - 2026</p>
</footer>

<?php $conn->close(); ?>
</body>
</html>