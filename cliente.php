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
    <link rel="stylesheet" href="styles/cliente.css">
</head>

<body>
    <button class="btn-cerrar-sesion" onclick="window.location.href='logout.php'">Cerrar sesión</button>
    <div class="container">
        <h1>Bienvenido</h1>
        <p>Has iniciado sesión como cliente.<br>¡Disfruta de tu experiencia!</p>

        <!-- Formulario para agregar un auto -->
        <h2 style="margin-top:40px;">Agregar Auto</h2>
        <form id="formAuto" style="margin-bottom:40px; display: flex; flex-direction: column; gap: 0;">
            <label for="modelo">Modelo:</label>
            <input type="text" id="modelo" name="modelo" required>

            <label for="marca">Marca:</label>
            <input type="text" id="marca" name="marca" required>

            <label for="conector">Tipo de Conector:</label>
            <input type="text" id="conector" name="conector" required>

            <label for="autonomia">Autonomía (km):</label>
            <input type="number" id="autonomia" name="autonomia" min="0" required>

            <label for="anio">Año de Fabricación:</label>
            <input type="number" id="anio" name="anio" min="1900" max="2099" required>

            <button type="submit" style="margin-top:24px;">Agregar Auto</button>
        </form>

        <!-- Listado de autos -->
        <h2 style="margin-top:40px;">Mis Autos</h2>
        <div id="listado_autos"></div>
        <!-- Mensaje de éxito/error SOLO ABAJO -->
        <div id="mensaje" style="margin-top:20px;"></div>

        <!-- Planificación de viajes -->
        <h2 style="margin-top:40px;">Planificar Viaje</h2>
        <form id="formViaje" style="margin-bottom:40px; display: flex; flex-direction: column; gap: 0;">
            <label for="origen">Origen:</label>
            <input type="text" id="origen" name="origen" required>

            <label for="destino">Destino:</label>
            <input type="text" id="destino" name="destino" required>

            <label for="fecha">Fecha y hora:</label>
            <input type="datetime-local" id="fecha" name="fecha" required>

            <label for="distancia_km">Distancia (km):</label>
            <input type="number" id="distancia_km" name="distancia_km" min="1" required>

            <label for="observaciones">Observaciones:</label>
            <input type="text" id="observaciones" name="observaciones">

            <button type="submit" style="margin-top:24px;">Guardar Viaje</button>
        </form>
        <div id="mensajeViaje" style="margin-bottom:20px;"></div>

        <!-- Historial de viajes -->
        <h2>Historial de Viajes</h2>
        <table id="tablaViajes" style="width:100%;margin-bottom:40px;">
            <thead>
                <tr>
                    <th>Origen</th>
                    <th>Destino</th>
                    <th>Fecha</th>
                    <th>Distancia (km)</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <script>
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
            fetch('api/cliente.php?listar=1')
            .then(res => res.json())
            .then(autos => {
                // Si la API devuelve autos, mostrar tabla, si no, mostrar mensaje
                if (Array.isArray(autos) && autos.length > 0 && autos[0].modelo !== undefined) {
                    let html = "<table><tr><th>ID</th><th>Modelo</th><th>Marca</th><th>Tipo de Conector</th><th>Autonomía (km)</th><th>Año</th><th>Acciones</th></tr>";
                    autos.forEach(auto => {
                        html += `<tr data-id="${auto.id}">
                            <td>${auto.id}</td>
                            <td class="editable" data-campo="modelo">${auto.modelo}</td>
                            <td class="editable" data-campo="marca">${auto.marca}</td>
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
            // Aquí deberías tener un endpoint en la API para agregar autos, por ejemplo: api/auto.php
            // Este ejemplo solo muestra cómo sería la llamada:
            fetch('api/auto.php', {
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
                    modelo: inputs[0].value,
                    marca: inputs[1].value,
                    conector: inputs[2].value,
                    autonomia: inputs[3].value,
                    anio: inputs[4].value
                };
                fetch('api/auto.php', {
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
                fetch('api/auto.php', {
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

        // --- VIAJES ---
        document.getElementById('formViaje').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('accion', 'agregar');
            fetch('controlador/ViajeControlador.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById('mensajeViaje').textContent = data.mensaje;
                listarViajes();
                this.reset();
            });
        });

        function listarViajes() {
            fetch('controlador/ViajeControlador.php?accion=listar')
                .then(res => res.json())
                .then(data => {
                    const tbody = document.querySelector('#tablaViajes tbody');
                    tbody.innerHTML = '';
                    data.forEach(viaje => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${viaje.origen}</td>
                            <td>${viaje.destino}</td>
                            <td>${viaje.fecha}</td>
                            <td>${viaje.distancia_km}</td>
                            <td>${viaje.observaciones || ''}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                });
        }
        listarViajes();

        // Cargar listado al iniciar
        window.onload = cargarListado;
    </script>
</body>

</html>