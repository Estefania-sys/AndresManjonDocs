<?php
// eliminar-usuario.php
session_start();

// 1. Verificación de permisos
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 3) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id']; // Cast a entero por seguridad
    
    try {
        // Conexión
        $db = new PDO('sqlite:biblioteca.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 2. Iniciar Transacción para asegurar integridad
        $db->beginTransaction();

        // Obtener el código de carnet antes de borrar
        $stmt = $db->prepare("SELECT codigo_de_carnet FROM Usuario WHERE id_usuario = :id");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Eliminar dependencias
            $stmtDelAlumno = $db->prepare("DELETE FROM Alumnado WHERE codigo_de_carnet = ?");
            $stmtDelAlumno->execute([$user['codigo_de_carnet']]);

            // Eliminar al usuario
            $stmtDelUsuario = $db->prepare("DELETE FROM Usuario WHERE id_usuario = ?");
            $stmtDelUsuario->execute([$id]);
        }

        // 3. Confirmar cambios
        $db->commit();
        
        header("Location: admin-usuarios.php?mensaje=usuario_eliminado");
        exit();

    } catch (Exception $e) {
        // Si algo falla, revertir cambios
        if (isset($db)) $db->rollBack();
        die("Error al eliminar usuario: " . $e->getMessage());
    }
} else {
    header("Location: admin-usuarios.php");
    exit();
}
