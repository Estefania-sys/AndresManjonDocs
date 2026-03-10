<?php
// estudiante_catalogo.php
session_start();

// Configuración inicial
$db_file = 'biblioteca.db';
$es_invitado = (isset($_GET['modo']) && $_GET['modo'] === 'invitado');
$esta_logueado = isset($_SESSION['usuario_id']);

try {
    $db = new PDO("sqlite:" . __DIR__ . "/" . $db_file);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- PÁGINACIÓN ---
    $libros_por_pagina = 8;
    $stmt_count = $db->query("SELECT COUNT(*) FROM Libro");
    $total_libros = $stmt_count->fetchColumn();
    $total_paginas = ceil($total_libros / $libros_por_pagina);
    
    $pagina_actual = isset($_GET['pag']) ? (int)$_GET['pag'] : 1;
    if ($pagina_actual < 1) $pagina_actual = 1;
    if ($pagina_actual > $total_paginas && $total_paginas > 0) $pagina_actual = $total_paginas;
    
    $offset = ($pagina_actual - 1) * $libros_por_pagina;

    // --- CONSULTA DE LIBROS ---
    $sql = "SELECT * FROM Libro LIMIT :limit OFFSET :offset";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':limit', $libros_por_pagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $libros = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilos.css">
    <title>Catálogo Escolar</title>
</head>
<body>
    <div class="contenedor-principal">
        <header class="cabecera-principal">
            <div class="usuario-identificado">
                <?php if ($esta_logueado): ?>
                    <h1>Hola, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></h1>
                    <a href="salir.php" class="boton-salir">Cerrar Sesión</a>
                <?php else: ?>
                    <h1>Catálogo de Libros</h1>
                    <a href="index.php" class="boton-salir" style="background: var(--azul);">Volver al login</a>
                <?php endif; ?>
            </div>
        </header>

        <main>
            <div class="grid-libros">
                <?php if (count($libros) > 0): ?>
                    <?php foreach ($libros as $libro): ?>
                        <article class="ficha-libro">
                            <div class="cuerpo-superior">
                                <div class="portada">
                                    <?php 
                                    // Ruta dinámica de la imagen
                                    $img_path = "Imagenes/Portadas/" . $libro['ubicacion_por_colores'] . "/" . $libro['isbn'] . ".jpg";
                                    $img_src = file_exists($img_path) ? $img_path : 'Imagenes/Portadas/default.jpg';
                                    ?>
                                    <img src="<?php echo htmlspecialchars($img_src); ?>" alt="Portada">
                                </div>
                                <div class="info-principal">
                                    <h2><?php echo htmlspecialchars($libro['titulo']); ?></h2>
                                    <p class="autor"><?php echo htmlspecialchars($libro['autor']); ?></p>
                                    <p class="isbn">ISBN: <?php echo htmlspecialchars($libro['isbn']); ?></p>
                                </div>
                                
                                <?php if ($esta_logueado): ?>
                                    <div class="botones-accion">
                                        <a href="solicitar-prestamo.php?id=<?php echo $libro['id_libro']; ?>" class="boton-primario">Pedir Libro</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align:center;">No se han encontrado libros en la base de datos.</p>
                <?php endif; ?>
            </div>

            <nav class="paginacion">
                <?php 
                $url_base = "?";
                if ($es_invitado) $url_base .= "modo=invitado&";
                ?>

                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <a href="<?php echo $url_base; ?>pag=<?php echo $i; ?>" 
                       class="btn-pag <?php echo ($i == $pagina_actual) ? 'activo' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($pagina_actual < $total_paginas): ?>
                    <a href="<?php echo $url_base; ?>pag=<?php echo ($pagina_actual + 1); ?>" class="btn-pag">
                        Siguiente &raquo;
                    </a>
                <?php endif; ?>
            </nav>
        </main>
    </div>

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
