<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

include "conexion.php";

header('Content-Type: application/json');

if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID de obra no recibido']);
    exit;
}

$obra_id = intval($_POST['id']);

try {
    $conexion->beginTransaction();

    // Borrar asignaciones de la tabla intermedia
    $stmt1 = $conexion->prepare("DELETE FROM empleados_obras WHERE obra_id = ?");
    $stmt1->execute([$obra_id]);

    //Borrar la obra
    $stmt2 = $conexion->prepare("DELETE FROM obras WHERE id = ?");
    $stmt2->execute([$obra_id]);

    $conexion->commit();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conexion->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>