<?php
session_start();
require_once '../Models/user_model.php';
$mensaje = "";

if (isset($_SESSION['user_id'])) {
    header("Location: panel.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ingresar'])) {
    $username = trim($_POST['username']);
    $contraseña = trim($_POST['password']); 

    if (empty($username) || empty($contraseña)) {
        $mensaje = "Todos los datos son obligatorios";
    } else {
      
        $respuesta = verificarCredencialesLogin($username, $contraseña);
        if ($respuesta) {
            $_SESSION['email'] = $username;
            $_SESSION['user_id'] = $respuesta['user_id'];
            header("Location: panel.php");
            exit();
        } else {
            $mensaje = "Nombre de usuario o contraseña incorrecta";
        }
    }
}

require_once __DIR__ . '/../Views/login.html';
?>