<?php

include "conexion.php";

if(isset($_POST['id']) && isset($_POST['estado'])){

$id = intval($_POST['id']);
$estado = $_POST['estado'];

$stmt = $conexion->prepare("UPDATE Empleados SET estado=? WHERE id=?");
$stmt->execute([$estado,$id]);

echo "ok";

}
?>