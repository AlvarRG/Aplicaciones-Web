<?php
// 1. Cargamos SÓLO la configuración de rutas (sin base de datos)
require_once __DIR__ . '/includes/config.php';

// 2. Cabecera
require_once __DIR__ . '/includes/vistas/comun/cabecera.php';

// 3. Barra Izquierda
require_once __DIR__ . '/includes/vistas/comun/sidebarIzq.php';
?>

    <main>
        <h2>Bienvenido</h2>
        <p>Esta es la página principal (Home). Aquí irán los productos destacados.</p>
    </main>

<?php
// 5. Barra Derecha
require_once __DIR__ . '/includes/vistas/comun/sidebarDer.php';

// 6. Pie
require_once __DIR__ . '/includes/vistas/comun/pie.php';
?>