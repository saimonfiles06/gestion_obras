<?php
$host = "localhost";        // servidor
$db   = "gestion_obras";    // nombre de la base de datos
$user = "root";              // usuario de MySQL
$pass = "";                  // contraseña vacía

try {
    $conexion = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("Error en la conexión: " . $e->getMessage());
}
?>