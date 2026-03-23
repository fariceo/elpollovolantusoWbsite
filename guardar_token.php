<?php
header('Content-Type: application/json; charset=UTF-8');

$host = "localhost";
$user = "root";
$pass = "clave";
$bd   = "volantuso";

$usuario   = $_POST['usuario'] ?? '';
$negocio   = $_POST['negocio'] ?? '';
$rol       = $_POST['rol'] ?? '';
$token_fcm = $_POST['token_fcm'] ?? '';

if ($usuario === '' || $negocio === '' || $rol === '' || $token_fcm === '') {
    echo json_encode([
        "success" => false,
        "msg" => "Faltan datos"
    ]);
    exit;
}

$conexion = new mysqli($host, $user, $pass, $bd);
if ($conexion->connect_error) {
    echo json_encode([
        "success" => false,
        "msg" => "Error de conexión: " . $conexion->connect_error
    ]);
    exit;
}

$sqlCrear = "CREATE TABLE IF NOT EXISTS tokens_fcm (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(100) NOT NULL,
    negocio VARCHAR(150) NOT NULL,
    rol VARCHAR(50) NOT NULL,
    token_fcm TEXT NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conexion->query($sqlCrear);

        $sql = "INSERT INTO tokens_fcm (usuario, negocio, rol, token_fcm)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE token_fcm = VALUES(token_fcm), fecha_registro = CURRENT_TIMESTAMP";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("ssss", $usuario, $negocio, $rol, $token_fcm);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "msg" => "Token guardado correctamente",
        "longitud_token" => strlen($token_fcm)
    ]);
} else {
    echo json_encode([
        "success" => false,
        "msg" => "Error al guardar token: " . $stmt->error
    ]);
}

$stmt->close();
$conexion->close();
?>