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
    <!-- Botón desplegable de idioma -->
    <div class="language-selector-top closed">
        <button class="lang-btn" onclick="toggleLangMenu()">
            <span class="flag">🌐</span>
            <span id="currentLang">ES</span>
            <span class="arrow">▼</span>
        </button>
        <div id="langMenu" class="lang-menu hidden">
            <button class="lang-option" onclick="changeLang('es')">
                <span class="flag">🇪🇸</span><span>Español</span>
            </button>
            <button class="lang-option" onclick="changeLang('en')">
                <span class="flag">🇺🇸</span><span>English</span>
            </button>
        </div>
    </div>

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
        <!-- Menú usuario -->
        <div class="usuario-menu">
            <div class="usuario-trigger">
                <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Usuario" class="icono-usuario">
                <span class="saludo">Hola, <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                <span class="flecha">&#9660;</span>
            </div>
            <div class="usuario-dropdown">
                <a href="logout.php" id="lnkLogout">Cerrar sesión</a>
            </div>
        </div>

        <!-- Tab Usuarios -->
        <div class="tab-content" id="tab-usuarios">
            <h1 id="tituloUsuarios">Gestión de Usuarios</h1>
            <form id="formulario">
                <input type="text" id="nombre" placeholder="Nombre de usuario" required>
                <input type="email" id="correo" placeholder="Correo electrónico" required>
                <input type="password" id="password" placeholder="Contraseña" required>
                <select id="tipo_usuario" required>
                    <option value="cliente">Cliente</option>
                    <option value="admin">Admin</option>
                    <option value="cargador">Cargador</option>
                </select>
                <button type="submit" id="btnAgregarUsuario">Agregar</button>
            </form>
            <button id="btn-listar">Listar Usuarios</button>
            <ul id="resultado"></ul>
        </div>

        <!-- Tab Autos -->
        <div class="tab-content" id="tab-autos" style="display:none;">
            <h1 id="tituloAutos">Gestión de Autos de Clientes</h1>
            <form id="formAgregarAuto" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:10px;">
                <select id="nuevoAutoUsuario" required style="padding:8px; border-radius:4px; border:1px solid #cbd5e1;">
                    <option id="optSelUsuario" value="">Seleccionar Usuario</option>
                </select>
                <input type="text" id="nuevoAutoMarca" placeholder="Marca" required style="padding:8px; border-radius:4px; border:1px solid #cbd5e1;">
                <input type="text" id="nuevoAutoModelo" placeholder="Modelo" required style="padding:8px; border-radius:4px; border:1px solid #cbd5e1;">
                <select id="nuevoAutoConector" required style="padding:8px; border-radius:4px; border:1px solid #cbd5e1;">
                    <option id="optTipoConector" value="">Tipo de conector</option>
                    <option value="Tipo 1" id="optC1">Tipo 1 (SAE J1772)</option>
                    <option value="Tipo 2" id="optC2">Tipo 2 (Mennekes)</option>
                    <option value="CCS Combo 1" id="optC3">CCS Combo 1</option>
                    <option value="CCS Combo 2" id="optC4">CCS Combo 2</option>
                    <option value="CHAdeMO" id="optC5">CHAdeMO</option>
                    <option value="Tesla (NACS)" id="optC6">Tesla (NACS)</option>
                    <option value="GB/T" id="optC7">GB/T</option>
                </select>
                <input type="number" id="nuevoAutoAutonomia" placeholder="Autonomía (km)" required style="padding:8px; border-radius:4px; border:1px solid #cbd5e1;">
                <input type="number" id="nuevoAutoAnio" placeholder="Año" required style="padding:8px; border-radius:4px; border:1px solid #cbd5e1;">
                <button id="btnAgregarAuto" type="submit" style="background:#4CAF50; color:#fff; border:none; padding:8px 16px; border-radius:4px; cursor:pointer; font-weight:bold;">Agregar Auto</button>
            </form>
            <div id="autosToolbar" style="display:flex; justify-content:flex-end; align-items:center; gap:8px; margin:10px 0 6px 0;">
                <label id="labelOrdenAutos" for="ordenAutos" style="font-size:0.95em; color:#555;">Orden:</label>
                <select id="ordenAutos" style="padding:6px 10px; border-radius:8px; border:1px solid #cbd5e1; background:#fff; color:#333;">
                    <option id="optOrderAscAutos" value="asc" selected>ID ascendente (1 → N)</option>
                    <option id="optOrderDescAutos" value="desc">ID descendente (N → 1)</option>
                </select>
            </div>
            <div id="listaAutos" style="margin-top: 20px;"></div>
        </div>

        <!-- Tab Cargadores -->
        <div class="tab-content" id="tab-cargadores" style="display:none;">
            <h1 id="tituloCargadores">Gestión de Cargadores</h1>
            <p id="subtituloCargadores" style="color: #64b5f6; text-shadow: 1px 1px 4px #222;">Administra los puntos de carga eléctrica</p>
            <h2 id="h2Mapa" style="margin-bottom: 10px; color:#1976d2; margin-top:40px;">Mapa de cargadores</h2>
            <div id="map" style="width:100%; min-width:300px; height:340px; margin-bottom: 20px; border-radius: 16px; box-shadow: 0 4px 24px 0 rgba(31, 38, 135, 0.13);"></div>
            <h2 id="h2Agregar" style="margin-top:40px;">Agregar Cargador</h2>
            <p id="pInstruccion" style="font-size:0.9em; color:#666; margin-bottom:12px;">Haz clic en el mapa para seleccionar la ubicación del cargador</p>
            <form id="formCargador" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; width:100%; margin-bottom:18px;">
                <input type="text" id="nombreCargador" placeholder="Nombre del cargador" required style="grid-column: 1 / -1;">
                <input type="text" id="descripcionCargador" placeholder="Descripción (opcional)" style="grid-column: 1 / -1;">
                <select id="tipoCargador" required>
                    <option id="optTipoCargador" value="">Tipo de cargador</option>
                    <option id="optACSlow" value="AC Lento">AC Lento (3-7 kW)</option>
                    <option id="optACFast" value="AC Rápido">AC Rápido (7-22 kW)</option>
                    <option id="optDCFast" value="DC Rápido">DC Rápido (50+ kW)</option>
                    <option id="optDCUltra" value="DC Ultra Rápido">DC Ultra Rápido (150+ kW)</option>
                </select>
                <input type="number" id="potenciaCargador" placeholder="Potencia (kW)" min="0" step="0.1" required>
                <select id="estadoCargador" required style="grid-column: 1 / -1;">
                    <option id="optDisp" value="disponible">Disponible</option>
                    <option id="optOcup" value="ocupado">Ocupado</option>
                    <option id="optMant" value="mantenimiento">En mantenimiento</option>
                    <option id="optFds" value="fuera_de_servicio">Fuera de servicio</option>
                </select>
                <div style="grid-column: 1 / -1;">
                    <label id="labelConectores" style="display:block; margin-bottom:8px; font-weight:600; color:#1976d2;">Tipos de conectores disponibles:</label>
                    <div style="display:grid; grid-template-columns: repeat(2, 1fr); gap:8px;">
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                            <input type="checkbox" name="conectores" value="Tipo 1" class="conector-checkbox">
                            <span id="lblTipo1">Tipo 1 (SAE J1772)</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                            <input type="checkbox" name="conectores" value="Tipo 2" class="conector-checkbox">
                            <span id="lblTipo2">Tipo 2 (Mennekes)</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                            <input type="checkbox" name="conectores" value="CCS Combo 1" class="conector-checkbox">
                            <span id="lblCCS1">CCS Combo 1</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                            <input type="checkbox" name="conectores" value="CCS Combo 2" class="conector-checkbox">
                            <span id="lblCCS2">CCS Combo 2</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                            <input type="checkbox" name="conectores" value="CHAdeMO" class="conector-checkbox">
                            <span id="lblCHA">CHAdeMO</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                            <input type="checkbox" name="conectores" value="Tesla (NACS)" class="conector-checkbox">
                            <span id="lblTesla">Tesla (NACS)</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                            <input type="checkbox" name="conectores" value="GB/T" class="conector-checkbox">
                            <span id="lblGBT">GB/T</span>
                        </label>
                    </div>
                </div>
                <button id="btnAddCargador" type="submit" disabled style="grid-column: 1 / -1; min-width:140px;">Agregar cargador</button>
                <span id="ubicacionSeleccionada" style="grid-column: 1 / -1; font-size: 0.9em; color: #555;"></span>
            </form>
            <h2 id="h2ListadoCargadores" style="margin-top:40px;">Listado de Cargadores</h2>
            <div id="tablaCargadores"></div>
        </div>
    </div>

    <script>
    // ===== IDIOMA =====
    function toggleLangMenu() {
        const menu = document.getElementById('langMenu');
        const selector = document.querySelector('.language-selector-top');
        menu.classList.toggle('hidden');
        selector.classList.toggle('closed', menu.classList.contains('hidden'));
    }
    function isEN() { return document.getElementById('currentLang')?.textContent === 'EN'; }
    function tr(es, en) { return isEN() ? en : es; }

    function changeLang(lang) {
        document.getElementById('currentLang').textContent = lang === 'en' ? 'EN' : 'ES';
        const setText = (sel, txt) => { const el = document.querySelector(sel); if (el) el.textContent = txt; };
        const setPh   = (sel, txt) => { const el = document.querySelector(sel); if (el) el.setAttribute('placeholder', txt); };

        if (lang === 'en') {
            const labels = document.querySelectorAll('.sidebar-text');
            if (labels.length >= 4) { labels[0].textContent='Users'; labels[1].textContent='Cars'; labels[2].textContent='Chargers'; labels[3].textContent='Logout'; }
            const saludo = document.querySelector('.saludo'); if (saludo) saludo.textContent = saludo.textContent.replace(/^Hola,/, 'Hello,');
            const lnk = document.getElementById('lnkLogout'); if (lnk) lnk.textContent = 'Log out';

            // Usuarios
            setText('#tituloUsuarios','User Management');
            setPh('#nombre','Username');
            setPh('#correo','Email');
            setPh('#password','Password');
            const tipoSel = document.getElementById('tipo_usuario');
            if (tipoSel) { tipoSel.options[0].text='Client'; tipoSel.options[1].text='Admin'; tipoSel.options[2].text='Charger'; }
            const btnForm = document.querySelector('#formulario button[type="submit"]'); if (btnForm) btnForm.textContent = 'Add';
            setText('#btn-listar','List Users');

            // Autos
            setText('#tituloAutos','Customer Vehicles Management');
            document.getElementById('optSelUsuario').textContent = 'Select User';
            setPh('#nuevoAutoMarca','Brand');
            setPh('#nuevoAutoModelo','Model');
            document.getElementById('optTipoConector').textContent = 'Connector type';
            document.getElementById('optC1').textContent = 'Type 1 (SAE J1772)';
            document.getElementById('optC2').textContent = 'Type 2 (Mennekes)';
            document.getElementById('optC3').textContent = 'CCS Combo 1';
            document.getElementById('optC4').textContent = 'CCS Combo 2';
            document.getElementById('optC5').textContent = 'CHAdeMO';
            document.getElementById('optC6').textContent = 'Tesla (NACS)';
            document.getElementById('optC7').textContent = 'GB/T';
            setPh('#nuevoAutoAutonomia','Range (km)');
            setPh('#nuevoAutoAnio','Year');
            setText('#btnAgregarAuto','Add Vehicle');
            setText('#labelOrdenAutos','Order:');
            const optAsc = document.getElementById('optOrderAscAutos'); if (optAsc) optAsc.textContent = 'ID ascending (1 → N)';
            const optDesc = document.getElementById('optOrderDescAutos'); if (optDesc) optDesc.textContent = 'ID descending (N → 1)';

            // Cargadores
            setText('#tituloCargadores','Charger Management');
            setText('#subtituloCargadores','Manage the electric charging points');
            setText('#h2Mapa','Chargers Map');
            setText('#h2Agregar','Add Charger');
            setText('#pInstruccion','Click on the map to select the charger location');
            setPh('#nombreCargador','Charger name');
            setPh('#descripcionCargador','Description (optional)');
            document.getElementById('optTipoCargador').textContent = 'Charger type';
            document.getElementById('optACSlow').textContent = 'AC Slow (3-7 kW)';
            document.getElementById('optACFast').textContent = 'AC Fast (7-22 kW)';
            document.getElementById('optDCFast').textContent = 'DC Fast (50+ kW)';
            document.getElementById('optDCUltra').textContent = 'DC Ultra Fast (150+ kW)';
            setPh('#potenciaCargador','Power (kW)');
            document.getElementById('optDisp').textContent = 'Available';
            document.getElementById('optOcup').textContent = 'Occupied';
            document.getElementById('optMant').textContent = 'Maintenance';
            document.getElementById('optFds').textContent  = 'Out of service';
            setText('#labelConectores','Available connector types:');
            document.getElementById('lblTipo1').textContent = 'Type 1 (SAE J1772)';
            document.getElementById('lblTipo2').textContent = 'Type 2 (Mennekes)';
            document.getElementById('lblCCS1').textContent = 'CCS Combo 1';
            document.getElementById('lblCCS2').textContent = 'CCS Combo 2';
            document.getElementById('lblCHA').textContent   = 'CHAdeMO';
            document.getElementById('lblTesla').textContent = 'Tesla (NACS)';
            document.getElementById('lblGBT').textContent   = 'GB/T';
            setText('#btnAddCargador','Add charger');
            setText('#h2ListadoCargadores','Charger List');
        } else {
            document.getElementById('currentLang').textContent = 'ES';
            const labels = document.querySelectorAll('.sidebar-text');
            if (labels.length >= 4) { labels[0].textContent='Usuarios'; labels[1].textContent='Autos'; labels[2].textContent='Cargadores'; labels[3].textContent='Salir'; }
            const saludo = document.querySelector('.saludo'); if (saludo) saludo.textContent = saludo.textContent.replace(/^Hello,/, 'Hola,');
            const lnk = document.getElementById('lnkLogout'); if (lnk) lnk.textContent = 'Cerrar sesión';

            // Usuarios
            setText('#tituloUsuarios','Gestión de Usuarios');
            setPh('#nombre','Nombre de usuario');
            setPh('#correo','Correo electrónico');
            setPh('#password','Contraseña');
            const tipoSel = document.getElementById('tipo_usuario');
            if (tipoSel) { tipoSel.options[0].text='Cliente'; tipoSel.options[1].text='Admin'; tipoSel.options[2].text='Cargador'; }
            const btnForm = document.querySelector('#formulario button[type="submit"]'); if (btnForm) btnForm.textContent = 'Agregar';
            setText('#btn-listar','Listar Usuarios');

            // Autos
            setText('#tituloAutos','Gestión de Autos de Clientes');
            document.getElementById('optSelUsuario').textContent = 'Seleccionar Usuario';
            setPh('#nuevoAutoMarca','Marca');
            setPh('#nuevoAutoModelo','Modelo');
            document.getElementById('optTipoConector').textContent = 'Tipo de conector';
            setPh('#nuevoAutoAutonomia','Autonomía (km)');
            setPh('#nuevoAutoAnio','Año');
            setText('#btnAgregarAuto','Agregar Auto');
            setText('#labelOrdenAutos','Orden:');
            const optAsc = document.getElementById('optOrderAscAutos'); if (optAsc) optAsc.textContent = 'ID ascendente (1 → N)';
            const optDesc = document.getElementById('optOrderDescAutos'); if (optDesc) optDesc.textContent = 'ID descendente (N → 1)';

            // Cargadores
            setText('#tituloCargadores','Gestión de Cargadores');
            setText('#subtituloCargadores','Administra los puntos de carga eléctrica');
            setText('#h2Mapa','Mapa de cargadores');
            setText('#h2Agregar','Agregar Cargador');
            setText('#pInstruccion','Haz clic en el mapa para seleccionar la ubicación del cargador');
            setPh('#nombreCargador','Nombre del cargador');
            setPh('#descripcionCargador','Descripción (opcional)');
            document.getElementById('optTipoCargador').textContent = 'Tipo de cargador';
            document.getElementById('optACSlow').textContent = 'AC Lento (3-7 kW)';
            document.getElementById('optACFast').textContent = 'AC Rápido (7-22 kW)';
            document.getElementById('optDCFast').textContent = 'DC Rápido (50+ kW)';
            document.getElementById('optDCUltra').textContent = 'DC Ultra Rápido (150+ kW)';
            setPh('#potenciaCargador','Potencia (kW)');
            document.getElementById('optDisp').textContent = 'Disponible';
            document.getElementById('optOcup').textContent = 'Ocupado';
            document.getElementById('optMant').textContent = 'En mantenimiento';
            document.getElementById('optFds').textContent  = 'Fuera de servicio';
            setText('#labelConectores','Tipos de conectores disponibles:');
            document.getElementById('lblTipo1').textContent = 'Tipo 1 (SAE J1772)';
            document.getElementById('lblTipo2').textContent = 'Tipo 2 (Mennekes)';
            document.getElementById('lblCCS1').textContent = 'CCS Combo 1';
            document.getElementById('lblCCS2').textContent = 'CCS Combo 2';
            document.getElementById('lblCHA').textContent   = 'CHAdeMO';
            document.getElementById('lblTesla').textContent = 'Tesla (NACS)';
            document.getElementById('lblGBT').textContent   = 'GB/T';
            setText('#btnAddCargador','Agregar cargador');
            setText('#h2ListadoCargadores','Listado de Cargadores');
        }
        // Refrescar tablas para que aparezcan traducidas
        try { listar(); } catch(e) {}
        try { listarAutos(); } catch(e) {}
        try {
            fetch('../api/cargadores.php').then(r=>r.json()).then(cs=>mostrarListaCargadores(cs));
        } catch(e){}
        toggleLangMenu();
    }

    document.addEventListener('click', function(e) {
        const selector = document.querySelector('.language-selector-top');
        const menu = document.getElementById('langMenu');
        if (selector && !selector.contains(e.target)) {
            menu.classList.add('hidden');
            selector.classList.add('closed');
        }
    });

    // ===== NAVEGACIÓN SIDEBAR =====
    document.querySelectorAll('.sidebar-item').forEach(item => {
        item.addEventListener('click', function() {
            const tab = this.dataset.tab;
            if (!tab) return;
            document.querySelectorAll('.sidebar-item').forEach(i => i.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('.tab-content').forEach(tc => tc.style.display = 'none');
            const target = document.getElementById('tab-' + tab);
            if (target) target.style.display = 'block';
            if (tab === 'cargadores') setTimeout(() => { try { google.maps.event.trigger(map, 'resize'); } catch(e){} }, 200);
            if (tab === 'autos') { listarAutos(); cargarUsuariosParaAutos(); }
        });
    });

    document.querySelector('.usuario-trigger').addEventListener('click', e => {
        e.stopPropagation();
        document.querySelector('.usuario-menu').classList.toggle('activo');
    });
    document.addEventListener('click', () => document.querySelector('.usuario-menu')?.classList.remove('activo'));

    // ===== GOOGLE MAPS =====
    let map, marcadores = {}, ubicacionTemporal = null;
    function initMap() {
        map = new google.maps.Map(document.getElementById("map"), { zoom: 13, center: { lat: -34.7176, lng: -55.9586 } });
        fetch('../api/cargadores.php').then(res => res.json()).then(cargadores => {
            mostrarListaCargadores(cargadores);
            cargadores.forEach(c => agregarCargador(c.id, c.nombre, {lat: parseFloat(c.latitud), lng: parseFloat(c.longitud)}));
        });
        map.addListener("click", e => {
            ubicacionTemporal = e.latLng;
            document.getElementById("ubicacionSeleccionada").textContent = "Ubicación seleccionada";
            document.querySelector("#formCargador button[type='submit']").disabled = false;
            if (marcadores['temporal']) marcadores['temporal'].setMap(null);
            marcadores['temporal'] = new google.maps.Marker({ position: ubicacionTemporal, map, icon: "https://maps.google.com/mapfiles/ms/icons/yellow-dot.png" });
        });
    }
    function agregarCargador(id, nombre, latLng) {
        if (marcadores['temporal']) { marcadores['temporal'].setMap(null); delete marcadores['temporal']; }
        const marcador = new google.maps.Marker({ position: latLng, map, title: nombre, icon: "https://maps.google.com/mapfiles/ms/icons/green-dot.png" });
        marcador.addListener('click', () => new google.maps.InfoWindow({ content: `<strong>${nombre}</strong>` }).open(map, marcador));
        marcadores[id] = marcador;
    }
    function mostrarMensaje(texto, tipo) {
        let toast = document.createElement('div');
        toast.className = 'mensaje-toast ' + (tipo === 'exito' ? 'exito' : 'error');
        toast.textContent = texto;
        document.body.appendChild(toast);
        setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 400); }, 2500);
    }

    // ===== USUARIOS (UNA SOLA VEZ) =====
    function listar() {
        fetch("../api/admin.php?listar_usuarios=1").then(res => res.json()).then(data => {
            let html = "";
            data.forEach(usuario => {
                html += `<li id="usuario-${usuario.usuario}"><div class="usuario-info">
                    <span id="nombre-${usuario.usuario}"><b>${tr('Usuario','User')}:</b> ${usuario.usuario}</span>
                    <span id="correo-${usuario.usuario}"><b>${tr('Correo','Email')}:</b> ${usuario.correo}</span>
                    <span id="tipo-${usuario.usuario}"><b>${tr('Tipo','Type')}:</b> ${usuario.tipo_usuario}</span>
                    <input type="text" id="input-${usuario.usuario}" value="${usuario.usuario}" style="display:none;">
                    <input type="email" id="input-correo-${usuario.usuario}" value="${usuario.correo}" style="display:none;">
                    <input type="password" id="input-pass-${usuario.usuario}" placeholder="${tr('Nueva contraseña','New password')}" style="display:none;">
                    <select id="input-tipo-${usuario.usuario}" style="display:none;">
                        <option value="cliente" ${usuario.tipo_usuario === 'cliente' ? 'selected' : ''}>${tr('Cliente','Client')}</option>
                        <option value="admin" ${usuario.tipo_usuario === 'admin' ? 'selected' : ''}>Admin</option>
                        <option value="cargador" ${usuario.tipo_usuario === 'cargador' ? 'selected' : ''}>${tr('Cargador','Charger')}</option>
                    </select></div>
                    <button class="btn-editar" onclick="editar('${usuario.usuario}')" id="btn-editar-${usuario.usuario}">${tr('Editar','Edit')}</button>
                    <button class="btn-guardar" onclick="guardar('${usuario.usuario}')" style="display:none;" id="guardar-${usuario.usuario}">${tr('Guardar','Save')}</button>
                    <button class="btn-cancelar" onclick="cancelar('${usuario.usuario}')" style="display:none;" id="cancelar-${usuario.usuario}">${tr('Cancelar','Cancel')}</button>
                    <button class="btn-eliminar" onclick="eliminar('${usuario.usuario}')" id="btn-eliminar-${usuario.usuario}">${tr('Eliminar','Delete')}</button>
                </li>`;
            });
            document.getElementById("resultado").innerHTML = html;
        });
    }
    document.getElementById("formulario").addEventListener("submit", e => {
        e.preventDefault();
        const body = `agregar_usuario=1&username=${encodeURIComponent(document.getElementById("nombre").value)}&correo=${encodeURIComponent(document.getElementById("correo").value)}&password=${encodeURIComponent(document.getElementById("password").value)}&tipo_usuario=${encodeURIComponent(document.getElementById("tipo_usuario").value)}`;
        fetch("../api/admin.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body })
            .then(res => res.json()).then(data => {
                mostrarMensaje(data.mensaje, data.mensaje.toLowerCase().includes('error') ? 'error' : 'exito');
                document.getElementById("formulario").reset();
                listar();
            });
    });
    document.getElementById("btn-listar").addEventListener("click", listar);
    window.editar = nombre => {
        ['nombre','correo','tipo'].forEach(f => document.getElementById(`${f}-${nombre}`).style.display = 'none');
        ['input','input-correo','input-pass','input-tipo'].forEach(f => document.getElementById(`${f}-${nombre}`).style.display = 'inline-block');
        ['guardar','cancelar'].forEach(b => document.getElementById(`${b}-${nombre}`).style.display = 'inline-block');
        ['btn-editar','btn-eliminar'].forEach(b => document.getElementById(`${b}-${nombre}`).style.display = 'none');
    };
    window.guardar = nombre => {
        let body = `modificar_usuario=1&nombre=${encodeURIComponent(nombre)}&nuevoNombre=${encodeURIComponent(document.getElementById(`input-${nombre}`).value)}&nuevoCorreo=${encodeURIComponent(document.getElementById(`input-correo-${nombre}`).value)}&nuevoTipoUsuario=${encodeURIComponent(document.getElementById(`input-tipo-${nombre}`).value)}`;
        const pass = document.getElementById(`input-pass-${nombre}`).value; if (pass) body += `&nuevaPassword=${encodeURIComponent(pass)}`;
        fetch("../api/admin.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body })
            .then(res => res.json()).then(data => { mostrarMensaje(data.mensaje, data.mensaje.toLowerCase().includes('error') ? 'error' : 'exito'); listar(); });
    };

    // ===== AUTOS =====
    function listarAutos(orden) {
        const ordenSel = orden || (document.getElementById('ordenAutos') ? document.getElementById('ordenAutos').value : 'asc');
        const url = `../api/admin.php?listar_autos=1&orden=${encodeURIComponent(ordenSel)}&_=${Date.now()}`;
        fetch(url)
            .then(res => res.json())
            .then(autos => {
                const container = document.getElementById('listaAutos');
                if (!autos || autos.length === 0) {
                    container.innerHTML = `<p style="text-align:center; color:#666;">${tr('No hay autos registrados.','No vehicles registered.')}</p>`;
                    return;
                }

                let html = '<table style="width:100%; border-collapse: collapse; margin-top:10px;">';
                html += `<thead><tr style="background:#1976d2; color:#fff;">
                    <th style="padding:12px; text-align:left;">${tr('ID','ID')}</th>
                    <th style="padding:12px; text-align:left;">${tr('Usuario','User')}</th>
                    <th style="padding:12px; text-align:left;">${tr('Marca','Brand')}</th>
                    <th style="padding:12px; text-align:left;">${tr('Modelo','Model')}</th>
                    <th style="padding:12px; text-align:left;">${tr('Conector','Connector')}</th>
                    <th style="padding:12px; text-align:left;">${tr('Autonomía (km)','Range (km)')}</th>
                    <th style="padding:12px; text-align:left;">${tr('Año','Year')}</th>
                    <th style="padding:12px; text-align:center;">${tr('Acciones','Actions')}</th>
                </tr></thead><tbody>`;

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
                            <button onclick="editarAuto(${auto.id})" id="btn-editar-${auto.id}" style="background:#2196F3; color:#fff; border:none; padding:6px 12px; border-radius:4px; cursor:pointer; margin-right:5px;">${tr('Editar','Edit')}</button>
                            <button onclick="guardarAuto(${auto.id})" id="btn-guardar-${auto.id}" style="display:none; background:#4CAF50; color:#fff; border:none; padding:6px 12px; border-radius:4px; cursor:pointer; margin-right:5px;">${tr('Guardar','Save')}</button>
                            <button onclick="cancelarEditarAuto(${auto.id})" id="btn-cancelar-${auto.id}" style="display:none; background:#9E9E9E; color:#fff; border:none; padding:6px 12px; border-radius:4px; cursor:pointer; margin-right:5px;">${tr('Cancelar','Cancel')}</button>
                            <button onclick="eliminarAuto(${auto.id})" style="background:#f44336; color:#fff; border:none; padding:6px 12px; border-radius:4px; cursor:pointer;">${tr('Eliminar','Delete')}</button>
                        </td>
                    </tr>`;
                });

                html += '</tbody></table>';
                container.innerHTML = html;
            })
            .catch(err => {
                console.error('Error al cargar autos:', err);
                document.getElementById('listaAutos').innerHTML = `<p style="color:#f44336;">${tr('Error al cargar los autos.','Error loading vehicles.')}</p>`;
            });
    }
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
    document.getElementById('formAgregarAuto').addEventListener('submit', e => {
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
    document.getElementById('ordenAutos')?.addEventListener('change', e => listarAutos(e.target.value));

    // ===== CARGADORES ===== (REEMPLAZA la función mostrarListaCargadores)
    function mostrarListaCargadores(cargadores) {
        const tablaDiv = document.getElementById('tablaCargadores');
        if (!cargadores || cargadores.length === 0) {
            tablaDiv.innerHTML = `<p style="text-align:center; color:#666;">${tr('No hay cargadores registrados.','No chargers registered.')}</p>`;
            return;
        }
        
        let html = `<table style="width:100%; border-collapse: collapse; margin-top:10px;">
            <thead><tr style="background:#1976d2; color:#fff;">
                <th style="padding:12px; text-align:left;">${tr('ID','ID')}</th>
                <th style="padding:12px; text-align:left;">${tr('Nombre','Name')}</th>
                <th style="padding:12px; text-align:left;">${tr('Descripción','Description')}</th>
                <th style="padding:12px; text-align:left;">${tr('Tipo','Type')}</th>
                <th style="padding:12px; text-align:left;">${tr('Potencia (kW)','Power (kW)')}</th>
                <th style="padding:12px; text-align:left;">${tr('Estado','Status')}</th>
                <th style="padding:12px; text-align:left;">${tr('Conectores','Connectors')}</th>
                <th style="padding:12px; text-align:left;">${tr('Ubicación','Location')}</th>
                <th style="padding:12px; text-align:center;">${tr('Acciones','Actions')}</th>
            </tr></thead><tbody>`;
        
        cargadores.forEach(c => {
            html += `<tr style="border-bottom:1px solid #ddd;" data-id="${c.id}">
                <td style="padding:10px;">${c.id}</td>
                <td style="padding:10px;">
                    <span id="ch-nombre-${c.id}">${c.nombre}</span>
                    <input type="text" id="ch-input-nombre-${c.id}" value="${c.nombre}" style="display:none; width:95%; padding:5px;">
                </td>
                <td style="padding:10px;">
                    <span id="ch-descripcion-${c.id}">${c.descripcion || '-'}</span>
                    <input type="text" id="ch-input-descripcion-${c.id}" value="${c.descripcion || ''}" style="display:none; width:95%; padding:5px;">
                </td>
                <td style="padding:10px;">
                    <span id="ch-tipo-${c.id}">${c.tipo || '-'}</span>
                    <select id="ch-input-tipo-${c.id}" style="display:none; width:95%; padding:5px;">
                        <option value="">-</option>
                        <option value="AC Lento" ${c.tipo === 'AC Lento' ? 'selected' : ''}>${tr('AC Lento','AC Slow')}</option>
                        <option value="AC Rápido" ${c.tipo === 'AC Rápido' ? 'selected' : ''}>${tr('AC Rápido','AC Fast')}</option>
                        <option value="DC Rápido" ${c.tipo === 'DC Rápido' ? 'selected' : ''}>${tr('DC Rápido','DC Fast')}</option>
                        <option value="DC Ultra Rápido" ${c.tipo === 'DC Ultra Rápido' ? 'selected' : ''}>${tr('DC Ultra Rápido','DC Ultra Fast')}</option>
                    </select>
                </td>
                <td style="padding:10px;">
                    <span id="ch-potencia-${c.id}">${c.potencia_kw}</span>
                    <input type="number" id="ch-input-potencia-${c.id}" value="${c.potencia_kw}" min="0" step="0.1" style="display:none; width:80px; padding:5px;">
                </td>
                <td style="padding:10px;">
                    <span id="ch-estado-${c.id}">${c.estado === 'disponible' ? tr('Disponible','Available') : c.estado === 'ocupado' ? tr('Ocupado','Occupied') : c.estado === 'mantenimiento' ? tr('En mantenimiento','In maintenance') : tr('Fuera de servicio','Out of service')}</span>
                    <select id="ch-input-estado-${c.id}" style="display:none; width:95%; padding:5px;">
                        <option value="disponible" ${c.estado === 'disponible' ? 'selected' : ''}>${tr('Disponible','Available')}</option>
                        <option value="ocupado" ${c.estado === 'ocupado' ? 'selected' : ''}>${tr('Ocupado','Occupied')}</option>
                        <option value="mantenimiento" ${c.estado === 'mantenimiento' ? 'selected' : ''}>${tr('En mantenimiento','In maintenance')}</option>
                        <option value="fuera_de_servicio" ${c.estado === 'fuera_de_servicio' ? 'selected' : ''}>${tr('Fuera de servicio','Out of service')}</option>
                    </select>
                </td>
                <td style="padding:10px; font-size:0.85em;">
                    <span id="ch-conectores-${c.id}">${c.conectores || '-'}</span>
                    <div id="ch-input-conectores-${c.id}" style="display:none;">
                        <label style="display:block; font-size:0.8em;"><input type="checkbox" value="Tipo 1" ${(c.conectores || '').includes('Tipo 1') ? 'checked' : ''}> Tipo 1</label>
                        <label style="display:block; font-size:0.8em;"><input type="checkbox" value="Tipo 2" ${(c.conectores || '').includes('Tipo 2') ? 'checked' : ''}> Tipo 2</label>
                        <label style="display:block; font-size:0.8em;"><input type="checkbox" value="CCS Combo 1" ${(c.conectores || '').includes('CCS Combo 1') ? 'checked' : ''}> CCS 1</label>
                        <label style="display:block; font-size:0.8em;"><input type="checkbox" value="CCS Combo 2" ${(c.conectores || '').includes('CCS Combo 2') ? 'checked' : ''}> CCS 2</label>
                        <label style="display:block; font-size:0.8em;"><input type="checkbox" value="CHAdeMO" ${(c.conectores || '').includes('CHAdeMO') ? 'checked' : ''}> CHAdeMO</label>
                        <label style="display:block; font-size:0.8em;"><input type="checkbox" value="Tesla (NACS)" ${(c.conectores || '').includes('Tesla (NACS)') ? 'checked' : ''}> Tesla</label>
                        <label style="display:block; font-size:0.8em;"><input type="checkbox" value="GB/T" ${(c.conectores || '').includes('GB/T') ? 'checked' : ''}> GB/T</label>
                    </div>
                </td>
                <td style="padding:10px; font-size:0.8em;">
                    ${c.latitud.toFixed(4)}, ${c.longitud.toFixed(4)}
                    <br><button onclick="centrarEnCargador(${c.id})" style="margin-top:4px; font-size:0.8em; padding:4px 8px; background:#4CAF50; color:#fff; border:none; border-radius:4px; cursor:pointer;">📍 ${tr('Ver','View')}</button>
                </td>
                <td style="padding:10px;">
                    <div style="display:flex; flex-direction:column; gap:4px;">
                        <button onclick="editarCargador(${c.id})" id="ch-btn-editar-${c.id}" style="background:#2196F3; color:#fff; border:none; padding:6px 12px; border-radius:4px; cursor:pointer;">${tr('Editar','Edit')}</button>
                        <button onclick="guardarCargador(${c.id})" id="ch-btn-guardar-${c.id}" style="display:none; background:#4CAF50; color:#fff; border:none; padding:6px 12px; border-radius:4px; cursor:pointer;">${tr('Guardar','Save')}</button>
                        <button onclick="cancelarEditarCargador(${c.id})" id="ch-btn-cancelar-${c.id}" style="display:none; background:#9E9E9E; color:#fff; border:none; padding:6px 12px; border-radius:4px; cursor:pointer;">${tr('Cancelar','Cancel')}</button>
                        <button onclick="eliminarCargador(${c.id})" id="ch-btn-eliminar-${c.id}" style="background:#f44336; color:#fff; border:none; padding:6px 12px; border-radius:4px; cursor:pointer;">${tr('Eliminar','Delete')}</button>
                    </div>
                </td>
            </tr>`;
        });
        
        html += '</tbody></table>';
        tablaDiv.innerHTML = html;
    }

    window.editarCargador = id => {
        ['ch-nombre','ch-descripcion','ch-tipo','ch-potencia','ch-estado','ch-conectores'].forEach(prefix => {
            const elem = document.getElementById(`${prefix}-${id}`);
            if (elem) elem.style.display = 'none';
        });
        ['ch-input-nombre','ch-input-descripcion','ch-input-tipo','ch-input-potencia','ch-input-estado','ch-input-conectores'].forEach(prefix => {
            const elem = document.getElementById(`${prefix}-${id}`);
            if (elem) elem.style.display = prefix === 'ch-input-potencia' ? 'inline-block' : 'block';
        });
        document.getElementById(`ch-btn-editar-${id}`).style.display = 'none';
        document.getElementById(`ch-btn-eliminar-${id}`).style.display = 'none';
        document.getElementById(`ch-btn-guardar-${id}`).style.display = 'inline-block';
        document.getElementById(`ch-btn-cancelar-${id}`).style.display = 'inline-block';
    };

    // AGREGAR después del formCargador (línea ~200 del HTML, después de </form>):
    document.getElementById('formCargador').addEventListener('submit', e => {
        e.preventDefault();
        if (!ubicacionTemporal) {
            mostrarMensaje('Selecciona una ubicación en el mapa', 'error');
            return;
        }
        const nombre = document.getElementById('nombreCargador').value;
        const descripcion = document.getElementById('descripcionCargador').value;
        const tipo = document.getElementById('tipoCargador').value;
        const potencia = document.getElementById('potenciaCargador').value;
        const estado = document.getElementById('estadoCargador').value;
        const checkboxes = document.querySelectorAll('.conector-checkbox:checked');
        const conectores = Array.from(checkboxes).map(cb => cb.value).join(', ');

        if (!conectores) {
            mostrarMensaje('Selecciona al menos un tipo de conector', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('agregar_cargador', '1');
        formData.append('nombre', nombre);
        formData.append('latitud', ubicacionTemporal.lat());
        formData.append('longitud', ubicacionTemporal.lng());
        formData.append('descripcion', descripcion);
        formData.append('tipo', tipo);
        formData.append('estado', estado);
        formData.append('potencia_kw', potencia);
        formData.append('conectores', conectores);

        fetch('../api/admin.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.mensaje && data.mensaje.toLowerCase().includes('éxito')) {
                    mostrarMensaje('Cargador agregado correctamente', 'exito');
                    document.getElementById('formCargador').reset();
                    document.getElementById("ubicacionSeleccionada").textContent = "";
                    document.querySelector("#formCargador button[type='submit']").disabled = true;
                    ubicacionTemporal = null;
                    if (marcadores['temporal']) { marcadores['temporal'].setMap(null); delete marcadores['temporal']; }
                    
                    // Recargar lista
                    fetch('../api/cargadores.php').then(r => r.json()).then(cargadores => {
                        mostrarListaCargadores(cargadores);
                        Object.values(marcadores).forEach(m => m.setMap(null));
                        marcadores = {};
                        cargadores.forEach(c => agregarCargador(c.id, c.nombre, {lat: parseFloat(c.latitud), lng: parseFloat(c.longitud)}));
                    });
                } else {
                    mostrarMensaje('Error al agregar: ' + (data.mensaje || data.error), 'error');
                }
            })
            .catch(() => mostrarMensaje('Error de conexión', 'error'));
    });

    window.cancelar = () => listar();
    window.eliminar = nombre => { if (confirm("¿Eliminar?")) fetch("../api/admin.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: `eliminar_usuario=1&nombre=${encodeURIComponent(nombre)}` }).then(res => res.json()).then(data => { mostrarMensaje(data.mensaje, data.mensaje.toLowerCase().includes('error') ? 'error' : 'exito'); listar(); }); };

    // ===== AUTOS (AGREGAR FUNCIONES FALTANTES) =====
    function opcionesConectorHTML(seleccionado) {
        const opciones = [
            { value: 'Tipo 1', label: tr('Tipo 1 (SAE J1772)','Type 1 (SAE J1772)') },
            { value: 'Tipo 2', label: tr('Tipo 2 (Mennekes)','Type 2 (Mennekes)') },
            { value: 'CCS Combo 1', label: 'CCS Combo 1' },
            { value: 'CCS Combo 2', label: 'CCS Combo 2' },
            { value: 'CHAdeMO', label: 'CHAdeMO' },
            { value: 'Tesla (NACS)', label: 'Tesla (NACS)' },
            { value: 'GB/T', label: 'GB/T' }
        ];
        return opciones.map(o => `<option value="${o.value}" ${o.value === seleccionado ? 'selected' : ''}>${o.label}</option>`).join('');
    }

    window.editarAuto = id => {
        ['marca','modelo','conector','autonomia','anio'].forEach(f => {
            const elem = document.getElementById(`${f}-${id}`);
            if (elem) elem.style.display = 'none';
        });
        ['input-marca','input-modelo','input-conector','input-autonomia','input-anio'].forEach(f => {
            const elem = document.getElementById(`${f}-${id}`);
            if (elem) elem.style.display = 'inline-block';
        });
        const btnEditar = document.getElementById(`btn-editar-${id}`);
        const btnGuardar = document.getElementById(`btn-guardar-${id}`);
        const btnCancelar = document.getElementById(`btn-cancelar-${id}`);
        if (btnEditar) btnEditar.style.display = 'none';
        if (btnGuardar) btnGuardar.style.display = 'inline-block';
        if (btnCancelar) btnCancelar.style.display = 'inline-block';
    };

    window.guardarAuto = id => {
        const marca = document.getElementById(`input-marca-${id}`)?.value;
        const modelo = document.getElementById(`input-modelo-${id}`)?.value;
        const conector = document.getElementById(`input-conector-${id}`)?.value;
        const autonomia = document.getElementById(`input-autonomia-${id}`)?.value;
        const anio = document.getElementById(`input-anio-${id}`)?.value;

        if (!marca || !modelo || !conector || !autonomia || !anio) {
            mostrarMensaje('Todos los campos son requeridos', 'error');
            return;
        }

        fetch('../api/admin.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                accion: 'modificar_auto',
                id: id,
                marca: marca,
                modelo: modelo,
                conector: conector,
                autonomia: parseInt(autonomia),
                anio: parseInt(anio)
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito) {
                mostrarMensaje('Auto actualizado correctamente', 'exito');
                listarAutos();
            } else {
                mostrarMensaje('Error al actualizar: ' + (data.mensaje || data.error || 'Error desconocido'), 'error');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            mostrarMensaje('Error de conexión al guardar auto', 'error');
        });
    };

    window.cancelarEditarAuto = () => listarAutos();

    window.eliminarAuto = id => {
        if (!confirm("¿Eliminar este auto?")) return;
        
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
                mostrarMensaje('Error al eliminar: ' + (data.mensaje || data.error || 'Error desconocido'), 'error');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            mostrarMensaje('Error de conexión al eliminar auto', 'error');
        });
    };

    // REEMPLAZA window.guardarCargador (línea ~735) con:
    window.guardarCargador = id => {
        const nombre = document.getElementById(`ch-input-nombre-${id}`)?.value;
        const descripcion = document.getElementById(`ch-input-descripcion-${id}`)?.value;
        const tipo = document.getElementById(`ch-input-tipo-${id}`)?.value;
        const potencia_kw = document.getElementById(`ch-input-potencia-${id}`)?.value;
        const estado = document.getElementById(`ch-input-estado-${id}`)?.value;
        const checkboxes = document.querySelectorAll(`#ch-input-conectores-${id} input[type="checkbox"]:checked`);
        const conectores = Array.from(checkboxes).map(cb => cb.value).join(', ');

        if (!nombre || !tipo || !potencia_kw || !estado) {
            mostrarMensaje('Completa todos los campos obligatorios', 'error');
            return;
        }

        if (!conectores) {
            mostrarMensaje('Selecciona al menos un conector', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('modificar_cargador', '1');
        formData.append('id', id);
        formData.append('nombre', nombre);
        formData.append('descripcion', descripcion);
        formData.append('tipo', tipo);
        formData.append('potencia_kw', potencia_kw);
        formData.append('estado', estado);
        formData.append('conectores', conectores);

        fetch('../api/admin.php', { method: 'POST', body: formData })
            .then(res => {
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                return res.json();
            })
            .then(data => {
                console.log('Respuesta servidor:', data);
                if (data.mensaje && data.mensaje.toLowerCase().includes('éxito')) {
                    mostrarMensaje('Cargador actualizado correctamente', 'exito');
                    fetch('../api/cargadores.php')
                        .then(r => r.json())
                        .then(cargadores => {
                            mostrarListaCargadores(cargadores);
                            Object.values(marcadores).forEach(m => m.setMap(null));
                            marcadores = {};
                            cargadores.forEach(c => agregarCargador(c.id, c.nombre, {lat: parseFloat(c.latitud), lng: parseFloat(c.longitud)}));
                        });
                } else {
                    mostrarMensaje('Error al actualizar: ' + (data.mensaje || data.error || JSON.stringify(data)), 'error');
                }
            })
            .catch(err => {
                console.error('Error completo:', err);
                mostrarMensaje('Error de conexión: ' + err.message, 'error');
            });
    };

    window.cancelarEditarCargador = id => {
        fetch('../api/cargadores.php').then(r => r.json()).then(cargadores => mostrarListaCargadores(cargadores));
    };

    window.eliminarCargador = id => {
        if (!confirm('¿Eliminar este cargador?')) return;
        const formData = new FormData();
        formData.append('eliminar_cargador', '1');
        formData.append('id', id);
        fetch('../api/admin.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.mensaje && data.mensaje.toLowerCase().includes('éxito')) {
                    mostrarMensaje('Cargador eliminado correctamente', 'exito');
                    if (marcadores[id]) { marcadores[id].setMap(null); delete marcadores[id]; }
                    fetch('../api/cargadores.php').then(r => r.json()).then(cargadores => mostrarListaCargadores(cargadores));
                } else {
                    mostrarMensaje('Error al eliminar: ' + (data.mensaje || data.error), 'error');
                }
            })
            .catch(() => mostrarMensaje('Error de conexión', 'error'));
    };

    window.centrarEnCargador = id => {
        const marcador = marcadores[id];
        if (marcador && map) {
            map.setCenter(marcador.getPosition());
            map.setZoom(16);
            google.maps.event.trigger(marcador, 'click');
        }
    };

    listar(); // Cargar usuarios al inicio
    </script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCcstapgk7BG-qavJNSKsSWIeYCv_h0wXU&callback=initMap&loading=async"></script>
</body>
</html>