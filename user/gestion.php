<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

include "conexion.php";

/* --------- PAGINACIÓN EMPLEADOS --------- */

$porPagina = 10;

$pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? intval($_GET['pagina']) : 1;

$inicio = ($pagina - 1) * $porPagina;

/* Total empleados */

$totalEmpleados = $conexion->query("
SELECT COUNT(*) FROM Empleados
WHERE rol IN ('jefe_obra','operario')
")->fetchColumn();

/* Total páginas */

$totalPaginas = ceil($totalEmpleados / $porPagina);

/* Obtener empleados */

$empleados = $conexion->query("
SELECT id,nombre,apellidos,rol,estado
FROM Empleados
WHERE rol IN ('jefe_obra','operario')
ORDER BY nombre ASC
LIMIT $porPagina OFFSET $inicio
")->fetchAll(PDO::FETCH_ASSOC);

/* Obtener obras */

$obras = $conexion->query("
SELECT id,poblacion,direccion
FROM Obras
")->fetchAll(PDO::FETCH_ASSOC);


/* ------------------- CALCULAR SEMANA SIGUIENTE ------------------- */

$hoy = new DateTime();

/* Lunes de la semana actual */
$lunes = new DateTime();
$lunes->modify('monday this week');

/* Semana siguiente */
$lunes->modify('+1 week');

$lunesStr = $lunes->format('d/m/Y');

/* Viernes de esa semana */
$viernes = clone $lunes;
$viernes->modify('+4 days');

$viernesStr = $viernes->format('d/m/Y');

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Gestión de Empleados y Obras</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../estilos/estilos.css">
    <link rel="shortcut icon" href="../img/logo.ico" type="image/x-icon">

</head>

<body>

    <div class="container mt-5">

        <!-- BOTONES PRINCIPALES -->

        <div class="mb-4 d-flex flex-wrap gap-2">

            <a href="crear_empleado.php" class="btn btn-primary">
                <i class="bi bi-person-add"></i> Registrar Empleado
            </a>

            <a href="crear_obra.php" class="btn btn-primary">
                <i class="bi bi-cone-striped"></i> Registrar Obra
            </a>

            <button id="btnBorrarEmpleado" class="btn btn-danger">
                <i class="bi bi-person-x"></i> Borrar Empleado
            </button>

            <button id="btnBorrarObra" class="btn btn-danger">
                <i class="bi bi-trash3"></i> Borrar Obra
            </button>

        </div>

        <!-- TITULO -->

        <h4>Asignar Empleados a Obras</h4>

        <p class="text-muted mb-3">
            Asignaciones para la semana del <?= $lunesStr ?> al <?= $viernesStr ?>
        </p>

        <div class="row">

            <!-- EMPLEADOS -->

            <div class="col-md-4">

                <h5>Empleados</h5>

                <input type="text" id="buscarEmpleado" class="form-control mb-2"
                    placeholder="Buscar empleado por nombre, apellidos o rol...">

                <!-- PAGINACION -->

                <nav class="mb-2">

                    <ul class="pagination pagination-sm">

                        <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $pagina - 1 ?>">Anterior</a>
                        </li>

                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>

                            <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                                <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
                            </li>

                        <?php endfor; ?>

                        <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $pagina + 1 ?>">Siguiente</a>
                        </li>

                    </ul>

                </nav>

                <div id="empleados">

                    <?php foreach ($empleados as $emp): ?>

                        <div class="empleado <?= $emp['rol'] ?> d-flex justify-content-between align-items-center mb-2"

                            draggable="<?= $emp['estado'] == 'trabajando' ? 'true' : 'false' ?>"

                            data-id="<?= $emp['id'] ?>"

                            data-nombre="<?= strtolower($emp['nombre'] . ' ' . $emp['apellidos']) ?>"

                            data-rol="<?= strtolower($emp['rol']) ?>">

                            <div>

                                <strong>
                                    <?= htmlspecialchars($emp['nombre'] . ' ' . $emp['apellidos']) ?>
                                </strong>

                                (<?= $emp['rol'] == 'jefe_obra' ? 'Jefe de Obra' : 'Operario' ?>)

                                -

                                <span class="estado <?= $emp['estado'] == 'trabajando' ? 'text-success' : 'text-danger' ?>">
                                    <?= ucfirst($emp['estado']) ?>
                                </span>

                            </div>

                            <div class="form-check form-switch">

                                <input class="form-check-input switch-estado"

                                    type="checkbox"

                                    data-id="<?= $emp['id'] ?>"

                                    <?= $emp['estado'] == 'trabajando' ? 'checked' : '' ?>>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>


            <!-- OBRAS -->

            <div class="col-md-8">

                <h5>Obras</h5>

                <input type="text" id="buscarObra" class="form-control mb-3"
                    placeholder="Buscar obra por población o dirección...">

                <div class="row">

                    <?php foreach ($obras as $obra): ?>

                        <div class="col-md-6 obra-col">

                            <div class="obra-container"

                                data-id="<?= $obra['id'] ?>"

                                data-obra="<?= strtolower($obra['poblacion'] . ' ' . $obra['direccion']) ?>">

                                <div class="obra-title">

                                    <?= htmlspecialchars($obra['poblacion'] . ' - ' . $obra['direccion']) ?>

                                </div>

                                <div class="empleados-asignados"></div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

        </div>


        <div class="d-flex gap-2 mt-3 flex-column flex-md-row">

            <button id="guardar" class="btn btn-primary">
                <i class="bi bi-floppy"></i> Guardar asignaciones
            </button>

            <button id="restablecer" class="btn btn-danger">
                <i class="bi bi-arrow-clockwise"></i> Restablecer asignaciones
            </button>

        </div>

    </div>
    <!-- MODALES DE CONFIRMACIÓN -->

    <!-- Modal fecha de asignación -->
    <div class="modal fade" id="modalFechaAsignacion" tabindex="-1" aria-labelledby="modalFechaAsignacionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Seleccionar fecha de asignación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="date" id="fechaAsignacion" class="form-control">
                </div>
                <div class="modal-footer">
                    <button type="button" id="confirmarFechaAsignacion" class="btn btn-primary">Confirmar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal restablecer asignaciones - paso 1 -->
    <div class="modal fade" id="confirmResetModal" tabindex="-1" aria-labelledby="confirmResetModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Restablecer asignaciones</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que quieres restablecer las asignaciones de empleados a obras? Esta acción no se puede deshacer.
                </div>
                <div class="modal-footer">
                    <button type="button" id="irConfirmacionFinal" class="btn btn-danger">Sí, continuar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal restablecer asignaciones - paso 2 -->
    <div class="modal fade" id="confirmResetFinalModal" tabindex="-1" aria-labelledby="confirmResetFinalModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmación final</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    Esta es la confirmación final. ¿Deseas eliminar todas las asignaciones?
                </div>
                <div class="modal-footer">
                    <button type="button" id="confirmarResetFinal" class="btn btn-danger">Sí, restablecer todo</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-arrow-left"></i> Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal borrar empleado -->
    <div class="modal fade" id="modalBorrarEmpleado" tabindex="-1" aria-labelledby="modalBorrarEmpleadoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Borrar empleado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que quieres borrar este empleado? Esta acción no se puede deshacer.
                </div>
                <div class="modal-footer">
                    <button type="button" id="confirmarBorrarEmpleado" class="btn btn-danger"><i class="bi bi-trash2"></i> Sí, borrar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-arrow-left"></i> Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal seleccionar obra antes de borrar -->
    <div class="modal fade" id="modalSeleccionObra" tabindex="-1" aria-labelledby="modalSeleccionObraLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Seleccionar obra a borrar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    Haz clic en la obra que deseas borrar, o continúa sin seleccionar ninguna para ver la lista completa.
                </div>
                <div class="modal-footer">
                    <button type="button" id="seleccionarObra" class="btn btn-danger">
                        <i class="bi bi-check-circle"></i>
                        Seleccionar obra
                    </button>

                    <button type="button" id="continuarSinSeleccion" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Continuar sin seleccionar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal borrar obra -->
    <div class="modal fade" id="modalBorrarObra" tabindex="-1" aria-labelledby="modalBorrarObraLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Borrar obra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que quieres borrar esta obra? Se eliminarán todas las asignaciones de empleados relacionadas.
                </div>
                <div class="modal-footer">
                    <button type="button" id="confirmarBorrarObra" class="btn btn-danger"><i class="bi bi-trash-fill"></i> Sí, borrar obra</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-arrow-left"></i> Cancelar</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <script src="js/dragdrop.js"></script>

    <script src="js/buscadores.js"></script>

    <script src="js/cambio_estado.js"></script>

</body>

</html>