<?php //prueba webhook
include './functions/configDb.php';

function obtenerProductos() {
    $db = conectarDB();
    $sql = "SELECT * FROM productos ORDER BY id ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$productos = obtenerProductos();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $id = isset($_POST['id']) ? trim($_POST['id']) : null;
    $name = isset($_POST['name']) ? trim($_POST['name']) : null;
    $price = isset($_POST['price']) ? floatval(str_replace(',','.',str_replace('€','',$_POST['price']))) : null;
    $image = isset($_POST['image']) ? trim($_POST['image']) : null;
    $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

    if (!$id || !$name || $price === null) {
        header('Location: index.php');
        exit;
    }

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['quantity'] += $quantity;
        if (!empty($image) && empty($_SESSION['cart'][$id]['image'])) {
            $_SESSION['cart'][$id]['image'] = $image;
        }
    } else {
        $_SESSION['cart'][$id] = [
            'id' => $id,
            'name' => $name,
            'price' => $price,
            'image' => $image,
            'quantity' => $quantity
        ];
    }

    header('Location: index.php');
    exit;
} 
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>SoleSearch - Sneaker Catalog (Bootstrap)</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

<style>
    :root {
        --primary: #1A1A1A;
        --secondary: #FF4500;
        --background-light: #F5F5F7;
        --background-dark: #101922;
        --neutral-white: #FFFFFF;
        --neutral-border: #E5E5E5;
    }
    body {
        font-family: 'Inter', sans-serif;
        background-color: var(--background-light);
        color: var(--primary);
    }
    .dark body {
        background-color: var(--background-dark);
        color: white;
    }
    .product-card:hover img {
        transform: scale(1.05);
    }
    .product-card img {
        transition: transform .3s ease;
    }
    .add-btn {
        opacity: 0;
        transition: opacity .3s ease;
    }
    .product-card:hover .add-btn {
        opacity: 1;
    }
</style>
</head>

<body>

<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top py-3">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="#">
            <svg fill="none" viewBox="0 0 48 48" class="fs-2 text-primary dark:text-neutral-white" style="width: 2rem; height: 2rem;">
    <path d="M24 4C25.8 14.2 33.8 22.2 44 24C33.8 25.8 25.8 33.8 24 44C22.2 33.8 14.2 25.8 4 24C14.2 22.2 22.2 14.2 24 4Z" fill="currentColor"/>
</svg>

      <strong>Deportivas Davante</strong>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>

      <form class="d-none d-lg-flex ms-auto me-3">
        <div class="input-group">
          <span class="input-group-text bg-light"><span class="material-symbols-outlined">search</span></span>
          <input type="text" class="form-control" placeholder="Buscar zapatillas...">
        </div>
      </form>

      <div class="d-flex gap-2">
          <?php
          if(!isset($_SESSION['usuario_id'])) {
            ?>
           <a href="login.php"><span class="material-symbols-outlined btn btn-light border">person</span></a>
          <?php }
          ?>
        <a href="shoppingCart.php" class="btn btn-light position-relative border" role="button">
          <span class="material-symbols-outlined">shopping_bag</span>
          <span id="cart-count" class="badge bg-secondary position-absolute top-0 end-0"><?php echo isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'],'quantity')) : 0; ?></span>
        </a>
      </div>
    </div>
  </div>
</nav>

<main class="container py-5">
  <div class="row g-4">

   

   <section class="col-12">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 class="fw-bold">Todas nuestras zapatillas</h2>
      <p class="text-muted"><?= count($productos) ?> productos</p>
    </div>
  </div>

  <div class="row g-4">

    <?php foreach ($productos as $producto): 
        $precioFormateado = number_format($producto['precio'], 2, ',', '.');
    ?>
    <div class="col-sm-6 col-xl-4">
      <div class="card product-card border-0 shadow-sm position-relative">
        <img src="<?= htmlspecialchars($producto['imagen']) ?>" class="card-img-top" style="height:250px; object-fit:cover;" alt="<?= htmlspecialchars($producto['nombre']) ?>">
        <div class="card-body d-flex flex-column">
          <h5 class="card-title fw-bold"><?= htmlspecialchars($producto['nombre']) ?></h5>
          <p class="text-muted">Stock: <?= $producto['stock'] ?></p>
          <p class="mt-auto fw-bold"><?= $precioFormateado ?>€</p>
        </div>
        <form method="POST" class="position-absolute bottom-0 end-0 m-3">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="id" value="<?= $producto['id'] ?>">
          <input type="hidden" name="name" value="<?= htmlspecialchars($producto['nombre']) ?>">
          <input type="hidden" name="price" value="<?= $precioFormateado ?>€">
          <input type="hidden" name="image" value="<?= htmlspecialchars($producto['imagen']) ?>">
          <button type="submit" class="btn btn-dark rounded-circle add-btn" aria-label="Añadir al carrito">
            <span class="material-symbols-outlined">add_shopping_cart</span>
          </button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>

  </div>
</section>

