<?php
session_start();
require_once 'conexion.php';

// Si no hay sesión activa, redirigir al login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

// Endpoint para manejar solicitudes de búsqueda
if (isset($_GET['action']) && $_GET['action'] === 'search') {
    header('Content-Type: application/json');

    // Obtener el término de búsqueda desde la solicitud
    $query = isset($_GET['q']) ? trim($_GET['q']) : '';

    if (empty($query)) {
        echo json_encode(['error' => 'No se proporcionó un término de búsqueda.']);
        exit();
    }

    // Función para buscar libros en OpenLibrary
    function searchBooks($query) {
        $apiUrl = "https://openlibrary.org/search.json?" . http_build_query(['title' => $query, 'limit' => 10]);
        $response = file_get_contents($apiUrl);
        $data = json_decode($response, true);

        $books = [];
        if (isset($data['docs'])) {
            foreach ($data['docs'] as $book) {
                $books[] = [
                    'title' => $book['title'],
                    'author' => isset($book['author_name']) ? implode(', ', $book['author_name']) : 'Desconocido',
                    'year' => $book['first_publish_year'] ?? 'Desconocido',
                    'cover' => isset($book['cover_i']) ? "https://covers.openlibrary.org/b/id/{$book['cover_i']}-M.jpg" : 'img/default-cover.png'
                ];
            }
        }
        return $books;
    }

    // Función para buscar películas en TMDb
    function searchMovies($query) {
        $apiKey = '832a3694fe96402af8b742808b950175';
        $apiUrl = "https://api.themoviedb.org/3/search/movie?api_key={$apiKey}&query=" . urlencode($query) . "&language=es-ES&page=1";
        $response = file_get_contents($apiUrl);
        $data = json_decode($response, true);

        $movies = [];
        if (isset($data['results'])) {
            foreach ($data['results'] as $movie) {
                $movies[] = [
                    'title' => $movie['title'],
                    'year' => isset($movie['release_date']) ? explode('-', $movie['release_date'])[0] : 'Desconocido',
                    'poster' => isset($movie['poster_path']) ? "https://image.tmdb.org/t/p/w500{$movie['poster_path']}" : 'img/default-cover.png'
                ];
            }
        }
        return $movies;
    }

    // Realizar las búsquedas
    $books = searchBooks($query);
    $movies = searchMovies($query);

    // Devolver los resultados como JSON
    echo json_encode(['books' => $books, 'movies' => $movies]);
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
                'poster' => isset($movie['poster_path']) ? "https://image.tmdb.org/t/p/w500" . $movie['poster_path'] : "img/default-cover.png"
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
    <link rel="icon" href="img/icono.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Playfair+Display:wght@500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="instorve.css">
</head>
<body>
    <header class="header9">
        <div class="logo9">
            <img id="icono" src="img/icono.png" alt="StoryVerse">
        </div>
        <div class="nav-links9">
            <button id="themeToggle10" class="theme-button10">
                <img id="themeIcon10" src="img/claro.png" alt="Modo Claro">
            </button>
        </div>
        <nav class="nav9">
            <div class="search-container9">
                <input type="text" id="search-input9" placeholder="Buscar por título, autor, editorial...">
                <button id="search-button9">Buscar</button>
            </div>
        </nav>
        <div class="nav-links9">
            <a href="#" id="favorites-link"><i class="fa-solid fa-heart"></i> Favoritos</a>
            <a href="#" id="recommendations-link"><i class="fa-solid fa-star"></i> Recomendaciones</a> <!-- Nuevo botón -->
            <?php if ($isAdmin): ?>
                <a href="admin.php"><i class="fa-solid fa-user-tie"></i>Administrador</a>
            <?php endif; ?>
            <a href="personal.php"><i class="fas fa-user"></i> Mi perfil </a>
            <a href="auth.php?action=logout"><i class="fa-solid fa-right-to-bracket"></i> Cerrar sesión</a>
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
                        // Mezclar libros y películas alternando
                        $books = isset($content['books']['docs']) ? array_slice($content['books']['docs'], 0, 8) : [];
                        $movies = array_slice($content['movies'], 0, 8);

                        $maxItems = max(count($books), count($movies));
                        for ($i = 0; $i < $maxItems; $i++) {
                            if (isset($books[$i])) {
                                $book = $books[$i];
                                if (isset($book['title'])) {
                                    echo '<div class="content-item-horizontal">';
                                    echo '<img src="' . (isset($book['cover_i']) ? "https://covers.openlibrary.org/b/id/" . $book['cover_i'] . "-M.jpg" : "img/default-cover.png") . '" alt="Portada del libro">';
                                    echo '<div class="buttons-overlay">';
                                    echo '<button class="favorite-button" onclick="addToFavorites(\'book\', \'' . htmlspecialchars($book['title']) . '\', \'' . (isset($book['cover_i']) ? "https://covers.openlibrary.org/b/id/" . $book['cover_i'] . "-M.jpg" : "img/default-cover.png") . '\', \'' . (isset($book['author_name']) ? htmlspecialchars(implode(', ', $book['author_name'])) : 'Desconocido') . '\', \'' . (isset($book['first_publish_year']) ? $book['first_publish_year'] : 'Desconocido') . '\')">Favoritos</button>';
                                    echo '<button class="preview-button" onclick="previewItem(\'book\', \'' . htmlspecialchars($book['title']) . '\')">Vista previa</button>';
                                    echo '</div>';
                                    echo '<p>' . htmlspecialchars($book['title']) . '</p>';
                                    echo '</div>';
                                }
                            }
                            if (isset($movies[$i])) {
                                $movie = $movies[$i];
                                echo '<div class="content-item-horizontal">';
                                echo '<img src="' . $movie['poster'] . '" alt="Póster de la película">';
                                echo '<div class="buttons-overlay">';
                                echo '<button class="favorite-button" onclick="addToFavorites(\'movie\', \'' . htmlspecialchars($movie['title']) . '\', \'' . htmlspecialchars($movie['poster']) . '\', \'\', \'' . (isset($movie['release_date']) ? explode('-', $movie['release_date'])[0] : 'Desconocido') . '\')">Favoritos</button>';
                                echo '<button class="preview-button" onclick="previewItem(\'movie\', \'' . htmlspecialchars($movie['title']) . '\')">Vista previa</button>';
                                echo '</div>';
                                echo '<p>' . htmlspecialchars($movie['title']) . '</p>';
                                echo '</div>';
                            }
                        }
                        ?>
                    </div>
                    <button class="carousel-button next" onclick="scrollCarousel('<?php echo $categoryName; ?>', 1)">&#10095;</button>
                </div>
            <?php endforeach; ?>
        </section>

        <!-- Contenedor para los resultados de búsqueda -->
        <section id="search-results" class="category-section10" style="display: none;">
            <h2>Resultados de la búsqueda</h2>
            <div id="search-results-container"></div>
        </section>

        <!-- Contenedor para los favoritos -->
        <section id="favorites-section" class="category-section10" style="display: none;">
            <div class="favorites-header">
                <h2>Favoritos</h2>
                <button id="close-favorites" class="close-favorites-button">✖</button>
            </div>
            <div id="favorites-container" class="horizontal-list">
                <!-- Los favoritos se cargarán aquí dinámicamente -->
            </div>
        </section>

        <!-- Contenedor para los elementos filtrados -->
        <section id="filtered-section" class="category-section10" style="display: none;">
            <div class="filtered-header">
                <h2 id="filtered-title"></h2>
                <button id="close-filtered" class="close-filtered-button">✖</button>
            </div>
            <div id="filtered-container" class="horizontal-list">
                <!-- Los elementos filtrados se cargarán aquí dinámicamente -->
            </div>
        </section>

        <!-- Contenedor para las recomendaciones -->
        <section id="recommendations-section" class="category-section10" style="display: none;">
            <div class="recommendations-header">
                <h2>Recomendaciones</h2>
                <button id="close-recommendations" class="close-recommendations-button">✖</button>
            </div>
            <div id="recommendations-container" class="horizontal-list">
                <!-- Las recomendaciones se cargarán aquí dinámicamente -->
            </div>
        </section>
    </main>

    <footer class="footer9">
        <p>&copy; 2023 StoryVerse. Todos los derechos reservados.</p>
    </footer>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
            const themeToggle = document.getElementById("themeToggle10");
            const themeIcon = document.getElementById("themeIcon10");
            const themeicono = document.getElementById("icono");
            const body = document.body;
            const currentTheme = localStorage.getItem("theme10") || "light";

            if (currentTheme === "dark") {
                body.classList.add("dark-mode10");
                themeIcon.src = "img/oscuro.png"; // Cambiar ícono al modo oscuro
                themeicono.src = "img/iconoc.png"; // Cambiar logo al modo oscuro
            }

            themeToggle.addEventListener("click", function () {
                if (body.classList.contains("dark-mode10")) {
                    body.classList.remove("dark-mode10");
                    themeIcon.src = "img/claro.png"; // Cambiar ícono al modo claro
                    themeicono.src = "img/icono.png"; // Cambiar logo al modo claro
                    localStorage.setItem("theme10", "light");
                } else {
                    body.classList.add("dark-mode10");
                    themeIcon.src = "img/oscuro.png"; // Cambiar ícono al modo oscuro
                    themeicono.src = "img/iconoc.png"; // Cambiar logo al modo oscuro
                    localStorage.setItem("theme10", "dark");
                }
            });
        });

        function addToFavorites(type, title, image, author = "", year = "") {
            // Obtener los favoritos actuales desde localStorage
            let favorites = JSON.parse(localStorage.getItem("favorites")) || [];

            // Evitar duplicados
            if (favorites.some(fav => fav.title === title && fav.type === type)) {
                alert("Este elemento ya está en favoritos.");
                return;
            }

            // Validar que los datos sean válidos
            if (!title || !image) {
                alert("No se puede añadir este elemento a favoritos porque falta información.");
                return;
            }

            // Crear el objeto del favorito
            const item = {
                type,
                title,
                image,
                author,
                year
            };

            // Agregar el nuevo favorito
            favorites.push(item);
            localStorage.setItem("favorites", JSON.stringify(favorites));
            alert(`${type === "book" ? "Libro" : "Película"} "${title}" añadido a favoritos.`);
        }

        function previewItem(type, title) {
            const previewContent = document.getElementById("previewContent");
            const modalTitle = document.getElementById("previewModalLabel");

            // Configurar el título del modal
            modalTitle.textContent = `Vista previa de ${type === 'book' ? 'libro' : 'película'}`;

            // Mostrar un mensaje de carga mientras se obtiene la información
            previewContent.innerHTML = `<p>Cargando información...</p>`;

            if (type === 'book') {
                // Llamar a la API de OpenLibrary para obtener detalles del libro
                fetch(`https://openlibrary.org/search.json?title=${encodeURIComponent(title)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.docs && data.docs.length > 0) {
                            const book = data.docs[0]; // Tomar el primer resultado
                            const bookKey = book.key; // Obtener la clave del libro para más detalles

                            // Hacer una segunda solicitud para obtener la descripción
                            fetch(`https://openlibrary.org${bookKey}.json`)
                                .then(response => response.json())
                                .then(details => {
                                    let description = details.description
                                        ? (typeof details.description === 'string' ? details.description : details.description.value)
                                        : 'No disponible';

                                    // Si hay descripción, traducirla al español
                                    if (description !== 'No disponible') {
                                        translateToSpanish(description).then(translatedDescription => {
                                            previewContent.innerHTML = `
                                                <p><strong>Título:</strong> ${book.title}</p>
                                                <p><strong>Autor:</strong> ${book.author_name ? book.author_name.join(', ') : 'Desconocido'}</p>
                                                <p><strong>Año:</strong> ${book.first_publish_year || 'Desconocido'}</p>
                                                <p><strong>Síntesis:</strong> ${translatedDescription}</p>
                                            `;
                                        }).catch(error => {
                                            console.error('Error al traducir la descripción:', error);
                                            previewContent.innerHTML = `
                                                <p><strong>Título:</strong> ${book.title}</p>
                                                <p><strong>Autor:</strong> ${book.author_name ? book.author_name.join(', ') : 'Desconocido'}</p>
                                                <p><strong>Año:</strong> ${book.first_publish_year || 'Desconocido'}</p>
                                                <p><strong>Síntesis:</strong> ${description}</p>
                                            `;
                                        });
                                    } else {
                                        previewContent.innerHTML = `
                                            <p><strong>Título:</strong> ${book.title}</p>
                                            <p><strong>Autor:</strong> ${book.author_name ? book.author_name.join(', ') : 'Desconocido'}</p>
                                            <p><strong>Año:</strong> ${book.first_publish_year || 'Desconocido'}</p>
                                            <p><strong>Síntesis:</strong> No disponible</p>
                                        `;
                                    }
                                })
                                .catch(error => {
                                    console.error('Error al obtener los detalles del libro:', error);
                                    previewContent.innerHTML = `
                                        <p><strong>Título:</strong> ${book.title}</p>
                                        <p><strong>Autor:</strong> ${book.author_name ? book.author_name.join(', ') : 'Desconocido'}</p>
                                        <p><strong>Año:</strong> ${book.first_publish_year || 'Desconocido'}</p>
                                        <p><strong>Síntesis:</strong> No disponible</p>
                                    `;
                                });
                        } else {
                            previewContent.innerHTML = `<p>No se encontró información para este libro.</p>`;
                        }
                    })
                    .catch(error => {
                        console.error('Error al obtener los detalles del libro:', error);
                        previewContent.innerHTML = `<p>Error al cargar la información del libro.</p>`;
                    });
            } else if (type === 'movie') {
                // Llamar a la API de The Movie Database para obtener detalles de la película
                const apiKey = '832a3694fe96402af8b742808b950175';
                fetch(`https://api.themoviedb.org/3/search/movie?api_key=${apiKey}&query=${encodeURIComponent(title)}&language=es-ES`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.results && data.results.length > 0) {
                            const movie = data.results[0]; // Tomar el primer resultado
                            previewContent.innerHTML = `
                                <p><strong>Título:</strong> ${movie.title}</p>
                                <p><strong>Año:</strong> ${movie.release_date ? movie.release_date.split('-')[0] : 'Desconocido'}</p>
                                <p><strong>Síntesis:</strong> ${movie.overview || 'No disponible'}</p>
                            `;
                        } else {
                            previewContent.innerHTML = `<p>No se encontró información para esta película.</p>`;
                        }
                    })
                    .catch(error => {
                        console.error('Error al obtener los detalles de la película:', error);
                        previewContent.innerHTML = `<p>Error al cargar la información de la película.</p>`;
                    });
            }

            // Mostrar el modal
            const previewModal = new bootstrap.Modal(document.getElementById("previewModal"));
            previewModal.show();
        }

        // Función para traducir texto al español usando la API de Google Translate
        function translateToSpanish(text) {
            const apiKey = 'AIzaSyCP7bbHyXIIu4OHckItawaZ67wXE5uiU4k'; // Clave de API de Google Translate
            const url = `https://translation.googleapis.com/language/translate/v2?key=${apiKey}`;
            const body = {
                q: text,
                target: 'es',
                source: 'en'
            };

            return fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.data && data.data.translations && data.data.translations.length > 0) {
                        return data.data.translations[0].translatedText;
                    } else {
                        throw new Error('No se pudo traducir el texto.');
                    }
                });
        }

        function scrollCarousel(category, direction) {
            const carousel = document.getElementById(`carousel-${category}`);
            const items = carousel.querySelectorAll('.content-item-horizontal');
            const hiddenItems = Array.from(items).filter(item => item.style.display === 'none');

            // Mostrar las portadas ocultas
            if (hiddenItems.length > 0) {
                hiddenItems.slice(0, 5).forEach(item => {
                    item.style.display = 'flex';
                });
            } else {
                // Desplazamiento del carrusel
                const scrollAmount = 800; // Ajusta este valor según sea necesario
                carousel.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
            }
        }

        document.getElementById('search-button9').addEventListener('click', function () {
            const query = document.getElementById('search-input9').value.trim();
            const defaultSections = document.querySelector('.category-section10'); // Secciones predeterminadas
            const searchResultsSection = document.getElementById('search-results'); // Contenedor de resultados
            const searchResultsContainer = document.getElementById('search-results-container'); // Contenedor de resultados dinámicos

            if (!query) {
                alert('Por favor, ingresa un término de búsqueda.');
                return;
            }

            // Ocultar las secciones predeterminadas y mostrar el contenedor de resultados
            defaultSections.style.display = 'none';
            searchResultsSection.style.display = 'block';
            searchResultsContainer.innerHTML = '<p>Cargando resultados...</p>';

            // Realizar la búsqueda
            fetch(`instorve.php?action=search&q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        searchResultsContainer.innerHTML = `<p>${data.error}</p>`;
                        return;
                    }

                    // Mostrar los resultados
                    const { books, movies } = data;
                    let resultsHTML = '';

                    if (books.length > 0) {
                        resultsHTML += '<h3>Libros:</h3><div class="horizontal-list">';
                        books.forEach(book => {
                            resultsHTML += `
                                <div class="content-item-horizontal">
                                    <img src="${book.cover}" alt="Portada del libro">
                                    <div class="buttons-overlay">
                                        <button class="favorite-button" onclick="addToFavorites('book', '${book.title}', '${book.cover}', '${book.author}', '${book.year}')">Favoritos</button>
                                        <button class="preview-button" onclick="previewItem('book', '${book.title}')">Vista previa</button>
                                    </div>
                                    <p><strong>${book.title}</strong></p>
                                    <p>Autor: ${book.author}</p>
                                    <p>Año: ${book.year}</p>
                                </div>
                            `;
                        });
                        resultsHTML += '</div>';
                    }

                    if (movies.length > 0) {
                        resultsHTML += '<h3>Películas:</h3><div class="horizontal-list">';
                        movies.forEach(movie => {
                            resultsHTML += `
                                <div class="content-item-horizontal">
                                    <img src="${movie.poster}" alt="Póster de la película">
                                    <div class="buttons-overlay">
                                        <button class="favorite-button" onclick="addToFavorites('movie', '${movie.title}', '${movie.poster}', '', '${movie.year}')">Favoritos</button>
                                        <button class="preview-button" onclick="previewItem('movie', '${movie.title}')">Vista previa</button>
                                    </div>
                                    <p><strong>${movie.title}</strong></p>
                                    <p>Año: ${movie.year}</p>
                                </div>
                            `;
                        });
                        resultsHTML += '</div>';
                    }

                    if (books.length === 0 && movies.length === 0) {
                        resultsHTML += '<p>No se encontraron resultados.</p>';
                    }

                    searchResultsContainer.innerHTML = resultsHTML;
                })
                .catch(error => {
                    console.error('Error al realizar la búsqueda:', error);
                    searchResultsContainer.innerHTML = '<p>Error al realizar la búsqueda. Inténtalo de nuevo más tarde.</p>';
                });
        });

        // Mostrar las secciones predeterminadas al borrar el texto de búsqueda
        document.getElementById('search-input9').addEventListener('input', function () {
            const query = this.value.trim();
            const defaultSections = document.querySelector('.category-section10'); // Secciones predeterminadas
            const searchResultsSection = document.getElementById('search-results'); // Contenedor de resultados

            if (!query) {
                // Mostrar las secciones predeterminadas y ocultar los resultados de búsqueda
                defaultSections.style.display = 'block';
                searchResultsSection.style.display = 'none';
            }
        });

        document.addEventListener("DOMContentLoaded", function () {
            const defaultSections = document.querySelector(".category-section10");
            const searchResultsSection = document.getElementById("search-results");
            const searchResultsContainer = document.getElementById("search-results-container");

            // Función para filtrar y mostrar películas o libros
            function filterContent(type) {
                // Ocultar las secciones predeterminadas
                defaultSections.style.display = "none";
                searchResultsSection.style.display = "block";
                searchResultsContainer.innerHTML = "<p>Cargando resultados...</p>";

                // Obtener los datos de las categorías
                const items = [];
                <?php foreach ($categories as $categoryName => $content): ?>
                    <?php if ($categoryName): ?>
                        items.push(...<?php echo json_encode($content); ?>);
                    <?php endif; ?>
                <?php endforeach; ?>
            }
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const favoritesSection = document.getElementById("favorites-section");
            const favoritesContainer = document.getElementById("favorites-container");
            const closeFavoritesButton = document.getElementById("close-favorites");
            const defaultSections = document.querySelector(".category-section10");
            const searchResultsSection = document.getElementById("search-results");

            // Almacenar favoritos en localStorage
            let favorites = JSON.parse(localStorage.getItem("favorites")) || [];

            // Función para renderizar los favoritos
            function renderFavorites() {
                favoritesContainer.innerHTML = "";
                let favorites = JSON.parse(localStorage.getItem("favorites")) || [];

                // Filtrar elementos inválidos
                favorites = favorites.filter(fav => fav.title && fav.image);

                if (favorites.length === 0) {
                    favoritesContainer.innerHTML = "<p>No tienes elementos en favoritos.</p>";
                    return;
                }

                favorites.forEach(item => {
                    favoritesContainer.innerHTML += `
                        <div class="content-item-horizontal">
                            <img src="${item.image}" alt="${item.type === 'book' ? 'Portada del libro' : 'Póster de la película'}">
                            <div class="buttons-overlay">
                                <button class="remove-favorite-button" onclick="removeFromFavorites('${item.type}', '${item.title}')">Eliminar</button>
                            </div>
                            <p><strong>${item.title}</strong></p>
                            <p>${item.type === 'book' ? `Autor: ${item.author}` : `Año: ${item.year}`}</p>
                        </div>
                    `;
                });
            }

            // Mostrar favoritos al hacer clic en el enlace
            document.getElementById("favorites-link").addEventListener("click", function (e) {
                e.preventDefault();
                defaultSections.style.display = "none";
                searchResultsSection.style.display = "none";
                favoritesSection.style.display = "block";
                renderFavorites();
            });

            // Cerrar favoritos
            closeFavoritesButton.addEventListener("click", function () {
                favoritesSection.style.display = "none";
                if (document.getElementById("search-input9").value.trim()) {
                    searchResultsSection.style.display = "block";
                } else {
                    defaultSections.style.display = "block";
                }
            });

            // Función para eliminar de favoritos
            window.removeFromFavorites = function (type, title) {
                favorites = favorites.filter(fav => !(fav.title === title && fav.type === type));
                localStorage.setItem("favorites", JSON.stringify(favorites));
                renderFavorites();
            };
        });

        function removeFromFavorites(type, title) {
            let favorites = JSON.parse(localStorage.getItem("favorites")) || [];
            favorites = favorites.filter(fav => !(fav.title === title && fav.type === type));
            localStorage.setItem("favorites", JSON.stringify(favorites));
            renderFavorites();
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Modal para vista previa -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewModalLabel">Vista previa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="previewContent">
                    <!-- Contenido de la vista previa se cargará aquí -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Vista Previa -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewModalLabel">Vista Previa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div id="previewContent">
                        <!-- Aquí se cargará dinámicamente el contenido de la vista previa -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
        const recommendationsSection = document.getElementById("recommendations-section");
        const recommendationsContainer = document.getElementById("recommendations-container");
        const closeRecommendationsButton = document.getElementById("close-recommendations");
        const defaultSections = document.querySelector(".category-section10");
        const searchResultsSection = document.getElementById("search-results");
    
        // Mostrar recomendaciones al hacer clic en el botón
        document.getElementById("recommendations-link").addEventListener("click", function (e) {
            e.preventDefault();
            defaultSections.style.display = "none";
            searchResultsSection.style.display = "none";
            recommendationsSection.style.display = "block";
            recommendationsContainer.innerHTML = "<p>Cargando recomendaciones...</p>";
    
            // Obtener favoritos y generar recomendaciones
            const favorites = JSON.parse(localStorage.getItem("favorites")) || [];
            const genres = {};
    
            // Contar géneros en los favoritos
            favorites.forEach(item => {
                if (item.type === "book" && item.genre) {
                    genres[item.genre] = (genres[item.genre] || 0) + 1;
                }
            });
    
            // Obtener el género más frecuente
            const preferredGenre = Object.keys(genres).reduce((a, b) => (genres[a] > genres[b] ? a : b), null);
    
            // Generar recomendaciones basadas en el género preferido
            fetch(`instorve.php?action=search&q=${encodeURIComponent(preferredGenre)}`)
                .then(response => response.json())
                .then(data => {
                    const { books, movies } = data;
                    let recommendationsHTML = "";
    
                    if (books.length > 0) {
                        recommendationsHTML += '<h3>Libros:</h3><div class="horizontal-list">';
                        books.slice(0, 10).forEach(book => {
                            recommendationsHTML += `
                                <div class="content-item-horizontal">
                                    <img src="${book.cover}" alt="Portada del libro">
                                    <div class="buttons-overlay">
                                        <button class="favorite-button" onclick="addToFavorites('book', '${book.title}', '${book.cover}', '${book.author}', '${book.year}')">Favoritos</button>
                                        <button class="preview-button" onclick="previewItem('book', '${book.title}')">Vista previa</button>
                                    </div>
                                    <p><strong>${book.title}</strong></p>
                                    <p>Autor: ${book.author}</p>
                                    <p>Año: ${book.year}</p>
                                </div><br>
                            `;
                        });
                        recommendationsHTML += "</div><br>";
                    }
    
                    if (movies.length > 0) {
                        recommendationsHTML += '<br><h3>Películas:</h3><div class="horizontal-list">';
                        movies.slice(0, 10).forEach(movie => {
                            recommendationsHTML += `
                                <div class="content-item-horizontal">
                                    <img src="${movie.poster}" alt="Póster de la película">
                                    <div class="buttons-overlay">
                                        <button class="favorite-button" onclick="addToFavorites('movie', '${movie.title}', '${movie.poster}', '', '${movie.year}')">Favoritos</button>
                                        <button class="preview-button" onclick="previewItem('movie', '${movie.title}')">Vista previa</button>
                                    </div>
                                    <p><strong>${movie.title}</strong></p>
                                    <p>Año: ${movie.year}</p>
                                </div>
                            `;
                        });
                        recommendationsHTML += "</div>";
                    }
    
                    if (books.length === 0 && movies.length === 0) {
                        recommendationsHTML += "<p>No se encontraron recomendaciones.</p>";
                    }
    
                    recommendationsContainer.innerHTML = recommendationsHTML;
                })
                .catch(error => {
                    console.error("Error al generar recomendaciones:", error);
                    recommendationsContainer.innerHTML = "<p>Error al cargar las recomendaciones. Inténtalo de nuevo más tarde.</p>";
                });
        });
    
        // Cerrar la sección de recomendaciones
        closeRecommendationsButton.addEventListener("click", function () {
            recommendationsSection.style.display = "none";
            if (document.getElementById("search-input9").value.trim()) {
                searchResultsSection.style.display = "block";
            } else {
                defaultSections.style.display = "block";
            }
        });
        });
    </script>
</body>
</html>
