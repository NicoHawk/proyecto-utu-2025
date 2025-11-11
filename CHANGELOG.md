# 📋 Changelog - Sistema de Gestión de Autos Eléctricos

## Versión 1.8.1 - 11 de Noviembre de 2025 🔧

### 🎯 Resumen
Parche de corrección crítica que soluciona errores en el sistema de reservas y sincronización de estados de cargadores. Esta versión corrige problemas de comunicación API-Controlador-Modelo y actualización de estados en tiempo real.

### 🐛 Correcciones Críticas

#### 🔴 Sistema de Reservas - Arquitectura MVC
**Problema:** La API `reservas.php` tenía código duplicado, mezcla de lógica de negocio con capa HTTP, y JavaScript dentro del archivo PHP que rompía la respuesta JSON.

**Solución:**
- ✅ Refactorización completa de `api/reservas.php`:
  - Eliminado código duplicado (bloque `listar_usuario` aparecía 2 veces)
  - Removido código JavaScript que estaba al final del archivo PHP
  - Implementado patrón MVC puro: API → Controlador → Modelo
  - Función `responder()` unificada para todas las respuestas JSON
  - Manejo correcto de `$_SESSION['usuario']` (campo VARCHAR, no INT)

**Archivos afectados:**
```php
// api/reservas.php - ANTES ❌
<?php
// ...código mezclado...
if ($method === 'GET' && ($_GET['accion'] ?? '') === 'listar_usuario') {
    $pdo = /* acceso directo a BD */
    // ...
}
// ...más abajo, duplicado...
if ($method === 'GET' && ($_GET['accion'] ?? '') === 'listar_usuario') {
    // mismo código otra vez
}
// ...y al final...
<script>setInterval(...);</script> // ← JavaScript en PHP!
?>

// api/reservas.php - AHORA ✅
<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../controlador/ReservaControlador.php';

function responder($data, $codigo = 200) {
    http_response_code($codigo);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$usuario = $_SESSION['usuario']; // VARCHAR, no usuario_id
// ...solo lógica HTTP, delega al Controlador...
```

#### 🟡 Modelo de Reservas - Compatibilidad con VARCHAR
**Problema:** El sistema asumía `usuario_id INT` pero la tabla `reservas` usa `usuario VARCHAR(50)` como FK.

**Solución:**
- ✅ Todos los métodos del `ReservaControlador` actualizados:
  - `crearReserva($usuario, ...)` - Recibe string username
  - `listarReservasUsuario($usuario)` - WHERE usuario = ?
  - `cancelarReserva($usuario, $reserva_id)` - Validación con username
- ✅ `Reserva.php` adaptado para trabajar con campo `usuario VARCHAR`

**Migración SQL (si aplicaste cambios previos erróneos):**
```sql
-- Verificar estructura actual
DESCRIBE reservas;

-- Si tenías usuario_id, revertir a usuario VARCHAR
-- ALTER TABLE reservas 
-- DROP FOREIGN KEY fk_usuario_id; -- si existía
-- ALTER TABLE reservas 
-- DROP COLUMN usuario_id;
-- ALTER TABLE reservas 
-- ADD COLUMN usuario VARCHAR(50) NOT NULL AFTER id;
```

#### 🔵 Frontend - Manejo de Respuestas API
**Problema:** El JavaScript esperaba formato `{reservas:[...]}` pero la API a veces devolvía array directo `[...]` o con otras claves.

**Solución:**
- ✅ Función `listarReservas()` tolerante a múltiples formatos:
  ```javascript
  const reservas = Array.isArray(resp) ? resp
                 : Array.isArray(resp?.reservas) ? resp.reservas
                 : Array.isArray(resp?.data) ? resp.data
                 : [];
  ```
- ✅ Mapeo flexible de campos (inicio/fin/estado/estación) con fallbacks:
  ```javascript
  const estacion = r.estacion || r.nombre_estacion || r.nombre || 
                  (r.cargador_id ? `Estación #${r.cargador_id}` : '-');
  ```
- ✅ Mismo patrón aplicado a `cargarHistorialReservas()`

#### 🟢 Actualización Automática de Estados
**Problema:** Los cargadores no volvían a "disponible" después de cancelar una reserva o al finalizar el tiempo reservado.

**Solución:**
- ✅ Nuevo método `marcarReservasCompletadas()` en `Reserva.php`:
  ```php
  public function marcarReservasCompletadas() {
      date_default_timezone_set('America/Montevideo');
      $sql = "UPDATE reservas 
              SET estado='completada' 
              WHERE estado='confirmada' AND fin < NOW()";
      return $this->conexion->exec($sql);
  }
  ```
- ✅ Método `ReservaControlador::marcarReservasCompletadas()` agregado
- ✅ Llamado automático antes de:
  - Listar cargadores (`api/cargadores.php`)
  - Listar reservas de usuario
  - Crear nueva reserva
- ✅ Estados de cargadores calculados en tiempo real:
  ```php
  // En api/cargadores.php
  $sql = "SELECT c.*,
         CASE WHEN EXISTS (
             SELECT 1 FROM reservas r
             WHERE r.cargador_id = c.id
               AND r.estado <> 'cancelada'
               AND NOW() BETWEEN r.inicio AND r.fin
         ) THEN 'ocupado' ELSE 'disponible' END AS estado
  FROM cargadores c";
  ```

#### 🟣 Historial de Reservas
**Problema:** El historial mostraba "Sin datos" aunque había reservas canceladas/completadas.

**Solución:**
- ✅ Filtro corregido en `cargarHistorialReservas()`:
  ```javascript
  const pasadas = reservas.filter(r => {
      const estado = String(r.estado || '').toLowerCase();
      // Canceladas O completadas O con fecha pasada
      if (estado === 'cancelada' || estado === 'completada') return true;
      const finDate = new Date((r.fin || '').replace(' ', 'T'));
      return finDate < ahora;
  });
  ```
- ✅ Ordenamiento descendente por fecha (más recientes primero)
- ✅ Estilos CSS para distinguir estados:
  ```css
  .estado-completada { color: #4ade80; }
  .estado-cancelada { color: #ef4444; }
  ```

#### 🔴 Refrescado Automático de Estados
**Problema:** Había que recargar manualmente la página para ver cambios de estado tras crear/cancelar reserva.

**Solución:**
- ✅ Función `refrescarEstados()` que actualiza sin tocar filtros ni ruta:
  ```javascript
  function refrescarEstados() {
      fetch('../api/cargadores.php')
        .then(r => r.json())
        .then(data => {
            estaciones = Array.isArray(data) ? data : [];
            // Repintar según contexto actual (con ruta o sin ruta)
            if (rutaCoordsLatLng.length) {
                // Con ruta: mantener filtro de cercanas
                const cercanas = aplicarFiltros(estaciones.filter(...));
                pintarEstaciones(cercanas);
            } else {
                // Sin ruta: mostrar todo filtrado
                const filtradas = aplicarFiltros(estaciones);
                pintarEstaciones(filtradas);
            }
        });
  }
  ```
- ✅ Auto-refresh cada 10 segundos cuando pestaña Viajes está activa:
  ```javascript
  setInterval(() => {
      const tab = document.getElementById('tab-viajes');
      if (tab && tab.style.display !== 'none') {
          refrescarEstados();
      }
  }, 10000);
  ```
- ✅ Refresh inmediato tras:
  - Crear reserva (después de submit exitoso)
  - Cancelar reserva (después de confirmar)
  - Volver a pestaña Viajes desde otra pestaña

---

### 🔧 Cambios Técnicos Detallados

#### Archivos Modificados

**API:**
- `api/reservas.php` ⚙️⚙️⚙️
  - **Líneas eliminadas:** ~50 (código duplicado y JS)
  - **Patrón MVC implementado:** Sí ✅
  - **Manejo de sesión:** Corregido para usar `$_SESSION['usuario']` VARCHAR
  - **Respuestas unificadas:** Función `responder()` en todas las salidas
  - **Soporte JSON:** Content-Type detection mejorado

- `api/cargadores.php` ⚙️
  - **Cálculo de estado:** Ahora dinámico basado en reservas activas
  - **Timezone:** `date_default_timezone_set('America/Montevideo')`
  - **Query optimizada:** CASE WHEN con subconsulta EXISTS

**Controlador:**
- `controlador/ReservaControlador.php` ⚙️
  - **Nuevo método:** `marcarReservasCompletadas()`
  - **Firma de funciones actualizada:** Todos reciben `$usuario` (VARCHAR)

**Modelo:**
- `modelo/Reserva.php` ⚙️
  - **Nuevo método:** `marcarReservasCompletadas()`
  - **Campo usuario:** Cambiado a VARCHAR en todas las queries
  - **Validación de solapamientos:** Mejorada para evitar duplicados

**Vista:**
- `vista/cliente.php` ⚙️⚙️
  - **Función nueva:** `refrescarEstados()`
  - **Auto-refresh:** Timer de 10s con verificación de pestaña activa
  - **Refresh manual:** En botones de crear/cancelar reserva
  - **Tolerancia de formatos:** Arrays flexibles en listar reservas
  - **Mapeo de campos:** Múltiples alias soportados (inicio/fecha_inicio/start/fechaInicio)

---

### 📊 Mejoras de UX

1. **Estados en Tiempo Real**
   - ✅ Cargadores pasan a "ocupado" al reservar (sin delay)
   - ✅ Vuelven a "disponible" al cancelar (inmediato)
   - ✅ Se liberan automáticamente al vencer reserva
   - ✅ Actualización cada 10s mientras se usa la app

2. **Historial Funcional**
   - ✅ Muestra reservas canceladas y completadas
   - ✅ Ordenamiento cronológico descendente
   - ✅ Colores distintivos por estado

3. **Reservas sin Errores**
   - ✅ Formulario de reserva funciona en todos los casos
   - ✅ Mensajes de error claros y específicos
   - ✅ Validación de solapamientos real

4. **Feedback Visual**
   - ✅ Logs en consola para debugging (`console.log` con emojis)
   - ✅ Mensajes de éxito/error en modales
   - ✅ Actualización automática de tablas

---

### 🚀 Testing Realizado

#### Casos de Prueba Validados ✅

1. **Crear Reserva**
   - [x] Reserva nueva se crea correctamente
   - [x] Cargador pasa a "ocupado" inmediatamente
   - [x] Aparece en "Mis reservas" sin recargar
   - [x] Se rechaza solape con reserva existente

2. **Cancelar Reserva**
   - [x] Estado cambia a "cancelada"
   - [x] Cargador vuelve a "disponible" (si no hay otras reservas)
   - [x] Desaparece de "Mis reservas"
   - [x] Aparece en "Historial"

3. **Reservas Vencidas**
   - [x] Se marcan como "completada" automáticamente
   - [x] Cargador se libera cuando fin < NOW()
   - [x] Aparecen en historial

4. **Sincronización**
   - [x] Auto-refresh cada 10s funciona
   - [x] Refresh manual tras crear/cancelar funciona
   - [x] Estados se actualizan sin perder filtros de ruta

5. **Historial**
   - [x] Muestra canceladas
   - [x] Muestra completadas
   - [x] Muestra pasadas (aunque estado sea "confirmada")
   - [x] Orden descendente por fecha

---

### 📝 Notas de Migración

#### Para usuarios de v1.8.0 → v1.8.1

**1. Verificar Estructura de Base de Datos**
```sql
-- La tabla reservas DEBE tener campo usuario VARCHAR
DESCRIBE reservas;

-- Esperado:
-- usuario | varchar(50) | NO | | NULL |
```

**2. No requiere migración SQL** si ya tenías:
- Campo `usuario VARCHAR(50)` en tabla `reservas`
- Tabla `usuarios` con columna `usuario VARCHAR(50)`

**3. Limpiar cache del navegador**
```javascript
// O presionar Ctrl+Shift+R en la página
// Para forzar recarga de cliente.php
```

**4. Verificar zona horaria en php.ini**
```ini
; Debería estar configurado:
date.timezone = America/Montevideo
```

---

### 🔍 Checklist de Cumplimiento MVC

#### API Layer ✅
- [x] Solo maneja HTTP requests/responses
- [x] Valida permisos y sesiones
- [x] Llama funciones del Controlador
- [x] Retorna JSON con función `responder()`
- [x] NO hace queries SQL
- [x] NO tiene código duplicado
- [x] NO tiene JavaScript dentro del PHP

#### Controlador Layer ✅
- [x] Lógica de negocio pura
- [x] Funciones con parámetros explícitos (`$usuario` VARCHAR)
- [x] Retorna arrays estructurados
- [x] NO hace echo/print
- [x] NO accede a $_POST/$_GET/$_SESSION directamente

#### Modelo Layer ✅
- [x] Interacción con BD (PDO)
- [x] Métodos CRUD correctos
- [x] Timezone configurado antes de queries con NOW()
- [x] Retorna datos sin procesar

---

### 🐛 Bugs Conocidos Solucionados

| # | Descripción | Severidad | Estado |
|---|-------------|-----------|--------|
| 1 | Error "Parse error: syntax error, unexpected token '}' in reservas.php" | 🔴 Crítico | ✅ Resuelto |
| 2 | "usuario_id no disponible en sesión" | 🔴 Crítico | ✅ Resuelto |
| 3 | Historial muestra "Sin datos" con reservas | 🟡 Medio | ✅ Resuelto |
| 4 | Cargadores no vuelven a "disponible" | 🔴 Crítico | ✅ Resuelto |
| 5 | Estados no se actualizan sin recargar | 🟡 Medio | ✅ Resuelto |
| 6 | Código JavaScript dentro de archivo PHP | 🔴 Crítico | ✅ Resuelto |
| 7 | Código duplicado en listar_usuario | 🟢 Menor | ✅ Resuelto |

---

### 📞 Soporte

**Desarrollado por:** ShonosTech  
**Fecha de Release:** 11 de Noviembre de 2025  
**Versión Anterior:** 1.8.0  
**Versión Actual:** 1.8.1  
**Tipo:** Patch (corrección de bugs críticos)

---

### 🎓 Lecciones Aprendidas

1. **Nunca mezclar capas MVC**
   - Mantener API, Controlador y Modelo estrictamente separados
   - Usar funciones helper (`responder()`) para consistencia

2. **Validar tipos de datos**
   - VARCHAR vs INT: Verificar schema antes de asumir
   - Usar prepared statements siempre

3. **Testing exhaustivo**
   - Probar edge cases (reservas vencidas, solapadas, etc.)
   - Validar sincronización en tiempo real

4. **Logs y debugging**
   - Console.log con emojis ayuda a identificar flujos
   - Mensajes de error específicos facilitan troubleshooting

---

### ✨ Próximas Mejoras (v1.9.0)

#### 🚀 Planificación de Viajes - Mejoras Avanzadas
- [ ] **Sistema de costo estimado por parada**
  - Cálculo automático: kWh necesarios × tarifa por kWh
  - Visualización en tiempo real en tabla de paradas sugeridas
  - Comparativa de costos entre diferentes estaciones
  - Total estimado del viaje completo

- [ ] **Optimización eco para salud de batería**
  - Recomendaciones para mantener carga entre 20–80%
  - Alertas cuando se planea carga fuera del rango óptimo
  - Sugerencias de paradas adicionales para evitar descargas profundas
  - Estadísticas de impacto en vida útil de la batería

- [ ] **Reordenar paradas manualmente**
  - Drag & drop en tabla de paradas sugeridas
  - Recalculo automático de ruta tras reordenar
  - Validación de autonomía tras cambios
  - Guardar configuración personalizada

- [ ] **Filtro por amenities en estaciones**
  - Nuevos campos en BD: `tiene_cafe`, `tiene_bano`, `abierto_24h`, `wifi`, `area_descanso`
  - Checkboxes en filtros de planificación
  - Iconos en marcadores del mapa indicando servicios disponibles
  - Información en popup y modal de detalle

- [ ] **Rutas favoritas persistentes**
  - Guardar rutas frecuentes con nombre personalizado
  - Listado de rutas guardadas con edición/eliminación
  - Carga rápida desde selector dropdown
  - Tabla: `rutas_favoritas` (id, usuario, nombre, origen, destino, auto_id, paradas_json, fecha_creacion)

- [ ] **Estadísticas de ahorro energético**
  - Dashboard con métricas: kWh ahorrados vs gasolina, CO₂ evitado, costo total de cargas
  - Gráficos de consumo por mes/año
  - Comparativa con vehículos a combustión
  - Badges de logros (ej: "100 cargas completadas", "1000 km sin emisiones")

---

### 💳 Sistema de Pago y Facturación (v2.0.0) - Planificado

#### 🎯 Requerimiento 7 - Pago Directo desde la App

**7.1 Integración de Pasarela de Pago**
- [ ] Selección de pasarela: Mercado Pago / PayPal / Stripe (configurable por región)
- [ ] Flujo de pago directo desde modal de reserva
- [ ] Confirmación de pago en tiempo real con webhook
- [ ] Actualización automática de estado de reserva: `pendiente_pago` → `confirmada`
- [ ] Manejo de errores: timeout, rechazo de tarjeta, fondos insuficientes
- [ ] Logs de transacciones en tabla `transacciones_pago`

**7.2 Módulo de Métodos de Pago**
- [ ] **Tarjeta de crédito/débito:**
  - Tokenización segura (PCI-DSS compliant)
  - Guardado opcional de tarjetas (vault)
  - Selector de tarjetas guardadas en checkout
  - Validación de CVV en cada pago
- [ ] **Saldo prepago:**
  - Tabla `billeteras` (usuario_id, saldo, moneda)
  - Recarga de saldo vía tarjeta o transferencia
  - Historial de movimientos (recargas, consumos, reembolsos)
  - Descuento automático al confirmar reserva
  - Notificación de saldo bajo
- [ ] **QR / Código de cupón:**
  - Tabla `cupones` (codigo, descuento_porcentaje, fecha_vencimiento, usos_maximos)
  - Validación de cupón en checkout
  - Aplicación de descuento en monto final

**7.3 Factura Electrónica**
- [ ] **Generación automática post-pago:**
  - Datos: N° factura, fecha, usuario, reserva_id, estación, kWh consumidos, tarifa, subtotal, IVA, total
  - Formato PDF con logo y datos fiscales de la empresa
  - Formato JSON estructurado (para contabilidad)
  - Hash SHA-256 para verificación de integridad
- [ ] **Almacenamiento y descarga:**
  - Tabla `facturas` (id, reserva_id, usuario_id, numero_factura, pdf_path, json_data, hash, fecha_emision)
  - Endpoint `GET /api/facturas.php?id=...` para descarga directa
  - Listado en "Mi cuenta" → "Mis facturas"
  - Envío automático por email al finalizar reserva
- [ ] **Cumplimiento normativo:**
  - Numeración secuencial y única
  - Campos obligatorios según legislación local (Uruguay: RUT, CAE, etc.)
  - Firma digital opcional (certificado DGI)
  - Reporte mensual de facturación para contabilidad

**🗄️ Tablas de Base de Datos Previstas**

```sql
-- Tabla de pagos
CREATE TABLE pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    usuario_id INT NOT NULL,
    metodo ENUM('tarjeta', 'saldo_prepago', 'cupon') NOT NULL,
    monto_total DECIMAL(10,2) NOT NULL,
    moneda VARCHAR(3) DEFAULT 'UYU',
    estado ENUM('pendiente', 'aprobado', 'rechazado', 'reembolsado') DEFAULT 'pendiente',
    referencia_externa VARCHAR(100), -- ID de transacción de la pasarela
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de facturas
CREATE TABLE facturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    usuario_id INT NOT NULL,
    numero_factura VARCHAR(50) UNIQUE NOT NULL,
    pdf_path VARCHAR(255),
    json_data JSON,
    hash_verificacion VARCHAR(64), -- SHA-256
    subtotal DECIMAL(10,2) NOT NULL,
    iva DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    fecha_emision TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    enviado_email BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_numero (numero_factura)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de billeteras (saldo prepago)
CREATE TABLE billeteras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNIQUE NOT NULL,
    saldo DECIMAL(10,2) DEFAULT 0.00,
    moneda VARCHAR(3) DEFAULT 'UYU',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de movimientos de billetera
CREATE TABLE movimientos_billetera (
    id INT AUTO_INCREMENT PRIMARY KEY,
    billetera_id INT NOT NULL,
    tipo ENUM('recarga', 'consumo', 'reembolso') NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    saldo_anterior DECIMAL(10,2) NOT NULL,
    saldo_nuevo DECIMAL(10,2) NOT NULL,
    descripcion VARCHAR(255),
    referencia_id INT, -- ID de pago o recarga
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (billetera_id) REFERENCES billeteras(id),
    INDEX idx_billetera (billetera_id),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de cupones de descuento
CREATE TABLE cupones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    descuento_porcentaje DECIMAL(5,2) NOT NULL,
    fecha_vencimiento DATE,
    usos_maximos INT DEFAULT 1,
    usos_actuales INT DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**🔌 Endpoints de API Futuros**

```php
// POST /api/pagos.php?accion=crear
// Body: { reserva_id, metodo, monto, token_tarjeta?, cupon_codigo? }
// Response: { exito: true, pago_id, estado, referencia_externa }

// GET /api/pagos.php?accion=listar_usuario
// Response: { exito: true, pagos: [...] }

// POST /api/pagos.php?accion=reembolsar
// Body: { pago_id, motivo }
// Response: { exito: true, nuevo_estado }

// GET /api/facturas.php?id=123
// Response: PDF download (Content-Type: application/pdf)

// GET /api/facturas.php?accion=listar_usuario
// Response: { exito: true, facturas: [...] }

// POST /api/billetera.php?accion=recargar
// Body: { monto, metodo_pago: 'tarjeta', token_tarjeta }
// Response: { exito: true, nuevo_saldo }

// GET /api/billetera.php?accion=consultar_saldo
// Response: { exito: true, saldo, moneda, movimientos_recientes: [...] }

// POST /api/cupones.php?accion=validar
// Body: { codigo, monto_base }
// Response: { exito: true, descuento_aplicado, monto_final }
```

**🔐 Seguridad Implementada**
- [ ] Tokenización de tarjetas (nunca almacenar números completos)
- [ ] Comunicación HTTPS obligatoria
- [ ] Hash SHA-256 en facturas para prevenir alteraciones
- [ ] Validación de webhook signatures (HMAC)
- [ ] Rate limiting en endpoints de pago
- [ ] Logs de auditoría en todas las transacciones
- [ ] Encriptación de datos sensibles en BD (AES-256)

---

### 🌐 Internacionalización y Responsive (v1.9.0)

#### 🗣️ Sistema Bilingüe Español/Inglés

**Arquitectura i18n**
- [ ] **Archivos de traducción:**
  - `i18n/es.json` - Español (idioma base)
  - `i18n/en.json` - Inglés
  - Estructura: `{ "clave": "traducción", "clave.subclave": "valor" }`
  - Sin uso de traductores automáticos - Solo diccionarios manuales (Wordreference, Cambridge)

- [ ] **Helper functions:**
  ```php
  // PHP: i18n/i18n.php
  function t($key, $params = []) {
      global $lang;
      $translations = json_decode(file_get_contents("i18n/{$lang}.json"), true);
      $text = $translations[$key] ?? $key;
      foreach ($params as $k => $v) {
          $text = str_replace("{{$k}}", $v, $text);
      }
      return $text;
  }
  ```
  
  ```javascript
  // JavaScript: i18n.js
  const i18n = {
      currentLang: localStorage.getItem('lang') || 'es',
      translations: {},
      async load(lang) {
          const response = await fetch(`../i18n/${lang}.json`);
          this.translations = await response.json();
          this.currentLang = lang;
          localStorage.setItem('lang', lang);
      },
      t(key, params = {}) {
          let text = this.translations[key] || key;
          Object.keys(params).forEach(k => {
              text = text.replace(`{${k}}`, params[k]);
          });
          return text;
      }
  };
  ```

- [ ] **Selector de idioma:**
  - Dropdown en header: 🌐 ES | EN
  - Guardado en `localStorage` (frontend) y sesión (backend)
  - Recarga dinámica de textos sin refresh completo
  - Fallback a español si falta traducción

**Terminología Clave (ES → EN)**

| Español | English | Contexto |
|---------|---------|----------|
| Reserva | Booking | Sistema de reservas |
| Cargador | Charging station | Estaciones de carga |
| Autonomía | Range | Autonomía del vehículo |
| Parada esencial | Essential stop | Planificación de rutas |
| Parada opcional | Optional stop | Sugerencias de conveniencia |
| Disponible | Available | Estado de cargador |
| Ocupado | In use | Estado de cargador |
| Fuera de servicio | Out of service | Estado de cargador |
| Tipo de conector | Connector type | Especificaciones técnicas |
| Potencia | Power output | kW del cargador |
| Duración | Duration | Tiempo de carga estimado |
| Mis reservas | My bookings | Panel de usuario |
| Historial | History | Cargas anteriores |
| Planificar viaje | Plan trip | Función principal |
| Batería actual | Current battery | % de carga |
| Alcance real | Actual range | Autonomía × % batería |

**Documentación Bilingüe**
- [ ] `README_EN.md` (versión reducida en inglés)
- [ ] Comentarios críticos en código en inglés
- [ ] Mensajes de error y validación traducidos

---

#### 📱 Responsive Avanzado

**Breakpoints Definidos**
```css
/* Móvil (portrait) */
@media (max-width: 480px) { ... }

/* Móvil (landscape) / Tablet (portrait) */
@media (min-width: 481px) and (max-width: 768px) { ... }

/* Tablet (landscape) / Desktop pequeño */
@media (min-width: 769px) and (max-width: 1024px) { ... }

/* Desktop estándar */
@media (min-width: 1025px) and (max-width: 1440px) { ... }

/* Desktop grande / 4K */
@media (min-width: 1441px) { ... }
```

**Adaptaciones por Breakpoint**

**< 480px (Móvil pequeño):**
- [ ] Sidebar colapsado con botón hamburguesa
- [ ] Tablas en modo stacked (vertical)
  ```html
  <tr data-label="Estación">cargador1</tr>
  <tr data-label="Estado">disponible</tr>
  ```
- [ ] Modales full-screen
- [ ] Inputs y botones 100% width
- [ ] Mapa con altura fija (300px)
- [ ] Filtros en accordion colapsable
- [ ] Font-size base: 14px

**481-768px (Tablet portrait):**
- [ ] Sidebar sticky lateral (250px)
- [ ] Tablas con scroll horizontal
- [ ] Grid de 2 columnas en formularios
- [ ] Modales 90% viewport
- [ ] Mapa altura 400px
- [ ] Font-size base: 15px

**769-1024px (Tablet landscape):**
- [ ] Sidebar fija (280px)
- [ ] Tablas completas visibles
- [ ] Grid de 3 columnas en formularios
- [ ] Modales 70% viewport
- [ ] Mapa altura 500px
- [ ] Font-size base: 16px

**1025-1440px (Desktop estándar):**
- [ ] Sidebar fija (300px)
- [ ] Layout actual optimizado
- [ ] Modales 50-60% viewport
- [ ] Mapa altura 600px

**> 1441px (Desktop grande):**
- [ ] Max-width container: 1600px
- [ ] Sidebar 320px
- [ ] Espaciado amplio
- [ ] Modales max-width 800px

**Tablas Móviles (modo stacked)**
```css
@media (max-width: 768px) {
    table, thead, tbody, th, td, tr {
        display: block;
    }
    thead tr {
        position: absolute;
        top: -9999px;
        left: -9999px;
    }
    tr {
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
    }
    td {
        border: none;
        position: relative;
        padding-left: 50%;
    }
    td:before {
        content: attr(data-label);
        position: absolute;
        left: 10px;
        font-weight: bold;
    }
}
```

**Accesibilidad (WCAG AA)**
- [ ] Contraste mínimo 4.5:1 para textos normales
- [ ] Contraste mínimo 3:1 para textos grandes (18px+)
- [ ] Focus visible en todos los elementos interactivos
- [ ] Navegación por teclado completa (Tab, Enter, Esc)
- [ ] Atributos ARIA en componentes dinámicos:
  ```html
  <button aria-label="Cerrar modal" aria-expanded="true">✕</button>
  <div role="alert" aria-live="polite">Reserva creada exitosamente</div>
  ```
- [ ] Alt text en todas las imágenes/iconos
- [ ] Skip links para navegación rápida
- [ ] Tamaño mínimo de botones: 44×44px (touch targets)

**Testing Responsive**
- [ ] Chrome DevTools (todos los breakpoints)
- [ ] Firefox Responsive Design Mode
- [ ] Safari (iOS real device testing)
- [ ] Chrome Mobile (Android real device testing)
- [ ] Lighthouse audit (Performance + Accessibility)

---

### 📞 Soporte

**Desarrollado por:** ShonosTech  
**Versión Actual:** 1.8.1  
**Próximas Versiones Planificadas:**
- v1.9.0 - Mejoras de planificación + i18n + responsive
- v2.0.0 - Sistema de pagos y facturación completo

---

**¿Tenés sugerencias o reportes de bugs?** Contactá al equipo de desarrollo.
