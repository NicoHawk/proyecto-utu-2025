# 📋 Changelog - Sistema de Gestión de Autos Eléctricos

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
  - Método `insertar()`: Ahora acepta parámetro `$correo`
  - Método `verificarCredenciales()`: Cambiado de usuario a correo
  - Método `modificar()`: Añadido parámetro `$nuevoCorreo` con 4 casos de actualización
  - Método `listar()`: Incluye campo `correo` en SELECT

**Controladores:**
- `controlador/UsuarioControlador.php`
  - `loginUsuario()`: Ahora recibe `$correo` en lugar de `$username`
  - `registrarUsuario()`: Acepta parámetro `$correo`
  - `modificarUsuario()`: Añadido parámetro `$nuevoCorreo` con lógica condicional

**APIs:**
- `api/login.php`
  - Cambiado de `$_POST['username']` a `$_POST['correo']`
  
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

**Desarrollado por:** Equipo UTU 2025  
**Fecha de Release:** 31 de Octubre de 2025  
**Versión Anterior:** 1.2.0  
**Versión Actual:** 1.3.0
