<?php
session_start();

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

// Conectar a la base de datos
$conn = new mysqli("localhost", "root", "", "spring_auth");

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener la lista de usuarios
$sql = "SELECT id, username, email, role_id FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg,rgb(201, 102, 234),rgb(160, 75, 162));
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            color: black;
        }

        .admin-container {
            background:hsla(293, 46.80%, 42.70%, 0.70);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 10px;
            width: 95%;
            max-width: 900px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        table {
            width: 100%;
            margin-top: 1rem;
            color: white;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid white;
            padding: 10px;
            text-align: center;
        }

        .btn-action {
            background-color: #dc3545;
            border: none;
            padding: 0.5rem;
            color: white;
            cursor: pointer;
        }

        .btn-action:hover {
            background-color: #ffffff;
            color: #dc3545;
        }

        .btn-edit {
            background-color:hsl(249, 100.00%, 51.40%);
            border: none;
            padding: 0.5rem;
            color: white;
            cursor: pointer;
        }

        .btn-edit:hover {
            background-color: #ffffff;
            color:hsl(203, 100.00%, 51.40%);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <h1>Panel de Administración</h1>
        <a href="logout.php" class="btn btn-light">Cerrar Sesión</a>
        <a href="home.php" class="btn btn-light">Regresar</a>
        <h2 class="mt-3">Usuarios</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($usuario = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($usuario['id']) ?></td>
                    <td><?= htmlspecialchars($usuario['username']) ?></td>
                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                    <td><?= $usuario['role_id'] == 1 ? "Admin" : "Usuario" ?></td>
                    <td>
                        <button onclick="editUser(<?= $usuario['id'] ?>, '<?= htmlspecialchars($usuario['username']) ?>', '<?= htmlspecialchars($usuario['email']) ?>')" class="btn btn-edit">Editar</button>
                        <form action="toggle_admin.php" method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                            <button type="submit" class="btn btn-warning">
                                <?= $usuario['role_id'] == 1 ? "Quitar Admin" : "Hacer Admin" ?>
                            </button>
                        </form>
                        <button onclick="deleteUser(<?= $usuario['id'] ?>)" class="btn btn-action">Eliminar</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h3 class="mt-3">Agregar Usuario</h3>
        <form action="add_user.php" method="post">
            <div class="mb-3">
                <input type="text" name="username" class="form-control" placeholder="Nombre de Usuario" required>
            </div>
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
            </div>
            <button type="submit" class="btn btn-lg btn-success">Agregar Usuario</button>
        </form>
    </div>

    <!-- Modal para editar usuario -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Editar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm" action="edit_user.php" method="post">
                        <input type="hidden" name="id" id="editUserId">
                        
                        <div class="mb-3">
                            <label for="editUsername" class="form-label">Nombre</label>
                            <input type="text" name="username" id="editUsername" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" name="email" id="editEmail" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="editPassword" class="form-label">Nueva Contraseña (opcional)</label>
                            <input type="password" name="password" id="editPassword" placeholder="Contraseña" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-success">Guardar Cambios</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editUser(id, username, email) {
            document.getElementById("editUserId").value = id;
            document.getElementById("editUsername").value = username;
            document.getElementById("editEmail").value = email;
            
            var editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
            editModal.show();
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
