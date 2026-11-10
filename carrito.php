<?php
session_start();

// Si el usuario no ha iniciado sesión en PHP, redirige a cuenta.php
if (!isset($_SESSION['usuario_nombre'])) {
    echo "<script>
        alert('Para continuar con la compra, debes iniciar sesión o registrarte.');
        window.location.href = 'cuenta.php';
    </script>";
    exit();
}

$nombreUsuario = $_SESSION['usuario_nombre'];
$correoUsuario = $_SESSION['usuario_correo'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito - Grand Slam</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: #f4f6f9; color: #333; display: flex; flex-direction: column; min-height: 100vh; }
        
        .header { background-color: #0d233a; color: white; text-align: center; padding: 25px 15px 15px 15px; }
        .header h1 { font-size: 28px; margin-bottom: 5px; }
        .header p { color: #ccc; }

        .navbar { background-color: #1a365d; display: flex; justify-content: center; align-items: center; flex-wrap: wrap; padding: 10px 0; }
        .navbar a { color: white; text-decoration: none; padding: 10px 20px; font-weight: bold; border-radius: 4px; }
        .navbar a:hover, .navbar a.activo { background-color: #d32f2f; }
        
        .btn-carrito { background-color: #e65100; }
        .insignia-carrito { background-color: #d32f2f; color: white; border-radius: 50%; padding: 2px 7px; font-size: 12px; margin-left: 5px; }

        .container { max-width: 900px; margin: 30px auto; padding: 0 15px; flex: 1; width: 100%; }
        h2 { color: #0d233a; margin-bottom: 20px; font-size: 22px; border-bottom: 2px solid #d32f2f; padding-bottom: 5px; }

        table { width: 100%; border-collapse: collapse; background: white; margin-bottom: 20px; border-radius: 8px; overflow: hidden; }
        th, td { padding: 12px; text-align: center; border-bottom: 1px solid #ddd; }
        th { background-color: #0d233a; color: white; }
        .img-tabla { width: 50px; height: 50px; object-fit: contain; }

        .total-box { font-size: 18px; font-weight: bold; color: #d32f2f; margin-top: 15px; text-align: right; }
        
        .btn { padding: 10px 15px; border-radius: 4px; font-weight: bold; cursor: pointer; border: none; text-decoration: none; display: inline-block; }
        .btn-rojo { background-color: #d32f2f; color: white; }
        .btn-verde { background-color: #2e7d32; color: white; }
        .btn-azul { background-color: #1a365d; color: white; }
        .acciones { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; }

        .formulario { background: white; padding: 20px; border-radius: 8px; }
        .campo { margin-bottom: 12px; display: flex; flex-direction: column; }
        .campo label { font-weight: bold; margin-bottom: 4px; }
        .campo input, .campo select, .campo textarea { padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        
        .seccion-tarjeta {
            background-color: #f8f9fa;
            border: 1px dashed #1a365d;
            padding: 15px;
            border-radius: 6px;
            margin-top: 10px;
            margin-bottom: 15px;
        }

        .footer { background-color: #0d233a; color: white; text-align: center; padding: 20px 15px; margin-top: auto; font-size: 14px; }
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
        <a href="cuenta.php">Cuenta</a>
        <a href="carrito.php" class="btn-carrito activo">🛒 Carrito <span id="contador-carrito" class="insignia-carrito">0</span></a>
    </nav>

    <main class="container">
        <h2 id="titulo">Tu Carrito de Compras</h2>
        <div id="contenido-carrito"></div>
    </main>

    <footer class="footer">
        <p>Hernandez Cuellar Emiliano | 5-J | 0850</p>
    </footer>

    <script>
        function obtenerCarrito() { return JSON.parse(localStorage.getItem('carrito')) || []; }

        function actualizarContador() {
            let carrito = obtenerCarrito();
            let total = carrito.reduce((sum, item) => sum + item.cantidad, 0);
            document.getElementById('contador-carrito').innerText = total;
        }

        function verCarrito() {
            let carrito = obtenerCarrito();
            let div = document.getElementById('contenido-carrito');
            document.getElementById('titulo').innerText = "Tu Carrito de Compras";
            actualizarContador();

            if (carrito.length === 0) {
                div.innerHTML = '<p>El carrito está vacío.</p><br><a href="bats.html" class="btn btn-azul">Ir a Comprar</a>';
                return;
            }

            let html = '<table><tr><th>Imagen</th><th>Producto</th><th>Precio</th><th>Cant.</th><th>Subtotal</th><th></th></tr>';
            let totalGeneral = 0;

            carrito.forEach((item, index) => {
                let subtotal = item.precio * item.cantidad;
                totalGeneral += subtotal;
                html += `<tr>
                    <td><img src="${item.imagen}" class="img-tabla"></td>
                    <td>${item.nombre}</td>
                    <td>$${item.precio}</td>
                    <td>${item.cantidad}</td>
                    <td>$${subtotal}</td>
                    <td><button onclick="eliminar(${index})" class="btn btn-rojo">X</button></td>
                </tr>`;
            });

            html += `</table>
                <div class="total-box">Total: $${totalGeneral.toLocaleString()} MXN</div>
                <div class="acciones">
                    <button onclick="vaciar()" class="btn btn-rojo">Vaciar Carrito</button>
                    <button onclick="verFormulario()" class="btn btn-verde">Finalizar Compra</button>
                </div>`;

            div.innerHTML = html;
        }

        function eliminar(index) {
            let carrito = obtenerCarrito();
            carrito.splice(index, 1);
            localStorage.setItem('carrito', JSON.stringify(carrito));
            verCarrito();
        }

        function vaciar() {
            localStorage.removeItem('carrito');
            verCarrito();
        }

        // CAMPOS HABILITADOS PARA QUE EL USUARIO LOS LLENE MANUALMENTE
        function verFormulario() {
            let div = document.getElementById('contenido-carrito');
            document.getElementById('titulo').innerText = "Formulario de Compra";

            div.innerHTML = `
                <form onsubmit="comprar(event)" class="formulario">
                    <div class="campo">
                        <label>Nombre Completo:</label>
                        <input type="text" id="nombre-comprador" placeholder="Ingresa tu nombre completo" required>
                    </div>
                    <div class="campo">
                        <label>Correo Electrónico:</label>
                        <input type="email" id="correo-comprador" placeholder="correo@ejemplo.com" required>
                    </div>
                    <div class="campo">
                        <label>Teléfono:</label>
                        <input type="tel" required placeholder="10 dígitos">
                    </div>
                    <div class="campo">
                        <label>Dirección:</label>
                        <input type="text" required placeholder="Calle, número y colonia">
                    </div>
                    <div class="campo">
                        <label>Referencias:</label>
                        <textarea rows="2" placeholder="Ej. Entre calles..."></textarea>
                    </div>
                    
                    <div class="campo">
                        <label>Forma de Pago:</label>
                        <select id="select-pago" onchange="cambiarFormaPago()" required>
                            <option value="">Selecciona pago</option>
                            <option value="tarjeta">Tarjeta de Débito/Crédito</option>
                            <option value="efectivo">Efectivo contra entrega</option>
                        </select>
                    </div>

                    <div id="datos-tarjeta" class="seccion-tarjeta" style="display: none;">
                        <h4 style="margin-bottom:10px;">Ingrese los datos de la tarjeta:</h4>
                        <div class="campo"><label>Propietario de la tarjeta:</label><input type="text" id="tarjeta-nombre" placeholder="Nombre en la tarjeta"></div>
                        <div class="campo"><label>Número de tarjeta:</label><input type="text" id="tarjeta-numero" placeholder="16 dígitos" maxlength="16"></div>
                        <div class="campo"><label>Fecha de vencimiento:</label><input type="text" id="tarjeta-expira" placeholder="MM/AA" maxlength="5"></div>
                    </div>

                    <div class="acciones">
                        <button type="button" onclick="verCarrito()" class="btn btn-azul">Volver al Carrito</button>
                        <button type="submit" class="btn btn-verde">Confirmar Pedido</button>
                    </div>
                </form>
            `;
        }

        function cambiarFormaPago() {
            let metodo = document.getElementById('select-pago').value;
            let contenedorTarjeta = document.getElementById('datos-tarjeta');
            let inputNombre = document.getElementById('tarjeta-nombre');
            let inputNumero = document.getElementById('tarjeta-numero');
            let inputExpira = document.getElementById('tarjeta-expira');

            if (metodo === 'tarjeta') {
                contenedorTarjeta.style.display = 'block';
                inputNombre.required = true;
                inputNumero.required = true;
                inputExpira.required = true;
            } else {
                contenedorTarjeta.style.display = 'none';
                inputNombre.required = false;
                inputNumero.required = false;
                inputExpira.required = false;
            }
        }

        function comprar(e) {
            e.preventDefault();
            let nombreIngresado = document.getElementById('nombre-comprador').value;
            alert(`¡Gracias por tu compra, ${nombreIngresado}! Tu pedido ha sido procesado con éxito.`);
            vaciar();
        }

        verCarrito();
    </script>
</body>
</html>