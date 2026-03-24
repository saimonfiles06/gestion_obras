document.addEventListener("DOMContentLoaded", function () {

    function normalizar(texto) {
        return texto
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .toLowerCase()
            .trim();
    }

    /* BUSCADOR EMPLEADOS */
    const buscadorEmpleado = document.getElementById("buscarEmpleado");

    buscadorEmpleado.addEventListener("input", function () {
        const texto = normalizar(this.value);

        document.querySelectorAll("#empleados .empleado").forEach(emp => {
            const nombre = normalizar(emp.dataset.nombre ?? "");
            const rol = normalizar(emp.dataset.rol ?? "");

            const estado = normalizar(
                emp.querySelector(".estado")?.textContent ?? ""
            );

            if (
                nombre.includes(texto) ||
                rol.includes(texto) ||
                estado.includes(texto)
            ) {
                emp.classList.remove("d-none");
            } else {
                emp.classList.add("d-none");
            }
        });
    });

    // BUSCADOR OBRAS
    const buscadorObra = document.getElementById("buscarObra");
    buscadorObra.addEventListener("input", function () {
        const texto = normalizar(this.value);

        document.querySelectorAll(".obra-container").forEach(obra => {
            const nombre = normalizar(obra.dataset.obra ?? "");
            const columna = obra.closest(".obra-col");

            if (nombre.includes(texto)) {
                columna.style.display = "";
            } else {
                columna.style.display = "none";
            }
        });
    });

});