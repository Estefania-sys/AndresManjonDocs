<?php
// privacidad.php
session_start();
require_once 'vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Función para determinar la página de retorno según el rol
function getReturnPage() {
    if (!isset($_SESSION['usuario_rol'])) return 'index.php';
    
    return match($_SESSION['usuario_rol']) {
        3 => 'admin.php',
        2 => 'profesor.php',
        1 => 'estudiante_mayor.php',
        default => 'index.php'
    };
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Privacidad | <?php echo getenv('APP_NAME'); ?></title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <main class="login" style="width: 600px; text-align: left;">
        <h1 class="titulo" style="text-align: center;">Política de Privacidad</h1>
        
        <div style="font-size: 0.9rem; color: #444; line-height: 1.6;">
            <p><strong>1. Datos Recopilados:</strong> El sistema almacena nombres, apellidos, curso y registros de préstamos de libros.</p>
            <p><strong>2. Uso de la Información:</strong> Los datos se utilizan exclusivamente para la gestión interna de la biblioteca y el control de devoluciones.</p>
            <p><strong>3. Seguridad:</strong> El acceso a los datos del alumnado está restringido a usuarios autorizados.</p>
            <p><strong>4. Derechos:</strong> Puedes solicitar la revisión de tus datos personales acudiendo a la secretaría del centro.</p>
        </div>

        <br>
        <a href="<?php echo getReturnPage(); ?>" class="boton-login" style="text-decoration: none; display: block; text-align: center;">Volver al Inicio</a>
    </main>

    <footer class="footer-login">
        <p>&copy; 2026 <?php echo getenv('APP_NAME'); ?></p>
        <div class="enlaces-footer">
            <a href="ayuda.php">Ayuda</a> • <a href="privacidad.php">Privacidad</a> • <a href="contacto.php">Contacto</a>
        </div>
    </footer>
</body>
</html>
