$(document).ready(function () {

    /**
     * Procesa la respuesta AJAX del servidor
     */
    function usuarioExiste(data, status) {
        const campo = $("#nombreUsuario");
        const marca = $("#validUsuario");

        if (status === "success") {
            if (data.trim() === "existe") { // Usamos trim() por si hay espacios
                marca.html('&#x274C; El nombre ya está reservado');
                campo[0].setCustomValidity("El nombre de usuario ya está en uso.");
            } else {
                marca.html('&#x2714;');
                campo[0].setCustomValidity("");
            }
        }
    }

    // Validación del correo
    $("#email").change(function () {
        const campo = $("#email");
        const marca = $("#validEmail");

        campo[0].setCustomValidity("");
        const esCorreoValido = campo[0].checkValidity();

        if (esCorreoValido) {
            marca.html('&#x2714;');
            campo[0].setCustomValidity("");
        } else {
            marca.html('&#x274C; El correo debe tener un formato válido');
            campo[0].setCustomValidity("El correo debe tener un formato válido");
        }
    });

    // Validación del usuario
    $("#nombreUsuario").change(function () {
        if ($("#validUsuario").length > 0) { // Solo si existe el span de validación
            const nombre = $(this).val();
            if (nombre !== "") {
                const url = RUTA_APP + "/includes/comprobarUsuario.php?user=" + encodeURIComponent(nombre);
                $.get(url, usuarioExiste);
            }
        }
    });
});