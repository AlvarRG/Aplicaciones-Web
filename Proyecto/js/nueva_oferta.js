document.addEventListener("DOMContentLoaded", function() {
    const selectProductos = document.querySelector(".select-multiple");
    const tbody = document.querySelector("#tablaProductosOferta tbody");
    const spanTotalNormal = document.getElementById("precioTotalNormal");
    const inputDescuento = document.getElementById("descuento");
    const inputPrecioFinal = document.getElementById("precio_final");

    let productosSeleccionados = {};
	let preseleccionados = selectProductos.querySelectorAll('option[selected]');
    preseleccionados.forEach(function(opt) {
        let id = opt.value;
        let precio = parseFloat(opt.getAttribute("data-precio"));
        let nombre = opt.getAttribute("data-nombre");
        let cantidad = parseInt(opt.getAttribute("data-cantidad")) || 1;
        
        productosSeleccionados[id] = { nombre: nombre, precio: precio, cantidad: cantidad };
    });
	
    renderTabla();

    function actualizarTotal() {
        let total = 0;
        for (let id in productosSeleccionados) {
            let p = productosSeleccionados[id];
            total += p.cantidad * p.precio;
        }
        spanTotalNormal.textContent = total.toFixed(2);
        actualizarDesdeDescuento(); // Ajusta precio o descuento al cambiar sumatorios
    }

    function renderTabla() {
        tbody.innerHTML = "";
        for (let id in productosSeleccionados) {
            let p = productosSeleccionados[id];
            let tr = document.createElement("tr");

            // En un fichero .js puro, la interpolación es normal (${variable})
            let hiddenId = `<input type="hidden" name="id_productos[]" value="${id}">`;
            let hiddenCant = `<input type="hidden" name="cantidades[]" value="${p.cantidad}">`;

            tr.innerHTML = `
                <td>${p.nombre} ${hiddenId} ${hiddenCant}</td>
                <td>
                    <button type="button" onclick="modificarCantidad('${id}', -1)" style="padding: 2px 7px;">-</button>
                    <strong style="margin: 0 10px;">${p.cantidad}</strong>
                    <button type="button" onclick="modificarCantidad('${id}', 1)" style="padding: 2px 7px;">+</button>
                </td>
                <td>${(p.cantidad * p.precio).toFixed(2)}€</td>
                <td><button type="button" onclick="eliminarProducto('${id}')" style="color: red;">X</button></td>
            `;
            tbody.appendChild(tr);
        }
        actualizarTotal();
    }

    window.modificarCantidad = function(id, variacion) {
        if (productosSeleccionados[id]) {
            productosSeleccionados[id].cantidad += variacion;
            if (productosSeleccionados[id].cantidad <= 0) {
                delete productosSeleccionados[id];
            }
            renderTabla();
        }
    };

    window.eliminarProducto = function(id) {
        delete productosSeleccionados[id];
        renderTabla();
    };

    selectProductos.addEventListener("dblclick", function(e) {
        let option = e.target;
        if (option.tagName === "OPTION") {
            let id = option.value;
            let precio = parseFloat(option.getAttribute("data-precio"));
            let nombre = option.getAttribute("data-nombre");

            if (productosSeleccionados[id]) {
                productosSeleccionados[id].cantidad++;
            } else {
                productosSeleccionados[id] = { nombre: nombre, precio: precio, cantidad: 1 };
            }
            renderTabla();
        }
    });

    function actualizarDesdePrecioFinal() {
        let total = parseFloat(spanTotalNormal.textContent);
        let pf = parseFloat(inputPrecioFinal.value) || 0;
        if (total > 0 && pf <= total) {
            let desc = 100 - ((pf / total) * 100);
            inputDescuento.value = desc.toFixed(2);
        } else if (total > 0 && pf > total) {
            inputDescuento.value = 0;
        }
    }

    function actualizarDesdeDescuento() {
        let total = parseFloat(spanTotalNormal.textContent);
        let desc = parseFloat(inputDescuento.value) || 0;
        if (total > 0) {
            let pf = total * (1 - (desc / 100));
            inputPrecioFinal.value = pf.toFixed(2);
        } else {
            inputPrecioFinal.value = "";
        }
    }

    inputPrecioFinal.addEventListener("input", actualizarDesdePrecioFinal);
    inputDescuento.addEventListener("input", actualizarDesdeDescuento);
});
