<?php
session_start();
include '../Models/user_model.php';

$mensaje = "";

if (isset($_SESSION['usuario'])) {
    header("Location: panel.php");
    exit();
}

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $contrasenia = trim($_POST['password']);

    if (registrarUsuario($username, $email, $contrasenia)) {
        header('Location: login.php');
        exit();
    } else {
        $mensaje = "El email o nombre de usuario ya está registrado.";
    }
}

require_once('../Views/register.html');
?>