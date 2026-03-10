<?php 
session_start(); 

/**
 * Función para determinar la página de inicio según el rol del usuario
 */
function obtenerPaginaInicio() {
    if (!isset($_SESSION['usuario_rol'])) return 'index.php';
    
    switch ($_SESSION['usuario_rol']) {
        case 3: return 'admin.php';
        case 2: return 'profesor.php';
        case 1: return 'estudiante_mayor.php';
        default: return 'index.php';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayuda - Biblioteca Escolar</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <main class="login" style="width: 500px; margin: 50px auto;"> 
        <h1 class="titulo">Centro de Ayuda</h1>
        <p class="subtitulo">¿Cómo podemos ayudarte hoy?</p>
        
        <section style="text-align: left; margin-bottom: 20px;">
            <details style="margin-bottom: 10px; cursor: pointer; padding: 10px; border-bottom: 1px solid #eee;">
                <summary><strong>¿Cómo pido un libro prestado?</strong></summary>
                <p style="font-size: 0.9rem; color: #666; padding: 10px 0;">Debes acudir al mostrador con tu carnet escolar o indicar tu nombre de usuario al administrador.</p>
            </details>

            <details style="margin-bottom: 10px; cursor: pointer; padding: 10px; border-bottom: 1px solid #eee;">
                <summary><strong>¿Cuál es el plazo de devolución?</strong></summary>
                <p style="font-size: 0.9rem; color: #666; padding: 10px 0;">El plazo estándar es de 14 días naturales, prorrogables por otros 7 si no hay reservas.</p>
            </details>

            <details style="margin-bottom: 10px; cursor: pointer; padding: 10px; border-bottom: 1px solid #eee;">
                <summary><strong>He olvidado mi contraseña</strong></summary>
                <p style="font-size: 0.9rem; color: #666; padding: 10px 0;">Contacta con el administrador de la biblioteca para restablecer tus credenciales.</p>
            </details>
        </section>

        <a href="<?php echo obtenerPaginaInicio(); ?>" class="boton-login" style="text-decoration: none; display: block; text-align: center;">Volver al Inicio</a>
    </main>

    <footer class="footer-login">
        <p>&copy; 2026 Sistema de Biblioteca Escolar - C.E.I.P. Andrés Manjón</p>
        <div class="enlaces-footer">
            <a href="ayuda.php">Ayuda</a>
            <span class="separador-punto">•</span>
            <a href="privacidad.php">Privacidad</a>
            <span class="separador-punto">•</span>
            <a href="contacto.php">Contacto</a>
        </div>
    </footer>
</body>
</html>
