<div class="vista-carta">
    <h1>Nuestra Carta</h1>
    <p>Pedido actual: <strong>Para llevar</strong> <a href="tipo_pedido.php">(Cambiar)</a></p>

    <section class="categoria-menu">
        <h2>Platos Principales</h2>
        <div class="grid-productos" style="display: flex; gap: 20px; flex-wrap: wrap;">
            <div class="tarjeta-producto" style="border: 1px solid #ccc; padding: 15px; width: 200px;">
                <img src="<?php echo RUTA_IMGS; ?>hamburguesa.jpg" width="100%" alt="Hamburguesa">
                <h3>Hamburguesa Bistro</h3>
                <p>10.00 € (Sin IVA)</p>
                <form action="#" method="POST">
                    <input type="number" name="cantidad" value="1" min="1" max="10" style="width: 50px;">
                    <button type="submit">Añadir</button>
                </form>
            </div>
        </div>
    </section>
</div>