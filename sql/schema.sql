-- ============================================================================
-- SCHEMA.SQL
-- Proyecto Final COMP4018 - Base de Datos de la NBA
-- Autor: Luis Gonzalez
--
-- Este archivo define las 9 tablas de la base de datos.
-- Modelo: 9 entidades, 7 relaciones, herencia total y disjunta.
-- ============================================================================

-- Usar la base de datos creada en phpMyAdmin
USE nba_db;

-- Limpiar tablas previas si existen (para poder re-ejecutar el script)
-- El orden de DROP es inverso al de CREATE: primero las hijas, después las padres
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS EstadisticasAvanzadas;
DROP TABLE IF EXISTS EstadisticasTemporada;
DROP TABLE IF EXISTS JugadorNoDrafteado;
DROP TABLE IF EXISTS JugadorDrafteado;
DROP TABLE IF EXISTS Jugador;
DROP TABLE IF EXISTS Temporada;
DROP TABLE IF EXISTS Equipo;
DROP TABLE IF EXISTS Universidad;
DROP TABLE IF EXISTS Pais;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- TABLAS DE REFERENCIA (no tienen FKs hacia otras tablas)
-- ============================================================================

-- 1. PAIS: países de origen de los jugadores
CREATE TABLE Pais (
    id_pais     INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(80) NOT NULL UNIQUE,
    continente  VARCHAR(40)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. UNIVERSIDAD: universidades donde estudiaron los jugadores
CREATE TABLE Universidad (
    id_universidad  INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(120) NOT NULL UNIQUE,
    estado          VARCHAR(60)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. EQUIPO: franquicias de la NBA
CREATE TABLE Equipo (
    id_equipo        INT AUTO_INCREMENT PRIMARY KEY,
    abreviacion      VARCHAR(5) NOT NULL UNIQUE,
    nombre_completo  VARCHAR(80),
    ciudad           VARCHAR(60),
    conferencia      VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. TEMPORADA: cada temporada regular de la NBA
CREATE TABLE Temporada (
    id_temporada  INT AUTO_INCREMENT PRIMARY KEY,
    etiqueta      VARCHAR(10) NOT NULL UNIQUE,    -- ej. "1996-97"
    anio_inicio   INT NOT NULL,                   -- 1996
    anio_fin      INT NOT NULL                    -- 1997
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- JERARQUÍA DE JUGADOR (supertipo + 2 subtipos)
-- ============================================================================

-- 5. JUGADOR (supertipo): datos fijos del jugador
CREATE TABLE Jugador (
    id_jugador      INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(100) NOT NULL,
    id_pais         INT NOT NULL,
    id_universidad  INT NULL,                     -- nullable: no todo jugador fue a universidad
    FOREIGN KEY (id_pais)        REFERENCES Pais(id_pais),
    FOREIGN KEY (id_universidad) REFERENCES Universidad(id_universidad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. JUGADOR DRAFTEADO (subtipo): jugadores seleccionados en el draft
CREATE TABLE JugadorDrafteado (
    id_jugador        INT PRIMARY KEY,
    anio_draft        INT NOT NULL,
    ronda             INT NOT NULL,
    numero_pick       INT NOT NULL,
    id_equipo_draft   INT NOT NULL,
    FOREIGN KEY (id_jugador)      REFERENCES Jugador(id_jugador) ON DELETE CASCADE,
    FOREIGN KEY (id_equipo_draft) REFERENCES Equipo(id_equipo),
    CHECK (ronda >= 1),
    CHECK (numero_pick >= 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. JUGADOR NO DRAFTEADO (subtipo): jugadores que entraron como agentes libres
CREATE TABLE JugadorNoDrafteado (
    id_jugador         INT PRIMARY KEY,
    anio_ingreso_liga  INT NOT NULL,
    FOREIGN KEY (id_jugador) REFERENCES Jugador(id_jugador) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- ENTIDADES ASOCIATIVAS DE ESTADÍSTICAS
-- ============================================================================

-- 8. ESTADÍSTICAS TEMPORADA: stats básicas de un jugador en una temporada
CREATE TABLE EstadisticasTemporada (
    id_jugador     INT NOT NULL,
    id_temporada   INT NOT NULL,
    id_equipo      INT NOT NULL,
    edad           INT,
    altura         DECIMAL(5,2),       -- altura en cm
    peso           DECIMAL(5,2),       -- peso en kg
    gp             INT,                -- games played
    pts            DECIMAL(5,2),       -- puntos por juego
    reb            DECIMAL(5,2),       -- rebotes por juego
    ast            DECIMAL(5,2),       -- asistencias por juego
    net_rating     DECIMAL(6,2),       -- puede ser negativo
    PRIMARY KEY (id_jugador, id_temporada),
    FOREIGN KEY (id_jugador)   REFERENCES Jugador(id_jugador)     ON DELETE CASCADE,
    FOREIGN KEY (id_temporada) REFERENCES Temporada(id_temporada),
    FOREIGN KEY (id_equipo)    REFERENCES Equipo(id_equipo),
    CHECK (edad   >= 0),
    CHECK (altura >= 0),
    CHECK (peso   >= 0),
    CHECK (gp     >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. ESTADÍSTICAS AVANZADAS: stats avanzadas que complementan EstadisticasTemporada
CREATE TABLE EstadisticasAvanzadas (
    id_jugador    INT NOT NULL,
    id_temporada  INT NOT NULL,
    oreb_pct      DECIMAL(5,3),    -- porcentaje de rebotes ofensivos
    dreb_pct      DECIMAL(5,3),    -- porcentaje de rebotes defensivos
    usg_pct       DECIMAL(5,3),    -- porcentaje de uso
    ts_pct        DECIMAL(5,3),    -- porcentaje de tiro efectivo
    ast_pct       DECIMAL(5,3),    -- porcentaje de asistencias
    PRIMARY KEY (id_jugador, id_temporada),
    FOREIGN KEY (id_jugador, id_temporada)
        REFERENCES EstadisticasTemporada(id_jugador, id_temporada) ON DELETE CASCADE,
    CHECK (oreb_pct BETWEEN 0 AND 1),
    CHECK (dreb_pct BETWEEN 0 AND 1),
    CHECK (usg_pct  BETWEEN 0 AND 1),
    CHECK (ts_pct   BETWEEN 0 AND 1),
    CHECK (ast_pct  BETWEEN 0 AND 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- FIN DEL SCHEMA
-- ============================================================================