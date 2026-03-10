<?php
// procesar-devolucion.php
session_start();
require_once 'vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// 1. Verificación de seguridad
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] != 2 && $_SESSION['usuario_rol'] != 3)) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_prestamo = (int)$_GET['id'];
    
    try {
        $db = new PDO('sqlite:' . getenv('DB_FILE'));
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Iniciamos transacción para asegurar consistencia
        $db->beginTransaction();

        // 2. Buscamos el libro asociado al préstamo
        $stmt_lib = $db->prepare("SELECT id_libro FROM Prestamo WHERE id_prestamo = ?");
        $stmt_lib->execute([$id_prestamo]);
        $prestamo = $stmt_lib->fetch(PDO::FETCH_ASSOC);

        if ($prestamo) {
            $id_libro = $prestamo['id_libro'];

            // 3. Actualizamos estado del préstamo y del libro
            $db->prepare("UPDATE Prestamo SET estado_del_prestamo = 'Finalizado' WHERE id_prestamo = ?")
               ->execute([$id_prestamo]);

            $db->prepare("UPDATE Libro SET estado_de_actividad = 'Disponible' WHERE id_libro = ?")
               ->execute([$id_libro]);

            $db->commit();
            
            // 4. Redirección basada en rol
            $location = ($_SESSION['usuario_rol'] == 2) ? "profesor-devolucion.php" : "admin-prestamos.php";
            header("Location: $location?mensaje=devuelto");
        } else {
            $db->rollBack();
            header("Location: index.php");
        }
        exit();

    } catch (Exception $e) {
        if (isset($db)) $db->rollBack();
        die("Error al procesar la devolución: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
}
