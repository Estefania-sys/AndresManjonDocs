<?php
// profesor-usuario.php
session_start();
require_once 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// 1. Seguridad
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 2) {
    header("Location: index.php");
    exit();
}

try {
    $db = new PDO('sqlite:' . getenv('DB_FILE'));
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

// 2. Lógica de búsqueda con consultas preparadas
$busqueda = isset($_GET['busqueda_usuario']) ? trim($_GET['busqueda_usuario']) : '';

$sql = "SELECT u.id_usuario, u.nombre, u.codigo_de_carnet, a.clase, a.estado_de_sancion 
        FROM Usuario u
        LEFT JOIN Alumnado a ON u.codigo_de_carnet = a.codigo_de_carnet";

$params = [];
if ($busqueda !== '') {
    $sql .= " WHERE u.nombre LIKE :q OR u.codigo_de_carnet LIKE :q OR a.clase LIKE :q";
    $params[':q'] = "%$busqueda%";
}

$stmt = $db->prepare($sql);
$stmt->execute($params);
$usuarios_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Clasificación
$grupos = ['Administradores' => [], 'Profesores' => [], 'Estudiantes' => []];
foreach ($usuarios_raw as $user) {
    $c = $user['codigo_de_carnet'] ?? '';
    if (str_contains($c, 'ADM')) $grupos['Administradores'][] = $user;
    elseif (str_contains($c, 'PRO')) $grupos['Profesores'][] = $user;
    else $grupos['Estudiantes'][] = $user;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios | <?php echo getenv('APP_NAME'); ?></title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <main class="contenedor-principal">
        <?php foreach ($grupos as $nombre_grupo => $lista_usuarios): ?>
            <?php if (!empty($lista_usuarios)): ?>
                <details class="desplegable-grupo" open>
                    <summary><?php echo $nombre_grupo; ?> <span class="contador-badge"><?php echo count($lista_usuarios); ?></span></summary>
                    <div class="contenido-desplegable">
                        <?php foreach ($lista_usuarios as $user): ?>
                            <?php endforeach; ?>
                    </div>
                </details>
            <?php endif; ?>
        <?php endforeach; ?>
    </main>
</body>
</html>
