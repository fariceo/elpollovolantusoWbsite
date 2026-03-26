<?php
include("../conexion.php");
header('Content-Type: application/json; charset=UTF-8');

function responder($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$accion = $_POST['accion'] ?? '';

if ($accion === "truncate") {
    mysqli_query($conexion, "DELETE FROM pedidos");
    responder(["success" => true]);
}

if ($accion === "insertar") {
    $usuario = $_POST['usuario'];
    $producto = $_POST['producto'];
    $cantidad = $_POST['cantidad'];
    $precio = $_POST['precio'];
    $total = $_POST['total'];
    $fecha = date('Y-m-d');

    $sql = "INSERT INTO pedidos (usuario, producto, cantidad, precio, total, fecha, estado, delivery, metodo_pago)
            VALUES ('$usuario', '$producto', '$cantidad', '$precio', '$total', '$fecha', '0', 0, 'efectivo')";
    if (mysqli_query($conexion, $sql)) {
        responder(["success" => true]);
    } else {
        responder(["success" => false, "message" => mysqli_error($conexion)]);
    }
}

if ($accion === "registrar_venta_y_listo") {
    $usuario = $_POST['usuario'];
    $fecha = $_POST['fecha'];

    // Marcar pedidos como estado 1
    mysqli_query($conexion, "UPDATE pedidos SET estado='1' WHERE usuario='$usuario' AND fecha='$fecha'");
    responder(["success" => true]);
}

if ($accion === "editar_cantidad") {
    $id = $_POST['id_pedido'];
    $nueva = $_POST['nueva_cantidad'];

    $sql = "SELECT precio, producto FROM pedidos WHERE id='$id'";
    $res = mysqli_query($conexion, $sql);
    $row = mysqli_fetch_assoc($res);

    $total = $row['precio'] * $nueva;
    mysqli_query($conexion, "UPDATE pedidos SET cantidad='$nueva', total='$total' WHERE id='$id'");
    responder([
        "success" => true,
        "producto" => $row['producto'],
        "cantidad" => $nueva,
        "precio" => $row['precio'],
        "total" => $total,
        "subtotal" => $total
    ]);
}

if ($accion === "eliminar_producto") {
    $id = $_POST['id_pedido'];
    mysqli_query($conexion, "DELETE FROM pedidos WHERE id='$id'");
    responder(["success" => true]);
}
?>