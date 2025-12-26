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

include './functions/configDb.php';
$error="";

function obtenerInventario() {
    $db = conectarDB();
    $sql = "SELECT * FROM productos ORDER BY id ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function insertInventario($nombre, $precio, $stock, $imagenUrl) {
    $db = conectarDB();
    
    $sql = "INSERT INTO productos (nombre, precio, stock, imagen) VALUES (:nombre, :precio, :stock, :imagen)";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':nombre' => $nombre,
        ':precio' => $precio,
        ':stock'  => $stock,
        ':imagen' => $imagenUrl
    ]);
}

function deleteInventario($id) {
    $db = conectarDB();
    $sql = "DELETE FROM productos WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $id]);
}

function updateInventario($id, $nombre, $precio, $stock, $imagenUrl) {
    $db = conectarDB();
    $sql = "UPDATE productos SET nombre = :nombre, precio = :precio, stock = :stock, imagen = :imagen WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':id' => $id,
        ':nombre' => $nombre,
        ':precio' => $precio,
        ':stock'  => $stock,
        ':imagen' => $imagenUrl
    ]);
}

function getProductoById($id) {
    $db = conectarDB();
    $sql = "SELECT * FROM productos WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$inventario = obtenerInventario();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_product') {
        $nombre = $_POST['nombre'] ;
        $precio = $_POST['precio'] ;
        $stock =$_POST['stock'];
        $imagenUrl = $_POST['imagen'] ;
        $error = '';

      insertInventario($nombre, $precio, $stock, $imagenUrl); 
      header('Location: ./inventario.php');
      exit();
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'delete_product') {
        $id = $_POST['id'];
        deleteInventario($id);
        header('Location: ./inventario.php');
        exit();
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'edit_product') {
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $precio = $_POST['precio'];
        $stock = $_POST['stock'];
        $imagenUrl = $_POST['imagen'];
        
        updateInventario($id, $nombre, $precio, $stock, $imagenUrl);
        header('Location: ./inventario.php');
        exit();
    }

 }
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Deportivas Davante - Inventario</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>

<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f6f7f8;
    }

    .bg-primary-custom {
        background-color: #137fec !important;
    }
    .text-primary-custom {
        color: #137fec !important;
    }

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

    .content {
        margin-left: 260px;
        padding: 40px;
    }

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
    
   

    .text-primary-custom {
        color: #137fec !important;
    }

    .bg-primary-custom {
        background-color: #137fec !important;
    }

    .form-control:focus {
        border-color: #137fec !important;
        box-shadow: 0 0 0 0.2rem rgba(19, 127, 236, 0.25);
    }

    .rounded-xl {
        border-radius: 0.75rem;
    }

    .material-symbols-outlined {
        font-variation-settings:
            'FILL'0,
            'wght'400,
            'GRAD'0,
            'opsz'24;
    }

    .mensaje-error {
        color: #fc172aff;
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
       
        <a class="active" href="inventario.php"><span class="material-symbols-outlined fs-4">inventory_2</span> Inventario</a>
        <a href="usuarios.php"><span class="material-symbols-outlined fs-4">group</span> Usuarios</a>
        

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
            <h2 class="fw-bold">Inventario de zapatos</h2>
            <p class="text-muted">Administra, agrega y edita detalles de las zapatillas</p>
        </div>


       <button class="btn bg-primary-custom text-white fw-bold d-flex align-items-center gap-2"
               data-bs-toggle="modal" data-bs-target="#addProductModal">
             <span class="material-symbols-outlined">add_circle</span>
             Añadir nuevo producto
         
         </button>


    </div>

    <div class="card shadow-sm">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Estado</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
<tbody>
    <?php foreach ($inventario as $item): ?>
        <tr>
            <td><?= $item['id']; ?></td>
            <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded bg-light"
                                 style="width:48px; height:48px; background-size:cover; background-position:center;
                                 background-image:url('<?= $item['imagen']; ?>');"></div>

                            <div>
                                <p class="fw-semibold m-0"><?= $item['nombre']; ?></p>
                                <small class="text-muted"><?= $item['nombre']; ?></small>
                            </div>
                        </div>
                    </td>
                    
                        <?php $precioFormateado = number_format($item['precio'], 2, ',', '.'); $precioFormateado = preg_replace('/,00$/', '', $precioFormateado); ?>
                        <td><?= $precioFormateado ?>€</td>
                    <td><?= $item['stock']; ?></td>
                    <td>
                        <?php
                            if ($item['stock'] > 10) {
                                echo '<span class="badge badge-green p-2">En Stock</span>';
                            } elseif ($item['stock'] > 0) {
                                echo '<span class="badge badge-yellow p-2">Stock Bajo</span>';
                            } else {
                                echo '<span class="badge badge-red p-2">Fuera de Stock</span>';
                            }
                        ?>
                    </td>

                    <td class="text-end">
                        <button class="btn btn-sm text-primary btn-edit-product" 
                                data-id="<?= $item['id']; ?>" 
                                data-nombre="<?= htmlspecialchars($item['nombre'], ENT_QUOTES); ?>" 
                                data-precio="<?= $item['precio']; ?>" 
                                data-stock="<?= $item['stock']; ?>" 
                                data-imagen="<?= htmlspecialchars($item['imagen'], ENT_QUOTES); ?>">
                            <span class="material-symbols-outlined">edit</span>
                        </button>
                        <form method="post" style="display:inline;" onsubmit="return confirm('¿Estás seguro de eliminar este producto?');">
                            <input type="hidden" name="action" value="delete_product">
                            <input type="hidden" name="id" value="<?= $item['id']; ?>">
                            <button type="submit" class="btn btn-sm text-danger">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </form>
                    </td>
</tr>
<?php endforeach; ?>
</tbody>
            
        </table>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <input type="hidden" name="action" value="add_product">
      <div class="modal-header">
        <h5 class="modal-title" id="addProductModalLabel">Añadir producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
         <div class="mb-3">
                                <label class="form-label fw-semibold">Nombre</label>
                                <div class="input-group">
                                    <input id="nombre" name="nombre" type="text" class="form-control"
                                        placeholder="Nombre del producto">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Precio</label>
                                <div class="input-group">
                                    <input id="precio" name="precio" type="number" step="0.01" class="form-control"
                                        placeholder="Precio del producto">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Stock</label>
                                <div class="input-group">
                                    <input id="stock" name="stock" type="number" class="form-control"
                                        placeholder="Stock del producto">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Imagen</label>
                                <div class="input-group">
                                    <input id="imagen" name="imagen" type="text" class="form-control"
                                        placeholder="URL de la imagen">
                                </div>
                            </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn bg-primary-custom text-white">Guardar</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <input type="hidden" name="action" value="edit_product">
      <input type="hidden" name="id" id="edit_id">
      <div class="modal-header">
        <h5 class="modal-title" id="editProductModalLabel">Editar producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
         <div class="mb-3">
                                <label class="form-label fw-semibold">Nombre</label>
                                <div class="input-group">
                                    <input id="edit_nombre" name="nombre" type="text" class="form-control"
                                        placeholder="Nombre del producto">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Precio</label>
                                <div class="input-group">
                                    <input id="edit_precio" name="precio" type="number" step="0.01" class="form-control"
                                        placeholder="Precio del producto">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Stock</label>
                                <div class="input-group">
                                    <input id="edit_stock" name="stock" type="number" class="form-control"
                                        placeholder="Stock del producto">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Imagen</label>
                                <div class="input-group">
                                    <input id="edit_imagen" name="imagen" type="text" class="form-control"
                                        placeholder="URL de la imagen">
                                </div>
                            </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn bg-primary-custom text-white">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.btn-edit-product');
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nombre = this.getAttribute('data-nombre');
            const precio = this.getAttribute('data-precio');
            const stock = this.getAttribute('data-stock');
            const imagen = this.getAttribute('data-imagen');
            
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nombre').value = nombre;
            document.getElementById('edit_precio').value = precio;
            document.getElementById('edit_stock').value = stock;
            document.getElementById('edit_imagen').value = imagen;
            
            var editModal = new bootstrap.Modal(document.getElementById('editProductModal'));
            editModal.show();
        });
    });
});
</script>

</body>
</html>

