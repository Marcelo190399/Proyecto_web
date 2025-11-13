<?php
session_start();
include 'conexion.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

$mensaje = ""; //Mensaje que se muestra en todos los errores.

$mensaje = $_SESSION['mensaje'] ?? '';
unset($_SESSION['mensaje']);


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Generar captcha nuevo cuando solo se carga la página
    $num1 = rand(1, 15);
    $num2 = rand(1, 15);
    $_SESSION['resultado'] = $num1 + $num2;
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';
    $captcha = $_POST['captcha'] ?? '';


    if ($captcha != $_SESSION['resultado']) {
        $mensaje = "Respuesta incorrecta";
    }elseif (empty($correo) || empty($password)) {
        $mensaje = "Completa usuario y contraseña.";
    } else { 
        $stmt = $conn->prepare("SELECT id, nombre, contrasena FROM usuarios WHERE correo = ?");
        if (!$stmt) {
            $mensaje = "Error en la consulta: " . $conn->error;
        } else {
            $stmt->bind_param("s", $correo);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($id, $nombre, $hash);
                $stmt->fetch();

                if (password_verify($password, trim($hash))) {
                    $_SESSION['usuario_id'] = $id;
                    $_SESSION['usuario_nombre'] = $nombre;
                    header("Location: protegida.php");
                    exit();
                } else {
                    $mensaje = "Contraseña incorrecta.";
                }
            } else {
                $mensaje = "Correo no registrado.";
            }
            $stmt->close();
        }
    }

    $_SESSION['mensaje'] = $mensaje;
    header("location: login.php");
}
$conn->close();

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
    <form class="login" action="login.php" method="post">
        <div class="inicio">Login</div>
        <?php if (!empty($mensaje)) : ?>
                <div class="mensaje <?= strpos($mensaje) !== false ? 'success' : '' ?>">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>
        <div class="input-group">
             <label class="label" for="email">Correo</label>
            <input type="email" name="correo" id="email" placeholder="escribe tu correo" required>
        </div>  
        <div class="input-group">
             <label class="label" for="password">Contraseña</label>
            <input type="password" name="password" id="password" placeholder="escribe tu contraseña" required>
        </div>  
        <div class="input-group">
            <label class="verificacion" for="verificación"> Cuanto es:<?php echo "$num1 + $num2"; ?> ?</label>
            <input type="text" name="captcha" placeholder="Escribe el resultado" required>
        </div> 
        <div class="pass">
            <a href="#">Olvidaste tu contraseña?</a>
        </div>   
        <button class="submit" type="submit"> Login</button>
        <div class="signun-link">No tienes cuenta? <a href="registro.php">Registrate aquí</a></div>    
    </form>
    </div>
</body>
</html>