// ---------------- VARIABLES ----------------
const contenedorEmpleados = document.getElementById('empleados');
let empleadoTemporal = null;
let obraTemporal = null;
let empleadoSeleccionado = null;
let obraSeleccionada = null;

// Modales
const modalFecha = new bootstrap.Modal(document.getElementById('modalFechaAsignacion'));
const modalConfirmacion = new bootstrap.Modal(document.getElementById('confirmResetModal'));
const modalConfirmacionFinal = new bootstrap.Modal(document.getElementById('confirmResetFinalModal'));
const modalSeleccionObra = new bootstrap.Modal(document.getElementById('modalSeleccionObra'));
const modalBorrarObra = new bootstrap.Modal(document.getElementById('modalBorrarObra'));

// ---------------- GLOBAL ASIGNACIONES ----------------
let empleadosInfo = {}; // { empleadoId: nombre }
let asignacionesGlobales = {}; // { obraId: { empleadoId: { nombre, fecha } } }

// ---------------- FUNCIONES ----------------
function activarDragEmpleado(emp) {
    emp.draggable = true;

    emp.addEventListener('dragstart', e => {
        e.dataTransfer.setData('empleadoId', emp.dataset.id);
        e.dataTransfer.effectAllowed = "move";
        emp.classList.add("dragging");
    });

    emp.addEventListener('dragend', e => {
        emp.classList.remove("dragging");
    });
}

// Inicializar drag y guardar información de empleados
document.querySelectorAll('.empleado').forEach(emp => {
    activarDragEmpleado(emp);
    empleadosInfo[emp.dataset.id] = emp.textContent.trim();
});

// ---------------- LOCALSTORAGE ----------------
function guardarAsignaciones() {
    localStorage.setItem('asignacionesGlobales', JSON.stringify(asignacionesGlobales));
}

function cargarAsignaciones() {
    const data = localStorage.getItem('asignacionesGlobales');
    if (data) asignacionesGlobales = JSON.parse(data);
}

// ---------------- RECONSTRUIR EMPLEADOS ASIGNADOS ----------------
function reconstruirAsignaciones() {
    document.querySelectorAll('.obra-container').forEach(obra => {
        const obraId = obra.dataset.id;
        const contenedor = obra.querySelector('.empleados-asignados');
        contenedor.innerHTML = "";

        if (asignacionesGlobales[obraId]) {
            for (let empId in asignacionesGlobales[obraId]) {
                const { nombre, fecha } = asignacionesGlobales[obraId][empId];
                const clon = document.createElement('div');
                clon.className = 'empleado empleado-asignado';
                clon.dataset.id = empId;
                clon.innerHTML = `${nombre} <small class="text-muted">(${fecha})</small>`;
                activarDragEmpleado(clon);
                contenedor.appendChild(clon);
            }
        }
    });
}

// ---------------- DRAG & DROP EN OBRAS ----------------
document.querySelectorAll('.obra-container').forEach(obra => {
    obra.addEventListener('dragover', e => e.preventDefault());
    obra.addEventListener('dragenter', () => obra.classList.add('dragover'));
    obra.addEventListener('dragleave', () => obra.classList.remove('dragover'));

    obra.addEventListener('drop', e => {
        e.preventDefault();
        obra.classList.remove('dragover');

        const empId = e.dataTransfer.getData('empleadoId');
        if (!empId) return;

        empleadoTemporal = document.querySelector(`.empleado[data-id="${empId}"]`);
        obraTemporal = obra;

        if (empleadoTemporal) {
            document.getElementById("fechaAsignacion").value = "";
            modalFecha.show();
        }
    });
});

// ---------------- CONFIRMAR FECHA ASIGNACION ----------------
document.getElementById("confirmarFechaAsignacion").addEventListener("click", () => {
    const fecha = document.getElementById("fechaAsignacion").value;
    if (!fecha) { alert("Selecciona una fecha"); return; }

    const obraId = obraTemporal.dataset.id;
    const empId = empleadoTemporal.dataset.id;

    if (!asignacionesGlobales[obraId]) asignacionesGlobales[obraId] = {};

    // Evitar conflicto con otras obras
    for (let oId in asignacionesGlobales) {
        if (oId !== obraId && asignacionesGlobales[oId]?.[empId]?.fecha === fecha) {
            alert("Este empleado ya está asignado a otra obra ese mismo día.");
            return;
        }
    }

    // Evitar duplicados en la misma obra
    if (asignacionesGlobales[obraId]?.[empId]?.fecha === fecha) {
        alert("Este empleado ya está asignado a esta obra ese día.");
        return;
    }

    const nombre = empleadosInfo[empId] || "Empleado desconocido";
    asignacionesGlobales[obraId][empId] = { nombre, fecha };
    guardarAsignaciones();
    reconstruirAsignaciones();
    modalFecha.hide();
});

// ---------------- DEVOLVER EMPLEADOS A LISTA ----------------
contenedorEmpleados.addEventListener('dragover', e => e.preventDefault());
contenedorEmpleados.addEventListener('drop', e => {
    e.preventDefault();

    const empId = e.dataTransfer.getData('empleadoId');
    if (!empId) return;

    // Eliminar asignación de cualquier obra
    for (let obraId in asignacionesGlobales) {
        if (asignacionesGlobales[obraId]?.[empId]) {
            delete asignacionesGlobales[obraId][empId];
        }
    }

    guardarAsignaciones();
    reconstruirAsignaciones();
});

// ---------------- GUARDAR ASIGNACIONES ----------------
document.getElementById('guardar').addEventListener('click', () => {
    console.log(asignacionesGlobales);
    alert('Asignaciones reflejadas en la interfaz.');
});

// ---------------- RESTABLECER ASIGNACIONES ----------------
document.getElementById('restablecer').addEventListener('click', () => { modalConfirmacion.show(); });
document.getElementById('irConfirmacionFinal').addEventListener('click', () => {
    modalConfirmacion.hide();
    modalConfirmacionFinal.show();
});
document.getElementById('confirmarResetFinal').addEventListener('click', () => {
    document.querySelectorAll('.empleados-asignados').forEach(c => c.innerHTML = "");
    asignacionesGlobales = {};
    guardarAsignaciones();
    modalConfirmacionFinal.hide();
});

// ---------------- BORRAR EMPLEADO ----------------
const btnBorrarEmpleado = document.getElementById('btnBorrarEmpleado');
btnBorrarEmpleado.addEventListener('dragover', e => e.preventDefault());
btnBorrarEmpleado.addEventListener('drop', e => {
    e.preventDefault();
    const empId = e.dataTransfer.getData('empleadoId');
    empleadoSeleccionado = document.querySelector(`.empleado[data-id="${empId}"]`);
    if (empleadoSeleccionado) new bootstrap.Modal(document.getElementById('modalBorrarEmpleado')).show();
});

document.getElementById('confirmarBorrarEmpleado').addEventListener('click', () => {
    if (!empleadoSeleccionado) return;
    const empId = empleadoSeleccionado.dataset.id;

    fetch('borrar_empleado.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${empId}`
    })
        .then(resp => resp.json())
        .then(data => {
            if (data.success) {
                empleadoSeleccionado.remove();
                for (let oId in asignacionesGlobales) delete asignacionesGlobales[oId]?.[empId];
                delete empleadosInfo[empId];
                guardarAsignaciones();
                empleadoSeleccionado = null;
                bootstrap.Modal.getInstance(document.getElementById('modalBorrarEmpleado')).hide();
            } else {
                alert("Error al borrar empleado: " + (data.error || "Desconocido"));
            }
        })
        .catch(err => alert("Error de conexión: " + err));
});
// ---------------- BORRAR OBRA ----------------

// Modo selección
let modoSeleccionObra = false;

// Botón borrar obra → abrir modal inicial
document.getElementById('btnBorrarObra').addEventListener('click', () => {
    obraSeleccionada = null;
    modoSeleccionObra = false;
    modalSeleccionObra.show();
});

// Botón "Seleccionar obra"
document.getElementById('seleccionarObra').addEventListener('click', () => {
    modoSeleccionObra = true;
    modalSeleccionObra.hide();
});

// Click en una obra → solo si estamos en modo selección
document.querySelectorAll('.obra-container').forEach(obra => {
    obra.addEventListener('click', () => {

        if (!modoSeleccionObra) return;

        obraSeleccionada = obra;
        modoSeleccionObra = false;

        modalBorrarObra.show();
    });
});

// Botón "continuar sin seleccionar"
document.getElementById('continuarSinSeleccion').addEventListener('click', () => {
    obraSeleccionada = null;
    modoSeleccionObra = false;
    modalSeleccionObra.hide();
    modalBorrarObra.show();
});

// Confirmar borrado
document.getElementById('confirmarBorrarObra').addEventListener('click', () => {

    if (!obraSeleccionada) {
        alert("No has seleccionado ninguna obra.");
        modalBorrarObra.hide();
        return;
    }

    const obraId = obraSeleccionada.dataset.id;

    fetch('borrar_obra.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${obraId}`
    })
    .then(resp => resp.json())
    .then(data => {
        if (data.success) {

            // Eliminar del DOM
            obraSeleccionada.closest('.obra-col').remove();

            // Limpiar asignaciones
            delete asignacionesGlobales[obraId];
            guardarAsignaciones();

            // Reset
            obraSeleccionada = null;

            modalBorrarObra.hide();

        } else {
            alert("Error al borrar obra: " + (data.error || "Desconocido"));
        }
    })
    .catch(err => alert("Error de conexión: " + err));
});

// ---------------- INICIALIZAR ----------------
cargarAsignaciones();
reconstruirAsignaciones();