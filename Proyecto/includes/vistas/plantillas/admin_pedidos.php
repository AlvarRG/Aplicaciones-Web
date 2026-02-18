<div class="gestion-pedidos-global">
    <h1>Monitor Global de Pedidos (Gerencia)</h1>
    <p>Historial completo de pedidos realizados en Bistro FDI.</p>

    <div class="filtros" style="background-color: #eef; padding: 15px; margin-bottom: 20px; border: 1px solid #ccc;">
        <strong>Filtrar por estado:</strong>
        <select>
            <option>Todos los pedidos</option>
            <option>En curso (No terminados)</option>
            <option>Entregados</option>
            <option>Cancelados</option>
        </select>
        <button type="button">Filtrar</button>
    </div>

    <table border="1" width="100%" cellpadding="8" style="border-collapse: collapse; text-align: left;">
        <thead style="background-color: #f2f2f2;">
            <tr>
                <th>Nº Pedido</th>
                <th>Fecha y Hora</th>
                <th>Cliente</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Total</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>#001</strong></td>
                <td>18/02/2026 13:45</td>
                <td>Juan Pérez (user_juan)</td>
                <td>Local (Mesa 4)</td>
                <td style="color: green; font-weight: bold;">Entregado</td>
                <td>34.50 €</td>
                <td><a href="#">Ver Detalles</a></td>
            </tr>
            <tr>
                <td><strong>#002</strong></td>
                <td>18/02/2026 14:10</td>
                <td>Ana Gómez (user_ana)</td>
                <td>Para Llevar</td>
                <td style="color: blue; font-weight: bold;">Listo Cocina</td>
                <td>15.00 €</td>
                <td><a href="#">Ver Detalles</a></td>
            </tr>
        </tbody>
    </table>
    
    <div style="margin-top: 20px; text-align: right;">
        <h3>Total Facturado Hoy: 49.50 €</h3>
    </div>
</div>