<?php
session_start();
require_once 'conexion.php';

// Si no hay sesión activa, redirigir al login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

// Obtener datos del usuario desde la sesión
$nombreUsuario = $_SESSION['username'];
$isAdmin = ($_SESSION['role_id'] == 1); // Suponiendo que el rol 1 es Admin

// Consumir la API de TVmaze
$apiUrl = "https://api.tvmaze.com/search/shows?q=harry+potter";
$response = file_get_contents($apiUrl);
$shows = json_decode($response, true); // Decodificar el JSON en un array asociativo
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca Digital - StoryVerse</title>
    <link rel="icon" href="../img/icono.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Playfair+Display:wght@500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="instorve.css">
</head>
<body>
    <header class="header9">
        <div class="logo9">
            <img src="../img/icono.png" alt="StoryVerse">
        </div>
        <div class="nav-links9">
            <button id="themeToggle10" class="theme-button10">
                <img id="themeIcon10" src="../img/claro.png" alt="Modo Claro">
            </button>
        </div>
        <nav class="nav9">
            <div class="search-container9">
                <input type="text" id="search-input9" placeholder="Buscar por título, autor, editorial...">
                <button id="search-button9">Buscar</button>
                <button id="advanced-search9">Avanzada</button>
            </div>
        </nav>
        <div class="nav-links9">
            <a href="#Contacto"><i class="fa-solid fa-film"></i> Películas</a>
            <a href="#Contacto"><i class="fa-solid fa-book"></i> Libros</a>

            <?php if ($isAdmin): ?>
                <a href="admin.php" class="menu-button"><i class="fa-solid fa-user-tie"></i>Administrador</a>
            <?php endif; ?>

            <a href="perfil.php"><i class="fas fa-user"></i> Mi perfil </a>
            <a href="logout.php"><i class="fa-solid fa-right-to-bracket"></i> Cerrar sesión</a>
        </div>
    </header>

    <main class="main10">
        <section class="category-section10">
            <p class="welcome-message">Bienvenido, <span style="color:rgb(126, 84, 26); font-size: 20px;"><?php echo htmlspecialchars($nombreUsuario); ?></span> </p>
            <h2 class="category-title10">Resultados de TVmaze para "Harry Potter"</h2>
            <div class="show-grid10">
                <?php
                if (isset($shows) && count($shows) > 0) {
                    $counter = 0; // Contador para limitar a 10 resultados
                    foreach ($shows as $show) {
                        if ($counter >= 10) break; // Mostrar solo 10 resultados

                        // Mostrar solo si el nombre del show está disponible
                        if (isset($show['show']['name'])) {
                            echo '<div class="show10">';
                            // Mostrar imagen si está disponible
                            if (isset($show['show']['image']['medium'])) {
                                echo '<img src="' . htmlspecialchars($show['show']['image']['medium']) . '" alt="Imagen del show" class="show-image10">';
                            } else {
                                echo '<img src="../img/default-show.jpg" alt="Imagen no disponible" class="show-image10">';
                            }
                            echo '<p class="show-title10">' . htmlspecialchars($show['show']['name']) . '</p>';
                            if (isset($show['show']['premiered'])) {
                                echo '<p class="show-premiere10">Estreno: ' . htmlspecialchars($show['show']['premiered']) . '</p>';
                            }
                            echo '</div>';
                            $counter++;
                        }
                    }
                } else {
                    echo '<p>No se encontraron resultados.</p>';
                }
                ?>
            </div>
        </section>
    </main>

    <footer class="footer9">
        <p>© 2025 Biblioteca Digital - StoryVerse</p>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const themeToggle = document.getElementById("themeToggle10");
            const themeIcon = document.getElementById("themeIcon10");
            const body = document.body;

            const currentTheme = localStorage.getItem("theme10");
            if (currentTheme === "dark") {
                body.classList.add("dark-mode10");
                themeIcon.src = "../img/oscuro.png";
            }

            themeToggle.addEventListener("click", function () {
                if (body.classList.contains("dark-mode10")) {
                    body.classList.remove("dark-mode10");
                    themeIcon.src = "../img/claro.png";
                    localStorage.setItem("theme10", "light");
                } else {
                    body.classList.add("dark-mode10");
                    themeIcon.src = "../img/oscuro.png";
                    localStorage.setItem("theme10", "dark");
                }
            });
        });
    </script>
</body>
</html>