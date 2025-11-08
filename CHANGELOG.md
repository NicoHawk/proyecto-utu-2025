# 📋 Changelog - Sistema de Gestión de Autos Eléctricos

## Versión 1.7.0 - 7 de Noviembre de 2025

### 🎯 Cambios Principales

#### 🧭 Barra rápida de planificación y geolocalización
- Nueva barra rápida encima del mapa para ingresar Origen/Destino y planificar en 1 click.
- Botón "📍 Usar mi ubicación" con geolocalización del navegador y origen auto‑rellenado.
- Campos internos sincronizados (inputs ocultos `#origen` y `#destino`) para evitar inconsistencias.
- Validaciones claras: origen/destino requeridos, radio entre 1–50 km y auto con autonomía.
- Mensajes de error/alertas amistosos y logs de depuración en consola: `[QuickBar]`, `[Geoloc]`, `[Planificar]`.

#### 🚘 Selector de auto visible y flujo sin fricción
- Selector de auto del usuario visible debajo de la barra rápida, con resumen del auto elegido.
- Carga automática de autos al abrir la pestaña Viajes; si hay un solo auto, se selecciona solo.
- El checkbox “Solo compatibles con mi auto” usa el conector del auto seleccionado.
- Auto‑selección y foco cuando falta elegir, evitando bloqueos de planificación.

#### 🚗 Sistema de Ruteo Real con OSRM
- **Planificación de viajes mejorada en panel cliente**
  - Integración con OSRM (Open Source Routing Machine) para rutas reales
  - Reemplazo de cálculo de línea recta por trazado vial real
  - Cálculo preciso de distancia en km y duración estimada
  - Fallback automático a línea recta si OSRM no está disponible
  - Polyline completa dibujada en Leaflet con estilo mejorado

#### 🔍 Sistema de Filtros de Estaciones
- **Filtros dinámicos en panel de viajes**
  - Filtro por **Tipo de cargador** (poblado dinámicamente desde DB)
  - Filtro por **Estado** (disponible, ocupado, mantenimiento, etc.)
  - Filtro por **Tipo de conector** (Tipo 1, Tipo 2, CCS, CHAdeMO, etc.)
  - Checkbox **"Solo compatibles con mi auto"** (filtra por conector del vehículo seleccionado)
  - Reaplicación automática de filtros al cambiar cualquier criterio
  - Integración completa con sistema de búsqueda de rutas

#### 🗺️ Mapa y popups mejorados
- Marcadores con popup compacto: nombre, dirección/desc., conectores, potencia y estado.
- Acciones directas desde el popup: "Reservar" y "Ver" (abre modal de detalle).
- Trazado de ruta con Leaflet; zoom automático al encuadre del recorrido.

#### 🎯 Sugerencias Inteligentes de Paradas
- **Cálculo basado en ruta real**
  - Sugerencias de paradas a lo largo de la ruta (no solo en línea recta)
  - Puntos calculados por fracción de distancia recorrida
  - Consideración de autonomía del vehículo seleccionado
  - Marcadores visuales:
    - 🟡 Amarillo: Paradas sugeridas con estación cercana
    - 🔴 Rosa: Puntos sin estación en el radio configurado
  - Tabla con columna "Recomendada" para identificar sugerencias

#### 🧩 Cambios de layout
- El título "Estaciones disponibles" se movió debajo del mapa para priorizar el contenido visual.
- Separadores y márgenes ajustados para mejor legibilidad.

#### 🔐 Sistema Automático de Gestión de Estados
- **Estados de cargadores gestionados por reservas**
  - Al **crear una reserva**: El cargador pasa automáticamente a **"ocupado"**
  - Al **cancelar una reserva**: El cargador vuelve a **"disponible"** (si no hay otras reservas activas)
  - Al **listar cargadores**: Sistema libera automáticamente cargadores cuyas reservas finalizaron
  - Métodos nuevos en modelo/Cargador.php:
    - `actualizarEstado($id, $estado)`: Actualiza estado de un cargador
    - `liberarCargadoresVencidos()`: Libera cargadores con reservas vencidas
    - `tieneReservaActiva($id)`: Verifica si hay reservas activas vigentes
  - Modificaciones en modelo/Reserva.php:
    - `crear()`: Marca cargador como ocupado tras reserva exitosa
    - `cancelar()`: Libera cargador si no quedan reservas activas
  - **Estados soportados:**
    - `disponible`: Libre para reservar
    - `ocupado`: Con reserva activa (gestionado automáticamente)
    - `mantenimiento`: Inhabilitado manualmente
    - `fuera de servicio`: Inhabilitado manualmente
  - Documentación completa en `GESTION_ESTADOS_CARGADORES.md`

#### 📐 Funciones de Cálculo Geométrico
- **Nuevas funciones de geometría en cliente.php**
  - `distancePointToRouteKm(p, routeLatLng)`: Distancia mínima de punto a polilínea
  - `longitudRutaKm(routeLatLng)`: Longitud total de ruta en km
  - `puntoEnRutaPorFraccion(routeLatLng, fraccion)`: Punto en ruta según fracción [0,1]
  - `obtenerRutaOSRM(orig, dest)`: Llamada a servicio OSRM con geometría completa

### 📝 Archivos Modificados

#### Vista Cliente
- `vista/cliente.php`:
  - Barra rápida (origen/destino/geolocalización/planificar) y selector visible de auto con resumen.
  - Inputs ocultos `#origen` y `#destino` para unificar el flujo interno.
  - Barra de filtros agregada con 4 criterios
  - Integración OSRM para ruteo real
  - Funciones `aplicarFiltros()` y `poblarFiltros()`
  - Listeners de cambio en filtros para reaplicación dinámica
  - Cálculo de paradas basado en ruta real (no línea recta)
  - Encabezado "Estaciones disponibles" reposicionado debajo del mapa

#### Modelo
- `modelo/Cargador.php`:
  - `actualizarEstado()`, `liberarCargadoresVencidos()`, `tieneReservaActiva()`
- `modelo/Reserva.php`:
  - Require de `Cargador.php` agregado
  - Lógica de actualización de estado en `crear()` y `cancelar()`

#### Controlador
- `controlador/CargadorControlador.php`:
  - `listarCargadores()` ahora ejecuta `liberarCargadoresVencidos()` antes de listar

### 🐛 Correcciones
- Cálculo de estaciones cercanas ahora usa distancia a toda la polilínea (no solo un segmento).
- Filtro de conectores soporta formato CSV y arrays en campo `conectores`.
- Manejo correcto de reservas solapadas (no permite conflictos).
- Fallback robusto a línea recta si OSRM falla o no responde.
- Botón “Planificar ruta” no reaccionaba si no había auto seleccionado: ahora auto‑selecciona si hay uno solo y muestra guía si no hay autos.
- Handler huérfano de un botón legacy (`#btnBuscarRuta`) causaba fallo de JS y rompía otros clicks: se agregó verificación antes de registrar el listener.
- Geolocalización robusta: actualización sincronizada de la barra rápida y campos internos; mensajes claros si el navegador deniega permisos.

### 📚 Documentación Nueva
- `GESTION_ESTADOS_CARGADORES.md`: Explicación completa del sistema de estados automáticos

---

## Versión 1.6.0 - 5 de Noviembre de 2025

### 🎯 Cambios Principales

#### 🏗️ Refactorización Completa de Arquitectura MVC
- **Patrón MVC implementado al 100%**
  - Separación estricta de responsabilidades entre Modelo, Vista, Controlador y API
  - Eliminación de violaciones del patrón MVC en toda la aplicación
  - Documentación completa de la arquitectura en `ARQUITECTURA.md`
  - Nuevo documento `CORRECIONES_MVC.md` detallando todos los cambios

#### 🔧 Corrección del ViajeControlador
- **Controlador ViajeControlador.php refactorizado**
  - ❌ **Antes:** Mezclaba lógica de API (session_start, header, echo)
  - ✅ **Ahora:** Solo contiene funciones puras de lógica de negocio
  - Funciones nuevas:
    - `agregarViaje($usuario, $origen, $destino, $fecha, $distancia_km, $observaciones)`
    - `listarViajesUsuario($usuario)`
  - Retorna arrays estructurados en lugar de hacer echo directo
  - Sin acceso a `$_POST`, `$_SESSION` o `$_GET` en las funciones principales

#### 🚀 API de Viajes Actualizada
- **api/viajes.php completamente renovada**
  - Ahora maneja correctamente HTTP requests/responses
  - Soporte para GET y POST con JSON y form-data
  - Llamadas correctas a funciones del controlador
  - Endpoints:
    - `GET ?accion=listar`: Lista viajes del usuario autenticado
    - `POST accion=agregar`: Crea nuevo viaje con validación
  - Headers CORS configurados correctamente

#### 🔌 Limpieza de API Cargadores
- **api/cargadores.php optimizada**
  - Eliminada variable `$conn` innecesaria (no se usaba)
  - Removido `require_once __DIR__ . '/../db.php'`
  - Ahora solo usa el CargadorControlador como debe ser
  - Código más limpio y mantenible

#### 🎨 Selector de Conectores Estandarizado
- **Campo Tipo de Conector en todas las vistas**
  - Vista Cliente (`vista/cliente.php`):
    - Input text reemplazado por `<select>` con opciones estándar
  - Vista Admin (`vista/formulario.php`):
    - Formulario de agregar: Select con opciones estándar
    - Edición inline: Select dinámico con función helper `opcionesConectorHTML()`
  - **Tipos de conector disponibles:**
    - Tipo 1 (SAE J1772)
    - Tipo 2 (Mennekes)
    - CCS Combo 1
    - CCS Combo 2
    - CHAdeMO
    - Tesla (NACS)
    - GB/T
  - Previene errores de tipeo y mantiene consistencia de datos

#### 🗄️ Base de Datos: Campo Conector con ENUM
- **Migración de VARCHAR a ENUM para `autos.conector`**
  - Enforcement de integridad de datos a nivel de base de datos
  - MySQL rechaza automáticamente valores no válidos
  - Mayor performance que VARCHAR
  - Script SQL proporcionado para migración segura
  - Valor por defecto: 'Tipo 2'

#### 📊 Orden de Columnas Actualizado
- **Tabla de Autos en Vista Cliente**
  - ❌ **Antes:** ID | Modelo | Marca | Tipo de Conector | Autonomía | Año | Acciones
  - ✅ **Ahora:** ID | Marca | Modelo | Tipo de Conector | Autonomía | Año | Acciones
  - Orden consistente con panel de administración
  - JavaScript de edición inline actualizado para nuevo orden

---

### 🔧 Cambios Técnicos Detallados

#### Arquitectura MVC Corregida

**Diagrama actualizado:**
```
┌─────────────────────────────────────────────────┐
│                   VISTA                         │
│  (cliente.php, formulario.php, cargador.php)   │
│              JavaScript + HTML                  │
└─────────────────┬───────────────────────────────┘
                  │ HTTP Requests (fetch/AJAX)
                  ↓
┌─────────────────────────────────────────────────┐
│                    API                          │
│  (autos.php, cargadores.php, viajes.php)       │
│  ✅ Valida requests                             │
│  ✅ Verifica permisos/sesiones                  │
│  ✅ Llama funciones del CONTROLADOR             │
│  ✅ Retorna JSON                                │
│  ❌ NO hace queries SQL                         │
└─────────────────┬───────────────────────────────┘
                  │ Llamadas a funciones
                  ↓
┌─────────────────────────────────────────────────┐
│               CONTROLADOR                       │
│  (ViajeControlador, AutoControlador, etc.)     │
│  ✅ Lógica de negocio                           │
│  ✅ Validaciones de datos                       │
│  ✅ Instancia y usa MODELOS                     │
│  ✅ Retorna arrays/datos                        │
│  ❌ NO hace queries SQL directas                │
│  ❌ NO hace echo/print                          │
└─────────────────┬───────────────────────────────┘
                  │ Usa métodos
                  ↓
┌─────────────────────────────────────────────────┐
│                    MODELO                       │
│       (Viaje, Auto, Cargador, Usuario)         │
│  ✅ Interacción con base de datos               │
│  ✅ Queries SQL (PDO)                           │
│  ✅ Métodos CRUD                                │
│  ❌ NO contiene lógica de negocio               │
└─────────────────┬───────────────────────────────┘
                  │ PDO
                  ↓
┌─────────────────────────────────────────────────┐
│              BASE DE DATOS MySQL                │
└─────────────────────────────────────────────────┘
```

#### Controlador ViajeControlador.php

**Antes (❌ Incorrecto):**
```php
<?php
session_start(); // ← Mezclaba lógica de API
header('Content-Type: application/json'); // ← Headers en controlador

$viajeModel = new Viaje();

function agregarViaje($viajeModel) {
    $usuario = $_SESSION['usuario'] ?? ''; // ← Acceso directo a sesión
    // ...
    echo json_encode(['success' => $ok]); // ← Echo en controlador
}

$accion = $_POST['accion'] ?? ''; // ← Lectura directa de POST
switch ($accion) {
    case 'agregar':
        agregarViaje($viajeModel);
        break;
}
?>
```

**Ahora (✅ Correcto):**
```php
<?php
require_once __DIR__ . '/../modelo/Viaje.php';

// Funciones puras del controlador
function agregarViaje($usuario, $origen, $destino, $fecha, $distancia_km = 0, $observaciones = null) {
    if (empty($usuario) || empty($origen) || empty($destino) || empty($fecha)) {
        return ['exito' => false, 'mensaje' => 'Faltan datos requeridos'];
    }
    $viajeModel = new Viaje();
    $ok = $viajeModel->insertar($usuario, $origen, $destino, $fecha, $distancia_km, $observaciones);
    return ['exito' => (bool)$ok, 'mensaje' => $ok ? 'Viaje registrado' : 'Error al registrar viaje'];
}

function listarViajesUsuario($usuario) {
    if (empty($usuario)) {
        return [];
    }
    $viajeModel = new Viaje();
    return $viajeModel->listarPorUsuario($usuario);
}
?>
```

#### API viajes.php

**Ahora maneja correctamente la capa HTTP:**
```php
<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../controlador/ViajeControlador.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $usuario = $_SESSION['usuario'] ?? '';
        $viajes = listarViajesUsuario($usuario); // ← Llama al controlador
        echo json_encode($viajes);
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $usuario = $_SESSION['usuario'] ?? '';
        $resultado = agregarViaje( // ← Llama al controlador con parámetros
            $usuario,
            $input['origen'] ?? '',
            $input['destino'] ?? '',
            $input['fecha'] ?? date('Y-m-d H:i:s'),
            $input['distancia_km'] ?? 0,
            $input['observaciones'] ?? null
        );
        echo json_encode($resultado);
        break;
}
?>
```

#### Vista formulario.php - Selector de Conectores

**Helper JavaScript agregado:**
```javascript
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
```

**Tabla de autos con select en edición:**
```javascript
html += `<td style="padding:10px;">
    <span id="conector-${auto.id}">${auto.conector}</span>
    <select id="input-conector-${auto.id}" style="display:none; width:100%; padding:5px;">
        ${opcionesConectorHTML(auto.conector)}
    </select>
</td>`;
```

#### Vista cliente.php - Actualizada

**Orden de columnas corregido:**
```javascript
// ✅ AHORA: Marca antes de Modelo
let html = "<table><tr><th>ID</th><th>Marca</th><th>Modelo</th><th>Tipo de Conector</th>...";
autos.forEach(auto => {
    html += `<tr data-id="${auto.id}">
        <td>${auto.id}</td>
        <td class="editable" data-campo="marca">${auto.marca}</td>
        <td class="editable" data-campo="modelo">${auto.modelo}</td>
        ...
    </tr>`;
});
```

**Función de guardado actualizada:**
```javascript
const datos = {
    accion: 'editar',
    id: id,
    marca: inputs[0].value,  // ← Marca primero
    modelo: inputs[1].value, // ← Modelo segundo
    conector: inputs[2].value,
    autonomia: inputs[3].value,
    anio: inputs[4].value
};
```

---

### 📦 Archivos Nuevos

- `ARQUITECTURA.md` - Documentación completa del patrón MVC implementado

### 📝 Archivos Modificados

**Controladores:**
- `controlador/ViajeControlador.php` ⚙️
  - Refactorizado completamente
  - Eliminada lógica de API
  - Funciones puras de negocio

**APIs:**
- `api/viajes.php` ⚙️
  - Actualizada para usar correctamente el controlador
  - Manejo apropiado de HTTP/JSON
- `api/cargadores.php` ⚙️
  - Limpieza de código innecesario
  - Eliminada variable `$conn`

**Vistas:**
- `vista/cliente.php`
  - Selector de conectores en formulario de agregar auto
  - Orden de columnas: ID | Marca | Modelo | ...
  - JavaScript de edición actualizado
- `vista/formulario.php`
  - Selector de conectores en formulario de agregar
  - Selector de conectores en edición inline
  - Función helper `opcionesConectorHTML()`

---

### 🐛 Correcciones de Bugs

1. **ViajeControlador violaba MVC**
   - Mezclaba responsabilidades de API y controlador
   - Solución: Refactorización completa siguiendo patrón MVC

2. **API viajes no funcionaba correctamente**
   - Dependía de estructura incorrecta del controlador
   - Solución: Actualizada para usar nuevas funciones del controlador

3. **Variable innecesaria en api/cargadores**
   - `$conn` declarada pero nunca usada
   - Solución: Eliminada junto con require innecesario

4. **Inconsistencia en tipos de conector**
   - Usuarios podían escribir cualquier valor
   - Solución: Select con opciones estándar en todas las vistas

5. **Orden de columnas inconsistente**
   - Vista cliente mostraba Modelo antes que Marca
   - Solución: Reordenamiento de columnas y actualización de JavaScript

---

### ✨ Mejoras de UX

1. **Selector de conectores estandarizado**
   - Previene errores de tipeo
   - Opciones claras con nombres descriptivos
   - Consistencia de datos garantizada

2. **Orden de columnas lógico**
   - Marca → Modelo (orden natural)
   - Consistente entre vista cliente y admin

3. **Arquitectura más mantenible**
   - Bugs más fáciles de encontrar y corregir
   - Código más limpio y organizado
   - Mejor rendimiento general

---

### 📝 Notas de Migración

#### Script SQL para ENUM en `autos.conector`

**⚠️ IMPORTANTE: Hacer backup antes de ejecutar**

```sql
-- 1. Verificar datos actuales
SELECT DISTINCT conector FROM autos;

-- 2. (Opcional) Actualizar valores no estándar
-- UPDATE autos SET conector = 'Tipo 2' WHERE conector NOT IN ('Tipo 1', 'Tipo 2', 'CCS Combo 1', 'CCS Combo 2', 'CHAdeMO', 'Tesla (NACS)', 'GB/T');

-- 3. Aplicar ENUM
ALTER TABLE autos 
MODIFY COLUMN conector ENUM(
    'Tipo 1',
    'Tipo 2',
    'CCS Combo 1',
    'CCS Combo 2',
    'CHAdeMO',
    'Tesla (NACS)',
    'GB/T'
) NOT NULL DEFAULT 'Tipo 2';

-- 4. Verificar cambio
DESCRIBE autos;
```

**Nota:** La migración a ENUM es **opcional** pero **recomendada** para mayor integridad de datos.

---

### 🚀 Beneficios de esta Versión

1. **Arquitectura MVC Pura**
   - Separación clara de responsabilidades
   - Código más testeable y mantenible
   - Siguiendo mejores prácticas de desarrollo

2. **Mayor Integridad de Datos**
   - Campo conector con valores validados
   - Enforcement a nivel de BD (si se usa ENUM)
   - Consistencia garantizada en UI

3. **Código más Limpio**
   - Eliminación de código innecesario
   - Funciones con propósito único
   - Mejor organización general

4. **Mejor Performance**
   - ENUM más eficiente que VARCHAR
   - Menos validaciones en runtime
   - Queries optimizadas

5. **Documentación Completa**
   - `ARQUITECTURA.md` explica todo el patrón
   - Ejemplos de código para cada capa
   - Guía clara para futuros desarrollos

---

### 🎓 Documentación Nueva

- **ARQUITECTURA.md**
  - Diagrama completo de capas MVC
  - Responsabilidades de cada capa
  - Qué DEBE y NO DEBE contener cada archivo
  - Ejemplos de código correcto e incorrecto
  - Flujo completo de una operación
  - Buenas prácticas implementadas
  - Tabla resumen de archivos por responsabilidad

---

### 🔍 Checklist de Cumplimiento MVC

#### API Layer ✅
- [x] Solo maneja HTTP requests/responses
- [x] Valida permisos y sesiones
- [x] Llama funciones del Controlador
- [x] Retorna JSON
- [x] NO hace queries SQL
- [x] NO instancia Modelos directamente

#### Controlador Layer ✅
- [x] Contiene lógica de negocio
- [x] Valida datos
- [x] Instancia y usa Modelos
- [x] Retorna arrays/datos procesados
- [x] NO hace queries SQL directas
- [x] NO hace echo/print
- [x] NO accede a $_POST/$_GET/$_SESSION directamente

#### Modelo Layer ✅
- [x] Interactúa con la base de datos
- [x] Métodos CRUD
- [x] Retorna datos de DB
- [x] NO contiene lógica de negocio
- [x] NO maneja sesiones

---

### 📞 Soporte

Para reportar bugs o sugerir mejoras, contactar al equipo de desarrollo.

**Desarrollado por:** ShonosTech  
**Fecha de Release:** 5 de Noviembre de 2025  
**Versión Anterior:** 1.5.0  
**Versión Actual:** 1.6.0

---

## Versión 1.5.0 - 1 de Noviembre de 2025

### 🎯 Cambios Principales

#### 🗺️ Sistema Completo de Planificación de Viajes
- **Planificador interactivo con mapa Leaflet**
  - Ingreso de origen y destino mediante dirección de texto
  - Selector de auto del usuario con información de autonomía
  - Radio configurable (1-50 km) para buscar estaciones cercanas a la ruta
  - Geocodificación automática con Nominatim (OpenStreetMap)
  - Visualización de ruta aproximada (línea recta) en mapa
  - Filtrado inteligente de estaciones cercanas a la ruta
  - **Recomendación automática de paradas** según autonomía del vehículo
  - Marcadores en mapa con información y acciones directas
  - Panel de estaciones con tabla detallada (nombre, tipo, estado, coords, recomendada)

#### 🔌 Estaciones de Carga - Información Detallada
- **Backend extendido para datos completos de estaciones**
  - Nuevos campos en tabla `cargadores`: `tipo`, `estado`, `potencia_kw`, `conectores`
  - Modelo actualizado con soporte completo para nuevos campos
  - API modificada para exponer y recibir toda la información
  - Estados posibles: `disponible`, `en_uso`, `fuera_de_servicio`
  - Tipos de cargador: Tipo 1, Tipo 2, CCS, CHAdeMO, Tesla Supercharger, etc.
- **Modal de detalle de estación**
  - Vista completa con nombre, coordenadas, tipo, estado, descripción
  - Botón directo para reservar desde el detalle
  - Diseño responsive y accesible

#### 📅 Sistema de Reservas con Calendar/Time Picker
- **Modal de reserva con calendario y hora**
  - Selector de fecha (date picker)
  - Selector de hora de inicio (time picker)
  - Duración configurable en minutos (múltiplos de 15)
  - Validación de campos antes de enviar
  - Cálculo automático de hora de fin
  - Envío JSON al backend con validación de solapamientos
- **Gestión de reservas del usuario**
  - Tabla "Mis reservas" con todas las reservas activas
  - Información: Estación, Inicio, Fin, Estado
  - Botón de cancelar para reservas no canceladas
  - Actualización automática tras crear/cancelar

#### 📋 Historial de Cargas y Viajes
- **Nueva pestaña Historial**
  - Muestra reservas pasadas y completadas (historial de cargas)
  - Tabla con estación, inicio, fin, estado
  - Filtrado automático de reservas pasadas
  - Comentarios TODO para futuro: tabla de viajes completos con estaciones usadas, distancia, consumo

#### 🧭 Panel de Cliente con pestañas (Autos/Viajes/Historial)
- Rediseño del panel de cliente con sidebar fija y navegación por pestañas (Autos/Viajes/Historial).
- Estructura por tarjetas `.tab-content` para separar formularios y listados.
- JavaScript de cambio de pestañas y estilos responsive en `styles/cliente.css`.

#### 🚘 Listado de autos del cliente (fix)
- `api/autos.php` ahora lista los autos del usuario autenticado correctamente (GET).
- Soporta agregar/editar/eliminar via JSON o `application/x-www-form-urlencoded`.
- Se eliminaron dependencias del flujo legacy del controlador que impedían listar.

#### 🎨 Panel de Administración minimalista (claro)
- `styles/formulario.css` rediseñado: sidebar blanca, tarjetas limpias, inputs/tablas con foco accesible y sombras suaves.
- Ajuste en `vista/formulario.php` para evitar error si no existe `#btn-cerrar-sesion`.

#### 🧩 Conflicto de estilos resuelto en cliente
- Se quitó el import de `../styles/formulario.css` en `vista/cliente.php` para no romper el layout del cliente.

#### ➕ Panel de Administración - Agregar Autos a Usuarios
- **Funcionalidad completa para que el administrador agregue autos a cualquier usuario**
  - Formulario intuitivo en la pestaña "Autos" del panel de administración
  - Selector dinámico de usuarios (carga desde la base de datos)
  - Campos para ingresar: Modelo, Marca, Conector, Autonomía (km), Año
  - Validación de campos requeridos
  - Actualización automática de la lista tras agregar un auto
  - Diseño responsive con grid layout

#### 🔄 Optimización y Unificación de APIs Administrativas
- **Consolidación de APIs en `admin.php`**
  - Todas las operaciones administrativas ahora en una sola API unificada
  - Reduce la cantidad de archivos y mejora la mantenibilidad
  - Implementación más limpia y organizada
  - Mejor reutilización de código (función `verificarAdmin()`)
  - Eliminado `api/autos_admin.php` (integrado en `admin.php`)

---

### 🔧 Cambios Técnicos Detallados

#### Sistema de Reservas ⚙️
- **Modelo `Reserva.php`**
  - Método `crear()`: Validación de solapamientos antes de insertar
  - Método `cancelar()`: Actualiza estado a 'cancelada'
  - Métodos `listarPorUsuario()` y `listarPorCargador()`: Consultas específicas
- **Controlador `ReservaControlador.php`**
  - Funciones proxy hacia el modelo que devuelven arrays para API
- **API `reservas.php`**
  - GET: `listar_usuario`, `listar_cargador`
  - POST: `crear` (con validación de solapes), `cancelar`
  - Soporte JSON completo con detección de Content-Type

#### Estaciones de Carga - Backend Completo ⚙️
- **Modelo `Cargador.php`**
  - Métodos `insertar()` y `modificar()` extendidos con: tipo, estado, potencia_kw, conectores
  - Parámetros opcionales con valores por defecto
- **Controlador `CargadorControlador.php`**
  - Funciones `agregarCargador()` y `modificarCargador()` actualizadas
  - Nueva función `modificarCargador()` para edición completa
- **API `cargadores.php`**
  - POST acción `agregar`: Acepta todos los nuevos campos
  - POST acción `modificar`: Permite editar estaciones con nuevos datos
  - GET: Devuelve todos los campos automáticamente
- **Migración SQL**
  - Archivo `MIGRACION_CARGADORES.sql` con ALTER TABLE para agregar columnas
  - Campos: tipo VARCHAR(50), estado VARCHAR(30), potencia_kw DECIMAL(5,2), conectores VARCHAR(255)

#### Correcciones de Arquitectura MVC ⚙️
- **Patrón MVC respetado al 100%**
  - `CargadorControlador.php`: Refactorizado completamente
    - Ahora usa el modelo `Cargador` en lugar de hacer queries SQL directas
    - Funciones: `listarCargadores()`, `agregarCargador()`, `eliminarCargador()`, `modificarCargador()`
  - `UsuarioControlador.php`: Orden de parámetros unificado
    - `registrarUsuario($username, $password, $tipo_usuario, $correo = '')`
    - Parámetro `correo` opcional con generación automática
  - `admin.php`: Eliminada lógica de base de datos
    - Ya no usa `mysqli` directamente
    - Todas las operaciones pasan por el Controlador
    - Eliminada función `getCargadorConn()`
  - `Cargador.php` (Modelo): Parámetro `descripcion` ahora opcional

#### API de Autos para usuario (nueva capa) 🚗
- `api/autos.php`
  - Inicia sesión si no estaba iniciada.
  - GET → devuelve autos del usuario autenticado.
  - POST/PUT/DELETE → mapeo a acciones de agregar/editar/eliminar para el usuario.
  - Soporta JSON y `application/x-www-form-urlencoded`.

#### Controlador Actualizado
- `controlador/AutoControlador.php`
  - **Nuevas funciones administrativas:**
    - `listarAutosAdmin($orden)`: Lista todos los autos con orden configurable
    - `agregarAutoAdmin(...)`: Agrega un auto a cualquier usuario
    - `editarAutoAdmin(...)`: Edita cualquier auto del sistema
    - `eliminarAutoAdmin($id)`: Elimina cualquier auto
  - Mantiene compatibilidad con llamadas directas (legacy)
  - **Patrón MVC respetado:** API → Controlador → Modelo

- **Nuevas funciones para usuario (no admin):**
  - `listarAutosUsuario($usuario)`
  - `agregarAutoUsuario($usuario, ...)`
  - `editarAutoUsuario($usuario, ...)`
  - `eliminarAutoUsuario($usuario, $id)`

#### API Unificada
- `api/admin.php`
  - **Nuevos endpoints GET:**
    - `listar_autos`: Lista todos los autos con ordenamiento (requiere admin)
  - **Nuevos endpoints POST:**
    - `accion=agregar_auto`: Agrega un auto a un usuario
    - `accion=editar_auto`: Edita cualquier auto del sistema
    - `accion=eliminar_auto`: Elimina cualquier auto del sistema
  - **Nueva función:** `verificarAdmin()` - Verifica permisos antes de ejecutar operaciones sensibles
  - **Soporte dual:** Maneja tanto JSON como POST tradicional
  - **Headers anti-caché** añadidos para datos en tiempo real
  - **Arquitectura MVC:** Llama a funciones del `AutoControlador` en lugar del modelo directamente

#### Vista Mejorada
- `vista/formulario.php`
  - **Formulario de agregar auto:**
    - Diseño en grid responsive
    - Selector de usuarios con carga dinámica
    - Campos: Usuario, Modelo, Marca, Conector, Autonomía, Año
    - Botón verde destacado para agregar
  - **JavaScript implementado:**
    - `cargarUsuariosParaAutos()`: Carga lista de usuarios al abrir pestaña
    - Manejador de submit para formulario de agregar auto
    - Integración con sistema de pestañas del sidebar
    - Limpieza automática del formulario tras agregar
  - **Actualización de fetch:**
    - Todas las llamadas ahora usan `../api/admin.php`
    - Nombres de acciones actualizados para consistencia

- `vista/cliente.php`
  - Nueva estructura con pestañas: `#tab-autos`, `#tab-viajes`, `#tab-historial`
  - Eliminado el import de `../styles/formulario.css` para evitar conflictos
  - **Planificador de viajes completo:**
    - Formulario con origen, destino, selector de auto, radio (km), botón "Buscar ruta"
    - Carga automática de autos del usuario en selector con data-autonomia
    - Validaciones: campos vacíos, radio entre 1-50, auto con autonomía
  - **Mapa Leaflet integrado:**
    - Inicialización en primera apertura de pestaña Viajes
    - Función `trazarRutaYSugerir()`: geocodifica, dibuja ruta, filtra estaciones, sugiere paradas
    - Marcadores con popup de información y botones "Reservar" y "Ver"
    - Tabla de estaciones con columnas: Estación, Tipo, Estado, Lat, Lon, Recomendada, Acciones
  - **Modal de detalle de estación:**
    - Muestra toda la info disponible (nombre, lat/lon, tipo, estado, descripción)
    - Botón "Reservar aquí" que abre el modal de reserva
  - **Modal de reserva:**
    - Inputs: fecha (date), hora (time), duración (minutos)
    - Submit JSON a `api/reservas.php`
    - Actualiza lista "Mis reservas" tras confirmar
  - **Pestaña Historial:**
    - Tabla de reservas pasadas y completadas
    - Filtrado por fecha de inicio < ahora o estado cancelada/completada
    - Comentarios TODO para implementar tabla de viajes
  - JS para cambiar pestañas y cargar listados de autos, cargadores, reservas e historial

---

### 📦 Archivos Nuevos

- `modelo/Reserva.php` - Modelo de reservas con validación de solapamientos
- `controlador/ReservaControlador.php` - Controlador de reservas
- `api/reservas.php` - API REST para gestión de reservas
- `MIGRACION_CARGADORES.sql` - Script SQL para extender tabla cargadores

### 📦 Archivos Eliminados

- ❌ `api/autos_admin.php` - Funcionalidad integrada en `admin.php`

### 📝 Archivos Modificados

**Modelos:**
- `modelo/Cargador.php` ⚙️
  - Extendido con campos tipo, estado, potencia_kw, conectores
  - Métodos `insertar()` y `modificar()` actualizados con nuevos parámetros opcionales
- `modelo/Auto.php`
  - Métodos `listarTodos()`, `actualizarAdmin()`, `eliminarAdmin()`

**Controladores:**
- `controlador/CargadorControlador.php` ⚙️
  - Refactorizado para usar el Modelo correctamente
  - Nuevas funciones: `modificarCargador()`
  - Parámetros extendidos en `agregarCargador()`
- `controlador/AutoControlador.php`
  - Funciones administrativas: `listarAutosAdmin()`, `agregarAutoAdmin()`, `editarAutoAdmin()`, `eliminarAutoAdmin()`
  - Funciones de usuario: `listarAutosUsuario()`, `agregarAutoUsuario()`, `editarAutoUsuario()`, `eliminarAutoUsuario()`
  - Arquitectura mejorada manteniendo patrón MVC
- `controlador/UsuarioControlador.php` ⚙️
  - Orden de parámetros corregido en `registrarUsuario()`
  - Soporte para correo opcional

**APIs:**
- `api/cargadores.php` ⚙️
  - POST acción `agregar`: Ahora acepta descripcion, tipo, estado, potencia_kw, conectores
  - POST acción `modificar`: Nueva acción para editar estaciones completas
  - Llama a funciones del `CargadorControlador` (respeta MVC)
- `api/reservas.php` (nuevo)
  - GET listar_usuario/listar_cargador
  - POST crear/cancelar con soporte JSON
- `api/admin.php`
  - Integración completa de gestión de autos
  - Función `verificarAdmin()` para seguridad
  - Soporte JSON/POST unificado
  - Llama a funciones del `AutoControlador` (respeta MVC)
  - Endpoints para listar, agregar, editar y eliminar autos
  - **Eliminada lógica de base de datos directa** ⚙️
  - **Ahora usa `CargadorControlador` correctamente** ⚙️
- `api/registro.php` ⚙️
  - Orden de parámetros corregido para llamar a `registrarUsuario()`

**Vistas:**
- `vista/cliente.php`
  - **Estructura de pestañas:** Autos, Viajes, Historial
  - **Planificador de viajes:** Formulario completo + mapa Leaflet + tabla de estaciones
  - **Modales:** Reserva (date/time/duration) y Detalle de estación
  - **Historial:** Tabla de reservas pasadas con comentarios TODO para viajes
  - **JavaScript:**
    - `cargarAutosSelector()`: Carga autos en el selector con autonomía
    - `trazarRutaYSugerir()`: Geocodifica, dibuja ruta, filtra y sugiere paradas
    - `renderPanelEstaciones()`: Renderiza tabla con tipo/estado
    - `abrirDetalleEstacion()`: Abre modal con info completa
    - `abrirReserva()`: Abre modal de reserva precargado
    - `listarReservas()`: Lista reservas activas
    - `cargarHistorialReservas()`: Filtra y muestra reservas pasadas
    - Event listeners para modales y botón "Buscar ruta"
  - **Leaflet CDN:** CSS y JS integrados
- `vista/formulario.php`
  - Formulario de agregar auto en pestaña "Autos"
  - Función `cargarUsuariosParaAutos()`
  - Manejador de submit para agregar autos
  - Actualización de URLs de fetch (de `autos_admin.php` a `admin.php`)
  - Actualización de nombres de acciones

**Estilos:**
- `styles/cliente.css`
  - Estilos para modales de reserva y detalle de estación (reutiliza clases existentes)
  - Planificador en grid responsive
  - Tablas de estaciones e historial
- `styles/formulario.css`
  - Formulario con fondo `#f8f9fa` y bordes redondeados
  - Grid responsive que se adapta al tamaño de la pantalla
  - Botón verde (`#4CAF50`) para agregar
  - Inputs con estilo consistente

---

### 🐛 Correcciones de Bugs

1. **Carga de usuarios al abrir pestaña de Autos**
   - Los usuarios ahora se cargan automáticamente cuando se abre la pestaña
   - Implementado en el event listener de las pestañas del sidebar

2. **Listado de autos del cliente no aparecía**
  - La API dependía del bloque legacy del controlador al ser requerida.
  - Solución: nuevas funciones explícitas en el controlador y mapeo directo en `api/autos.php`.

3. **Conflicto de estilos en cliente**
  - `formulario.css` afectaba al layout del cliente.
  - Solución: se eliminó el import en `vista/cliente.php`.

4. **Error JS potencial en admin**
  - Acceso a `#btn-cerrar-sesion` inexistente.
  - Solución: verificación de existencia antes de asignar el handler.

5. **"Acción POST no reconocida" en reservas**
  - API de reservas no leía el campo `accion` de JSON bodies
  - Solución: Detección de Content-Type application/json y parseo del body

---

### ✨ Mejoras de UX

1. **Flujo de planificación intuitivo**
   - Proceso claro: Origen → Destino → Auto → Buscar → Ver estaciones → Reservar
   - Validaciones inmediatas con mensajes claros
   - Recomendaciones automáticas de paradas según autonomía
   - Mapa interactivo con marcadores informativos

2. **Información completa de estaciones**
   - Tipo de cargador visible (Tipo 1, Tipo 2, CCS, CHAdeMO, etc.)
   - Estado en tiempo real (disponible, en uso, fuera de servicio)
   - Potencia y tipos de conectores disponibles
   - Modal de detalle con toda la información

3. **Reservas más usables**
   - Calendar y time picker nativos del navegador
   - Duración flexible en minutos (múltiplos de 15)
   - Validación de solapamientos en backend
   - Lista actualizada automáticamente

4. **Historial de cargas**
   - Vista separada para consultar reservas pasadas
   - Diferencia clara entre reservas activas e historial
   - Preparado para futuro: historial de viajes completos

5. **Proceso de agregar autos simplificado**
   - Formulario claro y organizado en la parte superior
   - Selector de usuario con formato: "nombre_usuario (tipo_usuario)"
   - Feedback inmediato con alert tras agregar
   - Lista de autos se actualiza automáticamente

6. **Consistencia visual**
   - Diseño alineado con el resto del panel de administración
   - Colores corporativos mantenidos
   - Espaciado adecuado entre elementos

7. **Cliente con navegación por pestañas**
  - Sidebar clara con estados activo/hover consistentes.
  - Transiciones suaves y tarjetas diferenciadas por sección.

8. **Admin minimalista**
  - Interfaz más limpia, foco accesible en inputs, tablas claras.

9. **Mejor organización del código**
   - API unificada más fácil de mantener
   - Menos archivos que gestionar
   - Código más limpio y reutilizable

---

### 📝 Notas de Migración

**Para actualizar la base de datos:**

1. **Tabla de reservas** (ejecutar manualmente):
```sql
CREATE TABLE IF NOT EXISTS reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    cargador_id INT NOT NULL,
    inicio DATETIME NOT NULL,
    fin DATETIME NOT NULL,
    estado VARCHAR(30) DEFAULT 'confirmada',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (cargador_id) REFERENCES cargadores(id) ON DELETE CASCADE
);
```

2. **Extensión de tabla cargadores:**
```bash
# Ejecutar el script de migración:
mysql -u root -p gestion_db < MIGRACION_CARGADORES.sql
```
O manualmente:
```sql
ALTER TABLE cargadores
ADD COLUMN tipo VARCHAR(50) DEFAULT '' AFTER descripcion,
ADD COLUMN estado VARCHAR(30) DEFAULT 'disponible' AFTER tipo,
ADD COLUMN potencia_kw DECIMAL(5,2) DEFAULT 0.00 AFTER estado,
ADD COLUMN conectores VARCHAR(255) DEFAULT '' AFTER potencia_kw;
```

---

### 🚀 Beneficios de la Optimización

1. **Menos archivos que mantener**
   - Reducción de código duplicado
   - Una sola API para todas las operaciones admin

2. **Mejor organización**
   - Todas las operaciones admin centralizadas
   - Más fácil encontrar y modificar funcionalidades

3. **Código más limpio**
   - Función `verificarAdmin()` reutilizable
   - Manejo consistente de JSON y POST tradicional
   - Headers centralizados
   - **Respeta patrón MVC:** API → Controlador → Modelo

4. **Mejor seguridad**
   - Verificación de permisos centralizada
   - Menos puntos de entrada a validar
   - Validación de solapamientos en reservas

5. **Experiencia de usuario superior**
   - Planificación de viajes visual e intuitiva
   - Información completa de estaciones
   - Gestión de reservas integrada
   - Historial de cargas disponible

---

### 🚀 Próximas Mejoras Sugeridas

**Sistema de Planificación:**
- [ ] Ruteo real con OSRM, Mapbox o Google Directions API (reemplazar línea recta)
- [ ] Cálculo de consumo estimado por viaje
- [ ] Exportar ruta planificada a PDF o compartir por link

**Estaciones:**
- [ ] Edición de estaciones desde panel admin con nuevos campos
- [ ] Filtros por tipo, estado, potencia en el mapa
- [ ] Ordenamiento por distancia al usuario
- [ ] Fotos de las estaciones
- [ ] Comentarios y ratings de usuarios

**Reservas:**
- [ ] Vista de calendario con slots disponibles
- [ ] Notificaciones por email/SMS antes de la reserva
- [ ] Código QR para check-in en la estación
- [ ] Tiempo máximo de reserva según tipo de usuario
- [ ] Penalización por no presentarse (no-show)

**Historial:**
- [ ] Implementar tabla `viajes` con estaciones usadas, distancia, consumo
- [ ] Gráficos de consumo y uso de estaciones
- [ ] Estadísticas mensuales/anuales
- [ ] Exportar historial a CSV/Excel
- [ ] Comparativa de eficiencia entre autos

**Validaciones:**
- [ ] Validación de datos del auto (ej: año entre 1900 y año actual+1)
- [ ] Impedir reservas en fechas pasadas (validación frontend)
- [ ] Límite de duración máxima por reserva
- [ ] Toasts/notificaciones en lugar de alerts
- [ ] Autocompletar modelo/marca basado en marcas existentes
- [ ] Vista previa antes de agregar el auto
- [ ] Agregar múltiples autos de una vez (batch insert)
- [ ] Importar autos desde CSV/Excel
- [ ] Búsqueda y filtrado de autos por usuario, marca o modelo
- [ ] Exportación de datos de autos a CSV/Excel

---

## Versión 1.4.0 - 31 de Octubre de 2025

### 🎯 Cambios Principales

#### 🚗 Panel de Administración - Gestión de Autos
- **Nueva funcionalidad completa para gestionar autos de todos los usuarios**
  - Los administradores pueden ver, editar y eliminar autos de cualquier usuario
  - Implementado ordenamiento ascendente/descendente por ID
  - Tabla visual con información completa: Usuario, Modelo, Marca, Conector, Autonomía, Año
  - Interfaz moderna con botones de acción (Editar/Eliminar) por cada auto
  - Sistema de pestañas en el panel de administración (Usuarios/Autos/Cargadores)

#### 🎨 Logo de la Empresa
- **Integración visual del logo corporativo**
  - Logo añadido en la barra superior de todas las páginas principales
  - Tamaño optimizado (60px) para mejor legibilidad sin afectar la altura de la barra
  - Visible en: `index.php`, `registro.html`, `principal.html`
  - Diseño responsive para dispositivos móviles

#### 🖌️ Mejoras de UI/Front‑end

**Barra superior y navegación**
- Hover unificado en azul corporativo `#1976d2` en enlaces de la barra:
  - `styles/index.css` → `.top-right a:hover`
  - `styles/principal.css` → `.top-right a:hover`
- Textos de navegación sin recortes ("Inicio / Registrarse / Contacto"):
  - `styles/index.css` → más `padding` en enlaces y `white-space: nowrap`.
- Consistencia y estabilidad en Principal:
  - `styles/principal.css` → barra fija, translúcida, `z-index: 1000`; eliminación de reglas duplicadas y `min-height: 100vh`.
- Tamaño del logo más legible sin cambiar la altura de la barra:
  - `styles/index.css` y `styles/principal.css` → `.logo` a `60px`.
- Ajustes de espaciado para evitar solapamientos:
  - `styles/index.css` → `padding-top: 90px` en el contenedor.
  - `styles/principal.css` → `padding-top` en la primera sección.

**Tipografía**
- Unificación de fuente en Principal:
  - `styles/principal.css` → `font-family: 'Montserrat', Arial, sans-serif;` en `html, body`.

**Formularios**
- Estilos coherentes para email:
  - `styles/index.css` y `styles/registro.css` → `input[type="email"]` y estados `:focus` añadidos.

---

### 🔧 Cambios Técnicos Detallados

#### Nueva API
- `api/autos_admin.php`
  - API exclusiva para administradores con manejo de autos globales
  - Verificación de sesión y tipo de usuario (admin)
  - Headers anti-caché para datos en tiempo real
  - Endpoints:
    - `listar`: Obtiene todos los autos con orden configurable (asc/desc)
    - `editar`: Actualiza cualquier auto sin restricción de usuario
    - `eliminar`: Elimina cualquier auto del sistema
  - Soporte para JSON y POST tradicional

#### Modelo Actualizado
- `modelo/Auto.php`
  - Nuevos métodos para administradores:
    - `listarTodos($orden)`: Lista global con orden ASC/DESC por ID
    - `actualizarAdmin($id, ...)`: Actualiza sin verificar usuario propietario
    - `eliminarAdmin($id)`: Elimina sin restricción de usuario
  - Protección contra inyección SQL en parámetro de orden

#### Vista Mejorada
- `vista/formulario.php`
  - Sistema de pestañas: Usuarios | Autos | Cargadores
  - Selector de ordenamiento con opciones visuales:
    - "ID ascendente (1 → N)"
    - "ID descendente (N → 1)"
  - Tabla dinámica de autos con carga asíncrona
  - Botones de acción con confirmación antes de eliminar
  - Formularios de edición con validación en tiempo real

#### Estilos Actualizados
- `styles/formulario.css`
  - Estilos para sistema de pestañas (tabs)
  - Tabla responsive para gestión de autos
  - Botones de acción con efectos hover (Editar: azul, Eliminar: rojo)
  - Selector de ordenamiento integrado en toolbar

---

### 🐛 Correcciones de Bugs

1. **Barra superior desapareciendo en principal.html**
   - Reglas CSS duplicadas causaban conflicto
   - `min-height: 100vh` en `.top-bar` provocaba salto visual
   - Solución: Unificación de reglas y z-index correcto

2. **Textos de navegación cortados**
   - Falta de espacio vertical en enlaces
   - Solución: Aumento de altura de barra a 80px y padding adecuado

3. **Logo poco legible**
   - Tamaño muy pequeño (36-42px) dificultaba identificación
   - Solución: Aumentado a 60px manteniendo proporciones

---

### ✨ Mejoras de UX

1. **Panel de Administración más completo**
   - Tres secciones bien definidas con navegación por pestañas
   - Gestión centralizada de usuarios, autos y cargadores
   - Selector visual de ordenamiento (ascendente/descendente)
   - Feedback inmediato al realizar acciones

2. **Experiencia visual mejorada**
   - Logo corporativo presente en toda la navegación
   - Colores consistentes (azul #1976d2 para hover)
   - Tipografía uniforme (Montserrat) en todas las páginas
   - Transiciones suaves y efectos hover profesionales

3. **Gestión de autos más intuitiva**
   - Tabla con toda la información relevante
   - Botones de acción claramente identificables
   - Confirmación antes de eliminar (previene errores)
   - Ordenamiento flexible según necesidades del admin

---

### 📦 Archivos Nuevos

- `api/autos_admin.php` - API para gestión administrativa de autos

### 📝 Archivos Modificados

**Modelos:**
- `modelo/Auto.php`
  - Métodos `listarTodos()`, `actualizarAdmin()`, `eliminarAdmin()`

**Vistas:**
- `vista/index.php` - Logo en barra superior
- `vista/registro.html` - Logo en barra superior
- `vista/principal.html` - Logo en barra superior
- `vista/formulario.php` - Pestaña de Autos con tabla y ordenamiento

**Estilos:**
- `styles/index.css` - Logo, hover azul, padding, email inputs
- `styles/principal.css` - Barra fija, logo, tipografía Montserrat, hover azul
- `styles/registro.css` - Email inputs
- `styles/formulario.css` - Estilos para pestañas y tabla de autos

---

### 🚀 Próximas Mejoras Sugeridas

- [ ] Búsqueda y filtrado de autos por usuario, marca o modelo
- [ ] Exportación de datos de autos a CSV/Excel
- [ ] Historial de modificaciones en autos
- [ ] Dashboard con estadísticas de autos por marca/año
- [ ] Validación de autonomía y año con rangos lógicos

---

## Versión 1.3.0 - 31 de Octubre de 2025

### 🎯 Cambios Principales

#### 🔐 Sistema de Autenticación Mejorado
- **Migración de autenticación basada en usuario a correo electrónico**
  - Los usuarios ahora inician sesión con su correo electrónico en lugar de nombre de usuario
  - Campo `correo` agregado a la tabla `usuarios` (VARCHAR(100) NOT NULL UNIQUE)
  - Actualización de formularios de login y registro para incluir correo electrónico

#### 📁 Reorganización de Arquitectura MVC
- **Carpeta `vista/` implementada**
  - Todos los archivos de vista movidos a la carpeta `vista/`
  - Actualización de todas las rutas relativas con prefijo `../`
  - Separación clara entre modelo, vista, controlador y API

#### 🗺️ Corrección de Mapa de Cargadores
- **Página Principal (principal.html)**
  - Corregida la ruta del fetch de cargadores: `'../api/cargadores.php'`
  - Los cargadores ahora se cargan correctamente al abrir la página

#### ⚙️ Panel de Administración
- **Gestión completa de usuarios**
  - Ahora se puede visualizar el correo de todos los usuarios
  - Capacidad de modificar correos electrónicos de usuarios
  - Añadida opción "Cargador" en el selector de tipo de usuario
  - Los administradores pueden cambiar usuarios a tipo "cargador"

#### 👤 Sistema de Edición de Perfil
- **Panel de Cliente**
  - Modal elegante para editar perfil personal
  - Los clientes pueden modificar:
    - Nombre de usuario
    - Correo electrónico
    - Contraseña (opcional)
  - Actualización automática de la sesión
  - Validación en tiempo real
  - Mensajes de éxito/error con diseño mejorado

#### 🚗 Panel de Cargador
- **Inicialización automática del mapa**
  - `initMap` definida globalmente (`window.initMap`)
  - Script de Google Maps cargado después de definir la función
  - El mapa ahora se carga automáticamente sin necesidad de refrescar

---

### 🔧 Cambios Técnicos Detallados

#### Base de Datos
```sql
-- Nuevo campo en tabla usuarios
ALTER TABLE usuarios ADD COLUMN correo VARCHAR(100) NOT NULL UNIQUE;
```

#### Archivos Modificados

**Modelos:**
- `modelo/Usuario.php`
- `api/autos.php`
  - Endpoints para listar/agregar/editar/eliminar autos del usuario autenticado
  - Soporte JSON y `application/x-www-form-urlencoded`
  - Método `insertar()`: Ahora acepta parámetro `$correo`
  - Método `verificarCredenciales()`: Cambiado de usuario a correo
  - Método `modificar()`: Añadido parámetro `$nuevoCorreo` con 4 casos de actualización
  - Método `listar()`: Incluye campo `correo` en SELECT

**Controladores:**
- `controlador/UsuarioControlador.php`
  - `loginUsuario()`: Ahora recibe `$correo` en lugar de `$username`
- `vista/cliente.php`
  - Estructura en pestañas (Autos/Viajes) y limpieza de import de estilos
  - `registrarUsuario()`: Acepta parámetro `$correo`
  - `modificarUsuario()`: Añadido parámetro `$nuevoCorreo` con lógica condicional
- `styles/formulario.css`: rediseño minimalista claro del admin (sidebar blanca, tarjetas, foco accesible)
- `styles/cliente.css`: sidebar y tarjetas para cliente, animaciones y responsive
  
- `api/registro.php`
  - Añadido manejo de campo `correo`
  
- `api/admin.php`
  - Endpoint `modificar_usuario`: Ahora recibe y procesa `nuevoCorreo`
  
- `api/cliente.php`
  - Nuevo endpoint `modificar_perfil` para edición de perfil personal
  - `session_start()` movido al inicio del archivo
  - Actualización automática de sesión tras modificación

**Vistas:**
- `vista/index.php`
  - Input cambiado a `type="email"`
  - Campo `correo` en lugar de `usuario`
  
- `vista/registro.html`
  - Añadido campo de correo electrónico entre usuario y contraseña
  
- `vista/formulario.php` (Panel Admin)
  - Columna de correo en lista de usuarios
  - Campo de email en formulario de edición
  - Opción "Cargador" agregada a selectores de tipo de usuario
  
- `vista/cliente.php`
  - Modal de edición de perfil implementado
  - Formulario con campos: usuario, correo, contraseña
  - Manejo de errores mejorado con console.log
  - Actualización dinámica del saludo tras edición
  
- `vista/cargador.php`
  - `window.initMap` definida globalmente
  - Script de Google Maps movido al final del documento
  - Carga automática del mapa sin necesidad de refresh

**Estilos:**
- `styles/index.css`
  - Añadido estilo para `input[type="email"]`
  
- `styles/registro.css`
  - Añadido estilo para `input[type="email"]`
  
- `styles/formulario.css`
  - Añadido estilo para `input[type="email"]`
  
- `styles/cliente.css`
  - Estilos completos para modal de edición de perfil
  - Animación `slideDown` para modal
  - Diseño responsive
  - Efectos hover y transiciones

---

### 🐛 Correcciones de Bugs

1. **Error de mapa no cargando en principal.html**
   - Ruta incorrecta: `'controlador/CargadorControlador.php'`
   - Ruta corregida: `'../api/cargadores.php'`

2. **Mapa de cargadores no apareciendo automáticamente**
   - `initMap` no estaba disponible globalmente cuando Google Maps la llamaba
   - Solución: Definir `window.initMap` antes de cargar el script

3. **Error de conexión al editar perfil**
   - `session_start()` duplicado en `api/cliente.php`
   - Sesión no iniciada al principio del archivo
   - Parámetros en orden incorrecto en `modificarUsuario()`

4. **Rutas rotas después de mover archivos a vista/**
   - Todas las rutas actualizadas con prefijo `../`
   - Afectó a: api/, styles/, controlador/

---

### ✨ Mejoras de UI/UX

1. **Modal de Edición de Perfil**
   - Diseño moderno con gradientes
   - Backdrop blur effect
   - Animación de entrada suave
   - Responsive design
   - Cierre con click fuera del modal o tecla ESC

2. **Consistencia Visual**
   - Todos los inputs de email con mismo estilo
   - Mensajes de éxito/error uniformes
   - Transiciones suaves en todos los elementos interactivos

3. **Feedback al Usuario**
   - Console.log para depuración
   - Mensajes de error más descriptivos
   - Actualización en tiempo real del saludo tras editar perfil

---

### 📝 Notas de Migración

**Para actualizar la base de datos:**
1. Ejecutar: `ALTER TABLE usuarios ADD COLUMN correo VARCHAR(100);`
2. Actualizar registros existentes con correos dummy
3. Ejecutar: `ALTER TABLE usuarios MODIFY correo VARCHAR(100) NOT NULL UNIQUE;`

**Cambios en el flujo de login:**
- Los usuarios ahora deben usar su correo electrónico para iniciar sesión
- El campo usuario se mantiene para identificación interna

---

### 🔒 Seguridad

- Validación de email en frontend y backend
- Passwords encriptados con `password_hash()` y `PASSWORD_BCRYPT`
- Sesiones actualizadas correctamente tras modificaciones
- Protección contra sesiones duplicadas

---

### 🎨 Estilos Nuevos

**Clases CSS Agregadas:**
- `.modal` - Contenedor principal del modal
- `.modal-content` - Contenido interno con animación
- `.close` - Botón de cerrar (×)
- Animación `@keyframes slideDown`
- Estilos responsive para modal

---

### 🚀 Próximas Mejoras Sugeridas

- [ ] Implementar edición de perfil para cargadores
- [ ] Validación de formato de email en tiempo real
- [ ] Recuperación de contraseña por correo
- [ ] Verificación de email al registrarse
- [ ] Foto de perfil personalizada
- [ ] Historial de cambios de perfil

---

### 📞 Soporte

Para reportar bugs o sugerir mejoras, contactar al equipo de desarrollo.

**Desarrollado por:** ShonosTech
**Fecha de Release:** 31 de Octubre de 2025  
**Versión Anterior:** 1.3.0  
**Versión Actual:** 1.4.0
