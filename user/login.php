<?php
session_start();
include "conexion.php";

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    // Validar correo
    $correo = filter_var(trim($_POST['correo'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password'] ?? '');

    if (!$correo) {
        $mensaje = "Correo inválido.";
    } elseif (empty($password)) {
        $mensaje = "La contraseña es obligatoria.";
    } else {
        // Buscar usuario en la base de datos
        $stmt = $conexion->prepare("SELECT id, password, rol FROM Empleados WHERE correo = ?");
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($password, $usuario['password'])) {
            // Login exitoso → iniciar sesión y redirigir
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_rol'] = $usuario['rol'];

            // Redirigir al panel de gestión
            header("Location: gestion.php");
            exit();
        } else {
            $mensaje = "Correo o contraseña incorrectos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es-ES">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
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

        <!-- Mostrar mensaje de error -->
        <?php if (!empty($mensaje)) : ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <!-- FORMULARIO CORRECTO -->
        <form action="login.php" method="POST" novalidate>
            <div class="mb-3 text-start">
                <label for="correo" class="form-label">Correo electrónico</label>
                <input type="email" id="correo" name="correo" class="form-control" placeholder="correo@ejemplo.com" required>
            </div>

            <div class="mb-3 text-start">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Contraseña" required>
            </div>

            <!-- Enlace al registro -->
            <a href="registro.php" class="login-link">¿No tienes cuenta? Crea una</a>

            <!-- BOTÓN PARA ENVIAR FORMULARIO -->
            <button type="submit" class="btn boton mt-3 w-100">Iniciar Sesión</button>
        </form>

    </div>
</div>

</body>
</html>