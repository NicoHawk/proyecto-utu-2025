## Versión 1.9.0 - 12 de Noviembre de 2025 💳

### 🎯 Resumen
Implementación completa del sistema de **pagos directos** y **generación automática de facturas en PDF**. Los usuarios ahora pueden pagar sus reservas desde la app usando métodos de pago configurables y descargar facturas profesionales en formato PDF.

---

### 🆕 Características Principales

#### 💳 Sistema de Pagos Integrado

**1. Métodos de Pago Configurables**
- ✅ **Tarjeta de crédito** (simulado - listo para integrar pasarela real)
- ✅ **Tarjeta de débito** (simulado)
- ✅ **Cuenta prepago** (simulado)
- ✅ Tabla `metodos_pago` en BD con campo `activo` para habilitar/deshabilitar métodos
- ✅ API `GET /api/pagos.php?accion=metodos` para listar métodos disponibles

**2. Flujo de Pago Completo**
- ✅ Botón **"Pagar"** en cada reserva confirmada (panel "Mis reservas")
- ✅ Modal de pago con:
  - Selector de método de pago
  - Monto total calculado automáticamente
  - Confirmación visual antes de procesar
- ✅ Proceso de pago en 2 pasos:
  1. `POST /api/pagos.php accion=iniciar` - Crea registro de pago con estado `pendiente`
  2. `POST /api/pagos.php accion=confirmar` - Marca pago como `aprobado` y actualiza reserva
- ✅ Actualización automática de tabla tras pago exitoso
- ✅ Manejo de errores con mensajes claros al usuario

**3. Arquitectura de Pagos MVC**
- ✅ **Modelo `Pago.php`:**
  - `obtenerMetodos()` - Lista métodos activos
  - `reservaDeUsuario($reservaId, $usuario)` - Valida pertenencia de reserva
  - `iniciar(...)` - Crea registro inicial
  - `actualizarEstado($pagoId, $estado)` - Cambia estado del pago
  - `marcarReservaPagada($pagoId)` - Actualiza `reservas.pagado=1` y `reservas.monto`
  - `obtenerPorReserva($reservaId)` - Consulta pago asociado
- ✅ **Controlador `PagoControlador.php`:**
  - `listarMetodos()` - Lógica de negocio para métodos
  - `iniciar($reservaId, $usuario, $metodoId, $monto)` - Validaciones antes de crear pago
  - `confirmar($pagoId, $estado)` - Aprueba/rechaza pago y actualiza reserva
  - `obtenerPagoReserva($reservaId)` - Consulta con validaciones
- ✅ **API `api/pagos.php`:**
  - Maneja HTTP requests/responses
  - Valida sesiones y permisos
  - Delega lógica al controlador
  - Retorna JSON estructurado

**4. Tabla de Base de Datos `pagos`**
```sql
CREATE TABLE pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    usuario_id VARCHAR(50) NOT NULL,
    metodo_id INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    moneda VARCHAR(3) DEFAULT 'UYU',
    estado ENUM('pendiente', 'aprobado', 'rechazado') DEFAULT 'pendiente',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    confirmado_en TIMESTAMP NULL,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id),
    FOREIGN KEY (metodo_id) REFERENCES metodos_pago(id),
    INDEX idx_reserva (reserva_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

#### 📄 Sistema de Facturación Automática

**1. Generación de Facturas en PDF**
- ✅ Botón **"Factura"** en cada reserva pagada
- ✅ Generación automática con **DomPDF** (biblioteca PHP)
- ✅ Diseño profesional con:
  - Encabezado con logo y título "FACTURA ELECTRÓNICA"
  - Datos del emisor (GestiónEV S.A.)
  - Datos del cliente (usuario, email)
  - Detalle de servicios (reserva de estación)
  - Monto total en pesos uruguayos (UYU)
  - Footer con nota legal
- ✅ Almacenamiento en carpeta `facturas/` con nombre único
- ✅ Descarga directa desde el navegador

**2. Arquitectura de Facturación MVC**
- ✅ **Modelo `Factura.php`:**
  - `generar($pago)` - Crea registro en BD con número único
  - `obtenerPorPago($pagoId)` - Consulta factura asociada a pago
  - `obtenerPorNumero($numero)` - Búsqueda por número de factura
  - `generarPDF($factura, $pago, $usuario)` - Genera archivo PDF con DomPDF
  - `plantillaHTML(...)` - Template HTML de la factura
- ✅ **Controlador `FacturaControlador.php`:**
  - `generarSiNoExiste($pagoId, $usuario)` - Lógica de generación/descarga
  - Valida permisos (solo dueño del pago)
  - Reutiliza factura existente si ya fue generada
  - Genera PDF automáticamente si falta
- ✅ **API `api/facturas.php`:**
  - `GET ?accion=generar&pago_id=X` - Genera/obtiene factura
  - `GET ?accion=descargar&pago_id=X` - Descarga PDF
  - Headers correctos para descarga de archivos
  - Validación de permisos y sesiones

**3. Tabla de Base de Datos `facturas`**
```sql
CREATE TABLE facturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pago_id INT UNIQUE NOT NULL,
    numero VARCHAR(50) UNIQUE NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    moneda VARCHAR(3) DEFAULT 'UYU',
    datos_json JSON,
    pdf_path VARCHAR(255),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pago_id) REFERENCES pagos(id),
    INDEX idx_numero (numero)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**4. Numeración de Facturas**
- ✅ Formato: `FAC-YYYYMMDD-NNNNNN`
  - Ejemplo: `FAC-20251112-000001`
- ✅ Generación automática con fecha actual
- ✅ Padding de 6 dígitos para el ID del pago
- ✅ Campo único en BD para prevenir duplicados

---

#### 💰 Nueva Pestaña "Pagos" en Panel Cliente

**1. Historial Completo de Pagos**
- ✅ Nueva pestaña **"Pagos"** (💳) en sidebar del cliente
- ✅ Tabla con columnas:
  - **ID Pago** - Número de identificación único
  - **Reserva** - Estación y fechas (inicio - fin)
  - **Fecha** - Cuándo se realizó el pago
  - **Método** - Forma de pago utilizada
  - **Monto** - Cantidad en moneda local
  - **Estado** - Badge visual (aprobado/pendiente/rechazado)
  - **Factura** - Botón de descarga/generación
- ✅ Estados con colores distintivos:
  - 🟢 Verde (`aprobado`)
  - 🟡 Amarillo (`pendiente`)
  - 🔴 Rojo (`rechazado`)

**2. Botones de Factura**
- ✅ **"📄 Descargar"** - Si la factura ya existe (descarga PDF)
- ✅ **"⚙️ Generar"** - Si aún no fue generada (crea y descarga)
- ✅ Generación automática en background
- ✅ Actualización de tabla tras generar factura
- ✅ Manejo de errores con alertas amigables

**3. Endpoint de API**
- ✅ `GET /api/pagos.php?accion=listar_usuario`
- ✅ JOIN con:
  - `metodos_pago` - Nombre del método usado
  - `reservas` - Datos de la reserva (inicio/fin)
  - `cargadores` - Nombre de la estación
  - `facturas` - Ruta del PDF si existe
- ✅ Ordenamiento descendente por fecha (más recientes primero)
- ✅ Solo muestra pagos del usuario autenticado

---

### 🔧 Cambios Técnicos Detallados

#### Instalación de Dependencias

**DomPDF para Generación de PDFs**
```bash
composer require dompdf/dompdf
```

- ✅ Integrado via `vendor/autoload.php`
- ✅ Sin configuración adicional requerida
- ✅ Compatible con PHP 7.4+ y 8.x

#### Modificaciones en Base de Datos

**Nuevas tablas:**
1. `metodos_pago` - Métodos disponibles
2. `pagos` - Registro de transacciones
3. `facturas` - Facturas generadas

**Modificaciones a tabla `reservas`:**
```sql
ALTER TABLE reservas ADD COLUMN pagado TINYINT(1) DEFAULT 0 AFTER estado;
ALTER TABLE reservas ADD COLUMN monto DECIMAL(10,2) DEFAULT 0.00 AFTER pagado;
```

#### Correcciones de Arquitectura MVC

**Problema corregido: Campo `usuario_id` en tabla `reservas`**
- ❌ **Antes:** Código asumía `usuario_id INT` (FK a `usuarios.id`)
- ✅ **Ahora:** Adaptado a `usuario VARCHAR(50)` (guarda username directamente)
- ✅ Todos los modelos y controladores actualizados:
  - `ReservaControlador::listarReservasUsuario($usuario)` - Recibe string
  - `Reserva::listarPorUsuarioId($usuarioId)` - WHERE usuario = ? (VARCHAR)
  - `PagoControlador::iniciar(...)` - Valida con username VARCHAR

**Funciones corregidas en `Reserva.php`:**
```php
// Antes ❌
public function usuarioIdPorUsuario(string $usuario): ?int {
    // Intentaba devolver INT
}

// Ahora ✅
public function usuarioIdPorUsuario(string $usuario): ?string {
    return $usuario; // Ya es VARCHAR, no necesita conversión
}
```

#### API `reservas.php` - Limpieza de Código

**Eliminaciones:**
- ❌ Código duplicado (bloques `listar_usuario` aparecían 2 veces)
- ❌ JavaScript dentro del archivo PHP (rompía JSON)
- ❌ Acceso directo a BD (violaba MVC)

**Mejoras:**
- ✅ Función `responder($data, $codigo)` unificada
- ✅ Todas las respuestas en JSON válido
- ✅ Headers `Content-Type: application/json`
- ✅ Manejo de errores con try/catch
- ✅ Logs de debugging (`display_errors` deshabilitado en producción)

---

### 📝 Archivos Creados

**Modelos:**
- `modelo/Pago.php` - Gestión de pagos en BD
- `modelo/Factura.php` - Gestión de facturas y generación de PDF

**Controladores:**
- `controlador/PagoControlador.php` - Lógica de negocio de pagos
- `controlador/FacturaControlador.php` - Lógica de negocio de facturación

**APIs:**
- `api/pagos.php` - Endpoints de pagos (métodos, iniciar, confirmar, listar)
- `api/facturas.php` - Endpoints de facturas (generar, descargar)

**Carpeta de Archivos:**
- `facturas/` - Almacenamiento de PDFs generados

**SQL:**
- Scripts de migración incluidos en comentarios del CHANGELOG

---

### 📝 Archivos Modificados

**APIs:**
- `api/reservas.php` ⚙️⚙️⚙️
  - Refactorización completa
  - Eliminado código duplicado
  - Eliminado JavaScript embebido
  - Patrón MVC respetado al 100%

**Modelos:**
- `modelo/Reserva.php` ⚙️
  - Método `usuarioIdPorUsuario()` devuelve string
  - Todos los métodos adaptados a `usuario VARCHAR`
  - Nuevos métodos para integración con pagos

**Controladores:**
- `controlador/ReservaControlador.php` ⚙️
  - Firmas de funciones actualizadas (reciben string en lugar de int)
  - Validaciones adaptadas a username VARCHAR

**Vistas:**
- `vista/cliente.php` ⚙️⚙️
  - **Nueva pestaña "Pagos"** con tabla completa
  - **Modal de pago** con selector de métodos
  - **JavaScript:**
    - `cargarPagos()` - Lista pagos del usuario
    - `cargarMetodosPago()` - Carga métodos disponibles
    - Event listeners para botones de pago y factura
    - Manejo de modales (abrir/cerrar)
    - Actualización automática tras operaciones

**Estilos:**
- `styles/cliente.css`
  - Estilos para modal de pago
  - Tabla de pagos con diseño responsive
  - Botones de factura (descargar/generar)
  - Estados de pago con colores distintivos

---

### 🐛 Correcciones de Bugs

1. **Error "usuario_id no disponible en sesión"**
   - ✅ Corregido: Ahora usa `$_SESSION['usuario']` (VARCHAR)
   - ✅ Todos los modelos adaptados al esquema real de BD

2. **Parse error en reservas.php**
   - ✅ Eliminado código JavaScript dentro del PHP
   - ✅ Eliminado código duplicado que rompía sintaxis

3. **JSON inválido con caracteres de control**
   - ✅ Uso de `ob_start()` y `ob_clean()` para limpiar buffer
   - ✅ Headers correctos antes de cualquier salida
   - ✅ Función `responder()` unificada para todas las APIs

4. **Modelo Pago con error "Column not found: usuario_id"**
   - ✅ Query adaptada para usar `reservas.usuario` (VARCHAR)
   - ✅ JOIN corregido en `reservaDeUsuario()`

5. **PagoControlador con error "Too few arguments"**
   - ✅ Constructor corregido para recibir PDO
   - ✅ Inyección de dependencias implementada correctamente

---

### ✨ Mejoras de UX

1. **Flujo de Pago Intuitivo**
   - ✅ Botón "Pagar" solo visible en reservas sin pagar
   - ✅ Modal de pago con información clara del monto
   - ✅ Mensajes de confirmación tras pago exitoso
   - ✅ Actualización automática de tabla sin recargar página

2. **Generación de Facturas Sin Fricción**
   - ✅ Descarga automática tras generar factura
   - ✅ Botón cambia de "Generar" a "Descargar" tras primera generación
   - ✅ Facturas almacenadas permanentemente (no se regeneran)

3. **Historial Completo de Pagos**
   - ✅ Vista centralizada de todos los pagos realizados
   - ✅ Información detallada de cada transacción
   - ✅ Estados visuales claros con badges de colores

4. **Diseño Profesional de Facturas**
   - ✅ PDF con estilo corporativo
   - ✅ Información completa y legible
   - ✅ Numeración única y verificable
   - ✅ Datos fiscales del emisor incluidos

---

### 📊 Estadísticas de la Versión

- **Líneas de código agregadas:** ~1,200
- **Archivos nuevos:** 6 (2 modelos, 2 controladores, 2 APIs)
- **Archivos modificados:** 8
- **Tablas de BD creadas:** 3
- **Endpoints de API nuevos:** 7
- **Bugs críticos corregidos:** 5

---

### 🚀 Preparación para v2.0.0

Esta versión sienta las bases para la implementación de:
- ✅ Sistema de pagos funcional (listo para integrar pasarela real)
- ✅ Facturación automática cumpliendo estándares
- ✅ Arquitectura MVC 100% respetada (facilita testing y mantenimiento)
- ✅ Base de datos normalizada y escalable

---

### 🎓 Lecciones Aprendidas

1. **Importancia de la Arquitectura MVC**
   - Separación estricta de capas facilita debugging
   - Código más testeable y mantenible
   - Cambios en una capa no afectan a otras

2. **Validación de Tipos de Datos**
   - Siempre verificar esquema de BD antes de asumir tipos
   - VARCHAR vs INT: Impacto en toda la aplicación
   - Usar prepared statements previene errores de tipos

3. **Manejo de JSON en PHP**
   - Buffer de salida (`ob_start()`) crítico para JSON válido
   - Headers antes de cualquier echo
   - Función unificada de respuesta (`responder()`) mejora consistencia

4. **Generación de PDFs**
   - DomPDF simplifica enormemente la creación de facturas
   - Templates HTML con CSS inline funcionan mejor
   - Almacenar PDFs evita regeneración innecesaria

---

### 📞 Soporte

**Desarrollado por:** ShonosTech  
**Fecha de Release:** 12 de Noviembre de 2025  
**Versión Anterior:** 1.8.1  
**Versión Actual:** 1.9.0  
**Tipo:** Feature Release (nuevas funcionalidades mayores)

---

### 🔮 Próxima Versión: 2.0.0 - Sistema Bilingüe y Responsive

**Fecha estimada:** Diciembre 2025  
**Características planificadas:**
- 🌐 **Internacionalización completa** (Español/Inglés)
- 📱 **Diseño responsive** para móviles y tablets
- ♿ **Accesibilidad WCAG AA**
- 🎨 **Rediseño visual** con mejores prácticas UI/UX

---

**¿Encontraste un bug?** Reportalo al equipo de desarrollo con:
- Navegador y versión
- Pasos para reproducir
- Screenshot del error (si aplica)

---

## 📋 Roadmap Versión 2.0.0 - Sistema Bilingüe y Responsive 🌐📱

### 🎯 Objetivos Principales

**Versión definitiva del sistema con:**
1. **Internacionalización completa** (Español ⇄ Inglés)
2. **Diseño responsive** para todos los dispositivos
3. **Accesibilidad mejorada** (WCAG 2.1 AA)
4. **Optimización de performance**

---

### 🌍 Sistema Bilingüe (Español/Inglés)

#### 📚 Arquitectura i18n

**Archivos de traducción JSON:**
```
i18n/
├── es.json  # Español (idioma base)
└── en.json  # English
```

**Estructura de archivos:**
```json
{
  "nav.autos": "Autos",
  "nav.viajes": "Viajes",
  "nav.historial": "Historial",
  "nav.pagos": "Pagos",
  "form.origen": "Origen",
  "form.destino": "Destino",
  "button.planificar": "Planificar ruta",
  "button.reservar": "Reservar",
  "button.pagar": "Pagar",
  "button.descargar": "Descargar",
  "status.disponible": "Disponible",
  "status.ocupado": "En uso",
  "message.reserva_exitosa": "Reserva creada exitosamente",
  "error.campos_requeridos": "Todos los campos son requeridos"
}
```

#### 🔧 Implementación Técnica

**1. Helper PHP (`i18n/i18n.php`):**
```php
<?php
function t($key, $params = []) {
    $lang = $_SESSION['lang'] ?? 'es';
    $file = __DIR__ . "/{$lang}.json";
    
    if (!file_exists($file)) {
        $file = __DIR__ . "/es.json";
    }
    
    $translations = json_decode(file_get_contents($file), true);
    $text = $translations[$key] ?? $key;
    
    foreach ($params as $k => $v) {
        $text = str_replace("{{$k}}", $v, $text);
    }
    
    return $text;
}

// Uso: <?= t('button.reservar') ?>
// Con parámetros: <?= t('message.reserva_creada', ['id' => 123]) ?>
?>
```

**2. Helper JavaScript (`js/i18n.js`):**
```javascript
const i18n = {
    currentLang: localStorage.getItem('lang') || 'es',
    translations: {},
    
    async load(lang) {
        const response = await fetch(`../i18n/${lang}.json`);
        this.translations = await response.json();
        this.currentLang = lang;
        localStorage.setItem('lang', lang);
        document.documentElement.lang = lang;
    },
    
    t(key, params = {}) {
        let text = this.translations[key] || key;
        Object.keys(params).forEach(k => {
            text = text.replace(`{${k}}`, params[k]);
        });
        return text;
    },
    
    async switchLang(lang) {
        await this.load(lang);
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            el.textContent = this.t(key);
        });
        document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
            const key = el.getAttribute('data-i18n-placeholder');
            el.placeholder = this.t(key);
        });
    }
};

// Uso: i18n.t('button.reservar')
// Cambiar idioma: await i18n.switchLang('en');
```

**3. Selector de idioma en header:**
```html
<div class="language-selector">
    <button onclick="cambiarIdioma('es')" class="lang-btn" data-lang="es">
        <span class="flag">🇪🇸</span> ES
    </button>
    <button onclick="cambiarIdioma('en')" class="lang-btn" data-lang="en">
        <span class="flag">🇺🇸</span> EN
    </button>
</div>
```

**4. API para cambiar idioma:**
```php
// api/idioma.php
<?php
session_start();
header('Content-Type: application/json');

$lang = $_POST['lang'] ?? 'es';
if (!in_array($lang, ['es', 'en'])) {
    $lang = 'es';
}

$_SESSION['lang'] = $lang;
echo json_encode(['exito' => true, 'lang' => $lang]);
?>
```

#### 📖 Diccionario de Términos Clave

| Español | English | Contexto |
|---------|---------|----------|
| Reserva | Booking | Sistema de reservas |
| Cargador | Charging station | Estaciones de carga |
| Estación | Station | Punto de carga |
| Autonomía | Range | Autonomía del vehículo |
| Batería | Battery | Nivel de carga |
| Parada esencial | Essential stop | Paradas obligatorias |
| Parada opcional | Optional stop | Sugerencias |
| Disponible | Available | Estado de cargador |
| Ocupado | In use | Estado de cargador |
| Fuera de servicio | Out of service | Cargador deshabilitado |
| Tipo de conector | Connector type | Especificaciones |
| Potencia | Power output | kW del cargador |
| Duración | Duration | Tiempo de carga |
| Mis reservas | My bookings | Panel de usuario |
| Historial | History | Cargas anteriores |
| Planificar viaje | Plan trip | Función principal |
| Batería actual | Current battery | % de carga |
| Alcance real | Actual range | Autonomía efectiva |
| Pagar | Pay | Acción de pago |
| Factura | Invoice | Documento fiscal |
| Método de pago | Payment method | Forma de pago |
| Tarjeta de crédito | Credit card | Método de pago |
| Cuenta prepago | Prepaid account | Método de pago |
| Estado del pago | Payment status | Estado de transacción |
| Aprobado | Approved | Pago exitoso |
| Pendiente | Pending | En proceso |
| Rechazado | Declined | Pago fallido |
| Descargar | Download | Acción sobre factura |
| Generar | Generate | Crear factura |
| Agregar auto | Add vehicle | Acción en panel |
| Editar perfil | Edit profile | Configuración |
| Cerrar sesión | Log out | Acción de logout |

#### 🎨 Adaptaciones de UI

**Textos estáticos marcados con atributo:**
```html
<button data-i18n="button.reservar">Reservar</button>
<h2 data-i18n="title.mis_reservas">Mis reservas</h2>
<label data-i18n="form.origen">Origen</label>
```

**Placeholders traducidos:**
```html
<input 
    type="text" 
    data-i18n-placeholder="form.placeholder.origen"
    placeholder="Ej: Av. 18 de Julio 1234"
>
```

**Contenido dinámico en JavaScript:**
```javascript
// Antes
alert('Reserva creada exitosamente');

// Ahora
alert(i18n.t('message.reserva_exitosa'));
```

---

### 📱 Diseño Responsive

#### 📐 Breakpoints Definidos

```css
/* Variables globales */
:root {
    --breakpoint-xs: 320px;   /* Móvil pequeño */
    --breakpoint-sm: 480px;   /* Móvil estándar */
    --breakpoint-md: 768px;   /* Tablet portrait */
    --breakpoint-lg: 1024px;  /* Tablet landscape / Desktop pequeño */
    --breakpoint-xl: 1440px;  /* Desktop estándar */
    --breakpoint-xxl: 1920px; /* Desktop grande / 4K */
}

/* Media queries */
@media (max-width: 480px) { /* Móvil pequeño */ }
@media (min-width: 481px) and (max-width: 768px) { /* Móvil grande / Tablet portrait */ }
@media (min-width: 769px) and (max-width: 1024px) { /* Tablet landscape */ }
@media (min-width: 1025px) and (max-width: 1440px) { /* Desktop estándar */ }
@media (min-width: 1441px) { /* Desktop grande */ }
```

#### 📱 Adaptaciones por Dispositivo

**Móvil (< 480px):**
- ✅ Sidebar colapsado con menú hamburguesa
- ✅ Tablas en modo "stacked" (vertical)
- ✅ Modales full-screen
- ✅ Inputs y botones 100% width
- ✅ Mapa con altura fija (300px)
- ✅ Font-size base: 14px
- ✅ Filtros en accordion colapsable

**Tablet Portrait (481-768px):**
- ✅ Sidebar sticky lateral (250px)
- ✅ Tablas con scroll horizontal
- ✅ Grid de 2 columnas en formularios
- ✅ Modales 90% viewport
- ✅ Mapa altura 400px
- ✅ Font-size base: 15px

**Tablet Landscape (769-1024px):**
- ✅ Sidebar fija (280px)
- ✅ Tablas completas visibles
- ✅ Grid de 3 columnas en formularios
- ✅ Modales 70% viewport
- ✅ Mapa altura 500px

**Desktop (1025-1440px):**
- ✅ Layout actual optimizado
- ✅ Sidebar fija (300px)
- ✅ Modales 50-60% viewport

**Desktop Grande (> 1441px):**
- ✅ Max-width container: 1600px
- ✅ Sidebar 320px
- ✅ Espaciado amplio

#### 🗂️ Tablas Responsive (modo stacked)

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
        padding: 10px;
    }
    
    td {
        border: none;
        position: relative;
        padding-left: 50%;
        text-align: right;
    }
    
    td:before {
        content: attr(data-label);
        position: absolute;
        left: 10px;
        font-weight: bold;
        text-align: left;
    }
}
```

**Ejemplo de uso:**
```html
<tr>
    <td data-label="Estación">cargador1</td>
    <td data-label="Estado">disponible</td>
    <td data-label="Tipo">DC Rápido</td>
</tr>
```

#### 🍔 Menú Hamburguesa

```html
<button class="hamburger" onclick="toggleSidebar()">
    <span></span>
    <span></span>
    <span></span>
</button>

<style>
.hamburger {
    display: none; /* Visible solo en móvil */
    flex-direction: column;
    gap: 4px;
    background: none;
    border: none;
    cursor: pointer;
}

.hamburger span {
    width: 25px;
    height: 3px;
    background: #fff;
    transition: 0.3s;
}

@media (max-width: 768px) {
    .hamburger { display: flex; }
    .sidebar { transform: translateX(-100%); }
    .sidebar.active { transform: translateX(0); }
}
</style>
```

#### 🎯 Touch Targets (mínimo 44×44px)

```css
/* Botones táctiles accesibles */
button, a.button, input[type="submit"] {
    min-height: 44px;
    min-width: 44px;
    padding: 12px 20px;
}

/* Checkboxes y radios más grandes en móvil */
@media (max-width: 768px) {
    input[type="checkbox"],
    input[type="radio"] {
        width: 24px;
        height: 24px;
    }
}
```

---

### ♿ Accesibilidad (WCAG 2.1 AA)

#### 🎨 Contraste de Colores

```css
/* Contraste mínimo 4.5:1 para textos normales */
:root {
    --text-primary: #1a1a1a;      /* Sobre fondo blanco: 16:1 ✅ */
    --text-secondary: #4a4a4a;    /* Sobre fondo blanco: 9.7:1 ✅ */
    --link-color: #0066cc;        /* Sobre fondo blanco: 7.4:1 ✅ */
    --error-text: #c00000;        /* Sobre fondo blanco: 8.6:1 ✅ */
}

/* Contraste mínimo 3:1 para textos grandes (18px+) */
h1, h2, h3 {
    color: var(--text-primary);
}
```

#### ⌨️ Navegación por Teclado

```html
<!-- Skip links para navegación rápida -->
<a href="#main-content" class="skip-link">Saltar al contenido principal</a>
<a href="#sidebar" class="skip-link">Ir al menú</a>

<style>
.skip-link {
    position: absolute;
    top: -40px;
    left: 0;
    background: #000;
    color: #fff;
    padding: 8px;
    text-decoration: none;
    z-index: 9999;
}

.skip-link:focus {
    top: 0;
}
</style>
```

**Focus visible en todos los elementos:**
```css
*:focus {
    outline: 2px solid #0066cc;
    outline-offset: 2px;
}

button:focus,
a:focus,
input:focus,
select:focus {
    box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.3);
}
```

#### 🔊 Atributos ARIA

```html
<!-- Botones con labels descriptivos -->
<button aria-label="Cerrar modal de reserva">✕</button>
<button aria-label="Editar auto modelo Tesla Model 3">
    <span aria-hidden="true">✏️</span>
</button>

<!-- Alerts dinámicos -->
<div role="alert" aria-live="polite" id="mensajeReserva">
    Reserva creada exitosamente
</div>

<!-- Modales accesibles -->
<div 
    role="dialog" 
    aria-modal="true" 
    aria-labelledby="modal-title"
    aria-describedby="modal-desc"
>
    <h2 id="modal-title">Confirmar reserva</h2>
    <p id="modal-desc">Estás por reservar el cargador...</p>
</div>

<!-- Tabs accesibles -->
<div role="tablist" aria-label="Pestañas del panel">
    <button 
        role="tab" 
        aria-selected="true" 
        aria-controls="tab-autos"
        id="tab-button-autos"
    >
        Autos
    </button>
</div>
<div role="tabpanel" id="tab-autos" aria-labelledby="tab-button-autos">
    <!-- Contenido -->
</div>
```

#### 🖼️ Imágenes y Iconos

```html
<!-- Alt text descriptivo -->
<img src="logo.png" alt="GestiónEV - Sistema de gestión de vehículos eléctricos">

<!-- Iconos decorativos -->
<span aria-hidden="true">🚗</span>

<!-- Iconos funcionales -->
<button>
    <span class="icon" aria-hidden="true">💳</span>
    <span>Pagar</span>
</button>
```

---

### ⚡ Optimización de Performance

#### 🚀 Lazy Loading de Componentes

```javascript
// Cargar mapa solo cuando se abre la pestaña Viajes
document.querySelector('[data-tab="viajes"]').addEventListener('click', () => {
    if (!mapaIniciado) {
        inicializarMapa();
        mapaIniciado = true;
    }
});
```

#### 📦 Minificación de Assets

```json
// package.json (npm scripts)
{
  "scripts": {
    "build:css": "cleancss -o styles/cliente.min.css styles/cliente.css",
    "build:js": "uglifyjs js/cliente.js -o js/cliente.min.js -c -m",
    "build": "npm run build:css && npm run build:js"
  }
}
```

#### 🗜️ Compresión de Imágenes

```bash
# Optimizar imágenes con TinyPNG o ImageOptim
# Formatos modernos: WebP para fotos, SVG para iconos
```

#### 📊 Performance Budget

| Métrica | Target | Actual |
|---------|--------|--------|
| First Contentful Paint | < 1.5s | ? |
| Time to Interactive | < 3.5s | ? |
| Speed Index | < 2.5s | ? |
| Total Bundle Size | < 500 KB | ? |

---

### 🧪 Testing y Validación

#### ✅ Checklist de Testing

**Funcional:**
- [ ] Todas las funciones trabajan en español
- [ ] Todas las funciones trabajan en inglés
- [ ] Cambio de idioma actualiza toda la UI
- [ ] Idioma se guarda en localStorage
- [ ] Idioma persiste tras cerrar sesión

**Responsive:**
- [ ] Layout funciona en móvil (320-480px)
- [ ] Layout funciona en tablet (481-1024px)
- [ ] Layout funciona en desktop (1025px+)
- [ ] Sidebar colapsa correctamente en móvil
- [ ] Tablas se adaptan a modo stacked
- [ ] Modales son responsivos
- [ ] Todos los touch targets > 44px

**Accesibilidad:**
- [ ] Navegación por teclado completa (Tab, Enter, Esc)
- [ ] Contraste de colores WCAG AA
- [ ] Atributos ARIA en componentes dinámicos
- [ ] Screen readers pueden leer todo el contenido
- [ ] Focus visible en todos los elementos
- [ ] Alt text en todas las imágenes

**Performance:**
- [ ] Lighthouse Score > 90
- [ ] First Contentful Paint < 1.5s
- [ ] Time to Interactive < 3.5s
- [ ] Bundle size < 500 KB

---

### 📅 Cronograma de Desarrollo

**Semana 1-2: Internacionalización**
- Días 1-3: Crear archivos JSON de traducción
- Días 4-6: Implementar helpers PHP y JS
- Días 7-10: Traducir todas las vistas
- Días 11-14: Testing y correcciones

**Semana 3-4: Responsive Design**
- Días 15-17: Definir breakpoints y variables CSS
- Días 18-21: Adaptar vistas para móvil
- Días 22-25: Adaptar vistas para tablet
- Días 26-28: Testing en dispositivos reales

**Semana 5: Accesibilidad y Performance**
- Días 29-31: Implementar atributos ARIA
- Días 32-33: Optimizar navegación por teclado
- Días 34-35: Optimización de assets y lazy loading

**Semana 6: Testing Final y Release**
- Días 36-38: Testing exhaustivo
- Días 39-40: Corrección de bugs
- Días 41-42: Documentación y release

---

### 🎁 Entregables v2.0.0

**Código:**
- ✅ Sistema bilingüe completo (ES/EN)
- ✅ CSS responsive con breakpoints
- ✅ JavaScript optimizado y minificado
- ✅ Imágenes optimizadas

**Documentación:**
- ✅ README actualizado en español e inglés
- ✅ Guía de traducción para agregar nuevos idiomas
- ✅ Guía de accesibilidad
- ✅ CHANGELOG completo v2.0.0

**Testing:**
- ✅ Reporte de testing en dispositivos
- ✅ Lighthouse audit report
- ✅ WCAG 2.1 AA compliance report

---

### 📞 Contacto

**Desarrollado por:** ShonosTech  
**Versión Actual:** 1.9.0  
**Próxima Versión:** 2.0.0  
**Fecha Estimada de Release:** Diciembre 2025  

---

**🎉 ¡Gracias por usar GestiónEV!**

Si tenés sugerencias o encontrás bugs, no dudes en contactarnos.

---

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

- [ ] Sistema de notificaciones push cuando reserva está por vencer
- [ ] Recordatorios automáticos 15 min antes del inicio
- [ ] Extensión de reserva desde la app
- [ ] Estadísticas de uso de estaciones (reportes admin)
- [ ] API REST completa con autenticación JWT
- [ ] Tests automatizados (PHPUnit + Jest)

---

**Gracias por usar nuestro sistema de gestión de autos eléctricos.** 🚗⚡

Si encontrás algún bug no cubierto en este parche, por favor reportalo al equipo de desarrollo.

---

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

#### ✨ Próximas Mejoras (v1.9.0)

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
