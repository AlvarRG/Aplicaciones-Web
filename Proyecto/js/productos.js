//Calcula dinámicamente el precio
document.addEventListener('DOMContentLoaded', function() {
    const inputBase = document.getElementById('p_base');
    const inputIva = document.getElementById('p_iva');
    const spanFinal = document.getElementById('p_final');

    function actualizarPrecio() {
		const base = parseFloat(inputBase.value) || 0;
		const iva = parseInt(inputIva.value);
		const total = base + (base * (iva / 100));
		
		spanFinal.innerText = total.toFixed(2);
	}

    // Escuchar cambios
    if (inputBase) inputBase.addEventListener('input', actualizarPrecio);
    if (inputIva) inputIva.addEventListener('change', actualizarPrecio);

    // Calcular nada más cargar la página por si hay datos previos
    actualizarPrecio();
});