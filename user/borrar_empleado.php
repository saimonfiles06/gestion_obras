<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

include "conexion.php";

if (!isset($_POST['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de empleado no enviado']);
    exit;
}

$id = intval($_POST['id']);

try {
    // Eliminar asignaciones del empleado en la tabla intermedia
    $stmt = $conexion->prepare("DELETE FROM empleados_obras WHERE empleado_id = ?");
    $stmt->execute([$id]);

    // Luego eliminar al empleado
    $stmt = $conexion->prepare("DELETE FROM empleados WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>