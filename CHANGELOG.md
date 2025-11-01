# 📋 Changelog - Sistema de Gestión de Autos Eléctricos

## Versión 1.5.0 - 1 de Noviembre de 2025

### 🎯 Cambios Principales

#### 🧭 Panel de Cliente con pestañas (Autos/Viajes)
- Rediseño del panel de cliente con sidebar fija y navegación por pestañas (Autos/Viajes).
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

#### Correcciones de Arquitectura MVC ⚙️
- **Patrón MVC respetado al 100%**
  - `CargadorControlador.php`: Refactorizado completamente
    - Ahora usa el modelo `Cargador` en lugar de hacer queries SQL directas
    - Funciones: `listarCargadores()`, `agregarCargador()`, `eliminarCargador()`
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
  - Nueva estructura con pestañas: `#tab-autos` y `#tab-viajes`.
  - Eliminado el import de `../styles/formulario.css` para evitar conflictos.
  - JS para cambiar pestañas y cargar listados de autos y viajes.

---

### � Archivos Eliminados

- ❌ `api/autos_admin.php` - Funcionalidad integrada en `admin.php`

### 📝 Archivos Modificados

**Controladores:**
- `controlador/AutoControlador.php`
  - Funciones administrativas: `listarAutosAdmin()`, `agregarAutoAdmin()`, `editarAutoAdmin()`, `eliminarAutoAdmin()`
  - Arquitectura mejorada manteniendo patrón MVC
- `controlador/CargadorControlador.php` ⚙️
  - Refactorizado completamente para usar el Modelo
  - Eliminadas queries SQL directas
  - Funciones: `listarCargadores()`, `agregarCargador()`, `eliminarCargador()`
- `controlador/UsuarioControlador.php` ⚙️
  - Orden de parámetros corregido en `registrarUsuario()`
  - Soporte para correo opcional

**Modelos:**
- `modelo/Cargador.php` ⚙️
  - Parámetro `descripcion` ahora opcional en `insertar()`

**APIs:**
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
- `vista/formulario.php`
  - Formulario de agregar auto en pestaña "Autos"
  - Función `cargarUsuariosParaAutos()`
  - Manejador de submit para agregar autos
  - Actualización de URLs de fetch (de `autos_admin.php` a `admin.php`)
  - Actualización de nombres de acciones

**Estilos:**
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

---

### ✨ Mejoras de UX

1. **Proceso de agregar autos simplificado**
   - Formulario claro y organizado en la parte superior
   - Selector de usuario con formato: "nombre_usuario (tipo_usuario)"
   - Feedback inmediato con alert tras agregar
   - Lista de autos se actualiza automáticamente

2. **Consistencia visual**
   - Diseño alineado con el resto del panel de administración
   - Colores corporativos mantenidos
   - Espaciado adecuado entre elementos

3. **Cliente con navegación por pestañas**
  - Sidebar clara con estados activo/hover consistentes.
  - Transiciones suaves y tarjetas diferenciadas por sección.

4. **Admin minimalista**
  - Interfaz más limpia, foco accesible en inputs, tablas claras.

3. **Mejor organización del código**
   - API unificada más fácil de mantener
   - Menos archivos que gestionar
   - Código más limpio y reutilizable

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

---

### 🚀 Próximas Mejoras Sugeridas

- [ ] Validación de datos del auto (ej: año entre 1900 y año actual+1)
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
