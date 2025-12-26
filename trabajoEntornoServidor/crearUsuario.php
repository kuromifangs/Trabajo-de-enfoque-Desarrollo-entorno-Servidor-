<?php
require_once './functions/configDb.php';

function registrarUsuario($nombre, $apellido, $email, $password) {
    $db = conectarDB();
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    $nombreCompleto=$nombre . " " . $apellido;
    $sql = "INSERT INTO usuarios (nombre, email, password) VALUES (:nombre, :email, :password)";
    $stmt = $db->prepare($sql);
    
    return $stmt->execute([
        ':nombre' => $nombreCompleto,
        ':email' => $email,
        ':password' => $passwordHash
    ]);
}

function comprobarEmail($email) {
    $db = conectarDB();
    $sql = "SELECT email FROM usuarios WHERE email = :email";
    $stmt = $db->prepare($sql);
    $stmt->execute([':email' => $email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
    

$error = '';
$exito = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmar = $_POST['confirmar_password'];
    
    if ($password !== $confirmar) {
        $error = 'Las contraseñas no coinciden';
    } elseif (!preg_match('/^(?=.{9,}$)(?=.*\d)(?=.*[^A-Za-z0-9]).+$/', $password)) {
        $error = 'La contraseña debe tener más de 8 caracteres, al menos 1 número y 1 símbolo';
    }elseif(isset(comprobarEmail($email)['email'])) {
            $error = 'El email ya está registrado.';
        }else {
        if (registrarUsuario($nombre, $apellido, $email, $password)) {
            $exito = true;
        }else {
            $error = 'Error al registrar el usuario. Inténtalo de nuevo.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Deportivas Davante - Crear Cuenta</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

    <style>
    body {
        font-family: "Plus Jakarta Sans", sans-serif;
        background-color: #f6f7f8;
        color: #101922;
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

    .form-control-lg-custom {
        height: 3rem;
        /* 48px */
        padding: 0.75rem 1rem;
    }

    .btn-toggle-password {
        background-color: transparent !important;
        border: none !important;
        color: #6c757d;
        z-index: 10;
    }

    .signup-container {
        max-width: 500px;
    }

    .input-group>.form-control {
        border-radius: 0.75rem !important;
        padding-right: 3rem;
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

        <main class="d-flex justify-content-center flex-grow-1 py-4 px-3 px-sm-5">
            <div class="container signup-container">
                <div class="row g-5">

                    <div class="col-12">

                        <h1 class="fw-bold display-6 mb-3">Crea Tu Cuenta</h1>
                        <p class="text-secondary mb-4">Únete a la comunidad de calzado deportivo.</p>


                        <?php if ($error): ?>
                        <div class="mensaje mensaje-error">
                            <?php echo $error; ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($exito): ?>
                        <div class="mensaje mensaje-exito">
                            Registro exitoso. <a href="login.php">Inicia sesión aquí</a>
                        </div>
                        <?php else: ?>

                        <form method="POST" action="">
                            <div class="row g-3 mb-3">
                                <div class="col-sm-6">
                                    <label for="firstName" class="form-label fw-semibold small text-secondary">Primer
                                        Nombre</label>
                                    <input type="text" name="nombre" id="firstName"
                                        class="form-control form-control-lg-custom rounded-xl"
                                        placeholder="Ingresa tu nombre"
                                        value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">

                                </div>
                                <div class="col-sm-6">
                                    <label for="lastName"
                                        class="form-label fw-semibold small text-secondary">Apellido</label>
                                    <input type="text" name="apellido" id="lastName"
                                        class="form-control form-control-lg-custom rounded-xl"
                                        placeholder="Ingresa tu apellido"
                                        value="<?php echo isset($_POST['apellido']) ? htmlspecialchars($_POST['apellido']) : ''; ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold small text-secondary">Correo
                                    electrónico</label>
                                <input type="email" name="email" id="email"
                                    class="form-control form-control-lg-custom rounded-xl"
                                    placeholder="tu_correo@ejemplo.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-baseline">
                                    <label for="password"
                                        class="form-label fw-semibold small text-secondary">Contraseña</label>
                                    <p class="text-muted small m-0" style="font-size: 0.75rem;">8+ caracteres, 1 número,
                                        1 símbolo</p>
                                </div>
                                <div class="input-group position-relative">
                                    <input type="password" name="password" id="password"
                                        class="form-control form-control-lg-custom rounded-xl"
                                        placeholder="Ingresa tu contraseña">
                                    <button class="btn btn-toggle-password position-absolute end-0 h-100" type="button"
                                        id="togglePassword">
                                        <span class="material-symbols-outlined small">visibility</span>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="confirmPassword"
                                    class="form-label fw-semibold small text-secondary">Confirmar Contraseña</label>
                                <div class="input-group position-relative">
                                    <input type="password" name="confirmar_password" id="confirmPassword"
                                        class="form-control form-control-lg-custom rounded-xl"
                                        placeholder="Confirma tu contraseña">
                                    <button class="btn btn-toggle-password position-absolute end-0 h-100" type="button"
                                        id="toggleConfirmPassword">
                                        <span class="material-symbols-outlined small">visibility_off</span>
                                    </button>
                                </div>
                            </div>

                            <div class="pt-2">
                                <button type="submit"
                                    class="btn bg-primary-custom text-white w-100 py-2 fw-bold rounded-xl shadow-sm"
                                    style="height: 3rem;">
                                    Crear Cuenta
                                </button>
                            </div>


                        </form>

                        <p class="text-center mt-4 text-secondary">
                            ¿Ya tienes cuenta?
                            <a href="login.php" class="text-primary-custom fw-semibold text-decoration-none">Inicia
                                Sesión</a>
                        </p>

                        <?php endif; ?>

                    </div>

                </div>
            </div>
        </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const togglePassword = document.getElementById('togglePassword');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');

        function setupToggle(button, input) {
            button.addEventListener('click', function(e) {
                e.preventDefault(); 
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);

                const icon = button.querySelector('.material-symbols-outlined');
                if (type === 'text') {
                    icon.textContent = 'visibility_off';
                } else {
                    icon.textContent = 'visibility';
                }
            });
        }

        setupToggle(togglePassword, passwordInput);
        setupToggle(toggleConfirmPassword, confirmPasswordInput);
    });
    </script>

</body>

</html>