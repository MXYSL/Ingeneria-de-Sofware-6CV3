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

// Función para consumir la API de OpenLibrary con diferentes criterios
function fetchBooks($query) {
    $apiUrl = "https://openlibrary.org/search.json?" . http_build_query(['title' => $query]);
    $response = file_get_contents($apiUrl);
    return json_decode($response, true);
}

// Función para obtener películas desde la API de The Movie Database
function fetchMovies($type = 'popular') {
    $apiKey = '832a3694fe96402af8b742808b950175';
    $apiUrl = "https://api.themoviedb.org/3/movie/{$type}?api_key={$apiKey}&language=es-ES";
    $response = file_get_contents($apiUrl);
    $data = json_decode($response, true);

    $movies = [];
    if (isset($data['results'])) {
        foreach ($data['results'] as $movie) {
            $movies[] = [
                'title' => $movie['title'],
                'poster' => isset($movie['poster_path']) ? "https://image.tmdb.org/t/p/w500" . $movie['poster_path'] : "../img/default-movie.png"
            ];
        }
    }
    return $movies;
}

// Obtener libros y películas para diferentes secciones
$categories = [
    'Populares' => [
        'books' => fetchBooks('bestsellers'),
        'movies' => fetchMovies('popular')
    ],
    'Fantasía' => [
        'books' => fetchBooks('fantasy'),
        'movies' => fetchMovies('popular')
    ],
    'Ciencia Ficción' => [
        'books' => fetchBooks('science fiction'),
        'movies' => fetchMovies('top_rated')
    ],
    'Misterio' => [
        'books' => fetchBooks('mystery'),
        'movies' => fetchMovies('now_playing')
    ]
    
];
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
    <link rel="stylesheet" href="instorve.css">
</head>
<body>
    <header class="header9">
        <div class="logo9">
            <img id= "icono" src="../img/icono.png" alt="StoryVerse">
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
            <a href="favoritos.php"><i class="fa-solid fa-heart"></i> Favoritos</a>

            <?php if ($isAdmin): ?>
                <a href="admin.php"><i class="fa-solid fa-user-tie"></i>Administrador</a>
            <?php endif; ?>

            <a href="perfil.php"><i class="fas fa-user"></i> Mi perfil </a>
            <a href="logout.php"><i class="fa-solid fa-right-to-bracket"></i> Cerrar sesión</a>
        </div>
    </header>

    <main class="main10">
        <section class="category-section10">
            <p class="welcome-message">Bienvenido, <span style="color:rgb(126, 84, 26); font-size: 20px;"><?php echo htmlspecialchars($nombreUsuario); ?></span> </p>
            <?php foreach ($categories as $categoryName => $content): ?>
                <h2 class="category-title10"><?php echo htmlspecialchars($categoryName); ?></h2>
                <div class="carousel-container">
                    <button class="carousel-button prev" onclick="scrollCarousel('<?php echo $categoryName; ?>', -1)">&#10094;</button>
                    <div class="horizontal-list" id="carousel-<?php echo $categoryName; ?>">
                        <?php
                        // Mezclar libros y películas
                        $books = isset($content['books']['docs']) ? array_slice($content['books']['docs'], 0, 8) : [];
                        $movies = array_slice($content['movies'], 0, 7);

                        $items = [];
                        foreach ($books as $book) {
                            if (isset($book['title'])) {
                                $items[] = [
                                    'type' => 'book',
                                    'title' => $book['title'],
                                    'image' => isset($book['cover_i']) ? "https://covers.openlibrary.org/b/id/" . $book['cover_i'] . "-M.jpg" : "../img/default-cover.png"
                                ];
                            }
                        }
                        foreach ($movies as $movie) {
                            $items[] = [
                                'type' => 'movie',
                                'title' => $movie['title'],
                                'image' => $movie['poster']
                            ];
                        }

                        // Mostrar los elementos
                        foreach ($items as $item): ?>
                            <div class="content-item-horizontal">
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['type'] === 'book' ? 'Portada del libro' : 'Póster de la película'); ?>">
                                <p><?php echo htmlspecialchars($item['title']); ?></p>
                                <button class="favorite-button" onclick="addToFavorites('<?php echo $item['type']; ?>', '<?php echo htmlspecialchars($item['title']); ?>')">Añadir a Favoritos</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-button next" onclick="scrollCarousel('<?php echo $categoryName; ?>', 1)">&#10095;</button>
                </div>
            <?php endforeach; ?>
        </section>
    </main>

    <footer class="footer9">
        <p>© 2025 Biblioteca Digital - StoryVerse</p>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const themeToggle = document.getElementById("themeToggle10");
            const themeIcon = document.getElementById("themeIcon10");
            const themeicono = document.getElementById("icono");
            const body = document.body;

            const currentTheme = localStorage.getItem("theme10");
            if (currentTheme === "dark") {
                body.classList.add("dark-mode10");
                themeIcon.src = "../img/oscuro.png"; // Cambiar ícono al modo oscuro
                themeicono.src = "../img/icono-oscuro.png"; // Cambiar logo al modo oscuro
            }

            themeToggle.addEventListener("click", function () {
                if (body.classList.contains("dark-mode10")) {
                    body.classList.remove("dark-mode10");
                    themeIcon.src = "../img/claro.png"; // Cambiar ícono al modo claro
                    themeicono.src = "../img/icono.png"; // Cambiar logo al modo claro
                    localStorage.setItem("theme10", "light");
                } else {
                    body.classList.add("dark-mode10");
                    themeIcon.src = "../img/oscuro.png"; // Cambiar ícono al modo oscuro
                    themeicono.src = "../img/icono-oscuro.png"; // Cambiar logo al modo oscuro
                    localStorage.setItem("theme10", "dark");
                }
            });
        });

        function addToFavorites(type, title) {
            alert(`${type === 'book' ? 'Libro' : 'Película'} "${title}" añadido a favoritos.`);
            // Aquí puedes agregar lógica para guardar en la base de datos o localStorage
        }

        function previewItem(type, title) {
            alert(`Vista previa de ${type === 'book' ? 'libro' : 'película'}: "${title}"`);
            // Aquí puedes agregar lógica para mostrar una vista previa
        }

        function scrollCarousel(category, direction) {
            const carousel = document.getElementById(`carousel-${category}`);
            const scrollAmount = 300; // Ajusta este valor según sea necesario
            carousel.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
        }
    </script>
</body>
</html>
