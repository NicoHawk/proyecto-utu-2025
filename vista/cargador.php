<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['tipo_usuario'] !== 'cargador') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Cargador</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/cargador.css">
</head>
<body>
    <!-- Selector de idioma -->
    <div class="language-selector-top closed">
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
    <div class="container">
        <h1 id="titulo-panel">Panel de Cargador</h1>
        <p id="descripcion-panel">Has iniciado sesión como usuario de tipo <b>cargador</b>.<br>Aquí podrás gestionar los puntos de carga.</p>
        
        <h2 id="titulo-mapa" style="margin-bottom: 10px; color:#1976d2; margin-top:40px;">Mapa de cargadores</h2>
        <div id="map" style="width:100%; min-width:300px; height:340px; margin-bottom: 20px; border-radius: 16px; box-shadow: 0 4px 24px 0 rgba(31, 38, 135, 0.13);"></div>
        
        <h2 id="titulo-agregar" style="margin-top:40px;">Agregar Cargador</h2>
        <p id="instruccion-mapa" style="font-size:0.9em; color:#666; margin-bottom:12px;">Haz clic en el mapa para seleccionar la ubicación del cargador</p>
        
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
                <label id="label-conectores" style="display:block; margin-bottom:8px; font-weight:600; color:#1976d2;">Tipos de conectores disponibles:</label>
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
            
            <button type="submit" id="btn-agregar" disabled style="grid-column: 1 / -1; min-width:140px;">Agregar cargador</button>
            <span id="ubicacionSeleccionada" style="grid-column: 1 / -1; font-size: 0.9em; color: #555;"></span>
        </form>

        <h2 id="titulo-listado" style="margin-top:40px;">Listado de Cargadores</h2>
        <div id="tablaCargadores"></div>
    </div>
    <script>
    // Toggle menú de idioma
    function toggleLangMenu() {
        const menu = document.getElementById('langMenu');
        const selector = document.querySelector('.language-selector-top');
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
            document.getElementById('titulo-panel').textContent = 'Panel de Cargador';
            document.getElementById('descripcion-panel').innerHTML = 'Has iniciado sesión como usuario de tipo <b>cargador</b>.<br>Aquí podrás gestionar los puntos de carga.';
            document.getElementById('titulo-mapa').textContent = 'Mapa de cargadores';
            document.getElementById('titulo-agregar').textContent = 'Agregar Cargador';
            document.getElementById('instruccion-mapa').textContent = 'Haz clic en el mapa para seleccionar la ubicación del cargador';
            document.getElementById('nombreCargador').placeholder = 'Nombre del cargador';
            document.getElementById('descripcionCargador').placeholder = 'Descripción (opcional)';
            document.getElementById('potenciaCargador').placeholder = 'Potencia (kW)';
            document.getElementById('label-conectores').textContent = 'Tipos de conectores disponibles:';
            document.getElementById('btn-agregar').textContent = 'Agregar cargador';
            document.getElementById('titulo-listado').textContent = 'Listado de Cargadores';
            document.querySelector('.usuario-dropdown a').textContent = 'Cerrar sesión';
        } else if (lang === 'en') {
            currentLangSpan.textContent = 'EN';
            document.getElementById('titulo-panel').textContent = 'Charger Panel';
            document.getElementById('descripcion-panel').innerHTML = 'You are logged in as <b>charger</b> user.<br>Here you can manage charging points.';
            document.getElementById('titulo-mapa').textContent = 'Chargers Map';
            document.getElementById('titulo-agregar').textContent = 'Add Charger';
            document.getElementById('instruccion-mapa').textContent = 'Click on the map to select the charger location';
            document.getElementById('nombreCargador').placeholder = 'Charger name';
            document.getElementById('descripcionCargador').placeholder = 'Description (optional)';
            document.getElementById('potenciaCargador').placeholder = 'Power (kW)';
            document.getElementById('label-conectores').textContent = 'Available connector types:';
            document.getElementById('btn-agregar').textContent = 'Add charger';
            document.getElementById('titulo-listado').textContent = 'Chargers List';
            document.querySelector('.usuario-dropdown a').textContent = 'Logout';
        }
        toggleLangMenu();
    }

    // Cerrar menú al hacer clic fuera
    document.addEventListener('click', function(e) {
        const selector = document.querySelector('.language-selector-top');
        const menu = document.getElementById('langMenu');
        if (selector && !selector.contains(e.target)) {
            menu.classList.add('hidden');
            selector.classList.add('closed');
        }
    });

    let map;
    let marcadores = {};
    let ubicacionTemporal = null;

    // Definir initMap globalmente ANTES de cargar el script de Google Maps
    window.initMap = function() {
        console.log('Inicializando mapa...');
            console.log('Usuario autenticado:', '<?php echo $_SESSION['usuario']; ?>');
            console.log('Tipo de usuario:', '<?php echo $_SESSION['tipo_usuario']; ?>');
        const centro = { lat: -34.7176, lng: -55.9586 };
        map = new google.maps.Map(document.getElementById("map"), {
            zoom: 13,
            center: centro,
        });
            console.log('Mapa creado exitosamente');
            console.log('Llamando a API cargadores...');
    fetch('../api/cargadores.php')
        .then(res => {
            if (!res.ok) {
                throw new Error('HTTP error! status: ' + res.status);
            }
            return res.json();
        })
        .then(cargadores => {
            console.log('Respuesta de la API cargadores:', cargadores);
            if (Array.isArray(cargadores)) {
                mostrarListaCargadores(cargadores);
                cargadores.forEach(cargador => {
                    const lat = parseFloat(cargador.latitud);
                    const lng = parseFloat(cargador.longitud);
                    if (!isNaN(lat) && !isNaN(lng)) {
                        console.log('Agregando marcador:', cargador.nombre, lat, lng);
                        agregarCargador(cargador.id, cargador.nombre, {lat: lat, lng: lng});
                    } else {
                        console.warn('Cargador con lat/lng inválido:', cargador);
                    }
                });
            } else {
                console.error('La respuesta de la API no es un array:', cargadores);
                document.getElementById('tablaCargadores').innerHTML = '<p style="color:red">Error: La API no devolvió un array válido.</p>';
            }
        })
        .catch(error => {
            console.error('Error al cargar cargadores:', error);
            document.getElementById('tablaCargadores').innerHTML = '<p style="color:red">Error al cargar los cargadores: ' + error.message + '</p>';
        });
        map.addListener("click", function(e) {
            ubicacionTemporal = e.latLng;
            document.getElementById("ubicacionSeleccionada").textContent = "Ubicación seleccionada";
            document.querySelector("#formCargador button[type='submit']").disabled = false;
            if (marcadores['temporal']) {
                marcadores['temporal'].setMap(null);
            }
            marcadores['temporal'] = new google.maps.Marker({
                position: ubicacionTemporal,
                map,
                icon: "https://maps.google.com/mapfiles/ms/icons/yellow-dot.png",
                title: "Ubicación seleccionada"
            });
        });
    }

    function agregarCargador(id, nombre, latLng) {
        if (marcadores['temporal']) {
            marcadores['temporal'].setMap(null);
            delete marcadores['temporal'];
        }
        const infoWindow = new google.maps.InfoWindow({ content: `<strong>${nombre}</strong>` });
        const marcador = new google.maps.Marker({
            position: latLng,
            map,
            title: nombre,
            icon: "https://maps.google.com/mapfiles/ms/icons/green-dot.png",
        });
        marcador.addListener('click', function() { infoWindow.open(map, marcador); });
        marcadores[id] = marcador;
    }

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
            // Valores seguros con defaults
            const potencia = cargador.potencia_kw || 0;
            const estado = cargador.estado || 'disponible';
            const tipo = cargador.tipo || '';
            const descripcion = cargador.descripcion || '';
            const conectores = cargador.conectores || '';
            
            html += `<tr data-id="${cargador.id}">
                <td>${cargador.id}</td>
                <td>
                    <span id="nombre-${cargador.id}">${cargador.nombre}</span>
                    <input type="text" id="input-nombre-${cargador.id}" value="${cargador.nombre}" style="display:none; width:95%;">
                </td>
                <td>
                    <span id="descripcion-${cargador.id}">${descripcion || '-'}</span>
                    <input type="text" id="input-descripcion-${cargador.id}" value="${descripcion}" style="display:none; width:95%;">
                </td>
                <td>
                    <span id="tipo-${cargador.id}">${tipo || '-'}</span>
                    <select id="input-tipo-${cargador.id}" style="display:none; width:95%;">
                        <option value="">-</option>
                        <option value="AC Lento" ${tipo === 'AC Lento' ? 'selected' : ''}>AC Lento</option>
                        <option value="AC Rápido" ${tipo === 'AC Rápido' ? 'selected' : ''}>AC Rápido</option>
                        <option value="DC Rápido" ${tipo === 'DC Rápido' ? 'selected' : ''}>DC Rápido</option>
                        <option value="DC Ultra Rápido" ${tipo === 'DC Ultra Rápido' ? 'selected' : ''}>DC Ultra Rápido</option>
                    </select>
                </td>
                <td>
                    <span id="potencia-${cargador.id}">${potencia || 0}</span>
                    <input type="number" id="input-potencia-${cargador.id}" value="${potencia || 0}" min="0" step="0.1" style="display:none; width:80px;">
                </td>
                <td>
                    <span id="estado-${cargador.id}">${estado}</span>
                    <select id="input-estado-${cargador.id}" style="display:none; width:95%;">
                        <option value="disponible" ${estado === 'disponible' ? 'selected' : ''}>Disponible</option>
                        <option value="ocupado" ${estado === 'ocupado' ? 'selected' : ''}>Ocupado</option>
                        <option value="mantenimiento" ${estado === 'mantenimiento' ? 'selected' : ''}>Mantenimiento</option>
                        <option value="fuera_de_servicio" ${estado === 'fuera_de_servicio' ? 'selected' : ''}>Fuera de servicio</option>
                    </select>
                </td>
                <td>
                    <span id="conectores-${cargador.id}" style="font-size:0.85em;">${conectores || '-'}</span>
                    <div id="input-conectores-${cargador.id}" style="display:none;">
                        <label style="display:block; font-size:0.8em;"><input type="checkbox" value="Tipo 1" ${conectores.includes('Tipo 1') ? 'checked' : ''}> Tipo 1</label>
                        <label style="display:block; font-size:0.8em;"><input type="checkbox" value="Tipo 2" ${conectores.includes('Tipo 2') ? 'checked' : ''}> Tipo 2</label>
                        <label style="display:block; font-size:0.8em;"><input type="checkbox" value="CCS Combo 1" ${conectores.includes('CCS Combo 1') ? 'checked' : ''}> CCS 1</label>
                        <label style="display:block; font-size:0.8em;"><input type="checkbox" value="CCS Combo 2" ${conectores.includes('CCS Combo 2') ? 'checked' : ''}> CCS 2</label>
                        <label style="display:block; font-size:0.8em;"><input type="checkbox" value="CHAdeMO" ${conectores.includes('CHAdeMO') ? 'checked' : ''}> CHAdeMO</label>
                        <label style="display:block; font-size:0.8em;"><input type="checkbox" value="Tesla (NACS)" ${conectores.includes('Tesla (NACS)') ? 'checked' : ''}> Tesla</label>
                        <label style="display:block; font-size:0.8em;"><input type="checkbox" value="GB/T" ${conectores.includes('GB/T') ? 'checked' : ''}> GB/T</label>
                    </div>
                </td>
                <td style="font-size:0.8em;">
                    ${parseFloat(cargador.latitud).toFixed(4)}, ${parseFloat(cargador.longitud).toFixed(4)}
                    <br><button class="btn-ver-ubicacion" onclick="centrarEnCargador(${cargador.id})">📍 Ver</button>
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
                
                // Guardar en la base de datos usando la API de cargadores
                fetch('../api/cargadores.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        accion: 'agregar',
                        nombre: nombre,
                        latitud: ubicacionTemporal.lat(),
                        longitud: ubicacionTemporal.lng(),
                        descripcion: descripcion,
                        tipo: tipo,
                        estado: estado,
                        potencia_kw: potencia,
                        conectores: conectores
                    })
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
    });

    // Función para mostrar mensajes tipo toast abajo a la derecha
    window.mostrarMensaje = function(texto, tipo) {
        let toast = document.createElement('div');
        toast.className = 'mensaje-toast ' + (tipo === 'exito' ? 'exito' : 'error');
        toast.textContent = texto;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 400);
        }, 2500);
    }

    // Función para eliminar cargadores
    window.eliminarCargador = function(id) {
        if (confirm("¿Eliminar este cargador?")) {
            fetch('../api/cargadores.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ accion: 'eliminar', id: id })
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
        
        // Primero obtenemos el cargador actual para mantener lat/lon
        fetch('../api/cargadores.php')
            .then(res => res.json())
            .then(cargadores => {
                const cargadorActual = cargadores.find(c => c.id == id);
                if (!cargadorActual) {
                    mostrarMensaje('Cargador no encontrado', 'error');
                    return;
                }
                
                // Ahora hacemos la modificación
                fetch('../api/cargadores.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        accion: 'modificar',
                        id: id,
                        nombre: nombre,
                        latitud: cargadorActual.latitud,
                        longitud: cargadorActual.longitud,
                        descripcion: descripcion,
                        tipo: tipo,
                        estado: estado,
                        potencia_kw: potencia,
                        conectores: conectores
                    })
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
            });
    }

    // Menú desplegable usuario
    document.addEventListener("DOMContentLoaded", function() {
        const toggle = document.getElementById('menuToggle');
        const menu = document.getElementById('menuList');
        if(toggle && menu){
            toggle.addEventListener('click', function(e){
                e.stopPropagation();
                menu.classList.toggle('show');
                this.setAttribute('aria-expanded', menu.classList.contains('show'));
            });
            document.addEventListener('click', function(){
                menu.classList.remove('show');
                toggle.setAttribute('aria-expanded', 'false');
            });
            document.addEventListener('keydown', function(e){
                if(e.key === 'Escape'){
                    menu.classList.remove('show');
                    toggle.setAttribute('aria-expanded', 'false');
                    toggle.focus();
                }
            });
        }
    });

    // Menú usuario con icono y saludo
    document.querySelector('.usuario-trigger').addEventListener('click', function(e) {
        e.stopPropagation();
        document.querySelector('.usuario-menu').classList.toggle('activo');
    });
    document.addEventListener('click', function() {
        document.querySelector('.usuario-menu').classList.remove('activo');
    });
    </script>
    <!-- Cargar Google Maps después de definir initMap -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCcstapgk7BG-qavJNSKsSWIeYCv_h0wXU&callback=initMap" async defer></script>
</body>
</html>