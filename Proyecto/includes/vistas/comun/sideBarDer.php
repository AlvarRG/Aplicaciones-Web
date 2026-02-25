<aside id="sidebarDer">
	<div id="mini-carrito" style="padding: 10px;">
    <h3 style="border-bottom: 2px solid #303030; padding-bottom: 5px;">Tu Pedido</h3>
    
    <?php
    // Comprobamos si hay algo en el carrito
    if (isset($_SESSION['carrito']) && count($_SESSION['carrito']) > 0) {
        $totalArticulos = array_sum($_SESSION['carrito']);
        
        echo "<p>Tienes <strong>$totalArticulos</strong> artículos.</p>";
        
        // Un botón que lleve a la página de confirmación/pago final
        echo '<a href="carrito.php" style="display: block; text-align: center; background-color: #28a745; color: white; padding: 10px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 15px;">';
        echo 'Revisar y Pagar';
        echo '</a>';
        
        // Botón para vaciar el carrito rápidamente
        echo '<form action="procesar_carrito.php" method="POST" style="margin-top: 10px; text-align: center;">';
        echo '<input type="hidden" name="accion" value="vaciar">';
        echo '<button type="submit" style="background: none; border: none; color: red; text-decoration: underline; cursor: pointer;">Vaciar carrito</button>';
        echo '</form>';
        
    } else {
        echo "<p style='color: #666; font-style: italic;'>Tu pedido está vacío.</p>";
        echo "<p><small>Añade platos desde la carta.</small></p>";
    }
    ?>
</div>
</aside>
