<?php

require_once __DIR__ . '/conexion.php'; 

/**
 * Función para registrar un nuevo usuario en la base de datos.
 * @param string $email El email del usuario.
 * @param string $password La contraseña del usuario (se hasheará antes de guardar).
 * @return bool True si el registro fue exitoso, false en caso contrario.
 */
function registrarUsuario($username, $email, $password) {
    global $conn; 

    // Verifica si el email o el username ya existen
    if (obtenerUsuarioPorEmail($email) || obtenerUsuarioPorUsername($username)) {
        error_log("Intento de registro con email o username ya existente: " . $email . " / " . $username);
        return false;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password_hash, created_at) VALUES (?, ?, ?, NOW())");

    if ($stmt === false) {
        error_log("Error al preparar la consulta de registro: " . mysqli_error($conn));
        return false;
    }

    mysqli_stmt_bind_param($stmt, "sss", $username, $email, $password_hash);
    $exito = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt); 

    return $exito;
}

/**
 * Función para verificar las credenciales de un usuario al iniciar sesión.
 * @param string $email El email del usuario.
 * @param string $password La contraseña ingresada por el usuario.
 * @return array|false El array asociativo del usuario si las credenciales son correctas, false en caso contrario.
 */


function verificarCredencialesLogin($username, $password) {
    global $conn;

    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $usuario = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($usuario && password_verify($password, $usuario['password_hash'])) {
        return $usuario; // Return user data instead of just true
    }
    return false;
}

/**
 * Función auxiliar para obtener un usuario por su email.
 * Útil para registro (verificar si existe) y login (obtener hash de contraseña).
 * @param string $email El email del usuario.
 * @return array|false El array asociativo del usuario si se encuentra, false en caso contrario.
 */
function obtenerUsuarioPorEmail($email) {
    global $conn; 

    $stmt = mysqli_prepare($conn, "SELECT user_id, email, password_hash, created_at, last_login_at FROM users WHERE email = ?");
    
    if ($stmt === false) {
        error_log("Error al preparar la consulta (obtenerUsuarioPorEmail): " . mysqli_error($conn));
        return false;
    }

    mysqli_stmt_bind_param($stmt, "s", $email); 
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $usuario = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);

    return $usuario;
}

function obtenerUsuarioPorUsername($username) {
    global $conn; 

    $stmt = mysqli_prepare($conn, "SELECT user_id, username FROM users WHERE username = ?");
    if ($stmt === false) {
        error_log("Error al preparar la consulta (obtenerUsuarioPorUsername): " . mysqli_error($conn));
        return false;
    }

    mysqli_stmt_bind_param($stmt, "s", $username); 
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $usuario = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);

    return $usuario;
}