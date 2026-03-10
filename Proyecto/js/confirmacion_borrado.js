//Saca un mensaje de confirmación al intentar borrar algo (categorías, productos...)
document.addEventListener('DOMContentLoaded', function () {
    const enlacesBorrar = document.querySelectorAll('.boton-borrar');

    enlacesBorrar.forEach(function(enlace) {
        enlace.addEventListener('click', function (event) {
            const mensaje = this.getAttribute('data-mensaje') || '¿Estás seguro de que deseas borrar este elemento?';
            if (!confirm(mensaje)) {
                event.preventDefault();
            }
        });
    });
});