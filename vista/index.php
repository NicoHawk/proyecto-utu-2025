<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/index.css">
</head>
<body>
    <div class="top-bar">
        <img src="../img/logo.png" alt="Logo de la empresa" class="logo">
        <div class="top-right">
            <a href="principal.html">Inicio</a>
            <a href="registro.html">Registrarse</a>
            <a href="#">Contacto</a>
        </div>
    </div>
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
        });
    </script>
</body>
</html>
