<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/index.css">
</head>
<body>
    <button class="menu-btn" id="abrirMenu" title="Abrir menú">&#9776;</button>
    <div class="menu-lateral" id="menuLateral">
        <button class="cerrar-menu" id="cerrarMenu" title="Cerrar menú">&times;</button>
    <a href="principal.html">Inicio</a>
    <a href="registro.html">Registrarse</a>
        <a href="#">Contacto</a>
    </div>
    <div class="menu-overlay" id="menuOverlay"></div>
    <div class="container">
        <h1>Iniciar Sesión</h1>
        <form id="loginForm">
            <input type="email" id="correo" name="correo" placeholder="Correo electrónico" required autocomplete="email">
            <input type="password" id="password" name="password" placeholder="Contraseña" required autocomplete="current-password">
            <button type="submit">Ingresar</button>
        </form>
    <button class="register-btn" onclick="window.location.href='registro.html'">Registrarse</button>
        <div id="mensaje" class="mensaje"></div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const mensajeDiv = document.getElementById('mensaje');
            mensajeDiv.style.display = 'none';

            loginForm.addEventListener('submit', function(event) {
                event.preventDefault();

                const correo = document.getElementById('correo').value;
                const password = document.getElementById('password').value;

                const formData = new FormData();
                formData.append('accion', 'login');
                formData.append('correo', correo);
                formData.append('password', password);

                fetch('../api/login.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    mensajeDiv.style.display = 'block';
                    if (data.success) {
                        mensajeDiv.textContent = 'Ingreso exitoso. Redirigiendo...';
                        mensajeDiv.className = 'mensaje success';
                        setTimeout(() => {
                            if (data.tipo_usuario === 'admin') {
                                window.location.href = 'formulario.php';
                            } else if (data.tipo_usuario === 'cliente') {
                                window.location.href = 'cliente.php';
                            } else if (data.tipo_usuario === 'cargador') {
                                window.location.href = 'cargador.php';
                            }
                        }, 900);
                    } else {
                        mensajeDiv.textContent = data.mensaje;
                        mensajeDiv.className = 'mensaje error';
                    }
                })
                .catch(error => {
                    mensajeDiv.style.display = 'block';
                    mensajeDiv.textContent = 'Error al conectar con el servidor.';
                    mensajeDiv.className = 'mensaje error';
                });
            });

            // Animación de menú lateral
            const abrirMenu = document.getElementById('abrirMenu');
            const cerrarMenu = document.getElementById('cerrarMenu');
            const menuLateral = document.getElementById('menuLateral');
            const menuOverlay = document.getElementById('menuOverlay');

            abrirMenu.addEventListener('click', function() {
                menuLateral.classList.add('abierto');
                menuOverlay.classList.add('visible');
            });
            cerrarMenu.addEventListener('click', function() {
                menuLateral.classList.remove('abierto');
                menuOverlay.classList.remove('visible');
            });
            menuOverlay.addEventListener('click', function() {
                menuLateral.classList.remove('abierto');
                menuOverlay.classList.remove('visible');
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === "Escape") {
                    menuLateral.classList.remove('abierto');
                    menuOverlay.classList.remove('visible');
                }
            });
        });
    </script>
</body>
</html>
