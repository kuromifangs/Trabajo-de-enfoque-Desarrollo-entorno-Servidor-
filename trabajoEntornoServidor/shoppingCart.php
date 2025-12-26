<?php
include './functions/configDb.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = isset($_POST['action']) ? $_POST['action'] : null;
    
    
    if ($action === 'checkout') {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ./login.php');
            exit;
        }
        
        if (empty($_SESSION['cart'])) {
            header('Location: shoppingCart.php');
            exit;
        }
        
       
        $total = 0;
        foreach($_SESSION['cart'] as $item) {
            $total += ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
        }
        
       
        try {
            $db = conectarDB();
            
           
            try {
                $db->exec("ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS contenido TEXT");
            } catch (PDOException $e) {
                
            }
            
            $direccion = 'Dirección no especificada';
            
            $contenido_serializado = serialize($_SESSION['cart']);
            
            $sql = "INSERT INTO pedidos (usuario_id, fecha, total, direccion, estado, contenido) VALUES (:usuario_id, NOW(), :total, :direccion, 'pendiente', :contenido)";
            $stmt = $db->prepare($sql);
            $resultado = $stmt->execute([
                ':usuario_id' => $_SESSION['usuario_id'],
                ':total' => $total,
                ':direccion' => $direccion,
                ':contenido' => $contenido_serializado
            ]);
            
            if ($resultado) {
                $_SESSION['cart'] = [];
                header('Location: index.php?pedido=success');
                exit;
            } else {
                header('Location: shoppingCart.php?error=No se pudo crear el pedido');
                exit;
            }
        } catch (PDOException $e) {
            error_log("Error al crear pedido: " . $e->getMessage());
            header('Location: shoppingCart.php?error=' . urlencode($e->getMessage()));
            exit;
        }
    }
    
    $id = isset($_POST['id']) ? trim($_POST['id']) : null;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : null;
    $image = isset($_POST['image']) ? trim($_POST['image']) : null;

    if (!$action || !$id) {
        header('Location: shoppingCart.php');
        exit;
    }

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    switch ($action) {
        case 'remove':
            if (isset($_SESSION['cart'][$id])) {
                unset($_SESSION['cart'][$id]);
            }
            break;
        case 'update':
            if ($quantity === null) {
                header('Location: shoppingCart.php');
                exit;
            }
            $q = max(0, intval($quantity));
            if ($q <= 0) {
                if (isset($_SESSION['cart'][$id])) unset($_SESSION['cart'][$id]);
            } else {
                if (isset($_SESSION['cart'][$id])) {
                    $_SESSION['cart'][$id]['quantity'] = $q;
                    if (!empty($image) && empty($_SESSION['cart'][$id]['image'])) {
                        $_SESSION['cart'][$id]['image'] = $image;
                    }
                } else {
                    $_SESSION['cart'][$id] = [
                        'id' => $id,
                        'name' => $_POST['name'] ?? 'Artículo',
                        'price' => isset($_POST['price']) ? floatval(str_replace(',','.',str_replace('€','',$_POST['price']))) : 0,
                        'image' => $_POST['image'] ?? '',
                        'quantity' => $q
                    ];
                }
            }
            break;
        default:
            header('Location: shoppingCart.php');
            exit;
    }

    header('Location: shoppingCart.php');
    exit;
}

$product_images = [
    '1' => 'https://static.nike.com/a/images/t_web_pdp_535_v2/f_auto,u_126ab356-44d8-4a06-89b4-fcdcc8df0245,c_scale,fl_relative,w_1.0,h_1.0,fl_layer_apply/36fc9b85-c228-4cb3-a405-2b16a4c85ba0/WMNS+AIR+JORDAN+1+MID.png',
    '2' => 'https://assets.adidas.com/images/h_2000,f_auto,q_auto,fl_lossy,c_fill,g_auto/1a6c516af64c4832829354533392a713_9366/Zapatilla_Superstar_II_Negro_JI0079_01_standard.jpg',
    '3' => 'https://static.nike.com/a/images/t_web_pdp_535_v2/f_auto,u_126ab356-44d8-4a06-89b4-fcdcc8df0245,c_scale,fl_relative,w_1.0,h_1.0,fl_layer_apply/7d261285-ddff-4c3a-84ca-c547d34e96b2/JORDAN+MVP+92.png',
    '4' => 'https://nb.scene7.com/is/image/NB/m2002rda_nb_02_i?$dw_detail_main_lg$&bgc=f1f1f1&layer=1&bgcolor=f1f1f1&blendMode=mult&scale=10&wid=1600&hei=1600',
    '5' => 'https://assets.adidas.com/images/h_2000,f_auto,q_auto,fl_lossy,c_fill,g_auto/b0078eb5bec54a78966655762862e460_9366/ZAPATILLAS_ADISTAR_CONTROL_5_Gris_KI6154_01_00_standard.jpg',
    '6' => 'https://static.nike.com/a/images/t_web_pdp_535_v2/f_auto/bb176883-ffdc-4121-a872-691a43072330/W+AIR+FORCE+1+%2707+LO.png',
    '7' => 'https://nb.scene7.com/is/image/NB/u1906wns_nb_02_i?$dw_detail_main_lg$&bgc=f1f1f1&layer=1&bgcolor=f1f1f1&blendMode=mult&scale=10&wid=1600&hei=1600',
    '8' => 'https://assets.adidas.com/images/h_2000,f_auto,q_auto,fl_lossy,c_fill,g_auto/5ddc0cfaf4864206a5920bed708f858c_9366/Zapatilla_Grand_Court_TD_Lifestyle_Court_Casual_Blanco_ID3028_01_standard.jpg',
    '9' => 'https://static.nike.com/a/images/t_web_pdp_535_v2/f_auto/a2d3a48b-98c1-45d5-a58b-b6b203949f6f/W+NIKE+PACIFIC.png',
];
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - SoleMates</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    
    <style>
        :root {
            --primary-color: #137fec;
            --bg-light: #f6f7f8;
            --bg-dark: #101922;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: #1D1D1F;
            min-height: 100vh;
        }
        
        [data-bs-theme="dark"] body {
            background-color: var(--bg-dark);
            color: #e5e7eb;
        }
        
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #1170d4;
            border-color: #1170d4;
        }
        
        .text-primary-custom {
            color: var(--primary-color) !important;
        }
        
        .navbar-custom {
            background-color: var(--bg-light);
            border-bottom: 1px solid #e5e7eb;
            backdrop-filter: blur(8px);
        }
        
        [data-bs-theme="dark"] .navbar-custom {
            background-color: rgba(16, 25, 34, 0.8);
            border-bottom-color: #374151;
        }
        
        .nav-link:hover {
            color: var(--primary-color) !important;
        }
        
        .badge-cart {
            position: absolute;
            top: -4px;
            right: -4px;
            background-color: var(--primary-color);
            font-size: 0.75rem;
            width: 1rem;
            height: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-icon {
            background-color: #e5e7eb;
            border: none;
            border-radius: 0.5rem;
            height: 2.5rem;
            padding: 0 0.625rem;
        }
        
        .btn-icon:hover {
            background-color: #d1d5db;
        }
        
        [data-bs-theme="dark"] .btn-icon {
            background-color: rgba(55, 65, 81, 0.5);
        }
        
        [data-bs-theme="dark"] .btn-icon:hover {
            background-color: #374151;
        }
        
        .cart-item {
            background-color: white;
            border: 1px solid transparent;
            border-radius: 0.75rem;
            transition: all 0.3s;
        }
        
        .cart-item:hover {
            border-color: #e5e7eb;
        }
        
        [data-bs-theme="dark"] .cart-item {
            background-color: rgba(31, 41, 55, 0.5);
        }
        
        [data-bs-theme="dark"] .cart-item:hover {
            border-color: #374151;
        }
        
        .product-image {
            width: 96px;
            height: 96px;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            border-radius: 0.5rem;
            background-color: #f3f4f6;
        }
        
        [data-bs-theme="dark"] .product-image {
            background-color: #374151;
        }
        
        .btn-quantity {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            background-color: #e5e7eb;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
            font-weight: 500;
        }
        
        .btn-quantity:hover {
            background-color: #d1d5db;
        }
        
        [data-bs-theme="dark"] .btn-quantity {
            background-color: #374151;
        }
        
        [data-bs-theme="dark"] .btn-quantity:hover {
            background-color: #4b5563;
        }
        
        .quantity-input {
            width: 2rem;
            text-align: center;
            background: transparent;
            border: none;
            font-weight: 500;
        }
        
        .quantity-input:focus {
            outline: none;
            box-shadow: none;
        }
        
        .quantity-input::-webkit-inner-spin-button,
        .quantity-input::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
        .btn-delete {
            color: #6b7280;
            background: none;
            border: none;
            transition: color 0.3s;
        }
        
        .btn-delete:hover {
            color: #dc2626;
        }
        
        .order-summary {
            background-color: white;
            border-radius: 0.75rem;
        }
        
        [data-bs-theme="dark"] .order-summary {
            background-color: rgba(31, 41, 55, 0.5);
        }
        
        .text-muted-custom {
            color: #6b7280;
        }
        
        [data-bs-theme="dark"] .text-muted-custom {
            color: #9ca3af;
        }
        
        .profile-avatar {
            width: 2.5rem;
            height: 2.5rem;
            background-size: cover;
            background-position: center;
            border-radius: 50%;
        }
        
        @media (min-width: 992px) {
            .order-summary {
                position: sticky;
                top: 7rem;
            }
        }
    </style>
</head>
<body>
    <header>
       <nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top py-3">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
            <svg fill="none" viewBox="0 0 48 48" class="fs-2 text-primary dark:text-neutral-white" style="width: 2rem; height: 2rem;">
    <path d="M24 4C25.8 14.2 33.8 22.2 44 24C33.8 25.8 25.8 33.8 24 44C22.2 33.8 14.2 25.8 4 24C14.2 22.2 22.2 14.2 24 4Z" fill="currentColor"/>
</svg>

      <strong>Deportivas Davante</strong>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error al procesar el pedido:</strong> <?= htmlspecialchars($_GET['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

      <form class="d-none d-lg-flex ms-auto me-3">
        <div class="input-group">
          <span class="input-group-text bg-light"><span class="material-symbols-outlined">search</span></span>
          <input type="text" class="form-control" placeholder="Buscar zapatillas...">
        </div>
      </form>

      <div class="d-flex gap-2">
        <button class="btn btn-light border">
          <span class="material-symbols-outlined">person</span>
        </button>
        <a href="shoppingCart.php" class="btn btn-light position-relative border" id="cart-button" role="button">
          <span class="material-symbols-outlined">shopping_bag</span>
          <span id="cart-count" class="badge bg-secondary position-absolute top-0 end-0"><?php echo isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'],'quantity')) : 0; ?></span>
        </a>
      </div>
    </div>
  </div>
</nav>
    </header>

    <main class="container my-4 my-md-5">
        <div class="mb-4 px-3">
            <?php $count = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'],'quantity')) : 0; ?>
            <h1 class="fs-4 fw-bold">Mi carrito (<?php echo $count; ?> productos)</h1>
        </div>

        <div class="row g-4 g-lg-5">
            <div class="col-lg-8">
              

                <?php if(!empty($_SESSION['cart'])): ?>
                  <?php foreach($_SESSION['cart'] as $pid => $item): ?>
                    <div class="cart-item p-3 mb-3" data-id="<?php echo htmlspecialchars($pid); ?>">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-6">
                                <div class="d-flex gap-3">
                                    <div class="product-image" style="background-image: url('<?php echo htmlspecialchars($item['image'] ?? ($product_images[$pid] ?? 'https://via.placeholder.com/96')); ?>');"></div>
                                    <div class="flex-grow-1">
                                        <p class="mb-1 fw-bold"><?php echo htmlspecialchars($item['name']); ?></p>
                                        <p class="mb-0 text-muted-custom small">Cantidad: <span class="item-qty"><?php echo $item['quantity'] ?? 1; ?></span></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex justify-content-center">
                                <div class="d-flex align-items-center gap-2">
                                    <form method="POST" class="d-inline-block me-1">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($pid); ?>">
                                        <input type="hidden" name="quantity" value="<?php echo max(0, ($item['quantity'] ?? 1) - 1); ?>">
                                        <button type="submit" class="btn-quantity">-</button>
                                    </form>

                                    <form method="POST" class="d-inline-block me-1">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($pid); ?>">
                                        <input type="number" name="quantity" class="quantity-input" value="<?php echo $item['quantity'] ?? 1; ?>" min="1" style="width:3rem;" onchange="this.form.submit()">
                                    </form>

                                    <form method="POST" class="d-inline-block">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($pid); ?>">
                                        <input type="hidden" name="quantity" value="<?php echo ($item['quantity'] ?? 1) + 1; ?>">
                                        <button type="submit" class="btn-quantity">+</button>
                                    </form>
                                </div>
                            </div>
                            <div class="col-md-2 text-end">
                                <p class="mb-0 fw-bold"><?php echo number_format($item['price'] ?? 0,2,',','.'); ?>€</p>
                            </div>
                            <div class="col-md-2 text-end">
                                <form method="POST" class="d-inline-block">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($pid); ?>">
                                    <button type="submit" class="btn-delete">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                  <?php endforeach; ?>
                  <a href="index.php" class="d-inline-flex align-items-center gap-2 text-primary-custom text-decoration-none fw-medium mt-3">
                      <span class="material-symbols-outlined">arrow_back</span>
                      Continuar comprando
                  </a>
                <?php else: ?>
                  <p class="text-muted">Tu carrito está vacío.</p>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="order-summary p-4">
                    <h2 class="fs-4 fw-bold mb-4">Resumen del pedido</h2>
                    
                    

                    <hr class="my-4">

                    <?php $total = 0; foreach($_SESSION['cart'] ?? [] as $it){ $total += ($it['price'] ?? 0) * ($it['quantity'] ?? 0); } ?>
                    <div class="d-flex justify-content-between fs-5 fw-bold mb-4">
                        <span>Total</span>
                        <span><?php echo number_format($total,2,',','.'); ?>€</span>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="action" value="checkout">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" <?= empty($_SESSION['cart']) ? 'disabled' : '' ?>>
                            Finalizar pedido
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
