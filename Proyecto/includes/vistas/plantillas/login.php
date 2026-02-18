<div class="vista-login">
    <h1>Iniciar Sesión</h1>
    
    <?php if (isset($_SESSION['error_login'])): ?>
        <div style="color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 15px;">
            <?php 
                echo $_SESSION['error_login']; 
                unset($_SESSION['error_login']); // Lo borramos para que no salga siempre
            ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo RUTA_APP; ?>/procesar_login.php" method="POST">
        <label>Usuario:</label><br>
        <input type="text" name="username" required><br><br>
        
        <label>Contraseña:</label><br>
        <input type="password" name="password" required><br><br>
        
        <button type="submit" class="boton">Entrar</button>
    </form>
</div>