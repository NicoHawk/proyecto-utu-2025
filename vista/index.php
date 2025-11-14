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
            <a href="contacto.html">Contacto</a>
            <!-- Selector de idioma -->
            <div class="language-selector closed">
                <button class="lang-btn" onclick="toggleLangMenu()">
                    <span class="flag">🌐</span>
                    <span id="currentLang">ES</span>
                    <span class="arrow">▼</span>
                </button>
                <div id="langMenu" class="lang-menu hidden">
                    <button class="lang-option" onclick="changeLang('es')">
                        <span class="flag">🇪🇸</span>
                        <span>Español</span>
                    </button>
                    <button class="lang-option" onclick="changeLang('en')">
                        <span class="flag">🇺🇸</span>
                        <span>English</span>
                    </button>
                </div>
            </div>
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
        // Toggle menú de idioma
        function toggleLangMenu() {
            const menu = document.getElementById('langMenu');
            const selector = document.querySelector('.language-selector');
            menu.classList.toggle('hidden');
            if (menu.classList.contains('hidden')) {
                selector.classList.add('closed');
            } else {
                selector.classList.remove('closed');
            }
        }

        // Cambiar idioma
        function changeLang(lang) {
            const currentLangSpan = document.getElementById('currentLang');
            if (lang === 'es') {
                currentLangSpan.textContent = 'ES';
                document.querySelector('h1').textContent = 'Iniciar Sesión';
                document.getElementById('correo').placeholder = 'Correo electrónico';
                document.getElementById('password').placeholder = 'Contraseña';
                document.querySelector('form button[type="submit"]').textContent = 'Ingresar';
                document.querySelector('.register-btn').textContent = 'Registrarse';
                document.querySelectorAll('.top-right a')[0].textContent = 'Inicio';
                document.querySelectorAll('.top-right a')[1].textContent = 'Registrarse';
                document.querySelectorAll('.top-right a')[2].textContent = 'Contacto';
            } else if (lang === 'en') {
                currentLangSpan.textContent = 'EN';
                document.querySelector('h1').textContent = 'Sign In';
                document.getElementById('correo').placeholder = 'Email';
                document.getElementById('password').placeholder = 'Password';
                document.querySelector('form button[type="submit"]').textContent = 'Login';
                document.querySelector('.register-btn').textContent = 'Sign Up';
                document.querySelectorAll('.top-right a')[0].textContent = 'Home';
                document.querySelectorAll('.top-right a')[1].textContent = 'Sign Up';
                document.querySelectorAll('.top-right a')[2].textContent = 'Contact';
            }
            toggleLangMenu();
        }

        // Cerrar menú al hacer clic fuera
        document.addEventListener('click', function(e) {
            const selector = document.querySelector('.language-selector');
            const menu = document.getElementById('langMenu');
            if (selector && !selector.contains(e.target)) {
                menu.classList.add('hidden');
                selector.classList.add('closed');
            }
        });

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
