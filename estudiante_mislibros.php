<?php
// estudiante_mislibros.php
session_start();
require_once 'vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 1) {
    header("Location: index.php");
    exit();
}

$db_file = getenv('DB_FILE');
$hoy = date('Y-m-d');

try {
    $db = new PDO("sqlite:" . __DIR__ . "/" . $db_file);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt_u = $db->prepare("SELECT codigo_de_carnet FROM Usuario WHERE id_usuario = ?");
    $stmt_u->execute([$_SESSION['usuario_id']]);
    $mi_carnet = $stmt_u->fetchColumn();

    $stmt_a = $db->prepare("SELECT id_alumnado, clase FROM Alumnado WHERE codigo_de_carnet = ?");
    $stmt_a->execute([$mi_carnet]);
    $alumno_data = $stmt_a->fetch(PDO::FETCH_ASSOC);
    $id_alumnado_real = $alumno_data['id_alumnado'] ?? null;
    $clase_actual = $alumno_data['clase'] ?? '';

    $url_catalogo = (preg_match('/[56]/', $clase_actual)) ? "estudiante_mayor.php" : "estudiante_pequeno.php";

    if (isset($_POST['cancelar_pedido'])) {
        $id_p = (int)$_POST['id_prestamo'];
        $id_l = (int)$_POST['id_libro'];
        
        $db->beginTransaction();
        try {
            $db->prepare("DELETE FROM Prestamo WHERE id_prestamo = ? AND id_alumnado = ?")->execute([$id_p, $id_alumnado_real]);
            $db->prepare("UPDATE Libro SET estado_de_actividad = 'Disponible' WHERE id_libro = ?")->execute([$id_l]);
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
        }
        header("Location: estudiante_mislibros.php");
        exit();
    }

    $stmt = $db->prepare("SELECT p.*, l.titulo, l.autor, l.isbn, l.ubicacion_por_colores 
                          FROM Prestamo p JOIN Libro l ON p.id_libro = l.id_libro 
                          WHERE p.id_alumnado = ? AND p.estado_del_prestamo = 'Activo'");
    $stmt->execute([$id_alumnado_real]);
    $mis_libros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_prestados = count($mis_libros);

} catch (PDOException $e) { 
    die("Error: " . $e->getMessage()); 
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Libros | <?php echo getenv('APP_NAME'); ?></title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <header class="cabecera-principal">
        <h1>Mis Libros Prestados</h1>
        <nav>
            <a href="<?php echo $url_catalogo; ?>">Catálogo</a>
            <a href="estudiante_mislibros.php" class="activo">Mis Libros</a>
        </nav>
    </header>
    <main class="contenedor-principal">
        <?php if ($total_prestados > 0): foreach ($mis_libros as $libro): 
            $es_vencido = ($libro['fecha_de_devolucion'] < $hoy); 
        ?>
            <article class="ficha-libro <?php echo $es_vencido ? 'ficha-vencida' : ''; ?>">
                <h2><?php echo htmlspecialchars($libro['titulo']); ?></h2>
                <form method="POST">
                    <input type="hidden" name="id_prestamo" value="<?php echo $libro['id_prestamo']; ?>">
                    <input type="hidden" name="id_libro" value="<?php echo $libro['id_libro']; ?>">
                    <button type="submit" name="cancelar_pedido" onclick="return confirm('¿Devolver?')">Devolver</button>
                </form>
            </article>
        <?php endforeach; else: ?>
            <p>No tienes libros prestados.</p>
        <?php endif; ?>
    </main>
    <footer>&copy; 2026 <?php echo getenv('APP_NAME'); ?></footer>
</body>
</html>
