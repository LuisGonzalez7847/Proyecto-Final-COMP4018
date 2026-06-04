# Proyecto Final COMP4018 — Base de Datos de la NBA

Proyecto final del curso COMP4018 (Bases de Datos). Consiste en el diseño e implementación de una base de datos relacional a partir del dataset público "NBA Players Data" de Kaggle, junto con una aplicación web en PHP para interactuar con ella.

## Autor
- Luis Gonzalez

## Curso
- COMP4018 — Bases de Datos
- Semestre 2025–2026

## Fuente del Dataset
- [NBA Players Data en Kaggle](https://www.kaggle.com/datasets/justinas/nba-players-data)

## Descripción
El dataset contiene estadísticas físicas y de juego de los jugadores de la NBA temporada por temporada, desde la temporada 1996-97 hasta la 2021-22. Cuenta con 22 columnas donde cada fila representa un jugador en una temporada específica.

## Diseño
El modelo de la base de datos cuenta con:
- **9 entidades** (incluyendo una jerarquía de especialización)
- **7 relaciones**
- **Herencia total y disjunta** entre `JugadorDrafteado` y `JugadorNoDrafteado`
- Normalización a **3NF / BCNF**

## Tecnologías
- **MySQL** (vía XAMPP) — base de datos
- **PHP** — lógica del servidor
- **HTML / CSS** — interfaz
- **Python (pandas)** — preprocesamiento del CSV

## Estructura del Repositorio
ProyectoNBA_COMP4018/
├── data/               # CSV original y CSVs procesados
├── preprocesamiento/   # Script Python que limpia y divide el CSV
├── sql/                # Esquema CREATE TABLE e INSERTs
├── web/                # Aplicación web en PHP
├── docs/               # Diagramas E/R y modelo relacional
└── presentacion/       # Presentación final