<aside id="sidebarDer">
	<div id="mini-carrito">
    <h3 class="mini-carrito-titulo">Tu Pedido</h3>
    
    <?php
    if (isset($_SESSION['carrito']) && count($_SESSION['carrito']) > 0) {
        $totalArticulos = array_sum($_SESSION['carrito']);
        
        echo "<p>Tienes <strong>$totalArticulos</strong> artículos.</p>";
        
        echo '<a href="carrito.php" class="mini-carrito-boton">';
        echo 'Revisar y Pagar';
        echo '</a>';
        
        echo '<form action="includes/procesar_carrito.php" method="POST" class="mini-carrito-form">';
        echo '<input type="hidden" name="accion" value="vaciar">';
        echo '<button type="submit" class="mini-carrito-vaciar">Vaciar carrito</button>';
        echo '</form>';
        
    } else {
        echo "<p class='mini-carrito-vacio'>Tu pedido está vacío.</p>";
        echo "<p><small>Añade platos desde la carta.</small></p>";
    }
    ?>
</div>
</aside>
