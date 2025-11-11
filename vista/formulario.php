<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}
if ($_SESSION['tipo_usuario'] !== 'admin') {
    echo "No tiene permiso para acceder a esta página.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/formulario.css">
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-logo">
            <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Logo" />
        </div>
        <ul class="sidebar-menu">
            <li class="sidebar-item active" data-tab="usuarios">
                <span class="sidebar-icon">👤</span>
                <span class="sidebar-text">Usuarios</span>
            </li>
            <li class="sidebar-item" data-tab="autos">
                <span class="sidebar-icon">🚗</span>
                <span class="sidebar-text">Autos</span>
            </li>
            <li class="sidebar-item" data-tab="cargadores">
                <span class="sidebar-icon">⚡</span>
                <span class="sidebar-text">Cargadores</span>
            </li>
            <li class="sidebar-item" onclick="window.location.href='logout.php'">
                <span class="sidebar-icon">🚪</span>
                <span class="sidebar-text">Salir</span>
            </li>
        </ul>
    </nav>

    <!-- Panel principal -->
    <div class="main-panel">
        <!-- Menú usuario arriba a la derecha -->
        <div class="usuario-menu">
            <div class="usuario-trigger">
                <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Usuario" class="icono-usuario">
                <span class="saludo">Hola, <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                <span class="flecha">&#9660;</span>
            </div>
            <div class="usuario-dropdown">
                <a href="logout.php">Cerrar sesión</a>
            </div>
        </div>

        <!-- Contenido de pestañas -->
        <div class="tab-content" id="tab-usuarios">
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
        </div>
        <div class="tab-content" id="tab-autos" style="display:none;">
            <h1>Gestión de Autos de Clientes</h1>
            
            <!-- Formulario para agregar auto -->
            <div style="background:#f8f9fa; padding:20px; border-radius:8px; margin-bottom:20px;">
                <h3 style="margin-top:0; color:#1976d2;">Agregar Nuevo Auto</h3>
                <form id="formAgregarAuto" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:10px;">
                    <select id="nuevoAutoUsuario" required style="padding:8px; border-radius:4px; border:1px solid #cbd5e1;">
                        <option value="">Seleccionar Usuario</option>
                    </select>
                    <input type="text" id="nuevoAutoMarca" placeholder="Marca" required style="padding:8px; border-radius:4px; border:1px solid #cbd5e1;">
                    <input type="text" id="nuevoAutoModelo" placeholder="Modelo" required style="padding:8px; border-radius:4px; border:1px solid #cbd5e1;">
                    <select id="nuevoAutoConector" required style="padding:8px; border-radius:4px; border:1px solid #cbd5e1;">
                        <option value="">Tipo de conector</option>
                        <option value="Tipo 1">Tipo 1 (SAE J1772)</option>
                        <option value="Tipo 2">Tipo 2 (Mennekes)</option>
                        <option value="CCS Combo 1">CCS Combo 1</option>
                        <option value="CCS Combo 2">CCS Combo 2</option>
                        <option value="CHAdeMO">CHAdeMO</option>
                        <option value="Tesla (NACS)">Tesla (NACS)</option>
                        <option value="GB/T">GB/T</option>
                    </select>
                    <input type="number" id="nuevoAutoAutonomia" placeholder="Autonomía (km)" required style="padding:8px; border-radius:4px; border:1px solid #cbd5e1;">
                    <input type="number" id="nuevoAutoAnio" placeholder="Año" required style="padding:8px; border-radius:4px; border:1px solid #cbd5e1;">
                    <button type="submit" style="background:#4CAF50; color:#fff; border:none; padding:8px 16px; border-radius:4px; cursor:pointer; font-weight:bold;">Agregar Auto</button>
                </form>
            </div>

            <div id="autosToolbar" style="display:flex; justify-content:flex-end; align-items:center; gap:8px; margin:10px 0 6px 0;">
                <label for="ordenAutos" style="font-size:0.95em; color:#555;">Orden:</label>
                <select id="ordenAutos" style="padding:6px 10px; border-radius:8px; border:1px solid #cbd5e1; background:#fff; color:#333;">
                    <option value="asc" selected>ID ascendente (1 → N)</option>
                    <option value="desc">ID descendente (N → 1)</option>
                </select>
            </div>
            <div id="listaAutos" style="margin-top: 20px;"></div>
        </div>
        <div class="tab-content" id="tab-cargadores" style="display:none;">
            <h1>Gestión de Cargadores</h1>
            <p style="color: #64b5f6; text-shadow: 1px 1px 4px #222;">Administra los puntos de carga eléctrica</p>

            <h2 style="margin-bottom: 10px; color:#1976d2; margin-top:40px;">Mapa de cargadores</h2>
            <div id="map" style="width:100%; min-width:300px; height:340px; margin-bottom: 20px; border-radius: 16px; box-shadow: 0 4px 24px 0 rgba(31, 38, 135, 0.13);"></div>
            
            <h2 style="margin-top:40px;">Agregar Cargador</h2>
            <p style="font-size:0.9em; color:#666; margin-bottom:12px;">Haz clic en el mapa para seleccionar la ubicación del cargador</p>
            
            <form id="formCargador" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; width:100%; margin-bottom:18px;">
                <input type="text" id="nombreCargador" placeholder="Nombre del cargador" required style="grid-column: 1 / -1;">
                
                <input type="text" id="descripcionCargador" placeholder="Descripción (opcional)" style="grid-column: 1 / -1;">
                
                <select id="tipoCargador" required>
                    <option value="">Tipo de cargador</option>
                    <option value="AC Lento">AC Lento (3-7 kW)</option>
                    <option value="AC Rápido">AC Rápido (7-22 kW)</option>
                    <option value="DC Rápido">DC Rápido (50+ kW)</option>
                    <option value="DC Ultra Rápido">DC Ultra Rápido (150+ kW)</option>
                </select>
                
                <input type="number" id="potenciaCargador" placeholder="Potencia (kW)" min="0" step="0.1" required>
                
                <select id="estadoCargador" required>
                    <option value="disponible">Disponible</option>
                    <option value="ocupado">Ocupado</option>
                    <option value="mantenimiento">En mantenimiento</option>
                    <option value="fuera_de_servicio">Fuera de servicio</option>
                </select>
                
                <div style="grid-column: 1 / -1;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; color:#1976d2;">Tipos de conectores disponibles:</label>
                    <div style="display:grid; grid-template-columns: repeat(2, 1fr); gap:8px;">
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                            <input type="checkbox" name="conectores" value="Tipo 1" class="conector-checkbox">
                            <span>Tipo 1 (SAE J1772)</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                            <input type="checkbox" name="conectores" value="Tipo 2" class="conector-checkbox">
                            <span>Tipo 2 (Mennekes)</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                            <input type="checkbox" name="conectores" value="CCS Combo 1" class="conector-checkbox">
                            <span>CCS Combo 1</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                            <input type="checkbox" name="conectores" value="CCS Combo 2" class="conector-checkbox">
                            <span>CCS Combo 2</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                            <input type="checkbox" name="conectores" value="CHAdeMO" class="conector-checkbox">
                            <span>CHAdeMO</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                            <input type="checkbox" name="conectores" value="Tesla (NACS)" class="conector-checkbox">
                            <span>Tesla (NACS)</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                            <input type="checkbox" name="conectores" value="GB/T" class="conector-checkbox">
                            <span>GB/T</span>
                        </label>
                    </div>
                </div>
                
                <button type="submit" disabled style="grid-column: 1 / -1; min-width:140px;">Agregar cargador</button>
                <span id="ubicacionSeleccionada" style="grid-column: 1 / -1; font-size: 0.9em; color: #555;"></span>
            </form>

            <h2 style="margin-top:40px;">Listado de Cargadores</h2>
            <div id="tablaCargadores"></div>
        </div>
    </div>

    <!-- Google Maps -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCcstapgk7BG-qavJNSKsSWIeYCv_h0wXU&callback=initMap" async defer></script>
    <script>
    // Pestañas (compatibilidad si usas botones de pestañas)
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('.tab-content').forEach(tab => tab.style.display = 'none');
            document.getElementById('tab-' + this.dataset.tab).style.display = 'block';
        });
    });

    // Sidebar -> cambiar pestañas (agregado para que funcione el menú lateral)
    document.querySelectorAll('.sidebar-item').forEach(item => {
        item.addEventListener('click', function() {
            const tab = this.dataset.tab;
            if (!tab) return;
            document.querySelectorAll('.sidebar-item').forEach(i => i.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('.tab-content').forEach(tc => tc.style.display = 'none');
            const target = document.getElementById('tab-' + tab);
            if (target) {
                target.style.display = 'block';
                // Forzar resize del mapa si abrimos la pestaña de cargadores
                if (tab === 'cargadores' && typeof google !== 'undefined' && typeof map !== 'undefined') {
                    setTimeout(() => {
                        try {
                            google.maps.event.trigger(map, 'resize');
                            if (window.lastCenter) map.setCenter(window.lastCenter);
                        } catch (e) { /* ignorar */ }
                    }, 200);
                    // Cargar lista de cargadores
                    fetch('../api/cargadores.php')
                        .then(res => res.json())
                        .then(cargadores => {
                            mostrarListaCargadores(cargadores);
                        });
                }
                // Cargar autos y usuarios si se abre la pestaña de autos
                if (tab === 'autos') {
                    listarAutos();
                    cargarUsuariosParaAutos();
                }
            }
        });
    });

    // Menú usuario desplegable
    document.querySelector('.usuario-trigger').addEventListener('click', function(e) {
        e.stopPropagation();
        document.querySelector('.usuario-menu').classList.toggle('activo');
    });
    document.addEventListener('click', function() {
        document.querySelector('.usuario-menu').classList.remove('activo');
    });

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
    fetch('../api/cargadores.php')
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
        const tablaDiv = document.getElementById('tablaCargadores');
        if (cargadores.length === 0) {
            tablaDiv.innerHTML = "<p>No hay cargadores registrados.</p>";
            return;
        }
        
        let html = `<table style="width:100%;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Tipo</th>
                    <th>Potencia (kW)</th>
                    <th>Estado</th>
                    <th>Conectores</th>
                    <th>Ubicación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>`;
        
        cargadores.forEach(cargador => {
            html += `<tr data-id="${cargador.id}">
                <td>${cargador.id}</td>
                <td>
                    <span id="nombre-${cargador.id}">${cargador.nombre}</span>
                    <input type="text" id="input-nombre-${cargador.id}" value="${cargador.nombre}" style="display:none; width:95%;">
                </td>
                <td>
                    <span id="descripcion-${cargador.id}">${cargador.descripcion || '-'}</span>
                    <input type="text" id="input-descripcion-${cargador.id}" value="${cargador.descripcion || ''}" style="display:none; width:95%;">
                </td>
                <td>
                    <span id="tipo-${cargador.id}">${cargador.tipo || '-'}</span>
                    <select id="input-tipo-${cargador.id}" style="display:none; width:95%;">
                        <option value="">-</option>
                        <option value="AC Lento" ${cargador.tipo === 'AC Lento' ? 'selected' : ''}>AC Lento</option>
                        <option value="AC Rápido" ${cargador.tipo === 'AC Rápido' ? 'selected' : ''}>AC Rápido</option>
                        <option value="DC Rápido" ${cargador.tipo === 'DC Rápido' ? 'selected' : ''}>DC Rápido</option>
                        <option value="DC Ultra Rápido" ${cargador.tipo === 'DC Ultra Rápido' ? 'selected' : ''}>DC Ultra Rápido</option>
                    </select>
                </td>
                <td>
                    <span id="potencia-${cargador.id}">${cargador.potencia_kw}</span>
                    <input type="number" id="input-potencia-${cargador.id}" value="${cargador.potencia_kw}" min="0" step="0.1" style="display:none; width:80px;">
                </td>
                <td>
                    <span id="estado-${cargador.id}">${cargador.estado}</span>
                    <select id="input-estado-${cargador.id}" style="display:none; width:95%;">
                        <option value="disponible" ${cargador.estado === 'disponible' ? 'selected' : ''}>Disponible</option>
                        <option value="ocupado" ${cargador.estado === 'ocupado' ? 'selected' : ''}>Ocupado</option>
                        <option value="mantenimiento" ${cargador.estado === 'mantenimiento' ? 'selected' : ''}>Mantenimiento</option>
                        <option value="fuera_de_servicio" ${cargador.estado === 'fuera_de_servicio' ? 'selected' : ''}>Fuera de servicio</option>
                    </select>
                </td>
                <td>
                    <span id="conectores-${cargador.id}" style="font-size:0.85em;">${cargador.conectores || '-'}</span>
                    <div id="input-conectores-${cargador.id}" style="display:none;">
                        <label style="display:block; font-size:0.8em;"><input type="checkbox" value="Tipo 1" ${(cargador.conectores || '').includes('Tipo 1') ? 'checked' : ''}> Tipo 1</label>
                        <label style="display:block; font-size:0.8em;"><input type="checkbox" value="Tipo 2" ${(cargador.conectores || '').includes('Tipo 2') ? 'checked' : ''}> Tipo 2</label>
                        <label style="display:block; font-size:0.8em;"><input type="checkbox" value="CCS Combo 1" ${(cargador.conectores || '').includes('CCS Combo 1') ? 'checked' : ''}> CCS 1</label>
                        <label style="display:block; font-size:0.8em;"><input type="checkbox" value="CCS Combo 2" ${(cargador.conectores || '').includes('CCS Combo 2') ? 'checked' : ''}> CCS 2</label>
                        <label style="display:block; font-size:0.8em;"><input type="checkbox" value="CHAdeMO" ${(cargador.conectores || '').includes('CHAdeMO') ? 'checked' : ''}> CHAdeMO</label>
                        <label style="display:block; font-size:0.8em;"><input type="checkbox" value="Tesla (NACS)" ${(cargador.conectores || '').includes('Tesla (NACS)') ? 'checked' : ''}> Tesla</label>
                        <label style="display:block; font-size:0.8em;"><input type="checkbox" value="GB/T" ${(cargador.conectores || '').includes('GB/T') ? 'checked' : ''}> GB/T</label>
                    </div>
                </td>
                <td style="font-size:0.8em;">
                    ${cargador.latitud.toFixed(4)}, ${cargador.longitud.toFixed(4)}
                    <br><button onclick="centrarEnCargador(${cargador.id})" style="margin-top:4px; font-size:0.8em; padding:4px 8px;">📍 Ver</button>
                </td>
                <td>
                    <div style="display:flex; flex-direction:column; gap:4px;">
                        <button class="btn-editar" onclick="editarCargador(${cargador.id})" id="btn-editar-${cargador.id}">Editar</button>
                        <button class="btn-guardar" onclick="guardarCargador(${cargador.id})" style="display:none;" id="btn-guardar-${cargador.id}">Guardar</button>
                        <button class="btn-cancelar" onclick="cancelarEditarCargador(${cargador.id})" style="display:none;" id="btn-cancelar-${cargador.id}">Cancelar</button>
                        <button class="btn-eliminar" onclick="eliminarCargador(${cargador.id})" id="btn-eliminar-${cargador.id}">Eliminar</button>
                    </div>
                </td>
            </tr>`;
        });
        
        html += `</tbody></table>`;
        tablaDiv.innerHTML = html;
    }

    // Función global para centrar el mapa en el cargador y hacer scroll automático al mapa
    window.centrarEnCargador = function(id) {
        const marcador = marcadores[id];
        const mapEl = document.getElementById('map');
        if (!mapEl) return;

        // Desplazar suavemente hasta el mapa
        mapEl.scrollIntoView({ behavior: 'smooth', block: 'start' });

        // Resaltar el mapa brevemente
        mapEl.classList.add('map-highlight');
        setTimeout(() => mapEl.classList.remove('map-highlight'), 1500);

        if (marcador) {
            // Asegurar que el mapa calcule bien su tamaño luego del scroll
            setTimeout(() => {
                try { google.maps.event.trigger(map, 'resize'); } catch (e) {}
                map.setCenter(marcador.getPosition());
                map.setZoom(16);
                // Abrir popup del marcador
                try { new google.maps.event.trigger(marcador, 'click'); } catch (e) {}
                // Hacer rebotar el marcador brevemente para llamar la atención
                try {
                    if (google.maps && google.maps.Animation && marcador.setAnimation) {
                        marcador.setAnimation(google.maps.Animation.BOUNCE);
                        setTimeout(() => marcador.setAnimation(null), 1400);
                    }
                } catch (e) {}
            }, 350);
        }
    };

    // Función para mostrar mensajes toast
    function mostrarMensaje(texto, tipo) {
        let toast = document.createElement('div');
        toast.className = 'mensaje-toast ' + (tipo === 'exito' ? 'exito' : 'error');
        toast.textContent = texto;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 400);
        }, 2500);
    }

    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById("formCargador");
        form.addEventListener("submit", function(e) {
            e.preventDefault();
            if (ubicacionTemporal) {
                const nombre = document.getElementById("nombreCargador").value;
                const descripcion = document.getElementById("descripcionCargador").value;
                const tipo = document.getElementById("tipoCargador").value;
                const potencia = document.getElementById("potenciaCargador").value;
                const estado = document.getElementById("estadoCargador").value;
                
                // Obtener los conectores seleccionados
                const conectoresCheckboxes = document.querySelectorAll('.conector-checkbox:checked');
                const conectores = Array.from(conectoresCheckboxes).map(cb => cb.value).join(', ');
                
                if (!conectores) {
                    mostrarMensaje('Debes seleccionar al menos un tipo de conector', 'error');
                    return;
                }
                
                // Guardar en la base de datos
                fetch('../api/admin.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `agregar_cargador=1&nombre=${encodeURIComponent(nombre)}&latitud=${encodeURIComponent(ubicacionTemporal.lat())}&longitud=${encodeURIComponent(ubicacionTemporal.lng())}&descripcion=${encodeURIComponent(descripcion)}&tipo=${encodeURIComponent(tipo)}&estado=${encodeURIComponent(estado)}&potencia_kw=${encodeURIComponent(potencia)}&conectores=${encodeURIComponent(conectores)}`
                })
                .then(res => res.json())
                .then(res => {
                    if(res.exito){
                        // Mostrar mensaje de éxito
                        mostrarMensaje('Cargador agregado correctamente', 'exito');
                        // Recargar lista y marcadores
                        fetch('../api/cargadores.php')
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
                    } else {
                        mostrarMensaje(res.mensaje || 'Error al agregar el cargador', 'error');
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
        <input type="email" id="correo" placeholder="Correo electrónico" required>
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
        const correo = document.getElementById("correo").value;
        const password = document.getElementById("password").value;
        const tipo_usuario = document.getElementById("tipo_usuario").value;
    fetch("../api/admin.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `agregar_usuario=1&username=${encodeURIComponent(nombre)}&correo=${encodeURIComponent(correo)}&password=${encodeURIComponent(password)}&tipo_usuario=${encodeURIComponent(tipo_usuario)}`
        })
            .then(res => res.json())
            .then(data => {
                mostrarMensaje(data.mensaje, data.mensaje.toLowerCase().includes('error') ? 'error' : 'exito');
                // Limpiar campos del formulario
                document.getElementById("nombre").value = "";
                document.getElementById("correo").value = "";
                document.getElementById("password").value = "";
                document.getElementById("tipo_usuario").value = "cliente";
                listar(); // recargar lista
            });
    });

    document.getElementById("btn-listar").addEventListener("click", listar);

    function listar() {
    fetch("../api/admin.php?listar_usuarios=1")
            .then(res => res.json())
            .then(data => {
                let html = "";
                data.forEach(usuario => {
                    html += `
                        <li id="usuario-${usuario.usuario}">
                            <div class="usuario-info">
                                <span id="nombre-${usuario.usuario}"><b>Usuario:</b> ${usuario.usuario}</span>
                                <span id="correo-${usuario.usuario}"><b>Correo:</b> ${usuario.correo}</span>
                                <span id="tipo-${usuario.usuario}"><b>Tipo:</b> ${usuario.tipo_usuario}</span>
                                <input type="text" id="input-${usuario.usuario}" value="${usuario.usuario}" style="display:none; margin-bottom:4px;">
                                <input type="email" id="input-correo-${usuario.usuario}" value="${usuario.correo}" style="display:none; margin-bottom:4px;">
                                <input type="password" id="input-pass-${usuario.usuario}" class="input-password" placeholder="Nueva contraseña" style="display:none; margin-bottom:4px;">
                                <select id="input-tipo-${usuario.usuario}" style="display:none; margin-bottom:4px;">
                                    <option value="cliente" ${usuario.tipo_usuario === 'cliente' ? 'selected' : ''}>Cliente</option>
                                    <option value="admin" ${usuario.tipo_usuario === 'admin' ? 'selected' : ''}>Admin</option>
                                    <option value="cargador" ${usuario.tipo_usuario === 'cargador' ? 'selected' : ''}>Cargador</option>
                                </select>
                            </div>
                            <button class="btn-editar" onclick="editar('${usuario.usuario}')" id="btn-editar-${usuario.usuario}">Editar</button>
                            <button class="btn-guardar" onclick="guardar('${usuario.usuario}')" style="display:none;" id="guardar-${usuario.usuario}">Guardar</button>
                            <button class="btn-cancelar" onclick="cancelar('${usuario.usuario}')" style="display:none;" id="cancelar-${usuario.usuario}">Cancelar</button>
                            <button class="btn-eliminar" onclick="eliminar('${usuario.usuario}')" id="btn-eliminar-${usuario.usuario}">Eliminar</button>
                        </li>`;
                });
                document.getElementById("resultado").innerHTML = html;
            });
    }

    window.editar = function (nombre) {
        document.getElementById(`nombre-${nombre}`).style.display = 'none';
        document.getElementById(`correo-${nombre}`).style.display = 'none';
        document.getElementById(`tipo-${nombre}`).style.display = 'none';
        document.getElementById(`input-${nombre}`).style.display = 'inline-block';
        document.getElementById(`input-correo-${nombre}`).style.display = 'inline-block';
        document.getElementById(`input-pass-${nombre}`).style.display = 'inline-block';
        document.getElementById(`input-tipo-${nombre}`).style.display = 'inline-block';
        document.getElementById(`guardar-${nombre}`).style.display = 'inline-block';
        document.getElementById(`cancelar-${nombre}`).style.display = 'inline-block';
        document.getElementById(`btn-editar-${nombre}`).style.display = 'none';
        document.getElementById(`btn-eliminar-${nombre}`).style.display = 'none';
    }

    window.guardar = function (nombre) {
        const nuevoNombre = document.getElementById(`input-${nombre}`).value;
        const nuevoCorreo = document.getElementById(`input-correo-${nombre}`).value;
        const nuevaPassword = document.getElementById(`input-pass-${nombre}`).value;
        const nuevoTipoUsuario = document.getElementById(`input-tipo-${nombre}`).value;
        let body = `modificar_usuario=1&nombre=${encodeURIComponent(nombre)}&nuevoNombre=${encodeURIComponent(nuevoNombre)}&nuevoCorreo=${encodeURIComponent(nuevoCorreo)}&nuevoTipoUsuario=${encodeURIComponent(nuevoTipoUsuario)}`;
        if (nuevaPassword) {
            body += `&nuevaPassword=${encodeURIComponent(nuevaPassword)}`;
        }
    fetch("../api/admin.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: body
        })
            .then(res => res.json())
            .then(data => {
                mostrarMensaje(data.mensaje, data.mensaje.toLowerCase().includes('error') ? 'error' : 'exito');
                listar();
            });
    }

    window.cancelar = function (nombre) {
        listar();
    }

    window.eliminar = function (nombre) {
        if (confirm("¿Eliminar este usuario?")) {
            fetch("../api/admin.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `eliminar_usuario=1&nombre=${encodeURIComponent(nombre)}`
            })
                .then(res => res.json())
                .then(data => {
                    mostrarMensaje(data.mensaje, data.mensaje.toLowerCase().includes('error') ? 'error' : 'exito');
                    listar();
                });
        }
    }

    // Función para eliminar cargadores
    window.eliminarCargador = function(id) {
        if (confirm("¿Eliminar este cargador?")) {
            fetch('../api/admin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `eliminar_cargador=1&id=${encodeURIComponent(id)}`
            })
            .then(res => res.json())
            .then(res => {
                if (res.exito) {
                    mostrarMensaje('Cargador eliminado correctamente', 'exito');
                    // Recargar lista y marcadores
                    fetch('../api/cargadores.php')
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
                } else {
                    mostrarMensaje(res.mensaje || 'Error al eliminar', 'error');
                }
            })
            .catch(() => mostrarMensaje('Error de conexión', 'error'));
        }
    }

    // Función para editar cargador
    window.editarCargador = function(id) {
        document.getElementById(`nombre-${id}`).style.display = 'none';
        document.getElementById(`descripcion-${id}`).style.display = 'none';
        document.getElementById(`tipo-${id}`).style.display = 'none';
        document.getElementById(`potencia-${id}`).style.display = 'none';
        document.getElementById(`estado-${id}`).style.display = 'none';
        document.getElementById(`conectores-${id}`).style.display = 'none';
        
        document.getElementById(`input-nombre-${id}`).style.display = 'inline-block';
        document.getElementById(`input-descripcion-${id}`).style.display = 'inline-block';
        document.getElementById(`input-tipo-${id}`).style.display = 'inline-block';
        document.getElementById(`input-potencia-${id}`).style.display = 'inline-block';
        document.getElementById(`input-estado-${id}`).style.display = 'inline-block';
        document.getElementById(`input-conectores-${id}`).style.display = 'block';
        
        document.getElementById(`btn-editar-${id}`).style.display = 'none';
        document.getElementById(`btn-eliminar-${id}`).style.display = 'none';
        document.getElementById(`btn-guardar-${id}`).style.display = 'inline-block';
        document.getElementById(`btn-cancelar-${id}`).style.display = 'inline-block';
    }

    // Función para cancelar edición de cargador
    window.cancelarEditarCargador = function(id) {
        fetch('../api/cargadores.php')
            .then(res => res.json())
            .then(cargadores => {
                mostrarListaCargadores(cargadores);
            });
    }

    // Función para guardar cargador editado
    window.guardarCargador = function(id) {
        const nombre = document.getElementById(`input-nombre-${id}`).value;
        const descripcion = document.getElementById(`input-descripcion-${id}`).value;
        const tipo = document.getElementById(`input-tipo-${id}`).value;
        const potencia = document.getElementById(`input-potencia-${id}`).value;
        const estado = document.getElementById(`input-estado-${id}`).value;
        
        // Obtener conectores seleccionados
        const checkboxes = document.querySelectorAll(`#input-conectores-${id} input[type="checkbox"]:checked`);
        const conectores = Array.from(checkboxes).map(cb => cb.value).join(', ');
        
        if (!nombre) {
            mostrarMensaje('El nombre es requerido', 'error');
            return;
        }
        
        fetch('../api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `modificar_cargador=1&id=${encodeURIComponent(id)}&nombre=${encodeURIComponent(nombre)}&descripcion=${encodeURIComponent(descripcion)}&tipo=${encodeURIComponent(tipo)}&potencia_kw=${encodeURIComponent(potencia)}&estado=${encodeURIComponent(estado)}&conectores=${encodeURIComponent(conectores)}`
        })
        .then(res => res.json())
        .then(res => {
            if (res.exito) {
                mostrarMensaje('Cargador actualizado correctamente', 'exito');
                // Recargar lista
                fetch('../api/cargadores.php')
                    .then(res => res.json())
                    .then(cargadores => {
                        mostrarListaCargadores(cargadores);
                        // Actualizar marcadores
                        Object.values(marcadores).forEach(m => m.setMap(null));
                        marcadores = {};
                        cargadores.forEach(cargador => {
                            agregarCargador(cargador.id, cargador.nombre, {lat: parseFloat(cargador.latitud), lng: parseFloat(cargador.longitud)});
                        });
                    });
            } else {
                mostrarMensaje(res.mensaje || 'Error al actualizar', 'error');
            }
        })
        .catch(() => mostrarMensaje('Error de conexión', 'error'));
    }

    // Listar usuarios al cargar la página
    listar();

    // Helper: opciones del selector de conector (edición inline)
    function opcionesConectorHTML(seleccionado) {
        const opciones = [
            { value: 'Tipo 1', label: 'Tipo 1 (SAE J1772)' },
            { value: 'Tipo 2', label: 'Tipo 2 (Mennekes)' },
            { value: 'CCS Combo 1', label: 'CCS Combo 1' },
            { value: 'CCS Combo 2', label: 'CCS Combo 2' },
            { value: 'CHAdeMO', label: 'CHAdeMO' },
            { value: 'Tesla (NACS)', label: 'Tesla (NACS)' },
            { value: 'GB/T', label: 'GB/T' }
        ];
        return opciones
            .map(o => `<option value="${o.value}" ${o.value === seleccionado ? 'selected' : ''}>${o.label}</option>`)
            .join('');
    }

    // Función para listar todos los autos
    function listarAutos(orden) {
        // Tomamos el orden desde el selector (por defecto asc)
        const ordenSel = orden || (document.getElementById('ordenAutos') ? document.getElementById('ordenAutos').value : 'asc');
        // Forzamos refresco con timestamp
        const url = `../api/admin.php?listar_autos=1&orden=${encodeURIComponent(ordenSel)}&_=${Date.now()}`;
        fetch(url)
            .then(res => res.json())
            .then(autos => {
                const container = document.getElementById('listaAutos');
                if (autos.length === 0) {
                    container.innerHTML = '<p style="text-align:center; color:#666;">No hay autos registrados.</p>';
                    return;
                }

                let html = '<table style="width:100%; border-collapse: collapse; margin-top:10px;">';
                html += '<thead><tr style="background:#1976d2; color:#fff;">';
                html += '<th style="padding:12px; text-align:left;">ID</th>';
                html += '<th style="padding:12px; text-align:left;">Usuario</th>';
                html += '<th style="padding:12px; text-align:left;">Marca</th>';
                html += '<th style="padding:12px; text-align:left;">Modelo</th>';
                html += '<th style="padding:12px; text-align:left;">Conector</th>';
                html += '<th style="padding:12px; text-align:left;">Autonomía (km)</th>';
                html += '<th style="padding:12px; text-align:left;">Año</th>';
                html += '<th style="padding:12px; text-align:center;">Acciones</th>';
                html += '</tr></thead><tbody>';

                autos.forEach(auto => {
                    html += `<tr style="border-bottom:1px solid #ddd;">
                        <td style="padding:10px;">${auto.id}</td>
                        <td style="padding:10px;"><strong>${auto.usuario}</strong></td>
                        <td style="padding:10px;">
                            <span id="marca-${auto.id}">${auto.marca}</span>
                            <input type="text" id="input-marca-${auto.id}" value="${auto.marca}" style="display:none; width:100%; padding:5px;">
                        </td>
                        <td style="padding:10px;">
                            <span id="modelo-${auto.id}">${auto.modelo}</span>
                            <input type="text" id="input-modelo-${auto.id}" value="${auto.modelo}" style="display:none; width:100%; padding:5px;">
                        </td>
                        <td style="padding:10px;">
                            <span id="conector-${auto.id}">${auto.conector}</span>
                            <select id="input-conector-${auto.id}" style="display:none; width:100%; padding:5px;">
                                ${opcionesConectorHTML(auto.conector)}
                            </select>
                        </td>
                        <td style="padding:10px;">
                            <span id="autonomia-${auto.id}">${auto.autonomia}</span>
                            <input type="number" id="input-autonomia-${auto.id}" value="${auto.autonomia}" style="display:none; width:80px; padding:5px;">
                        </td>
                        <td style="padding:10px;">
                            <span id="anio-${auto.id}">${auto.anio}</span>
                            <input type="number" id="input-anio-${auto.id}" value="${auto.anio}" style="display:none; width:80px; padding:5px;">
                        </td>
                        <td style="padding:10px; text-align:center;">
                            <button onclick="editarAuto(${auto.id})" id="btn-editar-${auto.id}" style="background:#2196F3; color:#fff; border:none; padding:6px 12px; border-radius:4px; cursor:pointer; margin-right:5px;">Editar</button>
                            <button onclick="guardarAuto(${auto.id})" id="btn-guardar-${auto.id}" style="display:none; background:#4CAF50; color:#fff; border:none; padding:6px 12px; border-radius:4px; cursor:pointer; margin-right:5px;">Guardar</button>
                            <button onclick="cancelarEditarAuto(${auto.id})" id="btn-cancelar-${auto.id}" style="display:none; background:#9E9E9E; color:#fff; border:none; padding:6px 12px; border-radius:4px; cursor:pointer; margin-right:5px;">Cancelar</button>
                            <button onclick="eliminarAuto(${auto.id})" style="background:#f44336; color:#fff; border:none; padding:6px 12px; border-radius:4px; cursor:pointer;">Eliminar</button>
                        </td>
                    </tr>`;
                });

                html += '</tbody></table>';
                container.innerHTML = html;
            })
            .catch(err => {
                console.error('Error al cargar autos:', err);
                document.getElementById('listaAutos').innerHTML = '<p style="color:#f44336;">Error al cargar los autos.</p>';
            });
    }

    // Función para editar auto
    window.editarAuto = function(id) {
        document.getElementById(`modelo-${id}`).style.display = 'none';
        document.getElementById(`marca-${id}`).style.display = 'none';
        document.getElementById(`conector-${id}`).style.display = 'none';
        document.getElementById(`autonomia-${id}`).style.display = 'none';
        document.getElementById(`anio-${id}`).style.display = 'none';

        document.getElementById(`input-modelo-${id}`).style.display = 'inline-block';
        document.getElementById(`input-marca-${id}`).style.display = 'inline-block';
        document.getElementById(`input-conector-${id}`).style.display = 'inline-block';
        document.getElementById(`input-autonomia-${id}`).style.display = 'inline-block';
        document.getElementById(`input-anio-${id}`).style.display = 'inline-block';

        document.getElementById(`btn-editar-${id}`).style.display = 'none';
        document.getElementById(`btn-guardar-${id}`).style.display = 'inline-block';
        document.getElementById(`btn-cancelar-${id}`).style.display = 'inline-block';
    }

    // Función para cancelar edición
    window.cancelarEditarAuto = function(id) {
        listarAutos();
    }

    // Función para guardar auto
    window.guardarAuto = function(id) {
        const modelo = document.getElementById(`input-modelo-${id}`).value;
        const marca = document.getElementById(`input-marca-${id}`).value;
        const conector = document.getElementById(`input-conector-${id}`).value;
        const autonomia = document.getElementById(`input-autonomia-${id}`).value;
        const anio = document.getElementById(`input-anio-${id}`).value;

        fetch('../api/admin.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                accion: 'editar_auto',
                id: id,
                modelo: modelo,
                marca: marca,
                conector: conector,
                autonomia: autonomia,
                anio: anio
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito) {
                mostrarMensaje('Auto actualizado correctamente', 'exito');
                listarAutos();
            } else {
                mostrarMensaje('Error al actualizar: ' + (data.mensaje || data.error), 'error');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            mostrarMensaje('Error de conexión', 'error');
        });
    }

    // Función para eliminar auto
    window.eliminarAuto = function(id) {
        if (!confirm('¿Está seguro de eliminar este auto?')) return;

        fetch('../api/admin.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                accion: 'eliminar_auto',
                id: id
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito) {
                mostrarMensaje('Auto eliminado correctamente', 'exito');
                listarAutos();
            } else {
                mostrarMensaje('Error al eliminar: ' + (data.mensaje || data.error), 'error');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            mostrarMensaje('Error de conexión', 'error');
        });
    }

    // Cargar autos cuando se abre la pestaña
    document.querySelectorAll('.sidebar-item').forEach(item => {
        item.addEventListener('click', function() {
            const tab = this.dataset.tab;
            if (tab === 'autos') {
                listarAutos();
                cargarUsuariosParaAutos();
            }
        });
    });

    // Función para cargar usuarios en el selector
    function cargarUsuariosParaAutos() {
        fetch("../api/admin.php?listar_usuarios=1")
            .then(res => res.json())
            .then(usuarios => {
                const select = document.getElementById('nuevoAutoUsuario');
                select.innerHTML = '<option value="">Seleccionar Usuario</option>';
                // Filtrar solo usuarios de tipo "cliente"
                const clientes = usuarios.filter(usuario => usuario.tipo_usuario === 'cliente');
                clientes.forEach(usuario => {
                    const option = document.createElement('option');
                    option.value = usuario.usuario;
                    option.textContent = usuario.usuario;
                    select.appendChild(option);
                });
            })
            .catch(err => console.error('Error al cargar usuarios:', err));
    }

    // Manejar formulario de agregar auto
    document.getElementById('formAgregarAuto').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const usuario = document.getElementById('nuevoAutoUsuario').value;
        const modelo = document.getElementById('nuevoAutoModelo').value;
        const marca = document.getElementById('nuevoAutoMarca').value;
        const conector = document.getElementById('nuevoAutoConector').value;
        const autonomia = document.getElementById('nuevoAutoAutonomia').value;
        const anio = document.getElementById('nuevoAutoAnio').value;

        fetch('../api/admin.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                accion: 'agregar_auto',
                usuario: usuario,
                modelo: modelo,
                marca: marca,
                conector: conector,
                autonomia: autonomia,
                anio: anio
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito) {
                mostrarMensaje('Auto agregado correctamente', 'exito');
                // Limpiar formulario
                document.getElementById('formAgregarAuto').reset();
                // Recargar lista
                listarAutos();
            } else {
                mostrarMensaje('Error al agregar: ' + (data.mensaje || data.error), 'error');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            mostrarMensaje('Error de conexión', 'error');
        });
    });

    // Cargar autos cuando se abre la pestaña
    document.querySelectorAll('.sidebar-item').forEach(item => {
        item.addEventListener('click', function() {
            const tab = this.dataset.tab;
            if (tab === 'autos') {
                listarAutos();
            }
        });
    });

    // Cambiar orden desde el selector
    (function(){
        const sel = document.getElementById('ordenAutos');
        if (sel) {
            sel.addEventListener('change', () => listarAutos(sel.value));
        }
    })();

    // Botón para cerrar sesión (si existiera)
    const btnCerrar = document.getElementById("btn-cerrar-sesion");
    if (btnCerrar) {
        btnCerrar.onclick = function () { window.location.href = "logout.php"; };
    }
    </script>
</body>
</html>