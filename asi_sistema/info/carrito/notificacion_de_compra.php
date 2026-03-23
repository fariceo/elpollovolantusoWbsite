<?php
header("Content-Type: application/json; charset=UTF-8");

require_once "/var/www/elpollovolantuso/vendor/autoload.php";
use Google\Auth\Credentials\ServiceAccountCredentials;

/* ===============================
   1️⃣ Obtener Access Token FCM
=============================== */
function obtenerAccessToken() {
    $rutaCredenciales = "/var/www/elpollovolantuso/firebase/firebase-adminsdk.json";
    $scopes = ["https://www.googleapis.com/auth/firebase.messaging"];

    if (!file_exists($rutaCredenciales)) {
        return ["success" => false, "msg" => "No existe el archivo de credenciales"];
    }

    try {
        $credentials = new ServiceAccountCredentials($scopes, $rutaCredenciales);
        $token = $credentials->fetchAuthToken();
        if (empty($token['access_token'])) {
            return ["success" => false, "msg" => "No se pudo obtener access token"];
        }
        return ["success" => true, "access_token" => $token['access_token']];
    } catch (Exception $e) {
        return ["success" => false, "msg" => "Error al obtener access token", "detalle" => $e->getMessage()];
    }
}

/* ===============================
   2️⃣ Obtener tokens por rol
=============================== */
function obtenerTokensPorRol($roles = ["cocinero", "admin"]) {
    $conexion = new mysqli("localhost","root","clave","volantuso");
    if ($conexion->connect_error) {
        return ["success" => false, "msg" => "Error de conexión a MySQL", "detalle" => $conexion->connect_error];
    }

    $placeholders = implode(",", array_fill(0, count($roles), "?"));
    $tipos = str_repeat("s", count($roles));

    $sql = "SELECT id, usuario, negocio, rol, token_fcm 
            FROM tokens_fcm 
            WHERE rol IN ($placeholders)";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param($tipos, ...$roles);
    $stmt->execute();
    $result = $stmt->get_result();

    $tokens = [];
    while ($row = $result->fetch_assoc()) {
        $tokens[] = $row;
    }

    $stmt->close();
    $conexion->close();

    return ["success" => true, "tokens" => $tokens];
}

/* ===============================
   3️⃣ Enviar notificación FCM a un token
=============================== */
function enviarNotificacionFCM($tokenDestino, $titulo, $mensaje) {
    $tokenData = obtenerAccessToken();
    if (!$tokenData["success"]) return $tokenData;

    $accessToken = $tokenData["access_token"];
    $projectId = "com-elrancho-cocina";
    $url = "https://fcm.googleapis.com/v1/projects/$projectId/messages:send";

    $payload = [
        "message" => [
            "token" => $tokenDestino,
            "notification" => [
                "title" => $titulo,
                "body" => $mensaje
            ],
            "android" => [
                "priority" => "high",
                "notification" => ["sound" => "default"]
            ]
        ]
    ];

    $headers = [
        "Authorization: Bearer " . $accessToken,
        "Content-Type: application/json"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    return [
        "success" => ($httpCode === 200),
        "http_code" => $httpCode,
        "curl_error" => $curlError,
        "response_raw" => $response
    ];
}

/* ===============================
   4️⃣ Ejecutar notificación de compra
=============================== */
$tokensData = obtenerTokensPorRol(["cocinero", "admin"]);

if (!$tokensData["success"] || count($tokensData["tokens"]) === 0) {
    echo json_encode(["success" => false, "msg" => "No hay tokens disponibles para enviar"], JSON_PRETTY_PRINT);
    exit;
}

$respuestas = [];
foreach ($tokensData["tokens"] as $tokenRow) {
    $res = enviarNotificacionFCM(
        $tokenRow["token_fcm"],
        "Nuevo pedido confirmado",
        "¡Un usuario ha realizado un pedido en {$tokenRow['negocio']}!"
    );
    $respuestas[] = [
        "id" => $tokenRow["id"],
        "usuario" => $tokenRow["usuario"],
        "rol" => $tokenRow["rol"],
        "resultado_fcm" => $res
    ];
}

echo json_encode([
    "success" => true,
    "mensaje" => "Notificaciones enviadas a cocinero y admin",
    "respuestas" => $respuestas
], JSON_PRETTY_PRINT);
?>