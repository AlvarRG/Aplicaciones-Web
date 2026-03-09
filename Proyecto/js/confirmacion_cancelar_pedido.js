document.addEventListener('DOMContentLoaded', function () {
    const formulariosCancelar = document.querySelectorAll('.form-cancelar-pedido-cliente');

    formulariosCancelar.forEach(function(formulario) {
        formulario.addEventListener('submit', function (event) {
            const mensaje = this.getAttribute('data-mensaje') || '¿Seguro que deseas cancelar tu pedido?';
            if (!confirm(mensaje)) {
                event.preventDefault();
            }
        });
    });
});

