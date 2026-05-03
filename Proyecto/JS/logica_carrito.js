$(document).ready(function () {
    // Escuchar el submit de cualquier formulario que vaya a procesar_carrito.php
    $(document).on("submit", 'form[action*="procesar_carrito.php"]', function (e) {
        // Si estamos en la página de revisar pedido (carrito.php), dejamos que actúe de forma nativa
        // para que la página se recargue y PHP vuelva a calcular todos los datos.
        if (window.location.pathname.includes('carrito.php')) {
            return;
        }
        // Impedimos que la página se recargue
        e.preventDefault();
        // Añadimos el parámetro ajax
        let datos = $(this).serialize() + "&ajax=1";
        let url = $(this).attr("action");

        $.post(url, datos, function (response) {
            if (response.status === "success") {
                // Actualizar el mini-carrito basado en response.totalArticulos
                let $miniCarrito = $("#mini-carrito");

                if (response.totalArticulos > 0) {
                    $miniCarrito.html(`
                        <h3 class="mini-carrito-titulo">Tu Pedido</h3>
                        <p>Tienes <strong>${response.totalArticulos}</strong> artículos.</p>
                        <a href="${RUTA_APP}/carrito.php" class="mini-carrito-boton">Revisar y Pagar</a>
                        <form action="${RUTA_APP}/includes/procesar_carrito.php" method="POST" class="mini-carrito-form">
                            <input type="hidden" name="accion" value="vaciar">
                            <button type="submit" class="mini-carrito-vaciar">Vaciar carrito</button>
                        </form>
                    `);
                } else {
                    $miniCarrito.html(`
                        <h3 class="mini-carrito-titulo">Tu Pedido</h3>
                        <p class='mini-carrito-vacio'>Tu pedido está vacío.</p>
                        <p><small>Añade platos desde la carta.</small></p>
                    `);
                }
            }
        });
    });
});