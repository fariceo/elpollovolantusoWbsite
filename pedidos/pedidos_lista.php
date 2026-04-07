<?php
include("../conexion.php");
?>

<div class="orders-section">
    <?php
    $musuario = mysqli_query($conexion, "SELECT DISTINCT usuario FROM pedidos WHERE estado!='2' AND estado!='10' AND estado!='100' ORDER BY id DESC");
    $index_acordeon = 0;

    while ($usuario = mysqli_fetch_array($musuario)) {

        $usuario_nombre = $usuario['usuario'];

        $consulta_fecha = mysqli_query($conexion, "
            SELECT fecha, hora, delivery, metodo_pago
            FROM pedidos
            WHERE estado!='2' AND estado!='10' AND estado!='100' AND usuario='$usuario_nombre'
            ORDER BY id DESC
            LIMIT 1
        ");
        $datos_fecha = mysqli_fetch_array($consulta_fecha);
        $fecha_pedido = $datos_fecha['fecha'] ?? '';
        $hora_pedido = $datos_fecha['hora'] ?? '';
        $delivery_actual = $datos_fecha['delivery'] ?? 'default';
        $metodo_pago_actual = $datos_fecha['metodo_pago'] ?? 'default';

        $buscar_compra = mysqli_query($conexion, "
            SELECT id, producto, precio, cantidad, total
            FROM pedidos
            WHERE estado!='2' AND estado!='10' AND estado!='100' AND usuario='$usuario_nombre'
            ORDER BY id ASC
        ");

        $productos = [];
        $subtotal_usuario = 0;
        $cantidad_total_productos = 0;

        while ($compra = mysqli_fetch_array($buscar_compra)) {
            $productos[] = $compra;
            $subtotal_usuario += floatval($compra['total']);
            $cantidad_total_productos += floatval($compra['cantidad']);
        }

        $extra_delivery = 0;
        $concepto_bandejas = 0;
        $total_usuario = $subtotal_usuario;

        if ($delivery_actual == "delivery") {
            $extra_delivery = 2;
            $concepto_bandejas = 0.25 * $cantidad_total_productos;
            $total_usuario = $subtotal_usuario + $extra_delivery + $concepto_bandejas;
        } elseif ($delivery_actual == "recoger en tienda") {
            $extra_delivery = 0;
            $concepto_bandejas = 0.25 * $cantidad_total_productos;
            $total_usuario = $subtotal_usuario + $concepto_bandejas;
        } elseif ($delivery_actual == "consumo en tienda") {
            $extra_delivery = 0;
            $concepto_bandejas = 0;
            $total_usuario = $subtotal_usuario;
        }

        $accordion_id = "order_content_" . md5($usuario_nombre);
    ?>

        <div class="order-card accordion-card">
            <div class="accordion-header" onclick="toggleAccordion('<?php echo $accordion_id; ?>', this)">
                <div class="accordion-header-left">
                    <h3 class="order-user-title"><?php echo $usuario_nombre; ?></h3>
                    <div class="accordion-mini-info">
                        Fecha: <?php echo $fecha_pedido; ?> | Hora: <?php echo $hora_pedido; ?> |
                        Total: <?php echo "$ " . number_format($total_usuario, 2); ?>
                    </div>
                </div>
                <div class="accordion-icon">+</div>
            </div>

            <div class="accordion-content" id="<?php echo $accordion_id; ?>" style="display: none;">

                <table class="order-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Total</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $compra): ?>
                            <tr>
                                <td><?php echo $compra['id'] ?></td>
                                <td><?php echo $compra['producto'] ?></td>
                                <td>
                                    <input
                                        type="number"
                                        min="1"
                                        step="1"
                                        value="<?php echo $compra['cantidad'] ?>"
                                        onchange="actualizar_cantidad_input('<?php echo $compra['id']; ?>','<?php echo $compra['precio']; ?>', this)"
                                        style="width: 70px; padding: 6px; text-align:center; border:1px solid #d1d5db; border-radius:8px;">
                                </td>
                                <td><?php echo "$ " . number_format($compra['precio'], 2) ?></td>
                                <td><?php echo "$ " . number_format($compra['total'], 2) ?></td>
                                <td>
                                    <button class="btn-delete-product"
                                        onclick="eliminar_producto('<?php echo addslashes($usuario_nombre); ?>','<?php echo addslashes($compra['producto']); ?>')">
                                        -
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="delivery-box">
                    <h4>Tipo de entrega</h4>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="delivery_<?php echo md5($usuario_nombre); ?>" value="delivery"
                                <?php echo ($delivery_actual == "delivery") ? "checked" : ""; ?>
                                onchange="cambiar_delivery('<?php echo addslashes($usuario_nombre); ?>','delivery')">
                            Delivery
                        </label>

                        <label>
                            <input type="radio" name="delivery_<?php echo md5($usuario_nombre); ?>" value="consumo en tienda"
                                <?php echo ($delivery_actual == "consumo en tienda") ? "checked" : ""; ?>
                                onchange="cambiar_delivery('<?php echo addslashes($usuario_nombre); ?>','consumo en tienda')">
                            Consumo en tienda
                        </label>

                        <label>
                            <input type="radio" name="delivery_<?php echo md5($usuario_nombre); ?>" value="recoger en tienda"
                                <?php echo ($delivery_actual == "recoger en tienda") ? "checked" : ""; ?>
                                onchange="cambiar_delivery('<?php echo addslashes($usuario_nombre); ?>','recoger en tienda')">
                            Recoger en tienda
                        </label>
                    </div>

                    <div class="delivery-cost-note">
                        Delivery suma $2.00 + $0.25 por producto. Recoger en tienda suma $0.25 por producto. Consumo en tienda no suma recargo.
                    </div>
                </div>

                <div class="payment-box">
                    <h4>Método de pago</h4>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="metodo_pago_<?php echo md5($usuario_nombre); ?>" value="efectivo"
                                <?php echo ($metodo_pago_actual == "efectivo") ? "checked" : ""; ?>
                                onchange="cambiar_metodo_pago('<?php echo addslashes($usuario_nombre); ?>','efectivo','<?php echo $total_usuario; ?>','<?php echo $fecha_pedido; ?>')">
                            Efectivo
                        </label>

                        <label>
                            <input type="radio" name="metodo_pago_<?php echo md5($usuario_nombre); ?>" value="fiado"
                                <?php echo ($metodo_pago_actual == "fiado") ? "checked" : ""; ?>
                                onchange="cambiar_metodo_pago('<?php echo addslashes($usuario_nombre); ?>','fiado','<?php echo $total_usuario; ?>','<?php echo $fecha_pedido; ?>')">
                            Fiado
                        </label>

                        <label>
                            <input type="radio" name="metodo_pago_<?php echo md5($usuario_nombre); ?>" value="transferencia"
                                <?php echo ($metodo_pago_actual == "transferencia") ? "checked" : ""; ?>
                                onchange="cambiar_metodo_pago('<?php echo addslashes($usuario_nombre); ?>','transferencia','<?php echo $total_usuario; ?>','<?php echo $fecha_pedido; ?>')">
                            Transferencia
                        </label>
                    </div>
                </div>

                <div class="summary-box">
                    <p><strong>Subtotal productos:</strong> <?php echo "$ " . number_format($subtotal_usuario, 2); ?></p>
                    <p><strong>Cantidad total de productos:</strong> <?php echo number_format($cantidad_total_productos, 2); ?></p>
                    <p><strong>Recargo delivery:</strong> <?php echo "$ " . number_format($extra_delivery, 2); ?></p>
                    <p><strong>Concepto bandejas:</strong> <?php echo "$ " . number_format($concepto_bandejas, 2); ?></p>
                    <p><strong>Total general:</strong> <?php echo "$ " . number_format($total_usuario, 2); ?></p>
                    <p><strong>Entrega actual:</strong> <?php echo $delivery_actual == "default" ? "No seleccionada" : $delivery_actual; ?></p>
                    <p><strong>Método de pago actual:</strong> <?php echo $metodo_pago_actual == "default" ? "No seleccionado" : $metodo_pago_actual; ?></p>
                </div>

                <div class="order-meta">
                    <div class="order-date" onclick="cambiar_fecha('<?php echo addslashes($usuario_nombre); ?>','<?php echo $fecha_pedido; ?>')">
                        Fecha: <?php echo $fecha_pedido; ?> | Hora: <?php echo $hora_pedido; ?>
                    </div>

                    <div class="order-ready">
                        <label class="ready-label">
                            <input type="checkbox" onchange="listo('<?php echo addslashes($usuario_nombre); ?>','<?php echo $total_usuario; ?>')">
                            <span>Pedido listo / Registrar venta</span>
                        </label>
                    </div>
                </div>

            </div>
        </div>

    <?php
        $index_acordeon++;
    }
    ?>
</div>

<script>
function toggleAccordion(id, element) {
    const contenidoActual = document.getElementById(id);
    const todos = document.querySelectorAll('.accordion-content');
    const iconos = document.querySelectorAll('.accordion-icon');
    const headers = document.querySelectorAll('.accordion-header');

    todos.forEach(function(item) {
        if (item.id !== id) {
            item.style.display = 'none';
        }
    });

    iconos.forEach(function(icon) {
        icon.textContent = '+';
    });

    headers.forEach(function(header) {
        header.classList.remove('active');
    });

    if (contenidoActual.style.display === 'block') {
        contenidoActual.style.display = 'none';
        element.classList.remove('active');
        element.querySelector('.accordion-icon').textContent = '+';
    } else {
        contenidoActual.style.display = 'block';
        element.classList.add('active');
        element.querySelector('.accordion-icon').textContent = '−';

        setTimeout(function() {
            const offset = 10;
            const top = element.getBoundingClientRect().top + window.pageYOffset - offset;

            window.scrollTo({
                top: top,
                behavior: 'smooth'
            });
        }, 50);
    }
}
</script>

<style>
.orders-section {
    max-width: 1200px;
    margin: 0 auto;
}

.order-card {
    background: #ffffff;
    border-radius: 18px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.07);
    padding: 20px;
    margin-bottom: 22px;
    overflow-x: auto;
}

.order-user-title {
    text-align: center;
    font-size: 22px;
    color: #2563eb;
    margin-bottom: 15px;
    font-weight: bold;
}

.order-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 16px;
    padding: 12px 14px;
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
}

.order-date {
    font-size: 14px;
    color: #374151;
    font-weight: 600;
    cursor: pointer;
    padding: 8px 12px;
    background: #ffffff;
    border: 1px dashed #cbd5e1;
    border-radius: 10px;
    transition: all 0.2s ease;
}

.order-date:hover {
    background: #eff6ff;
    border-color: #2563eb;
    color: #1d4ed8;
}

.order-ready {
    display: flex;
    align-items: center;
}

.ready-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #111827;
    font-weight: 600;
    cursor: pointer;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    padding: 10px 14px;
    border-radius: 10px;
}

.ready-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.btn-delete-product,
.btn-edit-product {
    color: #ffffff;
    border: none;
    padding: 8px 12px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 13px;
    font-weight: bold;
    transition: opacity 0.2s ease, transform 0.2s ease;
    margin: 2px;
}

.btn-delete-product {
    background: #ef4444;
}

.btn-edit-product {
    background: #2563eb;
}

.btn-delete-product:hover,
.btn-edit-product:hover {
    opacity: 0.92;
    transform: translateY(-1px);
}

.order-table {
    width: 100%;
    border-collapse: collapse;
    overflow: hidden;
    border-radius: 12px;
}

.order-table thead {
    background: #eff6ff;
}

.order-table th {
    padding: 14px 10px;
    font-size: 14px;
    color: #1e3a8a;
    border-bottom: 1px solid #dbeafe;
    text-align: center;
}

.order-table td {
    padding: 12px 10px;
    border-bottom: 1px solid #eef2f7;
    text-align: center;
    font-size: 14px;
}

.order-table tbody tr:hover {
    background: #f9fbff;
}

.delivery-box,
.payment-box {
    margin-top: 18px;
    padding: 14px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: #f8fafc;
}

.delivery-box h4,
.payment-box h4 {
    margin-bottom: 10px;
    color: #111827;
    font-size: 15px;
}

.radio-group {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
}

.radio-group label {
    display: flex;
    align-items: center;
    gap: 7px;
    cursor: pointer;
    font-size: 14px;
}

.delivery-cost-note {
    margin-top: 10px;
    color: #6b7280;
    font-size: 13px;
}

.summary-box {
    margin-top: 16px;
    padding: 14px;
    border-radius: 12px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
}

.summary-box p {
    margin: 4px 0;
    font-size: 14px;
}

.accordion-card {
    margin-bottom: 16px;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
}

.accordion-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 18px;
    cursor: pointer;
    background: #f9fafb;
    transition: all 0.2s ease;
}

.accordion-header:hover {
    background: #f3f4f6;
}

.accordion-header.active {
    background: #eef2ff;
}

.accordion-header-left {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.accordion-mini-info {
    font-size: 13px;
    color: #6b7280;
}

.accordion-icon {
    font-size: 24px;
    font-weight: bold;
    color: #374151;
    min-width: 30px;
    text-align: center;
}

.accordion-content {
    padding: 16px;
    border-top: 1px solid #e5e7eb;
}

@media (max-width: 768px) {
    .order-meta {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>