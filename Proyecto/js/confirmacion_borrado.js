// Saca un mensaje de confirmación al intentar borrar algo (categorías, productos...)
document.addEventListener('click', function (event) {
    // Buscamos si el click o alguno de sus padres es un .boton-borrar
    const enlace = event.target.closest('.boton-borrar');
    
    if (enlace) {
        const mensaje = enlace.getAttribute('data-mensaje') || '¿Estás seguro de que deseas borrar este elemento?';
        if (!confirm(mensaje)) {
            event.preventDefault();
        }
    }
});