<div class="vista-camarero" style="background-color: #f4f4f4; padding: 10px;">
    <div class="header-tablet" style="display: flex; justify-content: space-between; align-items: center; background: #333; color: white; padding: 10px 20px;">
        <h2>Bistro FDI - Terminal Camareros</h2>
        <div class="perfil-activo" style="display: flex; align-items: center; gap: 10px;">
            <img src="<?php echo RUTA_IMGS; ?>avatar_camarero.png" width="40" style="border-radius: 50%;">
            <span>Camarero: <strong>Carlos</strong></span>
        </div>
    </div>

    <div class="kanban-pedidos" style="display: flex; gap: 20px; margin-top: 20px;">
        
        <div class="columna" style="flex: 1; background: white; padding: 15px; border-radius: 8px;">
            <h3 style="border-bottom: 2px solid orange;">1. Pendientes de Cobro (Recibidos)</h3>
            <div class="tarjeta-pedido" style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                <strong>Pedido #005</strong> - Mesa 4<br>
                Total: 22.00 €<br><br>
                <button style="background: green; color: white; border: none; padding: 8px; cursor: pointer;">Confirmar Pago (Pasar a Cocina)</button>
            </div>
        </div>

        <div class="columna" style="flex: 1; background: white; padding: 15px; border-radius: 8px;">
            <h3 style="border-bottom: 2px solid blue;">2. Preparar Bebidas (Listo Cocina)</h3>
            <div class="tarjeta-pedido" style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                <strong>Pedido #003</strong> - Para Llevar<br>
                <em>Falta: 2x Coca-Cola</em><br><br>
                <button style="background: blue; color: white; border: none; padding: 8px; cursor: pointer;">Bolsa Lista (Pasar a Terminado)</button>
            </div>
        </div>

        <div class="columna" style="flex: 1; background: white; padding: 15px; border-radius: 8px;">
            <h3 style="border-bottom: 2px solid green;">3. Listos para Entregar (Terminados)</h3>
            <div class="tarjeta-pedido" style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                <strong>Pedido #001</strong> - Mesa 2<br><br>
                <button style="background: #333; color: white; border: none; padding: 8px; cursor: pointer;">Marcar como Entregado</button>
            </div>
        </div>

    </div>
</div>