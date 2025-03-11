<h2>📂 Repositorio</h2>
<p><a href="https://github.com/MXYSL/Ingeneria-de-Sofware-6CV3/tree/main/Proyecto%20Individual" target="_blank">Proyecto en GitHub</a></p>
<p>Acceso local: <a href="http://localhost/proyectoIS/index.html">http://localhost/proyectoIS/index.html</a></p>

<h2>📋 Requisitos</h2>
<ul>
    <li>PHP 7.4 o superior</li>
    <li>Servidor Apache o Nginx</li>
    <li>Base de datos MySQL</li>
    <li>Composer</li>
    <li>Cuenta de Google y Facebook para OAuth</li>
</ul>

<h2>⚙️ Instalación y Configuración</h2>
<h3>1️⃣ Clonar el repositorio</h3>
<pre><code>git clone https://github.com/MXYSL/Ingeneria-de-Sofware-6CV3.git
<h3>2️⃣ Instalar dependencias</h3>
<pre><code>composer install</code></pre>

<h3>3️⃣ Configurar la base de datos</h3>
<ul>
    <li>Crear una base de datos en MySQL.</li>
    <li>Importar <code>database.sql</code> (si aplica).</li>
    <li>Configurar credenciales en <code>config.php</code>:</li>
</ul>
<pre><code>define('DB_HOST', 'localhost');
<h3>4️⃣ Configurar autenticación con Google y Facebook</h3>
<ul>
    <li>Registrar una aplicación en <a href="https://console.developers.google.com/">Google Developers Console</a>.</li>
    <li>Registrar una aplicación en <a href="https://developers.facebook.com/">Facebook Developers</a>.</li>
    <li>Obtener claves API y agregarlas en <code>auth_google.php</code> y <code>auth_facebook.php</code>.</li>
</ul>

<h2>▶️ Ejecución del Proyecto</h2>
<pre><code>php -S localhost:8000</code></pre>
<p>Luego, accede a <a href="http://localhost/proyectoIS/index.html">http://localhost/proyectoIS/index.html</a></p>

<h2>🛠️ Pruebas</h2>
<p>Se pueden realizar pruebas con Postman o <code>curl</code>.</p>
<pre><code>curl -X POST -d "username=usuario&password=contraseña" http://localhost/proyectoIS/login_process.php</code></pre>

<h2>🔒 Seguridad Implementada</h2>
<ul>
    <li>Hashing de contraseñas con <code>password_hash()</code>.</li>
    <li>Uso seguro de sesiones con <code>session_start()</code> y <code>session_regenerate_id(true)</code>.</li>
    <li>Protección contra ataques web:
        <ul>
            <li>CSRF: Tokens en formularios.</li>
            <li>XSS: Escape de datos en HTML.</li>
            <li>SQL Injection: Uso de consultas preparadas.</li>
        </ul>
    </li>
</ul>
