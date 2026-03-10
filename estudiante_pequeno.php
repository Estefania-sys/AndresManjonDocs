<?php
// estudiante_pequeno.php
session_start();
require_once 'vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$db_file = getenv('DB_FILE');
$es_invitado = (isset($_GET['modo']) && $_GET['modo'] === 'invitado');
$esta_logueado = isset($_SESSION['usuario_id']);

if (!$es_invitado && !$esta_logueado) {
    header("Location: index.php");
    exit();
}

$clase_actual = $es_invitado ? "Invitado" : "Cargando...";
$total_prestados = 0;

try {
    $db = new PDO("sqlite:" . __DIR__ . "/" . $db_file);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($esta_logueado) {
        $stmt_u = $db->prepare("SELECT codigo_de_carnet FROM Usuario WHERE id_usuario = ?");
        $stmt_u->execute([$_SESSION['usuario_id']]);
        $mi_carnet = $stmt_u->fetchColumn();

        if ($mi_carnet) {
            $stmt_a = $db->prepare("SELECT id_alumnado, clase FROM Alumnado WHERE codigo_de_carnet = ?");
            $stmt_a->execute([$mi_carnet]);
            $alumno_data = $stmt_a->fetch(PDO::FETCH_ASSOC);
            
            if ($alumno_data) {
                $clase_actual = $alumno_data['clase'];
                $id_alumnado_real = $alumno_data['id_alumnado'];
                $stmt_count = $db->prepare("SELECT COUNT(*) FROM Prestamo WHERE id_alumnado = ? AND estado_del_prestamo = 'Activo'");
                $stmt_count->execute([$id_alumnado_real]);
                $total_prestados = $stmt_count->fetchColumn();
            }
        }
    }

    function limpiarTexto($texto) {
        return strtolower(str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $texto ?? ''));
    }

    $busqueda_ingresada = isset($_GET['busqueda_libro']) ? trim($_GET['busqueda_libro']) : '';
    $busqueda_limpia = limpiarTexto($busqueda_ingresada);

    $sql_base = "SELECT L.*, P.id_prestamo AS prestamo_activo FROM Libro L 
                 LEFT JOIN Prestamo P ON L.id_libro = P.id_libro AND P.estado_del_prestamo = 'Activo'";
    $stmt = $db->query($sql_base);
    $todos_los_libros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $libros_filtrados = [];
    foreach ($todos_los_libros as $l) {
        $campos = [$l['titulo'], $l['autor'], $l['isbn'], $l['ubicacion_por_colores']];
        $coincide = ($busqueda_limpia === '');
        foreach ($campos as $c) {
            if (strpos(limpiarTexto($c), $busqueda_limpia) !== false) { $coincide = true; break; }
        }
        if ($coincide) $libros_filtrados[] = $l;
    }

    $libros_por_pagina = 10;
    $total_paginas = ceil(count($libros_filtrados) / $libros_por_pagina);
    $pagina_actual = isset($_GET['pag']) ? (int)$_GET['pag'] : 1;
    $libros = array_slice($libros_filtrados, ($pagina_actual - 1) * $libros_por_pagina, $libros_por_pagina);

} catch (PDOException $e) { die("Error: " . $e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo | <?php echo getenv('APP_NAME'); ?></title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <header class="cabecera-principal">
        <h1>Catálogo de Libros</h1>
        <p><?php echo $es_invitado ? "Modo Invitado" : htmlspecialchars($_SESSION['usuario_nombre']); ?></p>
    </header>
    <main class="contenedor-principal">
        <?php foreach ($libros as $libro): ?>
            <article class="ficha-libro">
                <h2><?php echo htmlspecialchars($libro['titulo']); ?></h2>
                <p><?php echo htmlspecialchars($libro['autor']); ?></p>
                <span class="<?php echo !empty($libro['prestamo_activo']) ? 'estado-prestado' : 'estado-disponible'; ?>">
                    <?php echo !empty($libro['prestamo_activo']) ? 'En préstamo' : 'Disponible'; ?>
                </span>
            </article>
        <?php endforeach; ?>
    </main>
    <footer>&copy; 2026 <?php echo getenv('APP_NAME'); ?></footer>
</body>
</html>
