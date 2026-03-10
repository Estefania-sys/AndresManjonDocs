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
    <title>Contacto - Biblioteca Escolar</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <main class="login" style="width: 500px; margin: 50px auto;">
        <h1 class="titulo">Contacto</h1>
        <p class="subtitulo">Envía un mensaje al bibliotecario</p>
        
        <form action="#" method="POST">
            <div class="grupo-formulario">
                <label class="etiqueta">Asunto</label>
                <input type="text" class="campo" name="asunto" placeholder="Ej: Libro perdido, sugerencia..." required>
            </div>

            <div class="grupo-formulario">
                <label class="etiqueta">Mensaje</label>
                <textarea class="campo" name="mensaje" style="height: 120px; resize: vertical;" placeholder="Escribe aquí tu mensaje..." required></textarea>
            </div>

            <button type="button" class="boton-login" style="width: 100%;" onclick="alert('Mensaje enviado correctamente (Simulación)')">
                Enviar Mensaje
            </button>
        </form>

        <p style="margin-top: 20px; text-align: center;">
            <a href="<?php echo obtenerPaginaInicio(); ?>" style="color: var(--azul); font-size: 0.9rem; text-decoration: none;">&larr; Volver</a>
        </p> 
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
