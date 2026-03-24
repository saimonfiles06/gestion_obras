  /* CAMBIAR ESTADO EMPLEADO */

        document.querySelectorAll(".switch-estado").forEach(function(sw) {

            sw.addEventListener("change", function() {

                let id = this.dataset.id;

                let estado = this.checked ? "trabajando" : "libre";

                fetch("cambiar_estado.php", {

                        method: "POST",

                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },

                        body: "id=" + id + "&estado=" + estado

                    })
                    .then(res => res.text())
                    .then(data => {

                        if (data == "ok") {
                            location.reload();
                        }

                    });

            });

        });