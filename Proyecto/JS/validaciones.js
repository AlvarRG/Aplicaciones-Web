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
    $("#emailUsuario").change(function () {
        const campo = $("#emailUsuario");
        const marca = $("#validEmail");

        campo[0].setCustomValidity("");
        const esCorreoValido = campo[0].checkValidity();

        if (esCorreoValido) {
            marca.html('&#x2714;');
            campo[0].setCustomValidity("");
        } else {
            marca.html('&#x274C;');
            campo[0].setCustomValidity("El correo debe ser válido y acabar por @ucm.es");
        }
    });

    // Validación del usuario
    $("#nombreUsuario").change(function () {
        const nombre = $(this).val();
        if (nombre !== "") {
            const url = "/Aplicaciones-Web/ej3_2026/comprobarUsuario.php?user=" + encodeURIComponent(nombre);
            $.get(url, usuarioExiste);
        }
    });
});