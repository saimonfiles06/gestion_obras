<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

include "conexion.php";

$nombre = '';
$apellidos = '';
$rol = '';
$estado = ''; // Nuevo campo
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $rol = $_POST['rol'] ?? '';
    $estado = $_POST['estado'] ?? '';

    if (empty($nombre) || empty($apellidos) || empty($rol) || empty($estado)) {
        $mensaje = "Todos los campos son obligatorios.";
    } else {

        // Insertar empleado con estado
        $stmt = $conexion->prepare(
            "INSERT INTO Empleados (nombre, apellidos, correo, password, rol, estado) 
             VALUES (?, ?, NULL, NULL, ?, ?)"
        );

        if ($stmt->execute([$nombre, $apellidos, $rol, $estado])) {
            header("Location: gestion.php");
            exit();
        } else {
            $mensaje = "Error al guardar el empleado.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Crear Empleado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../estilos/estilos.css">
    <link rel="shortcut icon" href="../img/logo.ico" type="image/x-icon">
</head>

<body>

    <div class="registro-container">
        <div class="registro-form">
            <h2 class="text-center mb-4">Crear Nuevo Empleado</h2>

            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>

            <form action="crear_empleado.php" method="POST" novalidate>

                <div class="mb-3 text-start">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Introduce el nombre" value="<?= htmlspecialchars($nombre) ?>" required>
                </div>

                <div class="mb-3 text-start">
                    <label for="apellidos" class="form-label">Apellidos</label>
                    <input type="text" id="apellidos" name="apellidos" class="form-control" placeholder="Introduce los apellidos" value="<?= htmlspecialchars($apellidos) ?>" required>
                </div>

                <div class="mb-3 text-start">
                    <label for="rol" class="form-label">Rol</label>
                    <select id="rol" name="rol" class="form-select" required>
                        <option value="" disabled <?= $rol == '' ? 'selected' : '' ?>>Selecciona un rol</option>
                        <option value="jefe_obra" <?= $rol == 'jefe_obra' ? 'selected' : '' ?>>Jefe de Obra</option>
                        <option value="operario" <?= $rol == 'operario' ? 'selected' : '' ?>>Operario</option>
                    </select>
                </div>

                <div class="mb-3 text-start">
                    <label for="estado" class="form-label">Estado</label>
                    <select id="estado" name="estado" class="form-select" required>
                        <option value="" disabled <?= $estado == '' ? 'selected' : '' ?>>Selecciona un estado</option>
                        <option value="trabajando" <?= $estado == 'trabajando' ? 'selected' : '' ?>>Trabajando</option>
                        <option value="libre" <?= $estado == 'libre' ? 'selected' : '' ?>>Libre</option>
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="gestion.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver al panel</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Guardar cambios</button>
                </div>

            </form>
        </div>
    </div>

</body>

</html>