-- Queries del proyecto NBA
-- Cumple con requisitos: JOIN de 3+ tablas, GROUP BY/HAVING, subconsulta

-- Query 1: JOIN de 4 tablas
-- Top 10 jugadores con mas puntos por juego en una temporada y su equipo
SELECT
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
LIMIT 10;


-- Query 2: GROUP BY con HAVING
-- Paises con mas de 5 jugadores en la NBA
SELECT
    p.nombre AS pais,
    p.continente,
    COUNT(j.id_jugador) AS total_jugadores
FROM Jugador j
JOIN Pais p ON j.id_pais = p.id_pais
GROUP BY p.id_pais, p.nombre, p.continente
HAVING COUNT(j.id_jugador) > 5
ORDER BY total_jugadores DESC;


-- Query 3: Subconsulta
-- Jugadores que en alguna temporada superaron el promedio general de puntos
SELECT DISTINCT
    j.nombre AS jugador,
    MAX(et.pts) AS max_pts
FROM EstadisticasTemporada et
JOIN Jugador j ON et.id_jugador = j.id_jugador
WHERE et.pts > (SELECT AVG(pts) FROM EstadisticasTemporada)
GROUP BY j.id_jugador, j.nombre
ORDER BY max_pts DESC
LIMIT 20;


-- Query 4: Subconsulta con WITH (CTE) + agregacion
-- Top 5 universidades por promedio de puntos de sus jugadores
WITH puntos_por_jugador AS (
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
LIMIT 5;


-- Query 5: JOIN aprovechando la herencia (Drafteado vs NoDrafteado)
-- Comparar promedio de puntos entre drafteados y no drafteados
SELECT
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
JOIN EstadisticasTemporada et ON j.id_jugador = et.id_jugador;