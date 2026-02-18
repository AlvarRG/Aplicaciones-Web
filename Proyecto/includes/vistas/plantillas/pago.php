<div class="vista-pago">
    <h1>Finalizar Pago</h1>
    <h2>Total: 22.00 €</h2>

    <div class="metodos-pago" style="display: flex; gap: 40px; margin-top: 30px;">
        <div class="pago-tarjeta" style="border: 1px solid #ccc; padding: 20px; flex: 1;">
            <h3>Pagar con Tarjeta</h3>
            <form action="<?php echo RUTA_APP; ?>/pedido_confirmado.php" method="POST">
                <label>Número de Tarjeta:</label><br>
                <input type="text" placeholder="1234 5678 9101 1121" required><br><br>
                <label>Fecha Caducidad:</label><br>
                <input type="text" placeholder="MM/AA" required style="width: 80px;">
                <label>CVV:</label>
                <input type="text" placeholder="123" required style="width: 50px;"><br><br>
                <button type="submit" class="boton">Pagar 22.00 €</button>
            </form>
        </div>

        <div class="pago-camarero" style="border: 1px solid #ccc; padding: 20px; flex: 1; text-align: center;">
            <h3>Pagar al Camarero</h3>
            <p>Selecciona esta opción si deseas pagar en efectivo o con TPV físico en el local.</p>
            <br>
            <a href="<?php echo RUTA_APP; ?>/pedido_confirmado.php" class="boton-secundario">Solicitar Cobro en Mesa</a>
        </div>
    </div>
</div>