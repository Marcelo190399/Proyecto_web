<?php
session_start();
include ('conexion.php');
ini_set('display_errors', 1);
error_reporting(E_ALL);

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($nombre) || empty($correo) || empty($password)) {
        $mensaje = "Por favor completa todos los campos";
    } else {

        // Verificar si existe el correo
        $check = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $check->bind_param("s", $correo);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $mensaje = "El correo ya está registrado.";
            $check->close();
        } else {
            $check->close();

        // Hash de la contraseña
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insertar usuario
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, contrasena) VALUES (?, ?, ?)");
        if (!$stmt) {
                $mensaje = "Error en la consulta: " . $conn->error;
            } else {
                $stmt->bind_param("sss", $nombre, $correo, $passwordHash);

                if ($stmt->execute()) {
                    // Redirigir a login
                    header("Location: login.php");
                    exit();
                } else {
                    $mensaje = "Error al registrar: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de usuarios</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="contenedor">
    <form class="login" action="registro.php" method="post">
        <div class="inicio">Registro</div>
        <?php if (!empty($mensaje)) : ?>
                <div class="mensaje <?= strpos($mensaje) !== false ? 'success' : '' ?>">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>
        <div class="input-group">
             <label class="label" for="nombre">Nombre</label>
            <input type="name" name="nombre" id="name" placeholder="escribe tu nombre" required>
        </div>
        <div class="input-group">
             <label class="label" for="email">Correo</label>
            <input type="correo" name="correo" id="email" placeholder="escribe tu correo" required>
        </div>  
        <div class="input-group">
             <label class="label" for="password">Contraseña</label>
            <input type="password" name="password" id="password" placeholder="escribe tu contraseña" required>
        </div>   
        <div class="pass">
            <a href="#">Olvidaste tu contraseña?</a>
        </div>   
        <button class="submit" type="submit"> Crear cuenta</button>
        <div class="signun-link">Tienes cuenta? <a href="login.php">Inicia seción aquí</a></div>    
    </form>
    </div>
</body>
</html>