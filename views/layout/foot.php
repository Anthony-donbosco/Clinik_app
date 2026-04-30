<?php
/**
 * views/layout/foot.php
 */
?>
</div><!-- /.main-content -->
</div><!-- /.app-layout -->

<div id="toast-container" aria-live="polite"></div>

<?php 
// Detectamos la base path. Si está vacía, usamos /clinik_app por defecto.
$bp = defined('BASE_PATH') ? BASE_PATH : '/clinik_app'; 
?>

<!-- PASO CRÍTICO: Definimos la variable global para todo el sistema -->
<script>
    window.CLINIK_BASE_URL = '<?= rtrim($bp, '/') ?>';
    console.log("Sistema Clini-K iniciado en:", window.CLINIK_BASE_URL);
</script>

<!-- JS: Notificaciones -->
<script src="<?= $bp ?>/assets/js/notificaciones.js"></script>
</body>
</html>