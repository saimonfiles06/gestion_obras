<?php
include "conexion.php";

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    // Evitar warnings si los campos no existen
    $correo = filter_var(trim($_POST['correo'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password'] ?? '');
    $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''));
    $apellidos = htmlspecialchars(trim($_POST['apellidos'] ?? ''));

    // Validaciones
    if (!$correo) {
        $mensaje = "Correo inválido.";
    } elseif (strlen($password) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres.";
    } elseif (empty($nombre) || empty($apellidos)) {
        $mensaje = "Nombre y apellidos son obligatorios.";
    } else {

        $rol = "administrador"; // rol fijo
        $id_obra = null; // ningún administrador tendrá obra asignada

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Verificar si el correo ya existe
        $check = $conexion->prepare("SELECT id FROM Empleados WHERE correo = ?");
        $check->execute([$correo]);

        if ($check->rowCount() > 0) {
            $mensaje = "El correo ya está registrado.";
        } else {
            // Insertar nuevo usuario
            $stmt = $conexion->prepare(
                "INSERT INTO Empleados (rol, nombre, apellidos, correo, password, id_obra) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );

            if ($stmt->execute([$rol, $nombre, $apellidos, $correo, $password_hash, $id_obra])) {
                // Registro exitoso → redirigir al login
                header("Location: login.php");
                exit();
            } else {
                $mensaje = "Error al registrar. Intente de nuevo.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es-ES">

<head>
    <meta charset="UTF-8">
    <title>Registro de Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../estilos/estilos.css">
    <link rel="shortcut icon" href="../img/logo.ico" type="image/x-icon">
</head>

<body>

    <div class="registro-container">
        <div class="registro-form">

            <!-- Logo de la empresa -->
            <img src="../img/logo.png" alt="Logo de la empresa" class="logo-empresa">

            <?php if (!empty($mensaje)) : ?>
                <div class="alert alert-danger"><?php echo $mensaje; ?></div>
            <?php endif; ?>

            <form action="registro.php" method="POST" novalidate>
                <div class="mb-3 text-start">
                    <label for="correo" class="form-label">Correo electrónico</label>
                    <input type="email" id="correo" name="correo" class="form-control" placeholder="correo@ejemplo.com" required>
                </div>

                <div class="mb-3 text-start">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Al menos 6 caracteres" required>
                </div>

                <div class="mb-3 text-start">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Tu nombre" required>
                </div>

                <div class="mb-3 text-start">
                    <label for="apellidos" class="form-label">Apellidos</label>
                    <input type="text" id="apellidos" name="apellidos" class="form-control" placeholder="Tus apellidos" required>
                </div>

                <div class="mb-3 text-start">
                    <label for="rol" class="form-label">Rol</label>
                    <input type="text" id="rol" name="rol" class="form-control" value="administrador" readonly>
                </div>

                <!-- Enlace al login -->
                <a href="login.php" class="login-link">¿Ya tienes cuenta? Inicia sesión.</a>
                <button type="submit" class="btn boton">Registrarse</button>
            </form>
        </div>
    </div>

</body>

</html>