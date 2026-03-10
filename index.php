<?php
// index.php
session_start();

// 1. Cargamos el archivo de Composer
require_once __DIR__ . '/vendor/autoload.php';

// 2. Importamos la clase correctamente usando 'use'
use Dotenv\Dotenv;

// 3. Ahora inicializamos la clase
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usr = $_POST['usuario'] ?? '';
    $pwd = $_POST['contrasena'] ?? '';

    try {
        $db = new PDO("sqlite:" . __DIR__ . "/" . $db_file);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $db->prepare("SELECT * FROM Usuario WHERE nombre = ? OR username = ? OR codigo_de_carnet = ?");
        $stmt->execute([$usr, $usr, $usr]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && $pwd === $usuario['contrasenia']) {
            $_SESSION['usuario_id'] = $usuario['id_usuario'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_rol'] = $usuario['id_rol'];

            if ($usuario['id_rol'] == 3) {
                header("Location: admin.php");
            } elseif ($usuario['id_rol'] == 1) {
                $stmt_alu = $db->prepare("SELECT clase FROM Alumnado WHERE codigo_de_carnet = ?");
                $stmt_alu->execute([$usuario['codigo_de_carnet']]);
                $clase = $stmt_alu->fetchColumn();
                $es_pequeno = in_array($clase, ['Infantil', '1º Primaria', '2º Primaria']);
                header("Location: " . ($es_pequeno ? "estudiante_pequeno.php" : "estudiante_mayor.php"));
            } else {
                header("Location: profesor.php");
            }
            exit();
        } else {
            $error = "Usuario o contraseña incorrectos.";
        }
    } catch (PDOException $e) {
        $error = "Error de conexión: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login | <?php echo htmlspecialchars(getenv('APP_NAME')); ?></title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <section class="login">
        <img src="Imagenes/logoAndresManjon.jpg" alt="Logo Colegio" class="logo-colegio">
        <h1>Biblioteca Escolar</h1>
        
        <?php if(!empty($error)): ?>
            <div class="texto-error-login"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="usuario" placeholder="Usuario" required>
            <input type="password" name="contrasena" placeholder="********" required>
            <button type="submit">Iniciar Sesión</button>
        </form>

        <a href="estudiante_pequeno.php?modo=invitado">Acceso Infantil (Invitado)</a>
    </section>
</body>
</html>

