<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
include "conexion.php";

$poblacion = '';
$direccion = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $poblacion = trim($_POST['poblacion'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    if (empty($poblacion) || empty($direccion)) {
        $mensaje = "Ambos campos son obligatorios.";
    } else {
        // Insertar obra en la base de datos
        $stmt = $conexion->prepare("INSERT INTO Obras (poblacion, direccion) VALUES (?, ?)");
        if ($stmt->execute([$poblacion, $direccion])) {
            // Redirigir al panel tras guardar
            header("Location: gestion.php");
            exit();
        } else {
            $mensaje = "Error al guardar la obra.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Crear Obra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../estilos/estilos.css">
    <link rel="shortcut icon" href="../img/logo.ico" type="image/x-icon">
</head>

<body>

    <div class="registro-container">
        <div class="registro-form">
            <h2 class="text-center mb-4">Crear Nueva Obra</h2>

            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>

            <form action="crear_obra.php" method="POST" novalidate>
                <div class="mb-3 text-start">
                    <label for="poblacion" class="form-label">Población</label>
                    <input type="text" id="poblacion" name="poblacion" class="form-control" placeholder="Introduce la población" value="<?= htmlspecialchars($poblacion) ?>" required>
                </div>

                <div class="mb-3 text-start">
                    <label for="direccion" class="form-label">Dirección</label>
                    <input type="text" id="direccion" name="direccion" class="form-control" placeholder="Introduce la dirección" value="<?= htmlspecialchars($direccion) ?>" required>
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