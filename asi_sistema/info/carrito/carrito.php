<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: http://35.223.94.102/index.php");
    exit();
}

include '../../../conexion.php';

$usuario = $_SESSION['usuario'];

// ===============================
// 1️⃣ Procesar confirmación de pedido y enviar notificación
// ===============================
if (isset($_POST['confirmar'])) {

    // Actualizar pedidos a confirmados
    $sql = "UPDATE pedidos SET estado = 1 WHERE usuario = ? AND estado = 0";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->close();

    // Obtener detalle de los productos confirmados
    $sql2 = "SELECT producto, cantidad FROM pedidos WHERE usuario = ? AND estado = 1";
    $stmt2 = $conexion->prepare($sql2);
    $stmt2->bind_param("s", $usuario);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    $productosPedido = [];
    while ($row = $result2->fetch_assoc()) {
        $productosPedido[] = $row['producto'] . " x" . $row['cantidad'];
    }
    $stmt2->close();

$detallePedido = "\n- " . implode("\n- ", $productosPedido);

    // Enviar notificación push
    require_once "notificacion_de_compra.php"; // ajusta ruta

    $sqlTokens = "SELECT token_fcm FROM tokens_fcm WHERE rol='cocinero'";
    $resultTokens = $conexion->query($sqlTokens);
    while ($row = $resultTokens->fetch_assoc()) {
        enviarNotificacionFCM(
            $row['token_fcm'],
            "Nuevo pedido confirmado",
            "Pedido de $usuario: $detallePedido"
        );
    }

    // Mensaje de confirmación para mostrar en la página
    $mensajeConfirmacion = "✅ Pedido confirmado correctamente.";
}

// ===============================
// 2️⃣ Cargar pedidos como antes
// ===============================
$sql = "SELECT id, producto, cantidad, precio, total, estado 
        FROM pedidos 
        WHERE usuario = ? AND estado != 2";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

$totalFinal = 0;
$hayPedidosPendientes = false;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Carrito de Compras</title>

<style>
body { 
    font-family: Arial, sans-serif; 
    padding: 20px; 
    background-color: #f8f8f8; 
}
.carrito { 
    max-width: 700px; 
    margin: auto; 
    background: white; 
    padding: 20px; 
    border-radius: 10px; 
}
.item {
    border-bottom: 1px solid #ccc;
    padding: 15px 0;
    transition: all 0.3s ease;
}
.item.oculto {
    opacity: 0;
    transform: translateX(-25px);
}
.btnEliminar {
    background: red;
    color: #fff;
    border: none;
    padding: 6px 10px;
    border-radius: 6px;
    cursor: pointer;
}
</style>
</head>

<body>
<div class="carrito">
    <h2>🛒 Mi Carrito</h2>

    <?php if(isset($mensajeConfirmacion)): ?>
        <p style="color:green;font-weight:bold;text-align:center;"><?php echo $mensajeConfirmacion; ?></p>
    <?php endif; ?>

<?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        echo "<div class='item' id='item-{$row['id']}'>";

        echo "<strong>{$row['producto']}</strong><br>";

        if ($row['estado'] == 0) {
            $hayPedidosPendientes = true;

            echo "Cantidad: 
            <input type='number' value='{$row['cantidad']}' min='1'
            onchange='actualizarCantidad({$row['id']}, this.value)'><br>";
        } else {
            echo "Cantidad: {$row['cantidad']}<br>";
        }

        echo "Precio: $<span id='precio-{$row['id']}' data-precio='{$row['precio']}'>"
            . number_format($row['precio'], 2) . 
            "</span><br>";

        echo "Total: $<strong id='total-{$row['id']}' data-total='{$row['total']}'>"
            . number_format($row['total'], 2) . 
            "</strong><br>";

        if ($row['estado'] == 0) {
            echo "<button class='btnEliminar' onclick='eliminarProducto({$row['id']})'>🗑️ Eliminar</button>";
        }

        echo "</div>";

        $totalFinal += $row['total'];
    }

    echo "<div class='total-final' 
          style='font-size:18px;font-weight:bold;text-align:right;margin-top:20px;'>
          Total del carrito: $<span id='totalFinal'>".number_format($totalFinal, 2)."</span></div>";

} else {
    echo "<p>Tu carrito está vacío.</p>";
}

$stmt->close();
$conexion->close();
?>

<?php if ($totalFinal > 0 && $hayPedidosPendientes): ?>
    <div style="text-align:center;margin-top:30px;">
        <form method="post">
            <button type="submit" name="confirmar" 
            style="padding:10px 20px;font-size:16px;">✅ Confirmar Compra</button>
        </form>
    </div>
<?php endif; ?>

<a href="../../../index.php" 
style="display:block;text-align:center;margin-top:25px;text-decoration:none;color:#333;">
← Volver al menú</a>

</div>

<script>

function actualizarCantidad(id, cantidad) {

    fetch("actualizar_cantidad.php", {
        method: "POST",
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: "id=" + id + "&cantidad=" + cantidad
    })
    .then(res => res.text())
    .then(r => {
        console.log(r);

        if (r.includes("OK")) {
            let precio = parseFloat(document.getElementById("precio-" + id).dataset.precio);
            let nuevoTotal = precio * cantidad;

            document.getElementById("total-" + id).innerText = nuevoTotal.toFixed(2);
            document.getElementById("total-" + id).dataset.total = nuevoTotal;

            recalcularTotal();
        }
    });
}

function eliminarProducto(id) {

    if (!confirm("¿Seguro que deseas eliminar este producto?")) return;

    fetch("eliminar_producto.php", {
        method: "POST",
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: "id=" + id
    })
    .then(res => res.text())
    .then(r => {

        console.log(r);

        if (r.startsWith("OK")) {

            let partes = r.split("|");
            let nuevoTotal = partes[1];

            // Animación
            let item = document.getElementById("item-" + id);
            item.classList.add("oculto");

            setTimeout(() => {
                item.remove();
                recalcularTotal();
            }, 300);

            // 🔥 Actualizar contador del carrito
            let contador = parent.document.getElementById("n_productos");
            if (contador) contador.textContent = nuevoTotal;
        }
    });
}



function recalcularTotal() {
    let totales = document.querySelectorAll("[data-total]");
    let suma = 0;

    totales.forEach(t => {
        suma += parseFloat(t.dataset.total);
    });

    document.getElementById("totalFinal").innerText = suma.toFixed(2);
}

</script>

</body>
</html>