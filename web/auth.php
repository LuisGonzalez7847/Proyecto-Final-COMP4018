<?php
// Inicia la sesion si no esta iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si no hay usuario logueado, redirigir a login
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}