<?php
require_once 'configDb.php';

function obtenerUsuarios() {
    $db = conectarDB();
    $sql = "SELECT id, nombre, email, rol FROM usuarios ORDER BY id DESC";
    $stmt = $db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function obtenerUsuarioPorId($id) {
    $db = conectarDB();
    $sql = "SELECT id, nombre, email, rol FROM usuarios WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function actualizarUsuario($id, $nombre, $email, $rol, $password = null) {
    $db = conectarDB();
    
    if ($password) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET nombre = :nombre, email = :email, rol = :rol, password = :password WHERE id = :id";
        $params = [
            ':id' => $id,
            ':nombre' => $nombre,
            ':email' => $email,
            ':rol' => $rol,
            ':password' => $passwordHash
        ];
    } else {
        $sql = "UPDATE usuarios SET nombre = :nombre, email = :email, rol = :rol WHERE id = :id";
        $params = [
            ':id' => $id,
            ':nombre' => $nombre,
            ':email' => $email,
            ':rol' => $rol
        ];
    }
    
    $stmt = $db->prepare($sql);
    return $stmt->execute($params);
}

function eliminarUsuario($id) {
    $db = conectarDB();
    $sql = "DELETE FROM usuarios WHERE id = :id";
    $stmt = $db->prepare($sql);
    return $stmt->execute([':id' => $id]);
}

function emailExiste($email, $idExcluir = null) {
    $db = conectarDB();
    if ($idExcluir) {
        $sql = "SELECT id FROM usuarios WHERE email = :email AND id != :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':email' => $email, ':id' => $idExcluir]);
    } else {
        $sql = "SELECT id FROM usuarios WHERE email = :email";
        $stmt = $db->prepare($sql);
        $stmt->execute([':email' => $email]);
    }
    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}
?>
