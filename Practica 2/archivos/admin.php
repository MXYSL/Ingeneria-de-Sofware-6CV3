<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}
 
// Verificar permisos de administrador
function verificarAdmin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
        header("Location: login.php");
        exit("Acceso denegado.");
    }
}

// Funciones de manejo de usuarios
function agregarUsuario($conn, $nombre, $email, $password) {
    if (empty($nombre) || empty($email) || empty($password)) {
        return "Todos los campos son obligatorios.";
    }

    // Validaciones de correo
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Email inválido.";
    }

    // Verificar email existente
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        return "El email ya está registrado.";
    }
    $stmt->close();

    // Hash de contraseña
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    // Insertar usuario
    $role_id = 2; // Usuario normal por defecto
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $nombre, $email, $passwordHash, $role_id);

    if ($stmt->execute()) {
        $stmt->close();
        return "Usuario agregado correctamente.";
    } else {
        $stmt->close();
        return "Error al agregar usuario.";
    }
}

function obtenerUsuarioPorNombre($conn, $nombre) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $resultado = $stmt->get_result();
    return $resultado->fetch_assoc();
}

// Nueva función para obtener detalles de usuario con contraseña sin encriptar
function obtenerDetallesUsuario($conn, $nombre) {
    $stmt = $conn->prepare("SELECT id, username, email, password, role_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $resultado = $stmt->get_result();
    return $resultado->fetch_assoc();
}

function obtenerTodosLosUsuarios($conn) {
    $resultado = $conn->query("SELECT * FROM users");
    return $resultado;
}

function editarUsuario($conn, $id, $nombre, $email, $password = null) {
    if (empty($nombre) || empty($email)) {
        return "Todos los campos son obligatorios.";
    }

    if (!empty($password)) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nombre, $email, $passwordHash, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nombre, $email, $id);
    }

    if ($stmt->execute()) {
        $stmt->close();
        return "Usuario actualizado correctamente.";
    } else {
        $stmt->close();
        return "Error al actualizar usuario.";
    }
}

function eliminarUsuario($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $stmt->close();
        return "Usuario eliminado correctamente.";
    } else {
        $stmt->close();
        return "Error al eliminar usuario.";
    }
}

function cambiarRolUsuario($conn, $id) {
    // Obtener rol actual
    $stmt = $conn->prepare("SELECT role_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($role_id);
    $stmt->fetch();
    $stmt->close();

    // Cambiar rol
    $nuevoRol = ($role_id == 1) ? 2 : 1;
    $stmt = $conn->prepare("UPDATE users SET role_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $nuevoRol, $id);

    if ($stmt->execute()) {
        $stmt->close();
        return "Rol de usuario actualizado correctamente.";
    } else {
        $stmt->close();
        return "Error al actualizar el rol.";
    }
}

// Procesamiento de acciones
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
        case 'consultar_usuario':
            $usuario = obtenerDetallesUsuario($conn, $_POST['username'] ?? '');
            if ($usuario) {
                // Store user details in session for the modal
                $_SESSION['usuario_consulta'] = $usuario;
                $mensaje = "Usuario encontrado";
            } else {
                $mensaje = "Usuario no encontrado";
            }
            break;
        
        case 'agregar_usuario':
            $mensaje = agregarUsuario($conn, 
                $_POST['username'] ?? '', 
                $_POST['email'] ?? '', 
                $_POST['password'] ?? ''
            );
            break;

        case 'editar_usuario':
            $mensaje = editarUsuario($conn, 
                $_POST['id'] ?? 0, 
                $_POST['username'] ?? '', 
                $_POST['email'] ?? '', 
                $_POST['password'] ?? null
            );
            break;

        case 'eliminar_usuario':
            $mensaje = eliminarUsuario($conn, $_POST['id'] ?? 0);
            break;

        case 'cambiar_rol':
            $mensaje = cambiarRolUsuario($conn, $_POST['id'] ?? 0);
            break;
    }
}

// Verificar permisos de administrador
verificarAdmin();

// Obtener lista de usuarios
$usuarios = obtenerTodosLosUsuarios($conn);

// Obtener preferencia de tema
$tema_preferido = $_SESSION['tema'] ?? 'sistema';
?>
 
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="administrador.css">
</head>
<body class="<?php 
    if ($tema_preferido == 'oscuro') echo 'modo-oscuro';
    elseif ($tema_preferido == 'claro') echo 'modo-claro';
    else echo ''; 
?>">
    <div class="admin-dashboard">
        <div class="dashboard-nav">
            <h1>Panel de Administración</h1>
            <div>
                <div class="theme-toggle mb-2">
                    <button id="btnModoClaro" class="btn btn-sm btn-outline-light me-1">
                        <i class="fas fa-sun"></i> Claro
                    </button>
                    <button id="btnModoOscuro" class="btn btn-sm btn-outline-dark me-1">
                        <i class="fas fa-moon"></i> Oscuro
                    </button>
                    <button id="btnModoSistema" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-desktop"></i> Sistema
                    </button>
                </div>
                <div>
                    <a href="personal.php" class="btn btn-danger">Perfil</a>
                    <a href="instorve.php" class="btn btn-danger">Regresar</a>
                    <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
                </div>
            </div>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-info"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <div class="dashboard-actions">
            <button class="btn-custom" data-bs-toggle="modal" data-bs-target="#consultaModal">
                <i class="fas fa-search"></i>
                Consultar Usuario
            </button>
            <button class="btn-custom" data-bs-toggle="modal" data-bs-target="#agregarModal">
                <i class="fas fa-user-plus"></i>
                Agregar Nuevo
            </button>
            <button class="btn-custom" data-bs-toggle="modal" data-bs-target="#listaModal">
                <i class="fas fa-list"></i>
                Ver Lista Completa
            </button>
        </div>

        <!-- Modal Consulta Usuario -->
        <div class="modal fade" id="consultaModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Consultar Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form action="admin.php" method="post">
                            <input type="hidden" name="accion" value="consultar_usuario">
                            <input type="text" name="username" class="form-control" placeholder="Nombre de Usuario" required>
                            <button type="submit" class="btn btn-primary mt-3">Buscar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Agregar Usuario -->
        <div class="modal fade" id="agregarModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Agregar Nuevo Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form action="admin.php" method="post">
                            <input type="hidden" name="accion" value="agregar_usuario">
                            <input type="text" name="username" class="form-control mb-2" placeholder="Nombre de Usuario" required>
                            <input type="email" name="email" class="form-control mb-2" placeholder="Correo Electrónico" required>
                            <input type="password" name="password" class="form-control mb-2" placeholder="Contraseña" required>
                            <button type="submit" class="btn btn-success">Agregar Usuario</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Lista Completa -->
        <div class="modal fade" id="listaModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Lista Completa de Usuarios</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <table class="user-list-table table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>Rol</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($usuario = $usuarios->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($usuario['id']) ?></td>
                                        <td><?= htmlspecialchars($usuario['username']) ?></td>
                                        <td><?= htmlspecialchars($usuario['email']) ?></td>
                                        <td><?= $usuario['role_id'] == 1 ? 'Admin' : 'Usuario' ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <form action="admin.php" method="post" class="me-1">
                                                    <input type="hidden" name="accion" value="cambiar_rol">
                                                    <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-warning">
                                                        <?= $usuario['role_id'] == 1 ? 'Quitar Admin' : 'Hacer Admin' ?>
                                                    </button>
                                                </form>
                                                
                                                <form action="admin.php" method="post">
                                                    <input type="hidden" name="accion" value="eliminar_usuario">
                                                    <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Detalles de Usuario -->
        <div class="modal fade" id="detallesUsuarioModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detalles de Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form action="admin.php" method="post">
                            <div class="mb-3">
                                <label class="form-label" style="color: purple;">Usuario</label>
                                <input type="text" class="form-control" id="usuarioConsulta" name="username" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="color: red;">Correo</label>
                                <input type="email" class="form-control" id="correoConsulta" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="color: red;">Contraseña (Sin Encriptar)</label>
                                <input type="text" class="form-control" id="passwordConsulta" name="password">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Rol Actual</label>
                                <input type="text" class="form-control" id="rolConsulta" readonly>
                            </div>
                            <div class="d-flex justify-content-between">
                                <form action="admin.php" method="post" class="me-2">
                                    <input type="hidden" name="accion" value="cambiar_rol">
                                    <input type="hidden" name="id" id="idCambioRol">
                                    <button type="submit" class="btn btn-warning" id="btnCambiarRol">
                                        Cambiar Rol
                                    </button>
                                </form>
                                <form action="admin.php" method="post">
                                    <input type="hidden" name="accion" value="eliminar_usuario">
                                    <input type="hidden" name="id" id="idEliminarUsuario">
                                    <button type="submit" class="btn btn-danger" 
                                            onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
                                        Eliminar Usuario
                                    </button>
                                </form>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnModoClaro = document.getElementById('btnModoClaro');
        const btnModoOscuro = document.getElementById('btnModoOscuro');
        const btnModoSistema = document.getElementById('btnModoSistema');
        const body = document.body;

        // Función para cambiar el tema
        function cambiarTema(modo) {
            fetch('cambiar_tema.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'tema=' + modo
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    if (modo === 'claro') {
                        body.classList.remove('modo-oscuro');
                        body.classList.add('modo-claro');
                    } else if (modo === 'oscuro') {
                        body.classList.remove('modo-claro');
                        body.classList.add('modo-oscuro');
                    } else {
                        body.classList.remove('modo-claro', 'modo-oscuro');
                    }
                }
            });
        }

        // Event listeners para cambio de tema
        btnModoClaro.addEventListener('click', () => cambiarTema('claro'));
        btnModoOscuro.addEventListener('click', () => cambiarTema('oscuro'));
        btnModoSistema.addEventListener('click', () => cambiarTema('sistema'));

        // Poblar modal de detalles de usuario
        <?php if (isset($_SESSION['usuario_consulta'])): ?>
        // Populate modal with user details from session
        const usuario = <?php echo json_encode($_SESSION['usuario_consulta']); ?>;
        
        // Set values in the modal
        document.getElementById('usuarioConsulta').value = usuario.username;
        document.getElementById('correoConsulta').value = usuario.email;
        document.getElementById('passwordConsulta').value = usuario.password;
        document.getElementById('rolConsulta').value = usuario.role_id == 1 ? 'Admin' : 'Usuario';
        
        // Set hidden inputs for role change and delete
        document.getElementById('idCambioRol').value = usuario.id;
        document.getElementById('idEliminarUsuario').value = usuario.id;
        
        // Update change role button text
        const btnCambiarRol = document.getElementById('btnCambiarRol');
        btnCambiarRol.textContent = usuario.role_id == 1 ? 'Quitar Admin' : 'Hacer Admin';
        
        // Show the modal
        const detallesModal = new bootstrap.Modal(document.getElementById('detallesUsuarioModal'));
        detallesModal.show();
        
        // Clear the session variable
        <?php unset($_SESSION['usuario_consulta']); ?>
        <?php endif; ?>
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
