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

            <!-- Planificador: origen/destino + auto + radio y botón de búsqueda -->
            <div id="planificador" style="display:grid;grid-template-columns:1fr 1fr 1fr 160px 150px;gap:12px;align-items:end;margin-bottom:16px;">
                <div>
                    <label for="origen">Origen</label>
                    <input type="text" id="origen" placeholder="Ej: Av. 18 de Julio 1000, Montevideo" />
                </div>
                <div>
                    <label for="destino">Destino</label>
                    <input type="text" id="destino" placeholder="Ej: Terminal Tres Cruces" />
                </div>
                <div>
                    <label for="autoSelector">Auto</label>
                    <select id="autoSelector">
                        <option value="">Seleccioná un auto…</option>
                    </select>
                </div>
                <div>
                    <label for="bufferKm">Radio a la ruta (km)</label>
                    <input type="number" id="bufferKm" min="1" max="50" step="1" value="5" />
                </div>
                <div style="display:flex;gap:8px;">
                    <button id="btnBuscarRuta" type="button" style="width:100%;height:40px;align-self:center;">Buscar ruta</button>
                </div>
            </div>

            <!-- Mapa y estaciones en ruta -->
            <h2>Estaciones disponibles</h2>
            <div id="mapaRuta" style="height:380px;border-radius:12px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.35);margin-bottom:16px;"></div>
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
                    td.innerHTML = `<input type="text" value="${valor}" style="width:90%;">`;
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
                const inputs = tr.querySelectorAll('input');
                const datos = {
                    accion: 'editar',
                    id: id,
                    marca: inputs[0].value,
                    modelo: inputs[1].value,
                    conector: inputs[2].value,
                    autonomia: inputs[3].value,
                    anio: inputs[4].value
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

// --------- Mapa y planificación simple con Leaflet ---------
let mapa, capaRuta, capaParadas, capaEstaciones, estaciones = [];

// Inicializa mapa cuando se muestra la pestaña viajes por primera vez
let mapaInicializado = false;
document.querySelector('[data-tab="viajes"]').addEventListener('click', () => {
    if (!mapaInicializado) {
        initMapa();
        cargarCargadores();
        cargarAutosSelector();
        listarReservas();
        mapaInicializado = true;
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
    mapa = L.map('mapaRuta');
    const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(mapa);
    mapa.setView([-34.7, -55.95], 12);
    capaRuta = L.layerGroup().addTo(mapa);
    capaParadas = L.layerGroup().addTo(mapa);
    capaEstaciones = L.layerGroup().addTo(mapa);
}

function cargarCargadores() {
    fetch('../api/cargadores.php')
      .then(r => r.json())
      .then(data => {
          estaciones = Array.isArray(data) ? data : [];
          pintarEstaciones(estaciones);
          // Mostrar listado completo sin ruta
          renderPanelEstaciones(estaciones, []);
      });
}

function pintarEstaciones(lista) {
    capaEstaciones.clearLayers();
    lista.forEach(c => {
        const m = L.marker([c.latitud, c.longitud]).addTo(capaEstaciones);
        m.bindPopup(`<b>${c.nombre}</b><br/><button onclick=\"abrirReserva(${c.id})\">Reservar</button> <button onclick=\"abrirDetalleEstacion(${c.id})\">Ver</button>`);
    });
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
      })
      .catch(() => {
          sel.innerHTML = '<option value="">Error al cargar autos</option>';
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

async function trazarRutaYSugerir() {
    const origenTxt = document.getElementById('origen')?.value.trim();
    const destinoTxt = document.getElementById('destino')?.value.trim();
    const autoSel = document.getElementById('autoSelector');
    const autoAutonomia = parseFloat(autoSel?.selectedOptions[0]?.dataset?.autonomia || '0');
    const bufferKm = parseFloat(document.getElementById('bufferKm')?.value || '5');
    if (!origenTxt || !destinoTxt) { alert('Completá origen y destino'); return; }
    if (!autoAutonomia) { alert('Seleccioná un auto con autonomía'); return; }
    if (isNaN(bufferKm) || bufferKm < 1 || bufferKm > 50) { alert('Ingresá un radio entre 1 y 50 km'); return; }

    try {
        // Geocodificar con pequeño delay entre peticiones
        const orig = await geocode(origenTxt);
        await new Promise(resolve => setTimeout(resolve, 1000)); // 1 segundo de delay
        const dest = await geocode(destinoTxt);
        
        // Pintar ruta (línea recta como aproximación)
        capaRuta.clearLayers();
        const poly = L.polyline([[orig.lat, orig.lon], [dest.lat, dest.lon]], {color:'#00e5ff'}).addTo(capaRuta);
        mapa.fitBounds(poly.getBounds(), {padding:[30,30]});

        // Filtrar estaciones cercanas a la "ruta" (aprox. línea recta)
        const cercanas = estaciones.filter(e => {
            const d = distancePointToSegmentKm({lat:e.latitud, lon:e.longitud}, {lat:orig.lat, lon:orig.lon}, {lat:dest.lat, lon:dest.lon});
            return d <= bufferKm;
        });
        pintarEstaciones(cercanas);

        // Sugerir paradas según autonomía
        const totalKm = haversineKm({lat:orig.lat, lon:orig.lon}, {lat:dest.lat, lon:dest.lon});
        const paradasNecesarias = Math.max(0, Math.ceil(totalKm / autoAutonomia) - 1);
        capaParadas.clearLayers();
        const sugeridas = [];
        for (let i=1; i<=paradasNecesarias; i++) {
            const t = i / (paradasNecesarias + 1);
            const p = {lat: orig.lat + t*(dest.lat - orig.lat), lon: orig.lon + t*(dest.lon - orig.lon)};
            // buscar estación cercana a p dentro de bufferKm
            let mejor = null, mejorD = Infinity;
            cercanas.forEach(e => {
                const d = haversineKm({lat:e.latitud, lon:e.longitud}, p);
                if (d < mejorD) { mejorD = d; mejor = e; }
            });
            if (mejor && mejorD <= bufferKm) {
                sugeridas.push(mejor);
                L.circleMarker([mejor.latitud, mejor.longitud], {radius:8, color:'#ffea00'}).addTo(capaParadas)
                  .bindTooltip('Parada sugerida: ' + mejor.nombre);
            } else {
                // Marcar punto de parada teórica
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
        html += `<tr>
            <td>${c.nombre}</td>
            <td>${c.tipo || '-'}</td>
            <td>${c.estado || '-'}</td>
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
        if (resp.exito) { listarReservas(); modalReserva.style.display = 'none'; }
    }).catch(()=> alert('Error de red'));
});

// Buscar ruta botón
document.getElementById('btnBuscarRuta').addEventListener('click', trazarRutaYSugerir);
    </script>
    <!-- Leaflet JS -->
    <script
      src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
      integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
      crossorigin="">
    </script>
</body>

</html>