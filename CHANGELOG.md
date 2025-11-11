# 📋 Changelog - Sistema de Gestión de Autos Eléctricos

## Versión 1.8.0 - 11 de Noviembre de 2025

### 🆕 Resumen rápido (diff respecto versión preliminar 1.8.0 inicial)
- Recomendación de paradas siempre activa (se elimina checkbox “Forzar sugerencias”).
- Tipos de parada añadidos: esencial / opcional.
- Panel de resumen de ruta con alcance real y clasificación de paradas.
- Liberación y marcado automático de reservas vencidas como “completada”.
- Alineación izquierda de controles de planificación (origen/destino/auto/radio).
- Mejora visual del bloque de resumen (#resumenRuta).
- Sin cambios de esquema de BD en esta revisión (solo lógica y estilos).

### 🎯 Cambios Principales
#### 🔋 Uso de batería actual del vehículo
- Slider batería (%), impacto directo en cálculo de autonomía disponible.
- Colores (verde / naranja / rojo) según nivel.

#### 🧠 Algoritmo de recomendación siempre activo
- Antes: mostraba nada si la autonomía alcanzaba (opcional con checkbox).
- Ahora: siempre genera lista:
  - 🔴 Paradas esenciales (necesarias).
  - 🟡 Paradas opcionales (conveniencia: potencia, compatibilidad, posición estratégica).
- Margen de seguridad: 20%. Recarga simulada al 80%.
- Máximo paradas opcionales: 3; esenciales iterativas hasta cubrir ruta (límite 10).

#### 📊 Resumen de ruta
- Distancia total, autonomía base, alcance real (autonomía * % batería).
- Conteo separado esenciales / opcionales.
- Mensaje verde cuando no hay esenciales.

#### 🗺️ Marcadores diferenciados
- Esencial: círculo rojo/naranja.
- Opcional: círculo amarillo.
- Tooltip incluye km desde origen, batería antes/después, tiempo estimado.

#### 📋 Tabla de estaciones
- Columna “Parada Recomendada” muestra tipo, baterías, duración estimada y acción reservar.
- Estilos visuales para distinguir esencial (naranja) / opcional (amarillo).

#### ⚡ Reservas y estados
- Método `marcarReservasCompletadas()` en `Reserva.php`.
- Llamado antes de listar cargadores / reservas para liberar cargadores vencidos.
- Historial incluye automáticamente completadas sin acción manual.

#### 🎨 UI / Layout
- Quick-bar y selector de auto alineados a la izquierda.
- Inputs origen/destino con ancho fijo (≈280px).
- Bloque #resumenRuta con gradiente y tipografía mejorada.

### 🔧 Cambios Técnicos
- vista/cliente.php:
  - Eliminado checkbox “Forzar sugerencias”.
  - Función `calcularParadasRecomendadas()` refactorizada (añade tipo).
  - Función `mostrarResumenRuta()` nueva.
  - Marcadores Leaflet con color según `p.tipo`.
  - Removida redeclaración local de `paradasRecomendadas`.
  - Cierre correcto de `trazarRutaYSugerir()`.
- modelo/Reserva.php:
  - Nuevo método `marcarReservasCompletadas()`.
- controlador / APIs:
  - Integración de marcado automático en flujos de listado (reservas/cargadores).
- styles/cliente.css:
  - Estilos para `#resumenRuta` y realineación de `.quick-bar`.

### 🐛 Correcciones
1. Función principal sin cierre → se agregó `}`.
2. `renderPanelEstaciones` definida dentro de planificación → movida a ámbito global.
3. Paradas vacías con autonomía suficiente → ahora muestra opcionales + mensaje.
4. Error “renderPanelEstaciones is not defined” → resuelto moviendo definición.
5. Navegación rota por excepción JS → corregida sintaxis y listeners.
6. Sombra de variable `paradasRecomendadas` → eliminado `let` duplicado.

### ✨ UX
- Siempre hay sugerencias (evita pantalla “vacía”).
- Claridad esencial vs opcional reduce confusión de usuario.
- Resumen compacto e informativo inmediatamente bajo el mapa.
- Reservas se actualizan y liberan sin intervención manual.

### 📊 Ejemplos
- Ruta corta (distancia < autonomía real): 0 esenciales / N opcionales.
- Ruta supera autonomía parcial (batería baja): ≥1 esencial.
- Ruta muy larga con batería baja: varias esenciales, quizá sin opcionales.

### 🚀 Próximas Mejoras Sugeridas
- Sistema de costo estimado por parada (kWh * tarifa).
- Optimización eco (mantener entre 20–80% para salud de batería).
- Reordenar paradas manualmente y recalcular ruta.
- Amenities en estaciones (filtro: café/baño/24h).
- Persistir rutas favoritas.
- Estadísticas de ahorro energético.

### 💳 Requerimiento 7 (Pago y Facturación) – Planificado
- 7.1 Pago directo de una carga desde la app (integración pasarela).
- 7.2 Módulo métodos de pago: tarjeta / saldo prepago.
- 7.3 Factura electrónica (PDF + JSON) descargable + envío por email.
- Tablas previstas: `pagos` (id, reserva_id, usuario_id, método, total, fecha, estado), `facturas`.
- Endpoints futuros: `POST /api/pagos.php` (crear), `GET /api/facturas.php?id=...` (descarga).
- Generación hash verificación (integridad).

### 🌐 Próxima Entrega Bilingüe / Responsive
- Sistema i18n (archivos `i18n/es.php` / `i18n/en.php` JSON para frontend).
- Helper `t(clave)` en PHP y JS (cache localStorage).
- Diccionario manual (Wordreference) sin uso de traductor automático.
- Terminología clave: reserva=booking, cargador=charging station, autonomía=range, parada esencial=essential stop.
- Selector idioma (ES | EN) en header.
- README_EN.md reducido.
- Responsive avanzado:
  - Breakpoints: 480 / 768 / 1024 / 1440.
  - Tablas móviles en modo stacked (data-label).
  - Modales full-screen < 480px.
  - Accesibilidad (contraste AA) verificado en ambos idiomas.

### 📞 Soporte
- Sin cambios: canal regular de reporte.

**Fecha de Release:** 11/11/2025  
**Versión Anterior:** 1.7.0  
**Versión Actual:** 1.8.0  
**Tipo:** Minor (algoritmo + UX + mantenimiento)

---

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

### 📦 Archivos Eliminados

- ❌ `api/autos_admin.php` - Funcionalidad integrada en `admin.php`

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
    - Inputs: fecha (date), hora (time), duración (múltiplos de 15)
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
  - La API dependía de
