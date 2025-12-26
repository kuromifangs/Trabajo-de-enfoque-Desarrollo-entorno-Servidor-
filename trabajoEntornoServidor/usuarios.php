<?php
session_start();

if (isset($_GET['logout'])) {
    $_SESSION = array();
    session_destroy();
    header('Location: login.php');
    exit();
}


if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: ./login.php');
    exit();
}

require_once './functions/usuariosDb.php';

$mensaje = '';
$tipoMensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        if ($_POST['accion'] === 'editar' && isset($_POST['id'])) {
            $id = $_POST['id'];
            $nombre = $_POST['nombre'];
            $email = $_POST['email'];
            $rol = $_POST['rol'];
            $password = !empty($_POST['password']) ? $_POST['password'] : null;
            
            if (emailExiste($email, $id)) {
                $mensaje = 'El email ya está en uso por otro usuario';
                $tipoMensaje = 'danger';
            } else {
                if (actualizarUsuario($id, $nombre, $email, $rol, $password)) {
                    $mensaje = 'Usuario actualizado correctamente';
                    $tipoMensaje = 'success';
                } else {
                    $mensaje = 'Error al actualizar el usuario';
                    $tipoMensaje = 'danger';
                }
            }
        } elseif ($_POST['accion'] === 'eliminar' && isset($_POST['id'])) {
            $id = $_POST['id'];
            if ($id == $_SESSION['usuario_id']) {
                $mensaje = 'No puedes eliminar tu propio usuario';
                $tipoMensaje = 'danger';
            } else {
                if (eliminarUsuario($id)) {
                    $mensaje = 'Usuario eliminado correctamente';
                    $tipoMensaje = 'success';
                } else {
                    $mensaje = 'Error al eliminar el usuario';
                    $tipoMensaje = 'danger';
                }
            }
        }
    }
}

// Obtener todos los usuarios
$usuarios = obtenerUsuarios();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Deportivas Davante - Usuarios</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>

<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f6f7f8;
    }

    /* Custom primary color */
    .bg-primary-custom {
        background-color: #137fec !important;
    }
    .text-primary-custom {
        color: #137fec !important;
    }

    /* Sidebar */
    .sidebar {
        width: 260px;
        height: 100vh;
        background: white;
        border-right: 1px solid #e5e7eb;
        position: fixed;
        top: 0; left: 0;
        padding: 20px;
        overflow-y: auto;
    }

    .sidebar a {
        text-decoration: none;
        padding: 10px;
        border-radius: 8px;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .sidebar a:hover {
        background: #f3f4f6;
    }
    .sidebar a.active {
        background: rgba(19, 127, 236, 0.15);
        color: #137fec;
        font-weight: 600;
    }

    .sidebar .profile img {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        object-fit: cover;
    }

    /* Main content */
    .content {
        margin-left: 260px;
        padding: 40px;
    }

    /* Status pills */
    .badge-green {
        background: #d1fae5;
        color: #065f46;
    }
    .badge-yellow {
        background: #fef3c7;
        color: #92400e;
    }
    .badge-red {
        background: #fee2e2;
        color: #991b1b;
    }
</style>

</head>
<body>

<aside class="sidebar">

    <div class="d-flex align-items-center gap-3 p-2">
        <div class="d-flex justify-content-center align-items-center bg-primary-custom text-white rounded"
             style="width: 40px; height:40px;">
            <span class="material-symbols-outlined fs-4">store</span>
        </div>
        <h4 class="fw-bold m-0">Administrador</h4>
    </div>

    <nav class="mt-4 d-flex flex-column gap-1">
       
        <a href="inventario.php"><span class="material-symbols-outlined fs-4">inventory_2</span> Inventario</a>
        <a class="active" href="usuarios.php"><span class="material-symbols-outlined fs-4">group</span> Usuarios</a>
        

    <div class="mt-5 pt-3 border-top">
        <a href="#"><span class="material-symbols-outlined fs-4">settings</span> Ajustes</a>

        <div class="d-flex align-items-center gap-3 mt-3">
            <div style="
                width:42px; height:42px;
                background-size:cover;
                background-position:center;
                border-radius:10px;
                background-image:url('https://lh3.googleusercontent.com/aida-public/AB6AXuD23ml6sKU9CBJ6IzchWdp_ID-3B85y4xqxJfHxe94Houvr1GOMVPgVJtAA5RFlQzOEe_1_3H_QrYof6_SU4_wk0SBOCCeKQVoi6opFmXFEU0jaIQ-W1iOkAP21BU3wV1gM8BE_COKuKaxfb-lnGAfkIx5WhNYURUXE-mcT4-ew3fOsXNejZeX8ZCnZ1X2Ot1fGEL_A09EngwiripbwJv_8fFPzhlTNdZooQklS5OOyojOiwOp-Ml2xFXtadrkUOuXczP33BttChIM');"></div>

            <div>
                <p class="m-0 fw-semibold"><?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario'); ?></p>
                <small class="text-muted"><?= ucfirst($_SESSION['usuario_rol'] ?? 'Usuario'); ?></small>
            </div>

            <a href="?logout=1" class="ms-auto text-muted" title="Cerrar sesión">
                <span class="material-symbols-outlined fs-5">logout</span>
            </a>
        </div>
    </div>

</aside>

<main class="content">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Administrador de usuarios</h2>
            <p class="text-muted">Crea, modifica o elimina cuentas de cliente o administrador</p>
        </div>

        <a href="crearUsuario.php" class="btn bg-primary-custom text-white fw-bold d-flex align-items-center gap-2">
            <span class="material-symbols-outlined">add_circle</span>
            Añadir nuevo usuario
        </a>
    </div>

    <?php if ($mensaje): ?>
    <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($mensaje) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID DE USUARIO</th>
                    <th>NOMBRE COMPLETO</th>
                    <th>EMAIL</th>
                    <th>ROL</th>
                    <th class="text-end">ACCIONES</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($usuarios)): ?>
                <tr>
                    <td colspan="6" class="text-center py-4">No hay usuarios registrados</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td>
                            <p class="fw-semibold m-0"><?= htmlspecialchars($usuario['id']) ?></p>
                        </td>
                        <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                        <td><?= htmlspecialchars($usuario['email']) ?></td>
                        <td><?= ucfirst(htmlspecialchars($usuario['rol'])) ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm text-primary" onclick="editarUsuario(<?= $usuario['id'] ?>)" title="Editar">
                                <span class="material-symbols-outlined">edit</span>
                            </button>
                            <button class="btn btn-sm text-danger" onclick="confirmarEliminar(<?= $usuario['id'] ?>, '<?= htmlspecialchars($usuario['nombre']) ?>')" title="Eliminar">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

<div class="modal fade" id="modalEditarUsuario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="editar">
                    <input type="hidden" name="id" id="editId">
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre completo</label>
                        <input type="text" class="form-control" name="nombre" id="editNombre" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="editEmail" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select class="form-select" name="rol" id="editRol" required>
                            <option value="cliente">Cliente</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nueva contraseña (dejar en blanco para mantener la actual)</label>
                        <input type="password" class="form-control" name="password" id="editPassword">
                        <small class="text-muted">Mínimo 9 caracteres, 1 número y 1 símbolo</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn bg-primary-custom text-white">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEliminarUsuario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Eliminar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id" id="deleteId">
                    <p>¿Estás seguro de que deseas eliminar al usuario <strong id="deleteNombre"></strong>?</p>
                    <p class="text-danger">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Datos de usuarios para JavaScript
const usuarios = <?= json_encode($usuarios) ?>;

function editarUsuario(id) {
    const usuario = usuarios.find(u => u.id == id);
    if (usuario) {
        document.getElementById('editId').value = usuario.id;
        document.getElementById('editNombre').value = usuario.nombre;
        document.getElementById('editEmail').value = usuario.email;
        document.getElementById('editRol').value = usuario.rol;
        document.getElementById('editPassword').value = '';
        
        const modal = new bootstrap.Modal(document.getElementById('modalEditarUsuario'));
        modal.show();
    }
}

function confirmarEliminar(id, nombre) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteNombre').textContent = nombre;
    
    const modal = new bootstrap.Modal(document.getElementById('modalEliminarUsuario'));
    modal.show();
}
</script>

</body>
</html>
