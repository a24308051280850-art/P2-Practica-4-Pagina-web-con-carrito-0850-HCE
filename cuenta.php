<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('conexion.php');

$mensaje_exito = "";
$mensaje_error = "";

// CERRAR SESIÓN (Procesar antes de renderizar la página)
if (isset($_GET['logout'])) {
    session_destroy();
    session_start(); // Iniciar una sesión limpia temporal para mostrar el mensaje
    $_SESSION['mensaje_bienvenida'] = "Gracias por tu preferencia";
    header("Location: cuenta.php");
    exit();
}

// PROCESAR REGISTRO
if (isset($_POST['registro'])) {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt_check = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $stmt_check->bind_param("s", $correo);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();

    if ($res_check->num_rows > 0) {
        $mensaje_error = "El correo ya está registrado en la base de datos.";
    } else {
        $stmt_insert = $conexion->prepare("INSERT INTO usuarios (nombre, correo, password) VALUES (?, ?, ?)");
        $stmt_insert->bind_param("sss", $nombre, $correo, $password);
        if ($stmt_insert->execute()) {
            $_SESSION['usuario_nombre'] = $nombre;
            $_SESSION['usuario_correo'] = $correo;
            $_SESSION['mensaje_bienvenida'] = "Tu cuenta ha sido registrada correctamente";
            header("Location: cuenta.php");
            exit();
        } else {
            $mensaje_error = "Error al registrar en la base de datos.";
        }
    }
}

// PROCESAR LOGIN
if (isset($_POST['login'])) {
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];

    $stmt = $conexion->prepare("SELECT nombre, password FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        if (password_verify($password, $usuario['password'])) {
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_correo'] = $correo;
            $_SESSION['mensaje_bienvenida'] = "Bienvenido a Grand Slam " . $usuario['nombre'];
            header("Location: cuenta.php");
            exit();
        } else {
            $mensaje_error = "Contraseña incorrecta.";
        }
    } else {
        $mensaje_error = "El correo no está registrado.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cuenta - Grand Slam</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: #f4f6f9; color: #333; display: flex; flex-direction: column; min-height: 100vh; }
        .header { background-color: #0d233a; color: white; text-align: center; padding: 25px 15px 15px 15px; }
        .navbar { background-color: #1a365d; display: flex; justify-content: center; padding: 10px 0; }
        .navbar a { color: white; text-decoration: none; padding: 10px 20px; font-weight: bold; border-radius: 4px; }
        .navbar a.activo { background-color: #d32f2f; }
        .btn-carrito { background-color: #e65100; }
        .container { max-width: 450px; margin: 30px auto; padding: 0 15px; flex: 1; width: 100%; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .campo { margin-bottom: 15px; display: flex; flex-direction: column; }
        .campo label { font-weight: bold; margin-bottom: 5px; }
        .campo input { padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .btn { width: 100%; padding: 10px; border-radius: 4px; font-weight: bold; cursor: pointer; border: none; color: white; margin-top: 10px; }
        .btn-azul { background-color: #1a365d; }
        .btn-verde { background-color: #2e7d32; }
        .btn-rojo { background-color: #d32f2f; }
        
        .alerta-exito { background-color: #c8e6c9; color: #2e7d32; padding: 12px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-weight: bold; }
        .alerta-error { background-color: #ffcdd2; color: #b71c1c; padding: 12px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-weight: bold; }
        
        .footer { background-color: #0d233a; color: white; text-align: center; padding: 20px 15px; margin-top: auto; }
    </style>
</head>
<body>

    <header class="header">
        <h1>Grand Slam</h1>
        <p>La mejor tienda en artículos de béisbol</p>
    </header>

    <nav class="navbar">
        <a href="index.html">Inicio</a>
        <a href="bats.html">Bats</a>
        <a href="guantes.html">Guantes</a>
        <a href="cascos.html">Cascos</a>
        <a href="guanteletas.html">Guanteletas</a>
        <a href="protecciones.html">Protecciones</a>
        <a href="cuenta.php" class="activo">Cuenta</a>
        <a href="carrito.php" class="btn-carrito">🛒 Carrito</a>
    </nav>

    <main class="container">
        <!-- MOSTRAR MENSAJE DE ÉXITO (REGISTRO, LOGIN O DESCONEXIÓN) -->
        <?php if (isset($_SESSION['mensaje_bienvenida'])): ?>
            <div class="alerta-exito">
                <?php 
                    echo htmlspecialchars($_SESSION['mensaje_bienvenida']); 
                    unset($_SESSION['mensaje_bienvenida']); // Se borra el mensaje tras mostrarse
                ?>
            </div>
        <?php endif; ?>

        <!-- MOSTRAR MENSAJE DE ERROR -->
        <?php if (!empty($mensaje_error)): ?>
            <div class="alerta-error"><?php echo htmlspecialchars($mensaje_error); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['usuario_nombre'])): ?>
            <div class="card" style="text-align:center;">
                <h2>Mi Cuenta</h2>
                <br>
                <p>Bienvenido(a), <strong><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></strong></p>
                <p style="font-size: 14px; color: #666; margin-top:5px;">Correo: <?php echo htmlspecialchars($_SESSION['usuario_correo']); ?></p>
                <br>
                <a href="carrito.php" class="btn btn-verde" style="display:inline-block; text-decoration:none;">Ir al Carrito</a>
                <a href="cuenta.php?logout=1" class="btn btn-rojo" style="display:inline-block; text-decoration:none; margin-top:10px;">Cerrar Sesión</a>
            </div>
        <?php else: ?>
            <div class="card">
                <h2>Iniciar Sesión</h2>
                <form action="cuenta.php" method="POST">
                    <div class="campo">
                        <label>Correo Electrónico:</label>
                        <input type="email" name="correo" required>
                    </div>
                    <div class="campo">
                        <label>Contraseña:</label>
                        <input type="password" name="password" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-azul">Entrar</button>
                </form>
            </div>

            <div class="card">
                <h2>Crear Cuenta</h2>
                <form action="cuenta.php" method="POST">
                    <div class="campo">
                        <label>Nombre Completo:</label>
                        <input type="text" name="nombre" required>
                    </div>
                    <div class="campo">
                        <label>Correo Electrónico:</label>
                        <input type="email" name="correo" required>
                    </div>
                    <div class="campo">
                        <label>Contraseña:</label>
                        <input type="password" name="password" required>
                    </div>
                    <button type="submit" name="registro" class="btn btn-verde">Registrarse</button>
                </form>
            </div>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <p>Hernandez Cuellar Emiliano | 5-J | 0850</p>
    </footer>

</body>
</html>
