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
            <li class="sidebar-item" data-tab="pagos">
                <span class="sidebar-icon">💳</span>
                <span class="sidebar-text">Pagos</span>
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

                <label for="bateria_actual">Batería Actual (%):</label>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <input type="range" id="bateria_actual" name="bateria_actual" min="0" max="100" value="100" style="flex: 1;">
                    <span id="bateria_valor" style="min-width: 50px; font-weight: bold; color: #4ade80;">100%</span>
                </div>

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
<div style="display:flex;gap:18px;flex-wrap:wrap;margin:8px 0 16px 0;">
    <div>
        <label for="bufferKm">Radio de búsqueda (km)</label>
        <input type="number" id="bufferKm" value="5" min="1" max="50" style="width:90px;">
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

        <!-- Pestaña Pagos -->
<div id="tab-pagos" class="tab-content" style="display: none;">
    <h1>Mis Pagos y Facturas</h1>
    <p style="color: #b2ebf2; text-shadow: 1px 1px 4px #222;">Consultá tus pagos realizados y descargá tus facturas</p>

    <h2 style="margin-top:32px;">Historial de Pagos</h2>
    <table id="tablaPagos" style="width:100%;margin-bottom:40px;">
        <thead>
            <tr>
                <th>ID Pago</th>
                <th>Reserva</th>
                <th>Fecha</th>
                <th>Método</th>
                <th>Monto</th>
                <th>Estado</th>
                <th>Factura</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
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
            <div id="mensajeReserva" style="margin-top:15px;"></div>
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

    <!-- Modal Pago -->
<div id="modalPago" class="modal">
  <div class="modal-content">
    <span class="close close-pago">&times;</span>
    <h2>Pagar reserva</h2>
    <form id="formPago">
      <input type="hidden" id="pagoReservaId">
      <label>Método de pago</label>
      <select id="metodoPago" required></select>
      <label>Monto</label>
      <input type="number" id="pagoMonto" step="0.01" min="0" required>
      <button type="submit" style="margin-top:16px;">Pagar</button>
    </form>
    <div id="mensajePago" style="margin-top:12px;"></div>
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
                    let html = "<table><tr><th>ID</th><th>Marca</th><th>Modelo</th><th>Conector</th><th>Autonomía</th><th>Batería</th><th>Año</th><th>Acciones</th></tr>";
                    autos.forEach(auto => {
                        const bateria = auto.bateria_actual !== undefined ? auto.bateria_actual : 100;
                        html += `<tr data-id="${auto.id}">
                            <td>${auto.id}</td>
                            <td class="editable" data-campo="marca">${auto.marca}</td>
                            <td class="editable" data-campo="modelo">${auto.modelo}</td>
                            <td class="editable" data-campo="conector">${auto.conector}</td>
                            <td class="editable" data-campo="autonomia">${auto.autonomia}</td>
                            <td class="editable" data-campo="bateria_actual">${bateria}%</td>
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
                    bateria_actual: formData.get('bateria_actual'),
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
                    const valor = td.textContent.replace('%', '').trim();
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
                    } else if (campo === 'bateria_actual') {
                        td.innerHTML = `<input type="number" value="${valor}" style="width:90%;" min="0" max="100">`;
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
                    bateria_actual: editables[4].querySelector('input, select').value,
                    anio: editables[5].querySelector('input, select').value
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

        // Listener para el slider de batería
        const bateriaSlider = document.getElementById('bateria_actual');
        const bateriaValor = document.getElementById('bateria_valor');
        if (bateriaSlider && bateriaValor) {
            bateriaSlider.addEventListener('input', function() {
                const valor = this.value;
                bateriaValor.textContent = valor + '%';
                // Cambiar color según nivel
                if (valor < 20) {
                    bateriaValor.style.color = '#ef4444'; // rojo
                } else if (valor < 50) {
                    bateriaValor.style.color = '#f59e0b'; // naranja
                } else {
                    bateriaValor.style.color = '#4ade80'; // verde
                }
            });
        }

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
let mapa, capaRuta, capaParadas, capaEstaciones, estaciones = [], paradasRecomendadas = [];
let overrideOriginCoords = null; // si el usuario usa geolocalización para origen
let estacionSeleccionadaParaReserva = null; // estación desde marcador que pidió reservar
let currentLocation = null; // {lat, lon}
let rutaCoordsLatLng = []; // Array de [lat, lon] de la ruta actual
let rutaTotalKm = 0;
let refrescoEstadosTimer = null; // auto-refresh periódico

// Añadir handlers de la barra rápida (faltaban)
function syncOrigenDestino() {
    const o = document.getElementById('q_origen')?.value?.trim() || '';
    const d = document.getElementById('q_destino')?.value?.trim() || '';
    document.getElementById('origen').value = o;
    document.getElementById('destino').value = d;
}
document.getElementById('btnPlanificarQuick').addEventListener('click', () => {
    syncOrigenDestino();
    trazarRutaYSugerir();
});
document.getElementById('btnMiUbic').addEventListener('click', () => {
    if (!navigator.geolocation) { alert('Geolocalización no soportada'); return; }
    const btn = document.getElementById('btnMiUbic');
    btn.disabled = true; btn.textContent = 'Ubicando...';
    navigator.geolocation.getCurrentPosition(pos => {
        overrideOriginCoords = { lat: pos.coords.latitude, lon: pos.coords.longitude };
        document.getElementById('q_origen').value =
            `${pos.coords.latitude.toFixed(5)}, ${pos.coords.longitude.toFixed(5)}`;
        btn.disabled = false; btn.textContent = '📍 Usar mi ubicación';
    }, err => {
        alert('Error geolocalización: ' + err.message);
        btn.disabled = false; btn.textContent = '📍 Usar mi ubicación';
    }, { enableHighAccuracy: true, timeout: 10000 });
});
document.getElementById('bufferKm').addEventListener('change', () => {
    if (rutaCoordsLatLng.length > 1) trazarRutaYSugerir();
});
['q_origen','q_destino'].forEach(id => {
    document.getElementById(id).addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); document.getElementById('btnPlanificarQuick').click(); }
    });
});

// Inicializa mapa cuando se muestra la pestaña viajes por primera vez
let mapaInicializado = false;
document.querySelector('[data-tab="viajes"]').addEventListener('click', () => {
    if (!mapaInicializado) {
        initMapa();
        cargarCargadores();
        cargarAutosSelector();
        listarReservas();
        mapaInicializado = true;
        // Iniciar auto-refresh de estados cada 10s cuando la pestaña de viajes está activa
        if (!refrescoEstadosTimer) {
            refrescoEstadosTimer = setInterval(() => {
                // Solo refrescar si la pestaña viajes está visible
                const tab = document.getElementById('tab-viajes');
                if (tab && tab.style.display !== 'none') {
                    console.log('🔄 Auto-refresh estados');
                    refrescarEstados();
                }
            }, 10000); // Cada 10 segundos
        }
    } else {
        // Al volver a la pestaña, refrescar inmediatamente
        console.log('🔄 Volvió a pestaña Viajes - refrescando estados');
        refrescarEstados();
    }
});

// Cargar historial cuando se abre la pestaña Historial
let historialCargado = false;
document.querySelector('[data-tab="historial"]')?.addEventListener('click', () => {
    console.log('🔍 Pestaña Historial clickeada');
    cargarHistorialReservas(); // Cargar siempre para tener datos actualizados
    historialCargado = true;
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
    console.log('🔄 [RefrescarEstados] Solicitando estados actualizados...');
    fetch('../api/cargadores.php')
      .then(r => r.json())
      .then(data => {
          console.log('📊 [RefrescarEstados] Datos recibidos:', data);
          estaciones = Array.isArray(data) ? data : [];
          
          // Log de estados para debug
          const ocupados = estaciones.filter(e => e.estado === 'ocupado').length;
          const disponibles = estaciones.filter(e => e.estado === 'disponible').length;
          console.log(`📈 Estados: ${ocupados} ocupados, ${disponibles} disponibles`);
          
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
      .catch(err => console.warn('❌ [RefrescarEstados] Error:', err));
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
              opt.dataset.bateria = a.bateria_actual !== undefined ? a.bateria_actual : 100;
              opt.dataset.conector = a.conector || '';
              sel.appendChild(opt);
          });
          // Selección automática si solo hay uno
          if (sel.options.length === 2) sel.selectedIndex = 1;
          if (sel.value) {
              const o = sel.selectedOptions[0];
              document.getElementById('autoResumen').textContent = `🚗 Usando auto: ${o.textContent} | Autonomía: ${o.dataset.autonomia} km | Batería: ${o.dataset.bateria}%`;
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
    console.log('🔓 Abriendo modal de reserva para estación:', id);
    
    // Validar que el ID sea válido
    if (!id || isNaN(id) || parseInt(id) <= 0) {
        console.error('❌ ID inválido recibido:', id);
        alert('Error: ID de estación inválido');
        return;
    }
    
    document.getElementById('reservaCargadorId').value = parseInt(id);
    console.log('✅ Campo reservaCargadorId seteado a:', document.getElementById('reservaCargadorId').value);
    
    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const d = String(now.getDate()).padStart(2, '0');
    
    // Redondear a múltiplos de 15
    const mins = now.getMinutes();
    const rounded = Math.ceil(mins / 15) * 15;
    const minStr = String(rounded === 60 ? 0 : rounded).padStart(2, '0');
    const hourStr = String(rounded === 60 ? (now.getHours() + 1) % 24 : now.getHours()).padStart(2, '0');
    
    document.getElementById('reservaFecha').value = `${y}-${m}-${d}`;
    document.getElementById('reservaHoraInicio').value = `${hourStr}:${minStr}`;
    document.getElementById('reservaDuracion').value = 60;
    
    // Limpiar cualquier mensaje previo
    const msg = document.getElementById('mensajeReserva');
    msg.innerHTML = '';
    msg.className = '';
    
    document.getElementById('modalReserva').style.display = 'block';
    console.log('✅ Modal abierto');
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
    const autoBateria = parseFloat(autoSel?.selectedOptions[0]?.dataset?.bateria || '100');
    const bufferKm = parseFloat(document.getElementById('bufferKm')?.value || '5');
    console.log('[Planificar] origenTxt:', origenTxt, 'destinoTxt:', destinoTxt, 'autoAutonomia:', autoAutonomia, 'bateria:', autoBateria + '%', 'bufferKm:', bufferKm);
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
            coords = ruta.coords; // [[lat:lon], ...]
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

        // ========== ALGORITMO RECOMENDACIÓN SIEMPRE ACTIVO ==========
        function calcularParadasRecomendadas(estacionesCercanas, coords, totalKm, autoAutonomia, autoBateria, bufferKm) {
            const MARGEN_SEGURIDAD = 0.20;
            const OBJETIVO_POST_CARGA = 0.80;
            const estacionesValidas = estacionesCercanas.filter(e => e.estado === 'disponible');
            const resultado = [];
            let kmRestantes = totalKm;
            let kmRecorridos = 0;
            let bateriaActualPct = autoBateria;
            let paradaNum = 0;

            function elegirEstacionCercana(punto) {
                let mejor = null, mejorScore = -Infinity;
                estacionesValidas.forEach(est => {
                    const distKm = haversineKm({lat: est.latitud, lon: est.longitud}, punto);
                    if (distKm > bufferKm) return;
                    const potencia = parseFloat(est.potencia || '0');
                    const autoSel = document.getElementById('autoSelector');
                    const conectorAuto = autoSel?.selectedOptions[0]?.dataset?.conector || '';
                    const raw = (est.conectores ?? est.conector ?? '').toLowerCase();
                    const compatible = raw.includes(conectorAuto.toLowerCase()) ? 1.5 : 0.6;
                    const proximidad = Math.max(0, (bufferKm - distKm) / bufferKm); // 0..1
                    const score = proximidad * 2 + potencia * 0.05 + compatible;
                    if (score > mejorScore) { mejorScore = score; mejor = {est, distKm}; }
                });
                return mejor;
            }

            const alcanceInicial = autoAutonomia * (bateriaActualPct / 100);
            const necesitaParadas = alcanceInicial < totalKm;

            if (!necesitaParadas) {
                // Generar sugerencias opcionales (conveniencia) si no hacen falta paradas
                const centro = puntoEnRutaPorFraccion(coords, 0.5);
                const candidatos = estacionesValidas
                  .map(est => {
                      const distKm = haversineKm({lat: est.latitud, lon: est.longitud}, centro);
                      const potencia = parseFloat(est.potencia || '0');
                      const autoSel = document.getElementById('autoSelector');
                      const conectorAuto = autoSel?.selectedOptions[0]?.dataset?.conector || '';
                      const raw = (est.conectores ?? est.conector ?? '').toLowerCase();
                      const compatible = raw.includes(conectorAuto.toLowerCase()) ? 1.2 : 0.5;
                      const score = (1/(1+distKm)) + potencia*0.01 + compatible;
                      return {est, score, distKm};
                  })
                  .sort((a,b)=>b.score - a.score)
                  .slice(0, Math.min(3, estacionesValidas.length));

                return candidatos.map((c,i)=>({
                    id: c.est.id,
                    nombre: c.est.nombre,
                    numParada: i+1,
                    tipo: 'opcional',
                    kmDesdeOrigen: (totalKm * (0.3 + i*0.2)).toFixed(1),
                    bateriaAlLlegar: (bateriaActualPct - 5).toFixed(0),
                    bateriaDespues: bateriaActualPct.toFixed(0),
                    potencia: c.est.potencia || null,
                    tiempoEstimadoMin: c.est.potencia ? 10 : null
                }));
            }

            // Paradas esenciales
            while (kmRestantes > 0) {
                const kmConBateriaActual = autoAutonomia * (bateriaActualPct / 100) * (1 - MARGEN_SEGURIDAD);
                if (kmConBateriaActual >= kmRestantes) break;
                paradaNum++;
                const tramo = Math.min(kmConBateriaActual, kmRestantes);
                kmRecorridos += tramo;
                const puntoIdeal = puntoEnRutaPorFraccion(coords, kmRecorridos / totalKm);
                if (!puntoIdeal) break;
                const candidato = elegirEstacionCercana(puntoIdeal);
                if (!candidato) {
                    bateriaActualPct = OBJETIVO_POST_CARGA * 100;
                    if (paradaNum > 10) break;
                    continue;
                }
                if (resultado.some(r => r.id === candidato.est.id)) {
                    bateriaActualPct = OBJETIVO_POST_CARGA * 100;
                    continue;
                }
                const bateriaAlLlegarPct = Math.max(0, bateriaActualPct - (tramo / autoAutonomia * 100));
                const bateriaDespuesPct = Math.min(100, OBJETIVO_POST_CARGA * 100);
                let tiempoCargaMin = null;
                if (candidato.est.potencia) {
                    const capacidadKWh = 60;
                    const deltaPct = bateriaDespuesPct - bateriaAlLlegarPct;
                    const energiaNecesariaKWh = capacidadKWh * (deltaPct / 100);
                    tiempoCargaMin = Math.ceil((energiaNecesariaKWh / parseFloat(candidato.est.potencia)) * 60);
                }
                resultado.push({
                    id: candidato.est.id,
                    nombre: candidato.est.nombre,
                    numParada: paradaNum,
                    tipo: 'esencial',
                    kmDesdeOrigen: kmRecorridos.toFixed(1),
                    bateriaAlLlegar: bateriaAlLlegarPct.toFixed(0),
                    bateriaDespues: bateriaDespuesPct.toFixed(0),
                    potencia: candidato.est.potencia || null,
                    tiempoEstimadoMin: tiempoCargaMin
                });
                bateriaActualPct = bateriaDespuesPct;
                kmRestantes = totalKm - kmRecorridos;
                if (paradaNum > 10) break; // Limitar a 10 paradas esenciales
            }

            return resultado;
        }

        // Calcular y mostrar paradas recomendadas
        const paradas = calcularParadasRecomendadas(cercanas, coords, totalKm, autoAutonomia, autoBateria, bufferKm);
        console.log('🛑 Paradas recomendadas:', paradas);
        paradasRecomendadas = paradas;
        renderPanelEstaciones(cercanas, paradas);
        
        // Mostrar resumen de ruta
        mostrarResumenRuta(totalKm, autoAutonomia, autoBateria, paradas);
    } catch (err) {
        console.error('Planificar error:', err);
        alert('No se pudo planificar la ruta: ' + err.message);
    }
} // <--- CIERRE de trazarRutaYSugerir que faltaba

// ======= Funciones auxiliares (deben estar FUERA de trazarRutaYSugerir) =======
function renderPanelEstaciones(lista, sugeridas) {
    const cont = document.getElementById('panelEstaciones');
    const sugMap = new Map((sugeridas||[]).map(s => [String(s.id), s]));
    if (!lista || !lista.length) {
        cont.innerHTML = '<p>No hay estaciones cercanas a la ruta con el filtro actual.</p>';
        return;
    }
    let html = '<table style="width:100%"><thead><tr>' +
        '<th>Estación</th><th>Tipo</th><th>Estado</th><th>Lat</th><th>Lon</th><th>Parada Recomendada</th><th>Acciones</th>' +
        '</tr></thead><tbody>';
    lista.forEach(c => {
        const estTxt = String(c.estado||'-');
        const estadoBadge = `<span class="estado-badge ${estTxt ? 'estado-' + estTxt.toLowerCase().replace(/\s+/g,'-') : ''}">${estTxt}</span>`;
        let recomendada = '-';
        const sug = sugMap.get(String(c.id));
        if (sug) {
            recomendada =
              `<strong style="color:${sug.tipo==='esencial'?'#ff7043':'#ffea00'};">Parada ${sug.numParada} (${sug.tipo})</strong><br>` +
              `<small>📍 ${sug.kmDesdeOrigen} km<br>` +
              `🔋 ${sug.bateriaAlLlegar}% → ${sug.bateriaDespues}%` +
              (sug.tiempoEstimadoMin ? `<br>⏱️ ${sug.tiempoEstimadoMin} min` : '') +
              `</small><br><button class="btn-reservar-recomendada" data-id="${sug.id}">Reservar</button>`;
        }
        html += `<tr>
            <td>${c.nombre || '-'}</td>
            <td>${c.tipo || '-'}</td>
            <td>${estadoBadge}</td>
            <td>${Number(c.latitud).toFixed(5)}</td>
            <td>${Number(c.longitud).toFixed(5)}</td>
            <td>${recomendada}</td>
            <td>
                <button onclick="abrirDetalleEstacion(${c.id})">Ver</button>
                <button onclick="abrirReserva(${c.id})">Reservar</button>
            </td>
        </tr>`;
    });
    html += '</tbody></table>';
    cont.innerHTML = html;
}

document.addEventListener('click', e => {
    if (e.target.classList.contains('btn-reservar-recomendada')) {
        const id = e.target.getAttribute('data-id');
        abrirReserva(id);
    }
});

function abrirDetalleEstacion(id) {
    console.log('👁️ Abriendo detalle de estación:', id);
    const est = (estaciones||[]).find(e => Number(e.id) === Number(id));
    if (!est) {
        console.error('❌ Estación no encontrada:', id);
        return;
    }
    estacionSeleccionadaParaReserva = parseInt(id);
    console.log('✅ estacionSeleccionadaParaReserva =', estacionSeleccionadaParaReserva);
    
    document.getElementById('detNombre').textContent = est.nombre || 'Estación';
    document.getElementById('detLat').textContent = est.latitud ?? '-';
    document.getElementById('detLon').textContent = est.longitud ?? '-';
    document.getElementById('detTipo').textContent = est.tipo ?? '-';
    document.getElementById('detEstado').textContent = est.estado ?? '-';
    document.getElementById('detDesc').textContent = est.descripcion || est.direccion || '-';
    document.getElementById('modalEstacion').style.display = 'block';
}

function planificarParaEstacion(id) { 
    console.log('📍 planificarParaEstacion llamado con ID:', id);
    abrirReserva(id); 
}

function listarReservas() {
    fetch('../api/reservas.php?accion=listar_usuario', { cache: 'no-store' })
      .then(r=>r.json())
      .then(resp => {
          const tbody = document.querySelector('#tablaReservas tbody');
          tbody.innerHTML = '';

          const reservas = Array.isArray(resp?.reservas) ? resp.reservas : [];
          const ahora = new Date();

          const activas = reservas.filter(r => {
              const finStr = r.fin || '';
              const finDate = finStr ? new Date(finStr.replace(' ', 'T')) : null;
              const estado = String(r.estado || '').toLowerCase();
              return estado === 'confirmada' && finDate && finDate > ahora;
          });

          if (!activas.length) {
              tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No tenés reservas activas</td></tr>';
              return;
          }

          activas.forEach(r => {
              const estacion = r.estacion || (r.cargador_id ? `Estación #${r.cargador_id}` : '-');
              const pagado = Number(r.pagado || 0) === 1;
              const monto = (typeof r.monto !== 'undefined' && r.monto !== null) ? parseFloat(r.monto) : 0;

              let acciones = `<button class="btn-cancelar-reserva" data-id="${r.id}">Cancelar</button>`;
              if (!pagado) {
                  acciones += ` <button class="btn-pagar-reserva" data-id="${r.id}" data-monto="${monto.toFixed(2)}">Pagar</button>`;
              } else {
                  acciones += ` <button class="btn-factura" data-id="${r.id}">Factura</button>`;
              }

              const tr = document.createElement('tr');
              tr.innerHTML = `<td>${estacion}</td>
                              <td>${r.inicio || '-'}</td>
                              <td>${r.fin || '-'}</td>
                              <td>${r.estado || '-'}</td>
                              <td>${acciones}</td>`;
              tbody.appendChild(tr);
              tr.querySelector('.btn-cancelar-reserva').addEventListener('click', () => cancelarReserva(r.id));
          });
      })
      .catch(err => {
          console.error(err);
          document.querySelector('#tablaReservas tbody').innerHTML =
            '<tr><td colspan="5" style="text-align:center;">Error al cargar</td></tr>';
      });
}

// Función separada para cancelar
function cancelarReserva(id) {
    console.log('🗑️ Cancelando reserva ID:', id);
    
    if (!id) {
        alert('Error: ID de reserva no válido');
        return;
    }
    
    if (!confirm('¿Cancelar esta reserva?')) return;
    
    fetch('../api/reservas.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            accion: 'cancelar', 
            reserva_id: parseInt(id)
        })
    })
    .then(r => r.json())
    .then(resp => {
        console.log('📡 Respuesta cancelar:', resp);
        if (resp.exito) {
            // 🔥 REFRESCAR INMEDIATAMENTE DESPUÉS DE CANCELAR
            console.log('🔄 Refrescando estados inmediatamente después de cancelar...');
            listarReservas();
            refrescarEstados();
            
            // Mostrar mensaje después del refresh
            setTimeout(() => {
                alert('Reserva cancelada');
            }, 500);
        } else {
            alert('Error al cancelar: ' + (resp.mensaje || 'Error desconocido'));
        }
    })
    .catch(err => {
        console.error('❌ Error al cancelar:', err);
        alert('Error de conexión: ' + err.message);
    });
}

// Modal reserva submit
document.getElementById('formReserva').addEventListener('submit', function(e) {
    e.preventDefault();
    const cargadorId = document.getElementById('reservaCargadorId').value;
    const fecha = document.getElementById('reservaFecha').value;
    const hora = document.getElementById('reservaHoraInicio').value;
    const duracion = document.getElementById('reservaDuracion').value;
    
    console.log('📋 Datos del formulario:', {cargadorId, fecha, hora, duracion});
    
    const idNumerico = parseInt(cargadorId);
    if (!cargadorId || isNaN(idNumerico) || idNumerico <= 0) {
        const msg = document.getElementById('mensajeReserva');
        msg.innerHTML = `⚠️ Error: ID de estación inválido (${cargadorId})`;
        msg.className = 'mensaje error';
        setTimeout(() => { msg.innerHTML = ''; msg.className = ''; }, 4000);
        return;
    }
    
    if (!fecha || !hora || !duracion) {
        const msg = document.getElementById('mensajeReserva');
        msg.innerHTML = '⚠️ Por favor completá todos los campos';
        msg.className = 'mensaje error';
        setTimeout(() => { msg.innerHTML = ''; msg.className = ''; }, 3000);
                             return;
    }
    
    const inicio = `${fecha} ${hora}:00`;
    const duracionMin = parseInt(duracion);
    const fechaInicio = new Date(`${fecha}T${hora}:00`);
    const fechaFin = new Date(fechaInicio.getTime() + duracionMin * 60000);
    
    const year = fechaFin.getFullYear();
    const month = String(fechaFin.getMonth() + 1).padStart(2, '0');
    const day = String(fechaFin.getDate()).padStart(2, '0');
    const hours = String(fechaFin.getHours()).padStart(2, '0');
    const minutes = String(fechaFin.getMinutes()).padStart(2, '0');
    
    const fin = `${year}-${month}-${day} ${hours}:${minutes}:00`;
    
    console.log('⏰ Fechas calculadas:', {inicio, fin});
    
    const btnSubmit = this.querySelector('button[type="submit"]');
    const textoOriginal = btnSubmit.textContent;
    btnSubmit.disabled = true;
    btnSubmit.textContent = 'Procesando...';
    
    const payload = {
        accion: 'crear',
       

       
       
       
       
        cargador_id: idNumerico,
        inicio: inicio,
        fin: fin
    };
    
    console.log('📤 Enviando a la API:', payload);
    
    fetch('../api/reservas.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify(payload)
                         })
    .then(r => {
        console.log('📡 Respuesta status:', r.status);
        return r.text().then(text => {
            console.log('📡 Respuesta raw:', text);
            try {
                return JSON.parse(text);
            } catch(e) {
                console.error('❌ No es JSON válido:', text);
                throw new Error('Respuesta no es JSON: ' + text.substring(0, 100));
            }
        });
    })
    .then(resp => {
        console.log('✅ Respuesta API parseada:', resp);
        btnSubmit.disabled = false;
        btnSubmit.textContent = textoOriginal;
        
        const msg = document.getElementById('mensajeReserva');
        if (resp.exito) {
            msg.innerHTML = '✅ Reserva confirmada exitosamente';
            msg.className = 'mensaje exito';
            
            
            // 🔥 REFRESCAR INMEDIATAMENTE (sin esperar 2 segundos)
            console.log('🔄 Refrescando estados inmediatamente...');
            listarReservas();
            refrescarEstados();
            
            // Cerrar modal después de refrescar
            setTimeout(() => {
                document.getElementById('modalReserva').style.display = 'none';
                msg.innerHTML = '';
                msg.className = '';
                this.reset();
            }, 1500); // Reducido a 1.5 segundos
        } else {
            msg.innerHTML = '❌ ' + (resp.mensaje || 'Error al crear la reserva');
            msg.className = 'mensaje error';
        }
    })
    .catch(err => {
        console.error('❌ Error completo:', err);
        btnSubmit.disabled = false;
        btnSubmit.textContent = textoOriginal;
        const msg = document.getElementById('mensajeReserva');
        msg.innerHTML = '❌ ' + err.message;
        msg.className = 'mensaje error';
    });
});

// Cerrar modales
document.querySelectorAll('.close, .close-reserva, .close-estacion').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('modalReserva').style.display = 'none';
        document.getElementById('modalEstacion').style.display = 'none';
    });

});

document.getElementById('btnReservarDesdeDetalle')?.addEventListener('click', () => {
    console.log('🔘 Botón Reservar desde Detalle - ID:', estacionSeleccionadaParaReserva);
    if (estacionSeleccionadaParaReserva) {
        abrirReserva(estacionSeleccionadaParaReserva);
        document.getElementById('modalEstacion').style.display = 'none';
    } else {
        console.error('❌ No hay estación seleccionada');
        alert('Error: No se pudo identificar la estación');
    }
});

// Aplicar filtros cuando cambian los selects
['filtroTipo','filtroEstado','filtroConector','filtroCompatibleAuto'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', () => {
        if (rutaCoordsLatLng && rutaCoordsLatLng.length) {
            // Con ruta activa: re-aplicar filtros sobre cercanas
            const bufferKm = parseFloat(document.getElementById('bufferKm')?.value || '5');
            const cercanasBase = estaciones.filter(e => {
                const p = {lat: parseFloat(e.latitud), lon: parseFloat(e.longitud)};
                const d = distancePointToRouteKm(p, rutaCoordsLatLng);
                return d <= bufferKm;
            });
            const cercanas = aplicarFiltros(cercanasBase);
            pintarEstaciones(cercanas);
            renderPanelEstaciones(cercanas, paradasRecomendadas);
        } else {
            // Sin ruta: mostrar todo filtrado
            const filtradas = aplicarFiltros(estaciones);
            pintarEstaciones(filtradas);
            renderPanelEstaciones(filtradas, []);
        }
    });
});

// Función para mostrar resumen de ruta (AGREGAR DESPUÉS DE trazarRutaYSugerir)
function mostrarResumenRuta(totalKm, autoAutonomia, autoBateria, paradasRecomendadas) {
    console.log('📊 Mostrando resumen de ruta:', {totalKm, autoAutonomia, autoBateria, paradasCount: paradasRecomendadas.length});
    
    // Crear o actualizar panel de resumen
    let panel = document.getElementById('resumenRutaPanel');
    if (!panel) {
        panel = document.createElement('div');
        panel.id = 'resumenRutaPanel';
        panel.style.cssText = `
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            color: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        `;
        const mapaRuta = document.getElementById('mapaRuta');
        mapaRuta.parentNode.insertBefore(panel, mapaRuta.nextSibling);
    }
    
    const alcanceActual = (autoAutonomia * autoBateria / 100).toFixed(1);
    const necesitaCarga = totalKm > alcanceActual;
    const esenciales = paradasRecomendadas.filter(p => p.tipo === 'esencial');
    const opcionales = paradasRecomendadas.filter(p => p.tipo === 'opcional');
    
    let html = `
        <h3 style="margin:0 0 16px 0; color:#60a5fa; font-size:1.3em;">📊 Resumen de Ruta</h3>
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">
            <div>
                <div style="font-size:0.85em; color:#93c5fd; margin-bottom:4px;">Distancia Total</div>
                <div style="font-size:1.5em; font-weight:bold;">${totalKm.toFixed(1)} km</div>
            </div>
            <div>
                <div style="font-size:0.85em; color:#93c5fd; margin-bottom:4px;">Autonomía Actual</div>
                <div style="font-size:1.5em; font-weight:bold;">${alcanceActual} km (${autoBateria}%)</div>
            </div>
            <div>
                <div style="font-size:0.85em; color:#93c5fd; margin-bottom:4px;">Paradas Necesarias</div>
                <div style="font-size:1.5em; font-weight:bold; color:${necesitaCarga ? '#fbbf24' : '#34d399'};">
                    ${necesitaCarga ? esenciales.length : '0'}
                </div>
            </div>
        </div>
    `;
    
    if (necesitaCarga && esenciales.length > 0) {
        html += `
            <div style="margin-top:20px; padding:16px; background:rgba(251,191,36,0.15); border-radius:8px; border-left:4px solid #fbbf24;">
                <strong style="color:#fbbf24;">⚡ Paradas de Carga Esenciales:</strong>
                <ul style="margin:8px 0 0 20px; padding:0;">
        `;
        esenciales.forEach(p => {
            html += `
                <li style="margin:6px 0;">
                    <strong>${p.nombre}</strong> - 
                    ${p.kmDesdeOrigen}km • 
                    🔋 ${p.bateriaAlLlegar}% → ${p.bateriaDespues}%
                    ${p.tiempoEstimadoMin ? ` • ⏱️ ${p.tiempoEstimadoMin} min` : ''}
                </li>
            `;
        });
        html += '</ul></div>';
    }
    
    if (!necesitaCarga) {
        html += `
            <div style="margin-top:20px; padding:16px; background:rgba(52,211,153,0.15); border-radius:8px; border-left:4px solid #34d399;">
                <strong style="color:#34d399;">✅ No necesitás cargar para llegar al destino</strong>
                <p style="margin:8px 0 0 0; font-size:0.9em;">Tu batería actual (${autoBateria}%) es suficiente para recorrer ${totalKm.toFixed(1)} km.</p>
            </div>
        `;
    }
    
    if (opcionales.length > 0) {
        html += `
            <div style="margin-top:16px; padding:12px; background:rgba(234,179,8,0.1); border-radius:8px;">
                <strong style="color:#eab308;">💡 Sugerencias opcionales de carga:</strong>
                <small style="display:block; margin-top:4px; color:#fef3c7;">
                    ${opcionales.map(p => p.nombre).join(', ')}
                </small>
            </div>
        `;
    }
    
    panel.innerHTML = html;
}

// Función para cargar historial de reservas (completadas y canceladas)
function cargarHistorialReservas() {
    console.log('📊 Cargando historial de reservas...');
    fetch('../api/reservas.php?accion=listar_usuario', { cache: 'no-store' })
      .then(r => r.json())
      .then(resp => {
          console.log('📡 Respuesta historial:', resp);
          const tbody = document.querySelector('#tablaHistorialReservas tbody');
          if (!tbody) return;
          tbody.innerHTML = '';

          const reservas = Array.isArray(resp?.reservas) ? resp.reservas : [];

          if (!reservas.length) {
              tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Sin datos</td></tr>';
              return;
          }

          const ahora = new Date();
          const pasadas = reservas.filter(r => {
              const estado = String(r.estado || '').toLowerCase();
              if (estado === 'cancelada' || estado === 'completada') return true;
              const finStr = r.fin || '';
              if (!finStr) return false;
              const finDate = new Date(finStr.replace(' ', 'T'));
              return finDate < ahora;
          });

          if (!pasadas.length) {
              tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">No tenés historial de cargas</td></tr>';
              return;
          }

          pasadas.sort((a, b) => {
              const aIni = new Date((a.inicio || '').replace(' ', 'T'));
              const bIni = new Date((b.inicio || '').replace(' ', 'T'));
              return bIni - aIni;
          });

          pasadas.forEach(r => {
              const estacion = r.estacion || (r.cargador_id ? `Estación #${r.cargador_id}` : '-');
              const inicio = r.inicio || '-';
              const fin = r.fin || '-';
              const estado = r.estado || '-';
              const estadoClass = estado === 'completada' ? 'estado-completada'
                                : estado === 'cancelada' ? 'estado-cancelada' : '';
              const tr = document.createElement('tr');
              tr.innerHTML = `
                  <td>${estacion}</td>
                  <td>${inicio}</td>
                  <td>${fin}</td>
                  <td><span class="${estadoClass}">${estado}</span></td>
              `;
              tbody.appendChild(tr);
          });
      })
      .catch(err => {
          console.error('❌ Error al cargar historial:', err);
          const tbody = document.querySelector('#tablaHistorialReservas tbody');
          if (tbody) {
              tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Error al cargar historial</td></tr>';
          }
      });
}

// Métodos de pago - Cargar y manejar pagos
function cargarMetodosPago(){
  fetch('../api/pagos.php?accion=metodos', {cache:'no-store'})
    .then(r=>r.json())
    .then(d=>{
      const sel = document.getElementById('metodoPago');
      sel.innerHTML = '<option value="">Seleccionar...</option>';
      (d.metodos||[]).forEach(m=>{
        sel.innerHTML += `<option value="${m.id}">${m.nombre}</option>`;
      });
    })
    .catch(()=>{ document.getElementById('metodoPago').innerHTML = '<option value="">Error</option>'; });
}

document.addEventListener('click', e => {
  if (e.target.classList.contains('btn-pagar-reserva')) {
    document.getElementById('pagoReservaId').value = e.target.dataset.id;
    document.getElementById('pagoMonto').value = e.target.dataset.monto || 0;
    document.getElementById('mensajePago').textContent = '';
    document.getElementById('modalPago').style.display='block';
    cargarMetodosPago();
  }
  if (e.target.classList.contains('btn-factura')) {
    const reservaId = e.target.dataset.id;
    fetch('../api/reservas.php?accion=detalle_pago_reserva&id='+reservaId)
      .then(r=>r.json())
      .then(dp=>{
        if (!dp.pago_id) { alert('No se encontró pago asociado'); return; }
        window.open(`../api/facturas.php?accion=descargar&pago_id=${dp.pago_id}`, '_blank');
      })
      .catch(err => alert('Error: ' + err.message));
  }
});

document.getElementById('formPago').addEventListener('submit', e=>{
  e.preventDefault();
  const reservaId = document.getElementById('pagoReservaId').value;
  const metodoId  = document.getElementById('metodoPago').value;
  const monto     = document.getElementById('pagoMonto').value;

  const fd = new FormData();
  fd.append('accion','iniciar');
  fd.append('reserva_id',reservaId);
  fd.append('metodo_id',metodoId);
  fd.append('monto',monto);

  fetch('../api/pagos.php',{method:'POST',body:fd})
    .then(r=>r.json())
    .then(p=>{
      if(!p.exito){ mostrarMsgPago('Error al iniciar pago','error'); return; }
      const fd2 = new FormData();
      fd2.append('accion','confirmar');
      fd2.append('pago_id',p.pago_id);
      fd2.append('estado','aprobado');
      return fetch('../api/pagos.php',{method:'POST',body:fd2})
        .then(r=>r.json())
        .then(res=>{
          if(res.exito){
            mostrarMsgPago('Pago aprobado','exito');
            setTimeout(()=>{
              document.getElementById('modalPago').style.display='none';
              listarReservas();
            },800);
          } else mostrarMsgPago('Error al confirmar','error');
        });
    })
    .catch(()=>mostrarMsgPago('Error de conexión','error'));
});

function mostrarMsgPago(t, tipo){
  const d = document.getElementById('mensajePago');
  d.textContent = t;
  d.className = 'mensaje ' + tipo;
}

document.querySelectorAll('.close-pago').forEach(c=>c.addEventListener('click',()=> {
  document.getElementById('modalPago').style.display='none';
}));

// Cargar pagos cuando se abre la pestaña
let pagosCargado = false;
document.querySelector('[data-tab="pagos"]')?.addEventListener('click', () => {
    console.log('💳 Pestaña Pagos clickeada');
    cargarPagos();
});

function cargarPagos() {
    console.log('💳 Cargando pagos del usuario...');
    fetch('../api/pagos.php?accion=listar_usuario', { cache: 'no-store' })
      .then(r => {
          console.log('📡 Status response:', r.status);
          return r.json();
      })
      .then(resp => {
          console.log('📊 Pagos recibidos:', resp);
          const tbody = document.querySelector('#tablaPagos tbody');
          if (!tbody) {
              console.error('❌ No se encontró tbody de tablaPagos');
              return;
          }
          tbody.innerHTML = '';

          const pagos = Array.isArray(resp?.pagos) ? resp.pagos : [];
          console.log('📈 Total de pagos:', pagos.length);

          if (!pagos.length) {
              tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">No tenés pagos registrados</td></tr>';
              return;
          }

          pagos.forEach((p, idx) => {
              console.log(`  💳 Pago ${idx + 1}:`, p);
              const pagoId = p.pago_id || '-';
              const reservaInfo = p.estacion_nombre 
                ? `${p.estacion_nombre}<br><small style="color:#b2ebf2;">${p.reserva_inicio || ''} - ${p.reserva_fin || ''}</small>`
                : `Reserva #${p.reserva_id || '-'}`;
              const fecha = p.fecha_pago ? new Date(p.fecha_pago).toLocaleString('es-UY') : '-';
              const metodo = p.metodo_nombre || '-';
              const monto = p.monto ? `${p.moneda || 'UYU'} ${parseFloat(p.monto).toFixed(2)}` : '-';
              const estado = p.pago_estado || '-';
              
              const estadoClass = estado === 'aprobado' ? 'estado-completada' 
                                : estado === 'pendiente' ? 'estado-badge' 
                                : 'estado-cancelada';
              const estadoBadge = `<span class="${estadoClass}">${estado}</span>`;

              let facturaBtn = '-';
              if (estado === 'aprobado') {
                  if (p.pdf_path) {
                      facturaBtn = `<button class="btn-descargar-factura" data-pago-id="${pagoId}" title="Descargar PDF" style="background:#10b981;color:white;padding:6px 12px;border:none;border-radius:6px;cursor:pointer;">📄 Descargar</button>`;
                  } else {
                      facturaBtn = `<button class="btn-generar-factura" data-pago-id="${pagoId}" title="Generar PDF" style="background:#3b82f6;color:white;padding:6px 12px;border:none;border-radius:6px;cursor:pointer;">⚙️ Generar</button>`;
                  }
              }

              const tr = document.createElement('tr');
              tr.innerHTML = `
                  <td>#${pagoId}</td>
                  <td>${reservaInfo}</td>
                  <td>${fecha}</td>
                  <td>${metodo}</td>
                  <td><strong>${monto}</strong></td>
                  <td>${estadoBadge}</td>
                  <td>${facturaBtn}</td>
              `;
              tbody.appendChild(tr);
          });
      })
      .catch(err => {
          console.error('❌ Error al cargar pagos:', err);
          const tbody = document.querySelector('#tablaPagos tbody');
          if (tbody) {
              tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#ef4444;">Error al cargar pagos: ' + err.message + '</td></tr>';
          }
      });
}

// Manejar clics en botones de factura
document.addEventListener('click', e => {
    if (e.target.classList.contains('btn-descargar-factura')) {
        const pagoId = e.target.dataset.pagoId;
        console.log('📥 Descargando factura para pago:', pagoId);
        window.open(`../api/facturas.php?accion=descargar&pago_id=${pagoId}`, '_blank');
    }
    
    if (e.target.classList.contains('btn-generar-factura')) {
        const pagoId = e.target.dataset.pagoId;
        const btn = e.target;
        const textoOriginal = btn.textContent;
        
        btn.disabled = true;
        btn.textContent = '⏳ Generando...';
        
        console.log('⚙️ Generando factura para pago:', pagoId);
        
        fetch(`../api/facturas.php?accion=generar&pago_id=${pagoId}`)
          .then(r => r.json())
          .then(resp => {
              if (resp.exito) {
                  console.log('✅ Factura generada:', resp.pdf);
                  window.open(`../api/facturas.php?accion=descargar&pago_id=${pagoId}`, '_blank');
                  setTimeout(() => cargarPagos(), 500);
              } else {
                  alert('Error al generar factura: ' + (resp.mensaje || 'Error desconocido'));
                  btn.disabled = false;
                  btn.textContent = textoOriginal;
              }
          })
          .catch(err => {
              console.error('❌ Error:', err);
              alert('Error de conexión: ' + err.message);
              btn.disabled = false;
              btn.textContent = textoOriginal;
          });
    }
});
    </script>

    <!-- Leaflet JS (sin clave) -->
    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin="">
    </script>
</body>
</html>