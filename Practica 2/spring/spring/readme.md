<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h2>📌 Características</h2>
    <ul>
        <li><strong>Administradores</strong> pueden gestionar usuarios (CRUD completo).</li>
        <li><strong>Usuarios</strong> pueden ver y editar su perfil.</li>
        <li><strong>Protección</strong> de páginas contra accesos no autenticados.</li>
        <li><strong>Redirección automática</strong> a login si no hay sesión activa.</li>
        <li><strong>Pruebas</strong> con Postman o <code>curl</code>.</li>
    </ul>
    <h2>🛠️ Instalación y Ejecución</h2>
    <h3>🔧 Prerrequisitos</h3>
    <p>Asegúrate de tener instalado:</p>
    <ul>
        <li>☕ <a href="https://adoptium.net/">Java 17+</a></li>
        <li>🛠️ <a href="https://maven.apache.org/">Maven</a></li>
        <li>🐘 <a href="https://www.postgresql.org/">PostgreSQL</a></li>
        <li>🐳 <a href="https://www.docker.com/">Docker</a> (opcional para despliegue)</li>
        <li>🐘 <a href="https://www.php.net/">PHP</a> (para archivos PHP en el proyecto)</li>
    </ul>
    <h3>🚀 Pasos para correr la aplicación</h3>
    <ul> <h3>
# Configurar la base de datos en PostgreSQL <br>
1. Crea una base de datos en PostgreSQL (por ejemplo, 'auth_system') <br>
2. Configura las credenciales en application.properties: <br><br>
<li>spring.datasource.url=jdbc:postgresql://localhost:5432/auth_system <br>
<li>spring.datasource.username=tu_usuario<br>
<li>spring.datasource.password=tu_contraseña<br><br>
# Compilar y ejecutar con Maven<br>
<li>mvn clean install <br>
<li>mvn spring-boot:run <br>
  <br>
# La aplicación estará disponible en: http://localhost:8080
    </h3> </ul>
    <h2>📂 Desarrollo en PHP</h2>
    <p>El sistema incluye archivos PHP para ciertas funcionalidades. Los archivos PHP están ubicados en <code>src/main/resources/php</code> y manejan integraciones específicas.</p>
    <ul>
        <li><code>login.php</code> - Manejador de autenticación.</li>
        <li><code>perfil.php</code> - Edición del perfil de usuario.</li>
        <li><code>admin.php</code> - Panel de administración.</li>
    </ul>
    <h2>📝 Notas adicionales</h2>
    <ul>
        <li><strong>Usuarios y roles:</strong> Puedes preconfigurar roles y usuarios en el <code>schema.sql</code>.</li>
        <li><strong>Endpoints:</strong> Se pueden probar con Postman o <code>curl</code>.</li>
        <li><strong>Contribuciones:</strong> ¡Las contribuciones son bienvenidas! 😃</li>
    </ul>

  <p>📌 <strong>¡Disfruta construyendo con Spring Boot y PHP! 🚀</strong></p>
</body>
</html>
