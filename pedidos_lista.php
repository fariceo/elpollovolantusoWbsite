<?php
include("../conexion.php");
header('Content-Type: text/html; charset=UTF-8');
mysqli_set_charset($conexion, "utf8");

$sqlUsuarios = "SELECT usuario, fecha, MAX(delivery) AS delivery, MAX(metodo_pago) AS metodo_pago
                FROM pedidos
                WHERE estado='0'
                GROUP BY usuario, fecha
                ORDER BY fecha DESC, usuario ASC";

$resultUsuarios = mysqli_query($conexion, $sqlUsuarios);

if (!$resultUsuarios) {
    echo '<div class="error-box">Error en consulta principal: ' . mysqli_error($conexion) . '</div>';
    exit;
}

$grupoId = 0;

while ($usuarioRow = mysqli_fetch_assoc($resultUsuarios)) {
    $grupoId++;

    $usuario = $usuarioRow['usuario'];
    $fecha = $usuarioRow['fecha'];
    $delivery_actual = trim(strtolower($usuarioRow['delivery'] ?? 'consumo en tienda'));
    $metodo_pago_actual = trim(strtolower($usuarioRow['metodo_pago'] ?? 'efectivo'));

    $usuario_sql = mysqli_real_escape_string($conexion, $usuario);
    $fecha_sql = mysqli_real_escape_string($conexion, $fecha);

    $usuario_html = htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8');
    $fecha_html = htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8');

    // CONSULTAR PRODUCTOS DEL GRUPO
    $sqlProductos = "SELECT * FROM pedidos
                     WHERE usuario='$usuario_sql'
                     AND fecha='$fecha_sql'
                     AND estado='0'
                     ORDER BY id DESC";

    $resProductos = mysqli_query($conexion, $sqlProductos);

    $subtotal_usuario = 0;
    $cantidad_total_productos = 0;

    ob_start();

    if ($resProductos && mysqli_num_rows($resProductos) > 0) {
        while ($prod = mysqli_fetch_assoc($resProductos)) {
            $id = intval($prod['id']);
            $producto = htmlspecialchars($prod['producto'], ENT_QUOTES, 'UTF-8');
            $cantidad = floatval($prod['cantidad']);
            $precio = floatval($prod['precio']);
            $total = floatval($prod['total']);

            $subtotal_usuario += $total;
            $cantidad_total_productos += $cantidad;

            echo '<tr id="fila_' . $id . '">
                    <td>' . $producto . '</td>
                    <td>' . number_format($cantidad, 2) . '</td>
                    <td>$ ' . number_format($precio, 2) . '</td>
                    <td>$ ' . number_format($total, 2) . '</td>
                    <td>
                        <button class="btn-edit-product" onclick="editar_cantidad(' . $id . ', ' . $precio . ', ' . $grupoId . ')">Editar</button>
                        <button class="btn-delete-product" onclick="eliminar_producto(' . $id . ', ' . $grupoId . ')">Eliminar</button>
                    </td>
                  </tr>';
        }
    } else {
        echo '<tr><td colspan="5">No hay productos en este pedido</td></tr>';
    }

    $tablaProductos = ob_get_clean();

    // CÁLCULO DE RECARGOS
    $extra_delivery = 0;
    $concepto_bandejas = 0;

    if ($delivery_actual == "delivery") {
        $extra_delivery = 2.00;
        $concepto_bandejas = $cantidad_total_productos * 0.25;
    } elseif ($delivery_actual == "recoger en tienda") {
        $extra_delivery = 0;
        $concepto_bandejas = $cantidad_total_productos * 0.25;
    } elseif ($delivery_actual == "consumo en tienda") {
        $extra_delivery = 0;
        $concepto_bandejas = 0;
    }

    $total_usuario = $subtotal_usuario + $extra_delivery + $concepto_bandejas;

    $usuario_js = json_encode($usuario);
    $fecha_js = json_encode($fecha);
    $delivery_js = json_encode($delivery_actual);
    $metodo_pago_js = json_encode($metodo_pago_actual);

    echo '
    <div class="accordion-card" id="grupo_' . $grupoId . '">
        <div class="accordion-header" onclick="toggleAccordion(\'contenido_' . $grupoId . '\', this)">
            <div class="accordion-header-left">
                <strong>' . $usuario_html . '</strong>
                <span class="accordion-mini-info" id="miniinfo_' . $grupoId . '">
                    Fecha: <span id="fecha_' . $grupoId . '">' . $fecha_html . '</span> | 
                    Total: $ <span id="total_' . $grupoId . '">' . number_format($total_usuario, 2) . '</span>
                </span>
            </div>
            <span class="accordion-icon">+</span>
        </div>

        <div class="accordion-content" id="contenido_' . $grupoId . '">

            <table class="order-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    ' . $tablaProductos . '
                </tbody>
            </table>

            <div class="delivery-box">
                <h4>Tipo de entrega</h4>
                <div class="radio-group">
                    <label>
                        <input type="radio" name="delivery_' . md5($usuario . $fecha) . '" value="delivery"
                            ' . ($delivery_actual == "delivery" ? "checked" : "") . '
                            onchange="cambiar_delivery(' . $usuario_js . ', ' . $fecha_js . ', ' . $metodo_pago_js . ', \'delivery\', ' . $grupoId . ')">
                        Delivery
                    </label>

                    <label>
                        <input type="radio" name="delivery_' . md5($usuario . $fecha) . '" value="consumo en tienda"
                            ' . ($delivery_actual == "consumo en tienda" ? "checked" : "") . '
                            onchange="cambiar_delivery(' . $usuario_js . ', ' . $fecha_js . ', ' . $metodo_pago_js . ', \'consumo en tienda\', ' . $grupoId . ')">
                        Consumo en tienda
                    </label>

                    <label>
                        <input type="radio" name="delivery_' . md5($usuario . $fecha) . '" value="recoger en tienda"
                            ' . ($delivery_actual == "recoger en tienda" ? "checked" : "") . '
                            onchange="cambiar_delivery(' . $usuario_js . ', ' . $fecha_js . ', ' . $metodo_pago_js . ', \'recoger en tienda\', ' . $grupoId . ')">
                        Recoger en tienda
                    </label>
                </div>

                <div class="delivery-cost-note" style="margin-top:10px; color:#6b7280; font-size:13px;">
                    Delivery suma $2.00 + $0.25 por producto. Recoger en tienda suma $0.25 por producto. Consumo en tienda no suma recargo.
                </div>
            </div>

            <div class="payment-box">
                <h4>Método de pago</h4>
                <div class="radio-group">
                    <label>
                        <input type="radio" name="metodo_pago_' . md5($usuario . $fecha) . '" value="efectivo"
                            ' . ($metodo_pago_actual == "efectivo" ? "checked" : "") . '
                            onchange="cambiar_metodo_pago(' . $usuario_js . ', ' . $fecha_js . ', ' . $delivery_js . ', \'efectivo\', ' . $grupoId . ')">
                        Efectivo
                    </label>

                    <label>
                        <input type="radio" name="metodo_pago_' . md5($usuario . $fecha) . '" value="fiado"
                            ' . ($metodo_pago_actual == "fiado" ? "checked" : "") . '
                            onchange="cambiar_metodo_pago(' . $usuario_js . ', ' . $fecha_js . ', ' . $delivery_js . ', \'fiado\', ' . $grupoId . ')">
                        Fiado
                    </label>

                    <label>
                        <input type="radio" name="metodo_pago_' . md5($usuario . $fecha) . '" value="transferencia"
                            ' . ($metodo_pago_actual == "transferencia" ? "checked" : "") . '
                            onchange="cambiar_metodo_pago(' . $usuario_js . ', ' . $fecha_js . ', ' . $delivery_js . ', \'transferencia\', ' . $grupoId . ')">
                        Transferencia
                    </label>
                </div>
            </div>

            <div class="summary-box">
                <p><strong>Subtotal productos:</strong> $ <span id="subtotal_' . $grupoId . '">' . number_format($subtotal_usuario, 2) . '</span></p>
                <p><strong>Cantidad total de productos:</strong> ' . number_format($cantidad_total_productos, 2) . '</p>
                <p><strong>Recargo delivery:</strong> $ <span id="delivery_' . $grupoId . '">' . number_format($extra_delivery, 2) . '</span></p>
                <p><strong>Concepto bandejas:</strong> $ ' . number_format($concepto_bandejas, 2) . '</p>
                <p><strong>Total general:</strong> $ <span id="total_resumen_' . $grupoId . '">' . number_format($total_usuario, 2) . '</span></p>
                <p><strong>Entrega actual:</strong> ' . ($delivery_actual == "" ? "No seleccionada" : ucfirst($delivery_actual)) . '</p>
                <p><strong>Método de pago actual:</strong> ' . ($metodo_pago_actual == "" ? "No seleccionado" : ucfirst($metodo_pago_actual)) . '</p>
            </div>

            <div class="order-meta" style="margin-top:15px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div class="order-date">
                    Fecha: <span id="fecha_detalle_' . $grupoId . '">' . $fecha_html . '</span>
                </div>

                <div class="order-ready">
                    <label class="ready-label" style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" onchange="listo(' . $usuario_js . ', ' . $fecha_js . ', ' . $delivery_js . ', ' . $metodo_pago_js . ', ' . $grupoId . ')">
                        <span>Pedido listo / Registrar venta</span>
                    </label>
                </div>
            </div>

        </div>
    </div>';
}
?>