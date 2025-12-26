<?php

include './functions/configDb.php';
$error="";
function loginUsuario($email, $password) {
    $db = conectarDB();
    
    $sql = "SELECT * FROM usuarios WHERE email = :email";
    $stmt = $db->prepare($sql);
    $stmt->execute([':email' => $email]);
    
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario && password_verify($password, $usuario['password'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_rol'] = $usuario['rol'];
        return true;
    }
    
    return false;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    if (loginUsuario($email, $password)) {
        if($_SESSION['usuario_rol'] === 'admin') {
            header('Location: ./inventario.php');
        } else {
      header('Location: ./index.php');}
        exit;
    } else {
        $error = 'Email o contraseña incorrectos';
    }

}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Deportivas Davante - Bienvenido</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

    <style>
    body {
        font-family: "Plus Jakarta Sans", sans-serif;
        background-color: #f6f7f8;
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

    <div class="min-vh-100 d-flex flex-column">

        <header class="d-flex justify-content-between align-items-center px-4 py-3">
            <div class="d-flex align-items-center gap-2">
                <div class="text-primary-custom" style="width:28px;">
                    <svg fill="none" viewBox="0 0 48 48">
                        <path
                            d="M24 4C25.8 14.2 33.8 22.2 44 24C33.8 25.8 25.8 33.8 24 44C22.2 33.8 14.2 25.8 4 24C14.2 22.2 22.2 14.2 24 4Z"
                            fill="currentColor" />
                    </svg>
                </div>
                <h2 class="fw-bold m-0 text-dark">Deportivas Davante</h2>
            </div>
        </header>

        <main class="d-flex justify-content-center flex-grow-1 py-4">
            <div class="container" style="max-width: 1100px;">
                <div class="row align-items-center g-5">

                    <div class="col-lg-6 d-none d-lg-block">
                        <img src="https://static.nike.com/a/images/t_web_pdp_535_v2/f_auto,u_126ab356-44d8-4a06-89b4-fcdcc8df0245,c_scale,fl_relative,w_1.0,h_1.0,fl_layer_apply/36fc9b85-c228-4cb3-a405-2b16a4c85ba0/WMNS+AIR+JORDAN+1+MID.png"
                            class="img-fluid rounded-xl shadow">
                    </div>

                    <div class="col-lg-6">

                        <h1 class="fw-bold display-6 mb-3">Bienvenido</h1>
                        <p class="text-secondary mb-4">Inicia sesión para acceder a tu cuenta</p>

                        <?php if ($error): ?>
                        <div class="mensaje mensaje-error">
                            <?php echo $error; ?>
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Correo electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-secondary">
                                        <span class="material-symbols-outlined">mail</span>
                                    </span>
                                    <input id="email" name="email" type="email" class="form-control"
                                        placeholder="Correo electrónico">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-secondary">
                                        <span class="material-symbols-outlined">lock</span>
                                    </span>
                                    <input id="password" name="password" type="password" class="form-control"
                                        placeholder="Introduce tu contraseña">
                                </div>
                            </div>



                            <button type="submit" class="btn bg-primary-custom text-white w-100 py-2 fw-bold mb-3">
                                Entrar
                            </button>

                        </form>

                        <p class="text-center mt-3 text-secondary">
                            ¿No tienes cuenta?
                            <a href="crearUsuario.php"
                                class="text-primary-custom fw-semibold text-decoration-none">Crear cuenta</a>
                        </p>


                    </div>

                </div>
            </div>
        </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>