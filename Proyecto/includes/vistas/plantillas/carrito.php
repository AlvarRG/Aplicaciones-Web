<div class="vista-carrito">
    <h1>Tu Pedido (Mesa / Llevar)</h1>
    
    <table width="100%" border="1" style="border-collapse: collapse; text-align: left;">
        <tr>
            <th>Producto</th>
            <th>Cant.</th>
            <th>Precio (c/IVA)</th>
            <th>Subtotal</th>
        </tr>
        <tr>
            <td>Hamburguesa Bistro</td>
            <td><input type="number" name="cantidad" value="2" min="1" style="width: 40px;"></td>
            <td>11.00 €</td>
            <td>22.00 €</td>
        </tr>
    </table>

    <div class="totales" style="margin-top: 20px; text-align: right;">
        <h3>Total a pagar: 22.00 €</h3>
    </div>

    <div class="acciones-carrito" style="margin-top: 20px; display: flex; justify-content: space-between;">
        <a href="<?php echo RUTA_APP; ?>/index.php" class="boton-alerta" style="color: red;">Cancelar Pedido</a>
        <a href="<?php echo RUTA_APP; ?>/pago.php" class="boton">Confirmar y Pagar</a>
    </div>
</div>