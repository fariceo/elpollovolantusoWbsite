<?php
include("../conexion.php");
header('Content-Type: text/html; charset=UTF-8');

// Obtener todos los pedidos con estado = 0 (pendientes), agrupando por usuario y fecha
$sqlUsuarios = "SELECT usuario, fecha, delivery, metodo_pago, SUM(total) as total_general
                FROM pedidos
                WHERE estado='0'
                GROUP BY usuario, fecha, delivery, metodo_pago
                ORDER BY fecha DESC, usuario ASC";
$resultUsuarios = mysqli_query($conexion, $sqlUsuarios);

$grupoId = 0;

while ($usuarioRow = mysqli_fetch_assoc($resultUsuarios)) {
    $grupoId++;
    $usuario = $usuarioRow['usuario'];
    $fecha = $usuarioRow['fecha'];
    $delivery = $usuarioRow['delivery'];
    $metodo_pago = $usuarioRow['metodo_pago'];
    $total_general = $usuarioRow['total_general'];

    echo '<div class="accordion-card" id="grupo_' . $grupoId . '">
        <div class="accordion-header" onclick="toggleAccordion(\'contenido_' . $grupoId . '\', this)">
            <div class="accordion-header-left">
                <strong>' . $usuario . '</strong>
                <span class="accordion-mini-info" id="miniinfo_' . $grupoId . '">Fecha: ' . $fecha . ' | Total: $ ' . number_format($total_general, 2) . '</span>
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
                <tbody>';

    $sqlProductos = "SELECT * FROM pedidos WHERE usuario='$usuario' AND fecha='$fecha' AND estado='0' ORDER BY id DESC";
    $resProductos = mysqli_query($conexion, $sqlProductos);

    $subtotal = 0;
    while ($prod = mysqli_fetch_assoc($resProductos)) {
        $subtotal += $prod['total'];
        echo '<tr id="fila_' . $prod['id'] . '">
            <td>' . $prod['producto'] . '</td>
            <td>' . $prod['cantidad'] . '</td>
            <td>$ ' . number_format($prod['precio'], 2) . '</td>
            <td>$ ' . number_format($prod['total'], 2) . '</td>
            <td>
                <button class="btn-edit-product" onclick="editar_cantidad(' . $prod['id'] . ', ' . $prod['precio'] . ', ' . $grupoId . ')">Editar</button>
                <button class="btn-delete-product" onclick="eliminar_producto(' . $prod['id'] . ', ' . $grupoId . ')">Eliminar</button>
            </td>
        </tr>';
    }

    $extra_delivery = $delivery; // suponiendo que delivery ya contiene el monto extra

    echo '</tbody>
            </table>

            <div class="summary-box">
                <p>Subtotal: $ <span id="subtotal_' . $grupoId . '">' . number_format($subtotal, 2) . '</span></p>
                <p>Delivery: $ <span id="delivery_' . $grupoId . '">' . number_format($extra_delivery, 2) . '</span></p>
                <p>Total: $ <span id="total_' . $grupoId . '">' . number_format($subtotal + $extra_delivery, 2) . '</span></p>
            </div>

            <div style="text-align:center; margin-top:12px;">
                <button class="btn-success" onclick="listo(\'' . $usuario . '\', \'' . $fecha . '\', \'' . $delivery . '\', \'' . $metodo_pago . '\',' . $grupoId . ')">Marcar como Listo</button>
            </div>
        </div>
    </div>';
}
?>