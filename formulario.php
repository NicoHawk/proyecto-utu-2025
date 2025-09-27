<?php
session_start();
// Verificamos si hay un usuario logueado
if (!isset($_SESSION['usuario'])) {
// Si no está logueado, se lo redirige al login
header("Location: index.php");
exit;
}
// Verificamos si el rol del usuario es el adecuado para esta página
if ($_SESSION['tipo_usuario'] !== 'admin') {
// Si el usuario no tiene el rol adecuado, se puede mostrar un mensaje o redirigir
echo "No tiene permiso para acceder a esta página.";
exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles/formulario.css">
</head>

<body>
    <div class="container" style="max-width:1100px; flex-direction: row; align-items: flex-start; justify-content: center; gap: 40px;">
        <!-- Columna izquierda: Gestión de usuarios -->
        <div style="flex:1; min-width:320px; max-width:400px;">
            <h1>Gestión de Usuarios</h1>
            <form id="formulario">
                <input type="text" id="nombre" placeholder="Nombre de usuario" required>
                <input type="password" id="password" placeholder="Contraseña" required>
                <select id="tipo_usuario" required>
                    <option value="cliente">Cliente</option>
                    <option value="admin">Admin</option>
                    <option value="cargador">Cargador</option>
                </select>
                <button type="submit">Agregar</button>
            </form>
            <button id="btn-listar">Listar Usuarios</button>
            <ul id="resultado"></ul>
            <button id="btn-cerrar-sesion"
                style="margin-top:22px; width:100%; background:linear-gradient(90deg,#e53935 60%,#b71c1c 100%); color:#fff; font-weight:700;">Cerrar sesión</button>
        </div>
        <!-- Columna derecha: Mapa y cargadores -->
        <div style="flex:1.2; min-width:340px; max-width:650px; display:flex; flex-direction:column; align-items:center;">
            <h2 style="margin-bottom: 10px; color:#1976d2; text-align:center;">Mapa de cargadores</h2>
            <div id="map" style="width:100%; min-width:300px; height:340px; margin-bottom: 20px; border-radius: 16px; box-shadow: 0 4px 24px 0 rgba(31, 38, 135, 0.13);"></div>
            <form id="formCargador" style="display: flex; gap: 10px; align-items: center; width:100%; margin-bottom:18px;">
                <input type="text" id="nombreCargador" placeholder="Nombre del cargador" required style="flex:1;">
                <button type="submit" disabled style="min-width:140px;">Agregar cargador</button>
                <span id="ubicacionSeleccionada" style="font-size: 0.9em; color: #555;"></span>
            </form>
            <!-- Lista de cargadores -->
            <div id="listaCargadores" style="margin-top: 0; width:100%; max-width:600px;"></div>
        </div>
    </div>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCcstapgk7BG-qavJNSKsSWIeYCv_h0wXU&callback=initMap" async defer></script>
    <script>
        let map;
        let marcadores = {};
        let ubicacionTemporal = null;

        function initMap() {
            // Centrado en Pando, Canelones, Uruguay
            const centro = { lat: -34.7176, lng: -55.9586 };
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 13,
                center: centro,
            });

            // Cargar cargadores guardados
            fetch('api/cargadores.php')
                .then(res => res.json())
                .then(cargadores => {
                    mostrarListaCargadores(cargadores);
                    cargadores.forEach(cargador => {
                        agregarCargador(cargador.id, cargador.nombre, {lat: parseFloat(cargador.latitud), lng: parseFloat(cargador.longitud)});
                    });
                });

            // Al hacer clic en el mapa, guardar la ubicación y mostrar un marcador temporal
            map.addListener("click", function(e) {
                ubicacionTemporal = e.latLng;
                document.getElementById("ubicacionSeleccionada").textContent =
                    "Ubicación seleccionada";
                document.querySelector("#formCargador button[type='submit']").disabled = false;

                // Elimina marcador temporal anterior si existe
                if (marcadores['temporal']) {
                    marcadores['temporal'].setMap(null);
                }
                // Agrega marcador temporal
                marcadores['temporal'] = new google.maps.Marker({
                    position: ubicacionTemporal,
                    map,
                    icon: "https://maps.google.com/mapfiles/ms/icons/yellow-dot.png",
                    title: "Ubicación seleccionada"
                });
            });
        }

        function agregarCargador(id, nombre, latLng) {
            // Elimina marcador temporal
            if (marcadores['temporal']) {
                marcadores['temporal'].setMap(null);
                delete marcadores['temporal'];
            }
            // Crea el InfoWindow con el nombre
            const infoWindow = new google.maps.InfoWindow({
                content: `<strong>${nombre}</strong>`
            });
            // Agrega marcador definitivo
            const marcador = new google.maps.Marker({
                position: latLng,
                map,
                title: nombre,
                icon: "https://maps.google.com/mapfiles/ms/icons/green-dot.png",
            });
            marcador.addListener('click', function() {
                infoWindow.open(map, marcador);
            });
            marcadores[id] = marcador;
        }

        // Mostrar la lista de cargadores con botón para centrar el mapa
        function mostrarListaCargadores(cargadores) {
            const listaDiv = document.getElementById('listaCargadores');
            if (cargadores.length === 0) {
                listaDiv.innerHTML = "<p>No hay cargadores registrados.</p>";
                return;
            }
            let html = "<h3>Lista de cargadores</h3><ul style='list-style:none;padding:0;'>";
            cargadores.forEach(cargador => {
                html += `<li style="margin-bottom:10px;">
                    <strong>${cargador.nombre}</strong>
                    <button onclick="centrarEnCargador(${cargador.id})" style="margin-left:10px;">Ver en mapa</button>
                    <button onclick="eliminarCargador(${cargador.id})" style="margin-left:10px; background:#e53935; color:#fff; border:none; border-radius:4px; padding:5px 10px; cursor:pointer;">Eliminar</button>
                </li>`;
            });
            html += "</ul>";
            listaDiv.innerHTML = html;
        }

        // Función global para centrar el mapa en el cargador
        window.centrarEnCargador = function(id) {
            const marcador = marcadores[id];
            if (marcador) {
                map.setCenter(marcador.getPosition());
                map.setZoom(16);
                new google.maps.event.trigger(marcador, 'click');
            }
        };

        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById("formCargador");
            form.addEventListener("submit", function(e) {
                e.preventDefault();
                if (ubicacionTemporal) {
                    const nombre = document.getElementById("nombreCargador").value;
                    // Guardar en la base de datos
                    fetch('api/admin.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: `agregar_cargador=1&nombre=${encodeURIComponent(nombre)}&latitud=${encodeURIComponent(ubicacionTemporal.lat())}&longitud=${encodeURIComponent(ubicacionTemporal.lng())}`
                    })
                    .then(res => res.json())
                    .then(res => {
                        if(res.exito){
                            // Recargar lista y marcadores
                            fetch('api/cargadores.php')
                                .then(res => res.json())
                                .then(cargadores => {
                                    mostrarListaCargadores(cargadores);
                                    // Limpiar marcadores y volver a agregarlos
                                    Object.values(marcadores).forEach(m => m.setMap(null));
                                    marcadores = {};
                                    cargadores.forEach(cargador => {
                                        agregarCargador(cargador.id, cargador.nombre, {lat: parseFloat(cargador.latitud), lng: parseFloat(cargador.longitud)});
                                    });
                                });
                        }
                    });
                    form.reset();
                    document.getElementById("ubicacionSeleccionada").textContent = "";
                    ubicacionTemporal = null;
                    form.querySelector("button[type='submit']").disabled = true;
                }
            });
                // Cargar lista de usuarios automáticamente
                if (typeof listar === 'function') listar();
                // Cargar mapa automáticamente
                if (typeof initMap === 'function') initMap();
        });

        document.getElementById("formulario").innerHTML = `
            <input type="text" id="nombre" placeholder="Nombre de usuario" required>
            <input type="password" id="password" placeholder="Contraseña" required>
            <select id="tipo_usuario" required>
                <option value="cliente">Cliente</option>
                <option value="admin">Admin</option>
                <option value="cargador">Cargador</option>
            </select>
            <button type="submit">Agregar</button>
        `;

        document.getElementById("formulario").addEventListener("submit", function (e) {
            e.preventDefault();
            const nombre = document.getElementById("nombre").value;
            const password = document.getElementById("password").value;
            const tipo_usuario = document.getElementById("tipo_usuario").value;
            fetch("api/admin.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `agregar_usuario=1&username=${encodeURIComponent(nombre)}&password=${encodeURIComponent(password)}&tipo_usuario=${encodeURIComponent(tipo_usuario)}`
            })
                .then(res => res.json())
                .then(data => {
                    alert(data.mensaje);
                    // Limpiar campos del formulario
                    document.getElementById("nombre").value = "";
                    document.getElementById("password").value = "";
                    document.getElementById("tipo_usuario").value = "cliente";
                    listar(); // recargar lista
                });
        });

        document.getElementById("btn-listar").addEventListener("click", listar);

        function listar() {
            fetch("api/admin.php?listar_usuarios=1")
                .then(res => res.json())
                .then(data => {
                    let html = "";
                    data.forEach(usuario => {
                        html += `
                            <li id="usuario-${usuario.usuario}">
                                <div class="usuario-info">
                                    <span id="nombre-${usuario.usuario}"><b>Usuario:</b> ${usuario.usuario}</span>
                                    <span id="tipo-${usuario.usuario}"><b>Tipo:</b> ${usuario.tipo_usuario}</span>
                                    <input type="text" id="input-${usuario.usuario}" value="${usuario.usuario}" style="display:none; margin-bottom:4px;">
                                    <input type="password" id="input-pass-${usuario.usuario}" class="input-password" placeholder="Nueva contraseña" style="display:none; margin-bottom:4px;">
                                    <select id="input-tipo-${usuario.usuario}" style="display:none; margin-bottom:4px;">
                                        <option value="cliente" ${usuario.tipo_usuario === 'cliente' ? 'selected' : ''}>Cliente</option>
                                        <option value="admin" ${usuario.tipo_usuario === 'admin' ? 'selected' : ''}>Admin</option>
                                    </select>
                                </div>
                                <button class="btn-editar" onclick="editar('${usuario.usuario}')">Editar</button>
                                <button class="btn-guardar" onclick="guardar('${usuario.usuario}')" style="display:none;" id="guardar-${usuario.usuario}">Guardar</button>
                                <button class="btn-eliminar" onclick="eliminar('${usuario.usuario}')">Eliminar</button>
                            </li>`;
                    });
                    document.getElementById("resultado").innerHTML = html;
                });
        }

        window.editar = function (nombre) {
            document.getElementById(`nombre-${nombre}`).style.display = 'none';
            document.getElementById(`tipo-${nombre}`).style.display = 'none';
            document.getElementById(`input-${nombre}`).style.display = 'inline-block';
            document.getElementById(`input-pass-${nombre}`).style.display = 'inline-block';
            document.getElementById(`input-tipo-${nombre}`).style.display = 'inline-block';
            document.getElementById(`guardar-${nombre}`).style.display = 'inline-block';
        }

        window.guardar = function (nombre) {
            const nuevoNombre = document.getElementById(`input-${nombre}`).value;
            const nuevaPassword = document.getElementById(`input-pass-${nombre}`).value;
            const nuevoTipoUsuario = document.getElementById(`input-tipo-${nombre}`).value;
            let body = `accion=modificar&nombre=${encodeURIComponent(nombre)}&nuevoNombre=${encodeURIComponent(nuevoNombre)}&nuevoTipoUsuario=${encodeURIComponent(nuevoTipoUsuario)}`;
            if (nuevaPassword) {
                body += `&nuevaPassword=${encodeURIComponent(nuevaPassword)}`;
            }
            fetch("api/admin.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `modificar_usuario=1&nombre=${encodeURIComponent(nombre)}&nuevoNombre=${encodeURIComponent(nuevoNombre)}&nuevoTipoUsuario=${encodeURIComponent(nuevoTipoUsuario)}${nuevaPassword ? `&nuevaPassword=${encodeURIComponent(nuevaPassword)}` : ''}`
            })
                .then(res => res.json())
                .then(data => {
                    alert(data.mensaje);
                    listar();
                });
        }

        window.eliminar = function (nombre) {
            if (confirm("¿Eliminar este usuario?")) {
                fetch("api/admin.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `eliminar_usuario=1&nombre=${encodeURIComponent(nombre)}`
                })
                    .then(res => res.json())
                    .then(data => {
                        alert(data.mensaje);
                        listar();
                    });
            }
        }

        // Función para eliminar cargadores
        window.eliminarCargador = function(id) {
            if (confirm("¿Eliminar este cargador?")) {
                fetch('api/admin.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `eliminar_cargador=1&id=${encodeURIComponent(id)}`
                })
                .then(res => res.json())
                .then(res => {
                    if (res.exito) {
                        alert(res.mensaje || "Cargador eliminado");
                        // Recargar lista y marcadores
                        fetch('api/cargadores.php')
                            .then(res => res.json())
                            .then(cargadores => {
                                mostrarListaCargadores(cargadores);
                                // Limpiar marcadores y volver a agregarlos
                                Object.values(marcadores).forEach(m => m.setMap(null));
                                marcadores = {};
                                cargadores.forEach(cargador => {
                                    agregarCargador(
                                        cargador.id,
                                        cargador.nombre,
                                        {lat: parseFloat(cargador.latitud), lng: parseFloat(cargador.longitud)}
                                    );
                                });
                            });
                    } else {
                        alert(res.mensaje || "No se pudo eliminar el cargador.");
                    }
                })
                .catch(() => alert("Error de conexión al eliminar el cargador."));
            }
        }

        // Listar usuarios al cargar la página
        listar();

        // Botón para cerrar sesión
        document.getElementById("btn-cerrar-sesion").onclick = function () {
            window.location.href = "logout.php";
        };
    </script>
</body>

</html>