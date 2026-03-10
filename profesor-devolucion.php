<?php
// profesor-devolucion.php
session_start();
require_once 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// 1. Verificación de seguridad (Rol 2 = Profesor)
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

$subtitulos = [
    'Rojo' => 'Valores', 'Rosa' => 'Emociones', 'Morado' => 'Igualdad',
    'Amarillo' => 'Inglés', 'Marron' => 'Colecciones', 'Blanco' => 'Cómics',
    'Negro' => 'Música', 'Verde' => 'Infantil y 1.º ciclo',
    'Naranja' => '2.º y 3.º ciclo', 'Azul' => 'Naturaleza'
];

$busqueda = isset($_GET['busqueda_libro']) ? trim($_GET['busqueda_libro']) : '';

function obtenerPrestamos($db, $estado, $busqueda, $subtitulos) {
    $params = ['estado' => $estado];
    $query = "SELECT p.*, l.titulo, l.autor, l.isbn, l.ubicacion_por_colores,
              COALESCE(a.nombre, u.nombre) AS persona_nombre,
              COALESCE(a.apellidos, '') AS persona_apellidos,
              COALESCE(a.codigo_de_carnet, u.codigo_de_carnet) AS carnet,
              CASE WHEN a.id_alumnado IS NULL THEN 'Profesor' ELSE 'Alumno' END as tipo_usuario
              FROM Prestamo p
              JOIN Libro l ON p.id_libro = l.id_libro
              LEFT JOIN Alumnado a ON p.id_alumnado = a.id_alumnado
              LEFT JOIN Usuario u ON p.id_usuario = u.id_usuario
              WHERE p.estado_del_prestamo = :estado";
    
    if ($busqueda !== '') {
        $query .= " AND (l.titulo LIKE :busq OR l.autor LIKE :busq OR l.isbn LIKE :busq)";
        $params['busq'] = "%$busqueda%";
    }
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$prestamos_activos = obtenerPrestamos($db, 'Activo', $busqueda, $subtitulos);
$historial_prestamos = obtenerPrestamos($db, 'Devuelto', $busqueda, $subtitulos);
$hoy = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Préstamos | <?php echo getenv('APP_NAME'); ?></title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <script>
    function confirmarDevolucion(id) {
        if(confirm('¿Confirmar la devolución de este libro?')) {
            window.location.href = 'procesar-devolucion.php?id=' + id;
        }
    }
    </script>
</body>
</html>
