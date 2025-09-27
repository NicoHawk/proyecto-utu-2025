📌 Notas del Parche / Changelog

🔒 Seguridad y control de acceso

Protección de rutas:
Ahora los archivos formulario.php, cliente.php y cargador.php requieren que el usuario esté logueado y tenga el tipo de usuario adecuado.
Si se intenta acceder directamente por URL sin sesión válida, se redirige al login.

Conversión de cargador.html a cargador.php:
El panel de cargador ahora es un archivo PHP protegido por sesión, impidiendo el acceso directo sin autenticación.

Redirección tras login:
Los usuarios son redirigidos a su panel correspondiente (formulario.php, cliente.php o cargador.php) según su tipo de usuario.

Cierre de sesión real:
El botón Cerrar sesión destruye la sesión correctamente con logout.php, evitando el acceso a páginas protegidas tras salir.

⚙️ API y backend

Unificación de API REST para cargadores:
Todas las operaciones de listar, agregar y eliminar cargadores usan ahora cargadores.php.

Corrección de controlador a PDO:
CargadorControlador.php ahora funciona con PDO en lugar de MySQLi, mejorando compatibilidad y evitando errores.

Respuestas JSON consistentes:
La API de cargadores devuelve siempre resultados en formato JSON, facilitando la integración con el frontend.

🎨 Frontend y funcionalidad

Visualización de cargadores:
Los puntos de carga se muestran correctamente en el mapa tanto para admin como para cargador.

Actualización dinámica:
Al agregar o eliminar cargadores, la lista y los marcadores del mapa se refrescan automáticamente.

Control de permisos en frontend:
El usuario cargador solo puede eliminar cargadores si está autenticado correctamente.

✅ Resumen

La aplicación ahora es más segura, robusta y consistente, con:
Control de acceso real

APIs unificadas

Experiencia de usuario mejorada