<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['tipo_usuario'] !== 'cliente') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/cliente.css">
        <!-- Leaflet CSS para mapa sin clave -->
        <link
            rel="stylesheet"
            href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
            crossorigin=""
        />
</head>

<body>
    <!-- Sidebar -->

    <nav class="sidebar">
        <div class="sidebar-logo">
            <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Logo" />
        </div>
        <ul class="sidebar-menu">
            <li class="sidebar-item active" data-tab="autos">
                <span class="sidebar-icon">🚗</span>
                <span class="sidebar-text">Autos</span>
            </li>
            <li class="sidebar-item" data-tab="viajes">
                <span class="sidebar-icon">🗺️</span>
                <span class="sidebar-text">Viajes</span>
            </li>
            <li class="sidebar-item" data-tab="historial">
                <span class="sidebar-icon">📋</span>
                <span class="sidebar-text">Historial</span>
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
                <a href="#" id="editarPerfil">Editar perfil</a>
                <a href="logout.php">Cerrar sesión</a>
            </div>
        </div>
        <!-- Modal para editar perfil -->
        <div id="modalEditarPerfil" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Editar Perfil</h2>
                <form id="formEditarPerfil">
                    <label for="editUsuario">Nombre de Usuario:</label>
                    <input type="text" id="editUsuario" name="editUsuario" value="<?php echo htmlspecialchars($_SESSION['usuario']); ?>" required>

                    <label for="editCorreo">Correo Electrónico:</label>
                    <input type="email" id="editCorreo" name="editCorreo" value="<?php echo htmlspecialchars($_SESSION['correo'] ?? ''); ?>" required>

                    <label for="editPassword">Nueva Contraseña (dejar en blanco para mantener la actual):</label>
                    <input type="password" id="editPassword" name="editPassword" placeholder="Nueva contraseña">

                    <button type="submit" style="margin-top:24px;">Guardar Cambios</button>
                </form>
                <div id="mensajePerfil" style="margin-top:20px;"></div>
            </div>
        </div>

        <!-- Pestaña Autos -->
        <div id="tab-autos" class="tab-content">
            <h1>Mis Autos</h1>
            <p style="color: #b2ebf2; text-shadow: 1px 1px 4px #222;">Gestiona tu flota de vehículos eléctricos</p>

            <!-- Formulario para agregar un auto -->
            <h2 style="margin-top:40px;">Agregar Auto</h2>
            <form id="formAuto" style="margin-bottom:40px; display: flex; flex-direction: column; gap: 0;">
                <label for="marca">Marca:</label>
                <input type="text" id="marca" name="marca" required>

                <label for="modelo">Modelo:</label>
                <input type="text" id="modelo" name="modelo" required>

                <label for="conector">Tipo de conector:</label>
                <select id="conector" name="conector" required>
                    <option value="">Seleccionar tipo</option>
                    <option value="Tipo 1">Tipo 1 (SAE J1772)</option>
                    <option value="Tipo 2">Tipo 2 (Mennekes)</option>
                    <option value="CCS Combo 1">CCS Combo 1</option>
                    <option value="CCS Combo 2">CCS Combo 2</option>
                    <option value="CHAdeMO">CHAdeMO</option>
                    <option value="Tesla (NACS)">Tesla (NACS)</option>
                    <option value="GB/T">GB/T</option>
                </select>

                <label for="autonomia">Autonomía (km):</label>
                <input type="number" id="autonomia" name="autonomia" min="0" required>

                <label for="anio">Año de Fabricación:</label>
                <input type="number" id="anio" name="anio" min="1900" max="2099" required>

                <button type="submit" style="margin-top:24px;">Agregar Auto</button>
            </form>

            <!-- Listado de autos -->
            <h2 style="margin-top:40px;">Listado de Autos</h2>
            <div id="listado_autos"></div>
            <!-- Mensaje de éxito/error -->
            <div id="mensaje" style="margin-top:20px;"></div>
        </div>

        <!-- Pestaña Viajes -->
        <div id="tab-viajes" class="tab-content" style="display: none;">
            <h1>Planificación de Viajes</h1>
            <p style="color: #b2ebf2; text-shadow: 1px 1px 4px #222;">Ingresá origen y destino, elegí tu auto y te sugerimos paradas cercanas a la ruta</p>

            <!-- Eliminado planificador superior, solo quick-bar -->

            <!-- Filtros de estaciones (ahora abajo, antes de la tabla) -->

            <!-- Mapa y estaciones en ruta -->
            <!-- Barra rápida similar a la referencia: origen rápido / usar ubicación / destino / planificar -->
            <div class="quick-bar">
                <div class="qb-left">
                    <input type="text" id="q_origen" placeholder="Origen (ej: Av. 18 de Julio 1000, Montevideo)" />
                    <button id="btnMiUbic" class="btn-ghost">📍 Usar mi ubicación</button>
                </div>
                <div class="qb-right">
                    <input type="text" id="q_destino" placeholder="Destino (ej: Pando, Canelones)" />
                    <button id="btnPlanificarQuick" class="btn-primary">Planificar ruta</button>
                </div>
            </div>
            <!-- Selector visible de auto debajo del título/quick-bar -->
            <div id="autoPicker" style="margin:10px 0 10px 0;display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                <label for="autoSelector" style="min-width:180px;color:#b2ebf2;">Seleccioná un auto</label>
                <select id="autoSelector" style="min-width:260px;padding:8px 10px;border-radius:8px;border:1px solid #334; background:#0f1a21; color:#e0f7fa;"></select>
                <div id="autoResumen" style="font-size:0.9em;color:#b2ebf2;"></div>
            </div>
            <!-- Inputs ocultos internos -->
            <input type="hidden" id="origen" />
            <input type="hidden" id="destino" />

            <div id="mapaRuta" style="height:380px;border-radius:12px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.35);margin-bottom:24px;"></div>
            <h2 style="margin-top:0;">Estaciones disponibles</h2>
            <!-- Filtros abajo -->
            <div id="filtrosEstaciones" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:12px;align-items:end;margin:24px 0 12px 0;">
                <div>
                    <label for="filtroTipo">Tipo</label>
                    <select id="filtroTipo">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div>
                    <label for="filtroEstado">Estado</label>
                    <select id="filtroEstado">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div>
                    <label for="filtroConector">Conector</label>
                    <select id="filtroConector">
                        <option value="">Todos</option>
                        <option>Tipo 1</option>
                        <option>Tipo 2</option>
                        <option>CCS Combo 1</option>
                        <option>CCS Combo 2</option>
                        <option>CHAdeMO</option>
                        <option>Tesla (NACS)</option>
                        <option>GB/T</option>
                    </select>
                </div>
                <div style="display:flex;gap:8px;align-items:center;">
                    <input type="checkbox" id="filtroCompatibleAuto" />
                    <label for="filtroCompatibleAuto" style="margin:0;">Solo compatibles con mi auto</label>
                </div>
            </div>
            <div id="panelEstaciones"></div>

            <h2 style="margin-top:32px;">Mis reservas</h2>
            <table id="tablaReservas" style="width:100%;margin-bottom:40px;">
                <thead>
                    <tr>
                        <th>Estación</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <!-- Pestaña Historial -->
        <div id="tab-historial" class="tab-content" style="display: none;">
            <h1>Historial de Cargas y Viajes</h1>
            <p style="color: #b2ebf2; text-shadow: 1px 1px 4px #222;">Consultá tus reservas y viajes anteriores</p>

            <!-- TODO FUTURO: Implementar tabla 'viajes' con campos:
                 - id, usuario, auto_id, origen, destino, distancia_km, fecha_hora, estaciones_usadas (JSON)
                 - Permitir guardar un viaje cuando el usuario confirma al menos una reserva en una ruta planificada
                 - Mostrar listado con filtros por fecha, auto, etc.
                 - Opción de eliminar registros de viajes
            -->

            <h2>Historial de cargas (Reservas completadas y pasadas)</h2>
            <table id="tablaHistorialReservas" style="width:100%;margin-bottom:40px;">
                <thead>
                    <tr>
                        <th>Estación</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <p style="color:#b2ebf2;font-size:0.9em;">
                💡 <strong>Próximamente:</strong> Podrás ver un historial completo de viajes planificados con estaciones utilizadas, consumos estimados y más detalles.
            </p>
        </div>
    </div>

    <!-- Modal de Reserva (selección con calendario y hora) -->
    <div id="modalReserva" class="modal">
        <div class="modal-content">
            <span class="close close-reserva">&times;</span>
            <h2>Reservar estación</h2>
            <form id="formReserva">
                <input type="hidden" id="reservaCargadorId" />

                <label for="reservaFecha">Fecha:</label>
                <input type="date" id="reservaFecha" required />

                <label for="reservaHoraInicio">Hora de inicio:</label>
                <input type="time" id="reservaHoraInicio" required />

                <label for="reservaDuracion">Duración (minutos):</label>
                <input type="number" id="reservaDuracion" min="15" step="15" value="60" required />

                <button type="submit" style="margin-top: 20px;">Confirmar reserva</button>
            </form>
        </div>
    </div>

    <!-- Modal Detalle Estación -->
    <div id="modalEstacion" class="modal">
        <div class="modal-content">
            <span class="close close-estacion">&times;</span>
            <h2 id="detNombre">Estación</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label>Latitud</label>
                    <div id="detLat">-</div>
                </div>
                <div>
                    <label>Longitud</label>
                    <div id="detLon">-</div>
                </div>
                <div>
                    <label>Tipo de cargador</label>
                    <div id="detTipo">-</div>
                </div>
                <div>
                    <label>Estado</label>
                    <div id="detEstado">-</div>
                </div>
                <div style="grid-column:1 / -1;">
                    <label>Descripción</label>
                    <div id="detDesc">-</div>
                </div>
            </div>
            <div style="margin-top:16px;display:flex;gap:8px;justify-content:flex-end;">
                <button id="btnReservarDesdeDetalle" type="button">Reservar aquí</button>
            </div>
        </div>
    </div>

    <script>
        // Sidebar -> cambiar pestañas
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
                }
            });
        });

        // Protección extra: Si el usuario no está logueado, redirige (por si el navegador muestra caché)
        // Esto fuerza recarga y chequeo de sesión en el servidor
        if (!window.navigator.cookieEnabled) {
            alert('Las cookies están deshabilitadas. No se puede mantener la sesión.');
            window.location.href = 'index.php';
        }
        // Si el usuario vuelve con el botón atrás, fuerza recarga para validar sesión
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
        // Función para mostrar mensaje
        function mostrarMensaje(texto, tipo) {
            const mensajeDiv = document.getElementById('mensaje');
            mensajeDiv.innerHTML = texto;
            mensajeDiv.className = 'mensaje ' + tipo;
            setTimeout(() => {
                mensajeDiv.innerHTML = '';
                mensajeDiv.className = '';
            }, 3000);
        }

        // Función para cargar el listado de autos usando la API (ejemplo de integración API)
        function cargarListado() {
            // Listar autos del usuario autenticado
            fetch('../api/autos.php')
            .then(res => res.json())
            .then(autos => {
                // Si la API devuelve autos, mostrar tabla, si no, mostrar mensaje
                if (Array.isArray(autos) && autos.length > 0 && autos[0].modelo !== undefined) {
                    let html = "<table><tr><th>ID</th><th>Marca</th><th>Modelo</th><th>Tipo de Conector</th><th>Autonomía (km)</th><th>Año</th><th>Acciones</th></tr>";
                    autos.forEach(auto => {
                        html += `<tr data-id="${auto.id}">
                            <td>${auto.id}</td>
                            <td class="editable" data-campo="marca">${auto.marca}</td>
                            <td class="editable" data-campo="modelo">${auto.modelo}</td>
                            <td class="editable" data-campo="conector">${auto.conector}</td>
                            <td class="editable" data-campo="autonomia">${auto.autonomia}</td>
                            <td class="editable" data-campo="anio">${auto.anio}</td>
                            <td>
                                <button class="btn-editar">Editar</button>
                                <button class="btn-guardar" style="display:none;">Guardar</button>
                                <button class="btn-cancelar" style="display:none;">Cancelar</button>
                                <button class="btn-eliminar" style="background:#e57373;color:#fff;margin-left:5px;">Eliminar</button>
                            </td>
                        </tr>`;
                    });
                    html += "</table>";
                    document.getElementById('listado_autos').innerHTML = html;
                } else {
                    document.getElementById('listado_autos').innerHTML = '<p>No hay autos registrados.</p>';
                }
            });
        }

        // Evento para el formulario de autos (agregar) usando la API
        document.getElementById('formAuto').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            // Usamos la API real de autos
            fetch('../api/autos.php', {
                method: 'POST',
                body: new URLSearchParams({
                    accion: 'agregar',
                    modelo: formData.get('modelo'),
                    marca: formData.get('marca'),
                    conector: formData.get('conector'),
                    autonomia: formData.get('autonomia'),
                    anio: formData.get('anio')
                })
            })
            .then(response => response.json())
            .then(res => {
                if (res.exito) {
                    mostrarMensaje('Auto agregado correctamente.', 'exito');
                    document.getElementById('formAuto').reset();
                    cargarListado();
                } else {
                    mostrarMensaje('Error al agregar el auto.', 'error');
                }
            })
            .catch(() => {
                mostrarMensaje('Error de conexión.', 'error');
            });
        });

        // Evento para editar y eliminar autos usando la API (ejemplo, requiere endpoint en la API)
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-editar')) {
                const tr = e.target.closest('tr');
                tr.querySelectorAll('.editable').forEach(td => {
                    const valor = td.textContent;
                    const campo = td.dataset.campo;
                    
                    // Si es el campo conector, mostrar un select con las opciones
                    if (campo === 'conector') {
                        td.innerHTML = `<select style="width:90%;">
                            <option value="Tipo 1" ${valor === 'Tipo 1' ? 'selected' : ''}>Tipo 1 (SAE J1772)</option>
                            <option value="Tipo 2" ${valor === 'Tipo 2' ? 'selected' : ''}>Tipo 2 (Mennekes)</option>
                            <option value="CCS Combo 1" ${valor === 'CCS Combo 1' ? 'selected' : ''}>CCS Combo 1</option>
                            <option value="CCS Combo 2" ${valor === 'CCS Combo 2' ? 'selected' : ''}>CCS Combo 2</option>
                            <option value="CHAdeMO" ${valor === 'CHAdeMO' ? 'selected' : ''}>CHAdeMO</option>
                            <option value="Tesla (NACS)" ${valor === 'Tesla (NACS)' ? 'selected' : ''}>Tesla (NACS)</option>
                            <option value="GB/T" ${valor === 'GB/T' ? 'selected' : ''}>GB/T</option>
                        </select>`;
                    } else {
                        td.innerHTML = `<input type="text" value="${valor}" style="width:90%;">`;
                    }
                });
                tr.querySelector('.btn-editar').style.display = 'none';
                tr.querySelector('.btn-guardar').style.display = '';
                tr.querySelector('.btn-cancelar').style.display = '';
            }
            if (e.target.classList.contains('btn-cancelar')) {
                cargarListado();
            }
            if (e.target.classList.contains('btn-guardar')) {
                const tr = e.target.closest('tr');
                const id = tr.getAttribute('data-id');
                const editables = tr.querySelectorAll('.editable');
                const datos = {
                    accion: 'editar',
                    id: id,
                    marca: editables[0].querySelector('input, select').value,
                    modelo: editables[1].querySelector('input, select').value,
                    conector: editables[2].querySelector('input, select').value,
                    autonomia: editables[3].querySelector('input, select').value,
                    anio: editables[4].querySelector('input, select').value
                };
                fetch('../api/autos.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(datos)
                })
                .then(res => res.json())
                .then(res => {
                    if(res.exito){
                        mostrarMensaje('Auto actualizado correctamente.', 'exito');
                        cargarListado();
                    } else {
                        mostrarMensaje('Error al actualizar.', 'error');
                    }
                });
            }
            if (e.target.classList.contains('btn-eliminar')) {
                const tr = e.target.closest('tr');
                const id = tr.getAttribute('data-id');
                fetch('../api/autos.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ accion: 'eliminar', id: id })
                })
                .then(res => res.json())
                .then(res => {
                    if(res.exito){
                        mostrarMensaje('Auto eliminado correctamente.', 'exito');
                        cargarListado();
                    } else {
                        mostrarMensaje('Error al eliminar.', 'error');
                    }
                });
            }
        });

        // --- VIAJES (deshabilitado el guardado/listado de viajes) ---

        // Cargar listado al iniciar
        window.onload = cargarListado;

        // Planificación de viajes deshabilitada: no se carga selector de auto ni ruta

        document.querySelector('.usuario-trigger').addEventListener('click', function(e) {
    e.stopPropagation();
    document.querySelector('.usuario-menu').classList.toggle('activo');
});
document.addEventListener('click', function() {
    document.querySelector('.usuario-menu').classList.remove('activo');
});

// Modal para editar perfil
const modal = document.getElementById('modalEditarPerfil');
const closeModal = document.querySelector('.close');

document.getElementById('editarPerfil').addEventListener('click', function(e) {
    e.preventDefault();
    modal.style.display = 'block';
    document.querySelector('.usuario-menu').classList.remove('activo');
});

closeModal.addEventListener('click', function() {
    modal.style.display = 'none';
});

window.addEventListener('click', function(e) {
    if (e.target == modal) {
        modal.style.display = 'none';
    }
});

// Formulario de editar perfil
document.getElementById('formEditarPerfil').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData();
    formData.append('modificar_perfil', '1');
    formData.append('nuevoNombre', document.getElementById('editUsuario').value);
    formData.append('nuevoCorreo', document.getElementById('editCorreo').value);
    const password = document.getElementById('editPassword').value;
    if (password) {
        formData.append('nuevaPassword', password);
    }

    fetch('../api/cliente.php', {
        method: 'POST',
        body: formData
    })
    .then(res => {
        if (!res.ok) {
            throw new Error('Error HTTP: ' + res.status);
        }
        return res.json();
    })
    .then(data => {
        console.log('Respuesta del servidor:', data);
        const mensajeDiv = document.getElementById('mensajePerfil');
        if (data.success) {
            mensajeDiv.innerHTML = data.mensaje;
            mensajeDiv.className = 'mensaje exito';
            setTimeout(() => {
                modal.style.display = 'none';
                // Actualizar el saludo con el nuevo nombre
                document.querySelector('.saludo').textContent = 'Hola, ' + document.getElementById('editUsuario').value;
                mensajeDiv.innerHTML = '';
                mensajeDiv.className = '';
                // Limpiar el campo de contraseña
                document.getElementById('editPassword').value = '';
            }, 2000);
        } else {
            mensajeDiv.innerHTML = data.mensaje || 'Error al actualizar el perfil';
            mensajeDiv.className = 'mensaje error';
        }
    })
    .catch((error) => {
        console.error('Error:', error);
        const mensajeDiv = document.getElementById('mensajePerfil');
        mensajeDiv.innerHTML = 'Error de conexión: ' + error.message;
        mensajeDiv.className = 'mensaje error';
    });
});

// --------- Mapa y planificación con Leaflet + OSRM ---------
let mapa, capaRuta, capaParadas, capaEstaciones, estaciones = [];
let overrideOriginCoords = null; // si el usuario usa geolocalización para origen
let estacionSeleccionadaParaReserva = null; // estación desde marcador que pidió reservar
let currentLocation = null; // {lat, lon}
let rutaCoordsLatLng = []; // Array de [lat, lon] de la ruta actual
let rutaTotalKm = 0;
let refrescoEstadosTimer = null; // auto-refresh periódico

// Inicializa mapa cuando se muestra la pestaña viajes por primera vez
let mapaInicializado = false;
document.querySelector('[data-tab="viajes"]').addEventListener('click', () => {
    if (!mapaInicializado) {
        initMapa();
        cargarCargadores();
        cargarAutosSelector();
        listarReservas();
        mapaInicializado = true;
        // Iniciar auto-refresh de estados cada 30s cuando la pestaña de viajes está activa
        if (!refrescoEstadosTimer) {
            refrescoEstadosTimer = setInterval(() => {
                // Solo refrescar si la pestaña viajes está visible
                const tab = document.getElementById('tab-viajes');
                if (tab && tab.style.display !== 'none') {
                    refrescarEstados();
                }
            }, 30000);
        }
    }
});

// Cargar historial cuando se abre la pestaña Historial
let historialCargado = false;
document.querySelector('[data-tab="historial"]')?.addEventListener('click', () => {
    if (!historialCargado) {
        cargarHistorialReservas();
        historialCargado = true;
    }
});

function initMapa() {
    console.log('🗺️ Inicializando mapa Leaflet...');
    mapa = L.map('mapaRuta');
    console.log('✅ Mapa creado:', mapa);
    const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(mapa);
    console.log('✅ Tile layer agregado');
    mapa.setView([-34.7, -55.95], 12);
    console.log('✅ Vista centrada en Montevideo');
    capaRuta = L.layerGroup().addTo(mapa);
    console.log('✅ Capa de ruta creada');
    capaParadas = L.layerGroup().addTo(mapa);
    console.log('✅ Capa de paradas creada');
    capaEstaciones = L.layerGroup().addTo(mapa);
    console.log('✅ Capa de estaciones creada');
    console.log('🎉 Mapa inicializado completamente');
}

function cargarCargadores() {
    console.log('🔄 Cargando cargadores desde API...');
    fetch('../api/cargadores.php')
      .then(r => {
          console.log('📡 Respuesta recibida, status:', r.status);
          if (!r.ok) {
              throw new Error('HTTP error! status: ' + r.status);
          }
          return r.json();
      })
      .then(data => {
          console.log('📊 Datos recibidos:', data);
          console.log('📊 Total de cargadores:', Array.isArray(data) ? data.length : 'no es array');
          estaciones = Array.isArray(data) ? data : [];
          pintarEstaciones(estaciones);
          // Mostrar listado completo sin ruta
          renderPanelEstaciones(estaciones, []);
          // Poblar filtros dinámicamente
          poblarFiltros(estaciones);
      })
      .catch(error => {
          console.error('❌ Error al cargar cargadores:', error);
          alert('Error al cargar las estaciones de carga: ' + error.message);
      });
}

function pintarEstaciones(lista) {
    console.log('🎨 Pintando estaciones en mapa, total:', lista.length);
    capaEstaciones.clearLayers();
    lista.forEach(c => {
        console.log('  📍 Agregando marcador:', c.nombre, c.latitud, c.longitud);
        const estado = String(c.estado || '').toLowerCase();
        const estadoClass = estado ? ('pin-' + estado.replace(/\s+/g,'-')) : 'pin-disponible';
        const iconHtml = `<div class="marker-pin ${estadoClass}"></div>`;
        const icon = L.divIcon({
            className: 'custom-div-icon',
            html: iconHtml,
            iconSize: [30, 42],
            iconAnchor: [15, 42],
            popupAnchor: [0, -36]
        });
        const m = L.marker([c.latitud, c.longitud], {icon}).addTo(capaEstaciones);
        // Info breve tipo ejemplo: nombre, dirección, conectores, potencia, estado
        let info = `<b>${c.nombre || '-'}</b>`;
        if (c.direccion) info += `<br/><span style='color:#555;'>${c.direccion}</span>`;
        else if (c.descripcion) info += `<br/><span style='color:#555;'>${c.descripcion}</span>`;
        info += `<br/>`;
        if (c.conectores || c.conector) {
            info += `<span style='font-size:0.98em;'>🔌 <b>Conectores:</b> ${c.conectores ? c.conectores : c.conector}</span><br/>`;
        }
        if (c.potencia) {
            info += `<span style='font-size:0.98em;'>⚡ <b>Potencia:</b> ${c.potencia} kW</span><br/>`;
        }
        info += `<span class='estado-badge ${estado ? ('estado-' + estado.replace(/\s+/g,'-')) : ''}'>${c.estado || '-'}</span>`;
        info += `<br/><div style='margin-top:8px;display:flex;gap:8px;'><button onclick=\"planificarParaEstacion(${c.id})\" style='padding:6px 16px;background:#1976d2;color:#fff;border:none;border-radius:6px;font-weight:600;'>Reservar</button>`;
        info += `<button onclick=\"abrirDetalleEstacion(${c.id})\" style='padding:6px 16px;background:#e0f7fa;color:#1976d2;border:none;border-radius:6px;font-weight:600;'>Ver</button></div>`;
        m.bindPopup(info);
    });
}

// Refrescar estados desde el backend sin tocar la ruta ni filtros del usuario
function refrescarEstados() {
    fetch('../api/cargadores.php')
      .then(r => r.json())
      .then(data => {
          estaciones = Array.isArray(data) ? data : [];
          // Si hay ruta trazada, mantenemos el contexto de ruta y filtros actuales
          if (rutaCoordsLatLng && rutaCoordsLatLng.length) {
              const bufferKm = parseFloat(document.getElementById('bufferKm')?.value || '5');
              const cercanasBase = estaciones.filter(e => {
                  const p = {lat: parseFloat(e.latitud), lon: parseFloat(e.longitud)};
                  const d = distancePointToRouteKm(p, rutaCoordsLatLng);
                  return d <= bufferKm;
              });
              const cercanas = aplicarFiltros(cercanasBase);
              pintarEstaciones(cercanas);
              renderPanelEstaciones(cercanas, []);
          } else {
              // Sin ruta: mostrar todo con filtros actuales
              const filtradas = aplicarFiltros(estaciones);
              pintarEstaciones(filtradas);
              renderPanelEstaciones(filtradas, []);
          }
      })
      .catch(err => console.warn('Refresh estados falló:', err));
}

// Poblar selects de filtro con valores únicos
function poblarFiltros(lista) {
    const tipos = new Set();
    const estados = new Set();
    lista.forEach(c => {
        if (c.tipo) tipos.add(String(c.tipo));
        if (c.estado) estados.add(String(c.estado));
    });
    const selTipo = document.getElementById('filtroTipo');
    const selEstado = document.getElementById('filtroEstado');
    if (selTipo && selTipo.options.length === 1) {
        [...tipos].sort().forEach(t => {
            const opt = document.createElement('option');
            opt.value = t; opt.textContent = t; selTipo.appendChild(opt);
        });
    }
    if (selEstado && selEstado.options.length === 1) {
        [...estados].sort().forEach(t => {
            const opt = document.createElement('option');
            opt.value = t; opt.textContent = t; selEstado.appendChild(opt);
        });
    }
}

// Aplicar filtros sobre una lista de estaciones
function aplicarFiltros(lista) {
    const tipo = document.getElementById('filtroTipo')?.value || '';
    const estado = document.getElementById('filtroEstado')?.value || '';
    const conector = document.getElementById('filtroConector')?.value || '';
    const soloCompatibles = document.getElementById('filtroCompatibleAuto')?.checked || false;
    let conectorAuto = '';
    const autoSel = document.getElementById('autoSelector');
    if (soloCompatibles && autoSel && autoSel.value) {
        conectorAuto = autoSel.selectedOptions[0]?.dataset?.conector || '';
    }

    const matchConectores = (c, buscar) => {
        if (!buscar) return true;
        const raw = c.conectores ?? c.conector ?? '';
        if (Array.isArray(raw)) return raw.some(x => String(x).toLowerCase() === buscar.toLowerCase());
        const txt = String(raw);
        return txt.toLowerCase().split(/[,;\|]/).map(s => s.trim()).includes(buscar.toLowerCase());
    };

    const matchConectorAuto = (c) => {
        if (!soloCompatibles || !conectorAuto) return true;
        return matchConectores(c, conectorAuto);
    };

    return (lista || []).filter(c =>
        (!tipo || String(c.tipo) === tipo) &&
        (!estado || String(c.estado) === estado) &&
        matchConectores(c, conector) &&
        matchConectorAuto(c)
    );
}

// Cargar autos del usuario en el selector del planificador
function cargarAutosSelector() {
    const sel = document.getElementById('autoSelector');
    if (!sel) return;
    sel.innerHTML = '<option value="">Cargando autos…</option>';
    fetch('../api/autos.php')
      .then(r => r.json())
      .then(autos => {
          if (!Array.isArray(autos) || autos.length === 0) {
              sel.innerHTML = '<option value="">No tenés autos. Agregá uno en la pestaña Autos</option>';
              document.getElementById('autoResumen').textContent = '⚠️ No tenés autos cargados. Agregá uno para poder planificar.';
              return;
          }
          sel.innerHTML = '<option value="">Seleccioná un auto…</option>';
          autos.forEach(a => {
              const opt = document.createElement('option');
              opt.value = a.id;
              opt.textContent = `${a.marca} ${a.modelo} (${a.anio})`;
              opt.dataset.autonomia = a.autonomia || 0;
              opt.dataset.conector = a.conector || '';
              sel.appendChild(opt);
          });
          // Selección automática si solo hay uno
          if (sel.options.length === 2) sel.selectedIndex = 1;
          if (sel.value) {
              const o = sel.selectedOptions[0];
              document.getElementById('autoResumen').textContent = `🚗 Usando auto: ${o.textContent} | Autonomía: ${o.dataset.autonomia} km`;
          } else {
              document.getElementById('autoResumen').textContent = 'Seleccioná un auto (se usará el primero si no elegís)';
          }
      })
      .catch(() => {
          sel.innerHTML = '<option value="">Error al cargar autos</option>';
          document.getElementById('autoResumen').textContent = '❌ Error al cargar autos.';
      });
}

// Abrir modal de reserva con calendario/hora
function abrirReserva(id) {
    document.getElementById('reservaCargadorId').value = id;
    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const d = String(now.getDate()).padStart(2, '0');
    const h = String(now.getHours()).padStart(2, '0');
    // Redondear a múltiplos de 15
    const mins = now.getMinutes();
    const rounded = Math.ceil(mins / 15) * 15;
    const minStr = String(rounded === 60 ? 0 : rounded).padStart(2, '0');
    const hourStr = String(rounded === 60 ? (now.getHours() + 1) % 24 : now.getHours()).padStart(2, '0');
    document.getElementById('reservaFecha').value = `${y}-${m}-${d}`;
    document.getElementById('reservaHoraInicio').value = `${hourStr}:${minStr}`;
    document.getElementById('reservaDuracion').value = 60;
    document.getElementById('modalReserva').style.display = 'block';
}

// Geocodificar con Nominatim a través de nuestro proxy PHP
function geocode(q) {
    const url = '../api/geocode.php?q=' + encodeURIComponent(q);
    return fetch(url)
      .then(r => {
          if (!r.ok) throw new Error('Error en la geocodificación: ' + r.status);
          return r.json();
      })
      .then(arr => {
          if (!arr || arr.error) throw new Error(arr.error || 'Error de geocodificación');
          if (!arr.length) throw new Error('No se encontró la ubicación: "' + q + '"');
          return {lat: parseFloat(arr[0].lat), lon: parseFloat(arr[0].lon)};
      })
      .catch(err => {
          console.error('Error geocode:', err);
          throw new Error('No se pudo geocodificar "' + q + '". Probá con una dirección más específica (ej: "Montevideo, Uruguay").');
      });
}

function haversineKm(a, b) {
    const R = 6371;
    const dLat = (b.lat - a.lat) * Math.PI / 180;
    const dLon = (b.lon - a.lon) * Math.PI / 180;
    const lat1 = a.lat * Math.PI / 180;
    const lat2 = b.lat * Math.PI / 180;
    const x = Math.sin(dLat/2)**2 + Math.cos(lat1)*Math.cos(lat2)*Math.sin(dLon/2)**2;
    return 2 * R * Math.asin(Math.sqrt(x));
}

function distancePointToSegmentKm(p, a, b) {
    // p {lat, lon}; a,b {lat, lon}
    // Aproximación: convertir a plano local en metros
    function toXY(c) {
        const R = 6371000;
        const x = (c.lon * Math.PI/180) * R * Math.cos(((a.lat+b.lat)/2) * Math.PI/180);
        const y = (c.lat * Math.PI/180) * R;
        return {x,y};
    }
    const P = toXY(p), A = toXY(a), B = toXY(b);
    const ABx = B.x - A.x, ABy = B.y - A.y;
    const APx = P.x - A.x, APy = P.y - A.y;
    const ab2 = ABx*ABx + ABy*ABy;
    const t = ab2 === 0 ? 0 : Math.max(0, Math.min(1, (APx*ABx + APy*ABy)/ab2));
    const proj = {x: A.x + t*ABx, y: A.y + t*ABy};
    const dx = P.x - proj.x, dy = P.y - proj.y;
    const distM = Math.sqrt(dx*dx + dy*dy);
    return distM / 1000.0;
}

// Distancia mínima de un punto a una polilínea (ruta) en km
function distancePointToRouteKm(p, routeLatLng) {
    if (!routeLatLng || routeLatLng.length < 2) return Infinity;
    let minD = Infinity;
    for (let i = 1; i < routeLatLng.length; i++) {
        const a = {lat: routeLatLng[i-1][0], lon: routeLatLng[i-1][1]};
        const b = {lat: routeLatLng[i][0], lon: routeLatLng[i][1]};
        const d = distancePointToSegmentKm(p, a, b);
        if (d < minD) minD = d;
    }
    return minD;
}

// Calcular longitud total de una ruta en km
function longitudRutaKm(routeLatLng) {
    let total = 0;
    for (let i = 1; i < routeLatLng.length; i++) {
        total += haversineKm(
            {lat: routeLatLng[i-1][0], lon: routeLatLng[i-1][1]},
            {lat: routeLatLng[i][0], lon: routeLatLng[i][1]}
        );
    }
    return total;
}

// Punto aproximado en la ruta según fracción [0,1]
function puntoEnRutaPorFraccion(routeLatLng, fraccion) {
    if (!routeLatLng || routeLatLng.length === 0) return null;
    if (fraccion <= 0) return {lat: routeLatLng[0][0], lon: routeLatLng[0][1]};
    const total = longitudRutaKm(routeLatLng);
    const objetivo = fraccion * total;
    let acum = 0;
    for (let i = 1; i < routeLatLng.length; i++) {
        const a = {lat: routeLatLng[i-1][0], lon: routeLatLng[i-1][1]};
        const b = {lat: routeLatLng[i][0], lon: routeLatLng[i][1]};
        const seg = haversineKm(a, b);
        if (acum + seg >= objetivo) {
            const t = (objetivo - acum) / seg;
            return {lat: a.lat + t*(b.lat - a.lat), lon: a.lon + t*(b.lon - a.lon)};
        }
        acum += seg;
    }
    const ult = routeLatLng[routeLatLng.length - 1];
    return {lat: ult[0], lon: ult[1]};
}

// Llamada a OSRM para obtener ruta real
async function obtenerRutaOSRM(orig, dest) {
    const url = `https://router.project-osrm.org/route/v1/driving/${orig.lon},${orig.lat};${dest.lon},${dest.lat}?overview=full&geometries=geojson`;
    const resp = await fetch(url);
    if (!resp.ok) throw new Error('OSRM HTTP ' + resp.status);
    const data = await resp.json();
    if (!data.routes || !data.routes.length) throw new Error('Sin rutas');
    const r = data.routes[0];
    const coords = r.geometry.coordinates.map(([lon, lat]) => [lat, lon]);
    return {
        coords,
        distanciaKm: (r.distance || 0) / 1000,
        duracionSeg: (r.duration || 0)
    };
}

async function trazarRutaYSugerir() {
    console.log('[Planificar] Inicio trazarRutaYSugerir');
    const origenTxt = document.getElementById('origen')?.value.trim();
    const destinoTxt = document.getElementById('destino')?.value.trim();
    const autoSel = document.getElementById('autoSelector');
    if (autoSel && !autoSel.value && autoSel.options.length > 1) {
        // seleccionar primera opción válida automáticamente
        autoSel.selectedIndex = 1;
        console.log('[Planificar] Auto seleccionado automáticamente:', autoSel.selectedOptions[0].textContent);
        const o = autoSel.selectedOptions[0];
        document.getElementById('autoResumen').textContent = `🚗 Usando auto: ${o.textContent} | Autonomía: ${o.dataset.autonomia} km`;
    }
    const autoAutonomia = parseFloat(autoSel?.selectedOptions[0]?.dataset?.autonomia || '0');
    const bufferKm = parseFloat(document.getElementById('bufferKm')?.value || '5');
    console.log('[Planificar] origenTxt:', origenTxt, 'destinoTxt:', destinoTxt, 'autoAutonomia:', autoAutonomia, 'bufferKm:', bufferKm);
    if (!origenTxt || !destinoTxt) { alert('Completá origen y destino'); return; }
    if (!autoAutonomia) { alert('Seleccioná un auto con autonomía'); return; }
    if (isNaN(bufferKm) || bufferKm < 1 || bufferKm > 50) { alert('Ingresá un radio entre 1 y 50 km'); return; }

    try {
        // Soporte para origen por coordenadas (geolocalización rápida)
        let orig;
        if (overrideOriginCoords && typeof overrideOriginCoords.lat === 'number') {
            orig = overrideOriginCoords; // {lat, lon}
            // no hacemos delay en este caso
            overrideOriginCoords = null; // consumir una vez
        } else {
            // Geocodificar con pequeño delay entre peticiones
            orig = await geocode(origenTxt);
            await new Promise(resolve => setTimeout(resolve, 1000)); // 1 segundo de delay
        }
        const dest = await geocode(destinoTxt);
        
        // Obtener ruta real con OSRM (fallback a línea recta si falla)
        let coords = [];
        let totalKm = 0;
        try {
            const ruta = await obtenerRutaOSRM(orig, dest);
            coords = ruta.coords; // [[lat,lon], ...]
            totalKm = ruta.distanciaKm || longitudRutaKm(coords);
        } catch (err) {
            console.warn('OSRM falló, se usa línea recta:', err.message);
            coords = [[orig.lat, orig.lon], [dest.lat, dest.lon]];
            totalKm = haversineKm({lat:orig.lat, lon:orig.lon}, {lat:dest.lat, lon:dest.lon});
        }

        // Guardar ruta global
        rutaCoordsLatLng = coords;
        rutaTotalKm = totalKm;

        // Pintar ruta
        capaRuta.clearLayers();
        const poly = L.polyline(coords, {color:'#00e5ff', weight:5}).addTo(capaRuta);
        mapa.fitBounds(poly.getBounds(), {padding:[30,30]});

        // Filtrar estaciones cercanas a la ruta y aplicar filtros de UI
        const cercanasBase = estaciones.filter(e =>
            distancePointToRouteKm({lat:e.latitud, lon:e.longitud}, coords) <= bufferKm
        );
        const cercanas = aplicarFiltros(cercanasBase);
        pintarEstaciones(cercanas);

        // Sugerir paradas según autonomía a lo largo de la ruta
        const paradasNecesarias = Math.max(0, Math.ceil(totalKm / autoAutonomia) - 1);
        capaParadas.clearLayers();
        const sugeridas = [];
        for (let i=1; i<=paradasNecesarias; i++) {
            const t = i / (paradasNecesarias + 1);
            const p = puntoEnRutaPorFraccion(coords, t);
            if (!p) continue;
            let mejor = null, mejorD = Infinity;
            cercanas.forEach(e => {
                const d = haversineKm({lat:e.latitud, lon:e.longitud}, p);
                if (d < mejorD) { mejorD = d; mejor = e; }
            });
            if (mejor && mejorD <= bufferKm) {
                if (!sugeridas.find(s => s.id === mejor.id)) sugeridas.push(mejor);
                L.circleMarker([mejor.latitud, mejor.longitud], {radius:8, color:'#ffea00'}).addTo(capaParadas)
                  .bindTooltip('Parada sugerida: ' + mejor.nombre);
            } else {
                L.circleMarker([p.lat, p.lon], {radius:6, color:'#ff4081'}).addTo(capaParadas)
                  .bindTooltip('Punto sin estación cercana');
            }
        }

        renderPanelEstaciones(cercanas, sugeridas);
    } catch (e) {
        alert('No se pudo geocodificar: ' + e.message);
    }
}

function renderPanelEstaciones(lista, sugeridas) {
    const sugSet = new Set((sugeridas||[]).map(s => s.id));
    if (!lista.length) {
        document.getElementById('panelEstaciones').innerHTML = '<p>No hay estaciones cercanas a la ruta con el radio seleccionado.</p>';
        return;
    }
    let html = '<table style="width:100%"><thead><tr><th>Estación</th><th>Tipo</th><th>Estado</th><th>Lat</th><th>Lon</th><th>Recomendada</th><th>Acciones</th></tr></thead><tbody>';
    lista.forEach(c => {
        const est = String(c.estado||'').toLowerCase();
        const rowClass = est ? `row-estado-${est.replace(/\s+/g,'-')}` : '';
        const estadoBadge = `<span class="estado-badge ${est ? ('estado-' + est.replace(/\s+/g,'-')) : ''}">${c.estado || '-'}</span>`;
        html += `<tr class="${rowClass}">
            <td>${c.nombre}</td>
            <td>${c.tipo || '-'}</td>
            <td>${estadoBadge}</td>
            <td>${Number(c.latitud).toFixed(5)}</td>
            <td>${Number(c.longitud).toFixed(5)}</td>
            <td>${sugSet.has(c.id) ? 'Sí' : '-'}</td>
            <td><button onclick="abrirDetalleEstacion(${c.id})">Ver</button> <button onclick="abrirReserva(${c.id})">Reservar</button></td>
        </tr>`;
    });
    html += '</tbody></table>';
    document.getElementById('panelEstaciones').innerHTML = html;
}

// Detalle de estación (modal)
let estacionDetalleActual = null;
function abrirDetalleEstacion(id) {
    estacionDetalleActual = (estaciones || []).find(e => String(e.id) === String(id));
    if (!estacionDetalleActual) return;
    document.getElementById('detNombre').textContent = estacionDetalleActual.nombre || 'Estación';
    document.getElementById('detLat').textContent = estacionDetalleActual.latitud ?? '-';
    document.getElementById('detLon').textContent = estacionDetalleActual.longitud ?? '-';
    document.getElementById('detTipo').textContent = estacionDetalleActual.tipo || '-';
    document.getElementById('detEstado').textContent = estacionDetalleActual.estado || '-';
    document.getElementById('detDesc').textContent = estacionDetalleActual.descripcion || '-';
    document.getElementById('modalEstacion').style.display = 'block';
}


function listarReservas() {
    fetch('../api/reservas.php?accion=listar_usuario')
      .then(r=>r.json())
      .then(resp => {
          if (!resp || !resp.exito) return;
          const tbody = document.querySelector('#tablaReservas tbody');
          tbody.innerHTML = '';
          resp.reservas.forEach(r => {
              const tr = document.createElement('tr');
              tr.innerHTML = `
                <td>${r.estacion || r.cargador_id}</td>
                <td>${r.inicio}</td>
                <td>${r.fin}</td>
                <td>${r.estado}</td>
                <td>${r.estado !== 'cancelada' ? `<button data-reserva="${r.id}" class="btn-cancelar-reserva">Cancelar</button>` : '-'}</td>
              `;
              tbody.appendChild(tr);
          });
      });
}

// Cargar historial de reservas (todas, incluyendo pasadas y canceladas)
function cargarHistorialReservas() {
    fetch('../api/reservas.php?accion=listar_usuario')
      .then(r=>r.json())
      .then(resp => {
          if (!resp || !resp.exito) return;
          const tbody = document.querySelector('#tablaHistorialReservas tbody');
          tbody.innerHTML = '';
          // Filtrar solo reservas pasadas o finalizadas (inicio < ahora o canceladas)
          const ahora = new Date();
          const pasadas = resp.reservas.filter(r => {
              const inicio = new Date(r.inicio.replace(' ', 'T'));
              return inicio < ahora || r.estado === 'cancelada' || r.estado === 'completada';
          });
          if (pasadas.length === 0) {
              tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">No hay cargas en el historial</td></tr>';
              return;
          }
          pasadas.forEach(r => {
              const tr = document.createElement('tr');
              tr.innerHTML = `
                <td>${r.estacion || r.cargador_id}</td>
                <td>${r.inicio}</td>
                <td>${r.fin}</td>
                <td>${r.estado}</td>
              `;
              tbody.appendChild(tr);
          });
      });
}

document.addEventListener('click', function(e){
    if (e.target.classList.contains('btn-cancelar-reserva')) {
        const id = e.target.getAttribute('data-reserva');
        if (!confirm('¿Cancelar esta reserva?')) return;
        fetch('../api/reservas.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({accion:'cancelar', reserva_id: id})
        }).then(r=>r.json()).then(resp => {
            if (!resp || !resp.exito) { alert('No se pudo cancelar'); return; }
            listarReservas();
            // Refrescar estaciones para liberar el estado si corresponde
            if (typeof cargarCargadores === 'function') cargarCargadores();
        })
    }
});

// Botón de trazar ruta eliminado; el planificador está deshabilitado

// Manejo del modal de reservas
const modalReserva = document.getElementById('modalReserva');
const cerrarReservaBtn = document.querySelector('.close-reserva');
cerrarReservaBtn.addEventListener('click', ()=> modalReserva.style.display = 'none');
window.addEventListener('click', (e)=> { if (e.target === modalReserva) modalReserva.style.display = 'none'; });

// Cierre modal detalle estación
const modalEstacion = document.getElementById('modalEstacion');
const cerrarEstacionBtn = document.querySelector('.close-estacion');
cerrarEstacionBtn.addEventListener('click', ()=> modalEstacion.style.display = 'none');
window.addEventListener('click', (e)=> { if (e.target === modalEstacion) modalEstacion.style.display = 'none'; });

// Botón reservar desde detalle
document.getElementById('btnReservarDesdeDetalle').addEventListener('click', function(){
    if (estacionDetalleActual) {
        abrirReserva(estacionDetalleActual.id);
        modalEstacion.style.display = 'none';
    }
});

document.getElementById('formReserva').addEventListener('submit', function(e){
    e.preventDefault();
    const cargador_id = parseInt(document.getElementById('reservaCargadorId').value, 10);
    const fecha = document.getElementById('reservaFecha').value; // YYYY-MM-DD
    const hora = document.getElementById('reservaHoraInicio').value; // HH:MM
    const dur = parseInt(document.getElementById('reservaDuracion').value || '60', 10);
    if (!cargador_id || !fecha || !hora) return;
    // Construir inicio/fin en formato 'YYYY-MM-DD HH:MM'
    const inicioISO = `${fecha}T${hora}:00`;
    const start = new Date(inicioISO);
    const end = new Date(start.getTime() + dur*60000);
    const pad = (n)=> String(n).padStart(2,'0');
    const inicio = `${start.getFullYear()}-${pad(start.getMonth()+1)}-${pad(start.getDate())} ${pad(start.getHours())}:${pad(start.getMinutes())}`;
    const fin = `${end.getFullYear()}-${pad(end.getMonth()+1)}-${pad(end.getDate())} ${pad(end.getHours())}:${pad(end.getMinutes())}`;

    fetch('../api/reservas.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({accion:'crear', cargador_id, inicio, fin})
    }).then(r=>r.json()).then(resp => {
        alert(resp.mensaje || (resp.exito ? 'Reserva creada' : 'No se pudo crear la reserva'));
        if (resp.exito) { 
            listarReservas(); 
            // Refrescar estaciones para reflejar estado "ocupado" en tiempo real si corresponde
            if (typeof cargarCargadores === 'function') cargarCargadores();
            modalReserva.style.display = 'none'; 
        }
    }).catch(()=> alert('Error de red'));
});

// Buscar ruta botón
// Buscar ruta botón
// Listener antiguo eliminado: solo agregar si existe el botón
const btnBuscarRuta = document.getElementById('btnBuscarRuta');
if (btnBuscarRuta) {
    btnBuscarRuta.addEventListener('click', () => {
        console.log('[UI] Click en btnBuscarRuta');
        trazarRutaYSugerir();
    });
}

// --- Quick-bar: geolocalización y planificación rápida ---
document.getElementById('btnMiUbic')?.addEventListener('click', function(){
    console.log('[Geoloc] Click en usar mi ubicación');
    if (!navigator.geolocation) { alert('Geolocalización no soportada por el navegador'); return; }
    const btn = this;
    btn.disabled = true;
    btn.textContent = '📡 Obteniendo...';
    navigator.geolocation.getCurrentPosition(function(pos){
        const lat = pos.coords.latitude;
        const lon = pos.coords.longitude;
        currentLocation = {lat, lon};
        overrideOriginCoords = {lat, lon};
        document.getElementById('q_origen').value = 'Mi ubicación actual';
        document.getElementById('origen').value = 'Mi ubicación actual';
        console.log('[Geoloc] Coordenadas obtenidas:', currentLocation);
        btn.disabled = false;
        btn.textContent = '📍 Usar mi ubicación';
    }, function(err){
        alert('No se pudo obtener la ubicación: ' + err.message);
        console.warn('[Geoloc] Error:', err);
        btn.disabled = false;
        btn.textContent = '📍 Usar mi ubicación';
    }, { enableHighAccuracy: true, timeout: 10000 });
});

document.getElementById('btnPlanificarQuick')?.addEventListener('click', function(){
    console.log('[QuickBar] Click en Planificar ruta');
    // Copiar valores de la barra rápida a los campos internos
    const qorig = document.getElementById('q_origen')?.value?.trim();
    const qdest = document.getElementById('q_destino')?.value?.trim();
    document.getElementById('origen').value = qorig;
    document.getElementById('destino').value = qdest;
    // Si el usuario escribió manualmente, quitamos overrideOriginCoords
    if (qorig !== 'Mi ubicación actual') overrideOriginCoords = null;

    // Cargar autos si el selector está vacío
    const autoSel = document.getElementById('autoSelector');
    if (autoSel && autoSel.options.length <= 1) {
        fetch('../api/autos.php')
          .then(r => r.json())
          .then(autos => {
            autoSel.innerHTML = '<option value="">Seleccioná un auto…</option>';
            autos.forEach(a => {
                const opt = document.createElement('option');
                opt.value = a.id;
                opt.textContent = `${a.marca} ${a.modelo} (${a.anio})`;
                opt.dataset.autonomia = a.autonomia || 0;
                opt.dataset.conector = a.conector || '';
                autoSel.appendChild(opt);
            });
            if (autoSel.options.length === 2) autoSel.selectedIndex = 1;
            if (!autoSel.value) {
                alert('No tenés autos cargados. Agregá uno en la pestaña Autos.');
                return;
            }
            lanzarPlanificacion();
          });
        return;
    }
    // Seleccionar auto automáticamente si hay solo uno
    if (autoSel && autoSel.options.length === 2) {
        autoSel.selectedIndex = 1;
    }
    if (autoSel && !autoSel.value) {
        alert('Seleccioná un auto para planificar la ruta');
        autoSel.focus();
        return;
    }
    if (!qorig) { alert('Ingresá un origen'); console.warn('[QuickBar] Falta origen'); return; }
    if (!qdest) { alert('Ingresá un destino'); console.warn('[QuickBar] Falta destino'); return; }
    lanzarPlanificacion();
});

function lanzarPlanificacion() {
    // Lanzar la búsqueda (toma overrideOriginCoords si está seteado)
    trazarRutaYSugerir();
}

// Si se hace click en un marcador para "Reservar" la estación, abrimos el planificador modal o quick flow
function planificarParaEstacion(id) {
    // guardar selección para intentar reservar una vez trazada la ruta
    estacionSeleccionadaParaReserva = id;
    // Abrir el modal planificador si existe
    const modalPlan = document.getElementById('modalPlan');
    if (modalPlan) {
        modalPlan.style.display = 'block';
        // preseleccionar el destino en el modal (si el DOM del modal tiene #destino)
        const destField = document.getElementById('destino');
        const est = estaciones.find(e => String(e.id) === String(id));
        if (est && destField) destField.value = `${est.nombre}`;
        return;
    }
    // Si no hay modal, usamos la quick-bar: rellenar destino y abrir la búsqueda
    const est2 = estaciones.find(e => String(e.id) === String(id));
    if (est2) {
        document.getElementById('q_destino').value = est2.nombre || '';
        trazarRutaYSugerir();
    }
}

// Reaplicar filtros cuando cambian
['filtroTipo','filtroEstado','filtroConector','filtroCompatibleAuto','autoSelector'].forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('change', () => {
        if (id === 'autoSelector') {
            const sel = /** @type {HTMLSelectElement} */(el);
            if (sel.value) {
                const o = sel.selectedOptions[0];
                document.getElementById('autoResumen').textContent = `🚗 Usando auto: ${o.textContent} | Autonomía: ${o.dataset.autonomia} km`;
            } else {
                document.getElementById('autoResumen').textContent = 'Seleccioná un auto (se usará el primero si no elegís)';
            }
        }
        if (!rutaCoordsLatLng.length) {
            // Sin ruta: solo repinta y panel con filtros aplicados sobre todas
            const filtradas = aplicarFiltros(estaciones);
            pintarEstaciones(filtradas);
            renderPanelEstaciones(filtradas, []);
            return;
        }
        const bufferKm = parseFloat(document.getElementById('bufferKm')?.value || '5');
        const cercanasBase = estaciones.filter(e =>
            distancePointToRouteKm({lat:e.latitud, lon:e.longitud}, rutaCoordsLatLng) <= bufferKm
        );
        const cercanas = aplicarFiltros(cercanasBase);
        pintarEstaciones(cercanas);
        // No recalculamos sugeridas aquí para mantener número de paradas; se volverán a calcular al buscar ruta
        renderPanelEstaciones(cercanas, []);
    });
});
    </script>
    <!-- Leaflet JS -->
    <script
      src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
      integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
      crossorigin="">
    </script>
</body>

</html>