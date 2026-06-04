-- Schema de la base de datos NBA
-- 9 tablas: Pais, Universidad, Equipo, Temporada, Jugador,
-- JugadorDrafteado, JugadorNoDrafteado, EstadisticasTemporada, EstadisticasAvanzadas

USE nba_db;

-- Limpiar tablas previas si existen
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

-- Tablas de referencia

CREATE TABLE Pais (
    id_pais     INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(80) NOT NULL UNIQUE,
    continente  VARCHAR(40)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE Universidad (
    id_universidad  INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(120) NOT NULL UNIQUE,
    estado          VARCHAR(60)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE Equipo (
    id_equipo        INT AUTO_INCREMENT PRIMARY KEY,
    abreviacion      VARCHAR(5) NOT NULL UNIQUE,
    nombre_completo  VARCHAR(80),
    ciudad           VARCHAR(60),
    conferencia      VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE Temporada (
    id_temporada  INT AUTO_INCREMENT PRIMARY KEY,
    etiqueta      VARCHAR(10) NOT NULL UNIQUE,
    anio_inicio   INT NOT NULL,
    anio_fin      INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Jerarquia Jugador

CREATE TABLE Jugador (
    id_jugador      INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(100) NOT NULL,
    id_pais         INT NOT NULL,
    id_universidad  INT NULL,
    FOREIGN KEY (id_pais)        REFERENCES Pais(id_pais),
    FOREIGN KEY (id_universidad) REFERENCES Universidad(id_universidad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE JugadorDrafteado (
    id_jugador        INT PRIMARY KEY,
    anio_draft        INT NOT NULL,
    ronda             INT NOT NULL,
    numero_pick       INT NOT NULL,
    id_equipo_draft   INT NOT NULL,
    FOREIGN KEY (id_jugador)      REFERENCES Jugador(id_jugador) ON DELETE CASCADE,
    FOREIGN KEY (id_equipo_draft) REFERENCES Equipo(id_equipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE JugadorNoDrafteado (
    id_jugador         INT PRIMARY KEY,
    anio_ingreso_liga  INT NOT NULL,
    FOREIGN KEY (id_jugador) REFERENCES Jugador(id_jugador) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estadisticas

CREATE TABLE EstadisticasTemporada (
    id_jugador     INT NOT NULL,
    id_temporada   INT NOT NULL,
    id_equipo      INT NOT NULL,
    edad           INT,
    altura         DECIMAL(5,2),
    peso           DECIMAL(5,2),
    gp             INT,
    pts            DECIMAL(5,2),
    reb            DECIMAL(5,2),
    ast            DECIMAL(5,2),
    net_rating     DECIMAL(6,2),
    PRIMARY KEY (id_jugador, id_temporada),
    FOREIGN KEY (id_jugador)   REFERENCES Jugador(id_jugador)     ON DELETE CASCADE,
    FOREIGN KEY (id_temporada) REFERENCES Temporada(id_temporada),
    FOREIGN KEY (id_equipo)    REFERENCES Equipo(id_equipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE EstadisticasAvanzadas (
    id_jugador    INT NOT NULL,
    id_temporada  INT NOT NULL,
    oreb_pct      DECIMAL(6,4),
    dreb_pct      DECIMAL(6,4),
    usg_pct       DECIMAL(6,4),
    ts_pct        DECIMAL(6,4),
    ast_pct       DECIMAL(6,4),
    PRIMARY KEY (id_jugador, id_temporada),
    FOREIGN KEY (id_jugador, id_temporada)
        REFERENCES EstadisticasTemporada(id_jugador, id_temporada) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;