<?php
error_reporting(0);
include("../conexion.php");
ini_set('date.timezone', 'America/Guayaquil');

$fecha = date("Y-m-d");
$hora = date("G:i:s");

function limpiar($conexion, $valor) {
    return mysqli_real_escape_string($conexion, trim($valor));
}

// cambiar fecha por usuario
if (!empty($_POST['id_fecha'])) {
    $usuario_fecha = limpiar($conexion, $_POST['id_fecha']);
    $nueva_fecha = limpiar($conexion, $_POST['fecha']);
    mysqli_query($conexion, "UPDATE pedidos SET fecha='$nueva_fecha' WHERE usuario='$usuario_fecha' AND estado!='2' AND estado!='10'");
    exit("ok");
}

// insertar pedido
if (!empty($_POST['producto'])) {
    $usuario = ucfirst(limpiar($conexion, $_POST['usuario']));
    $producto = limpiar($conexion, $_POST['producto']);
    $cantidad = floatval($_POST['cantidad']);
    $precio = floatval($_POST['precio']);
    $total = floatval($_POST['total']);

    if ($usuario == "" || $producto == "" || $cantidad <= 0 || $precio < 0 || $total < 0) {
        exit("error_datos_invalidos");
    }

    mysqli_query($conexion, "INSERT INTO pedidos (`usuario`,`producto`,`cantidad`,`precio`,`total`,`estado`,`delivery`,`metodo_pago`,`fecha`,`hora`) VALUES ('$usuario','$producto','$cantidad','$precio','$total','0','default','default','$fecha','$hora')");
    exit("ok");
}

// registrar venta y marcar listo
if (!empty($_POST['registrar_venta_y_listo']) && !empty($_POST['usuario_pedido_listo'])) {
    $usuario_listo = ucfirst(limpiar($conexion, $_POST['usuario_pedido_listo']));
    $total_general_venta = isset($_POST['total_general_venta']) ? floatval($_POST['total_general_venta']) : 0;

    $consulta = mysqli_query($conexion, "
        SELECT producto, cantidad, precio, total, delivery, metodo_pago
        FROM pedidos 
        WHERE usuario='$usuario_listo' AND estado!='2' AND estado!='10'
    ");

    $productos_array = [];
    $cantidad_total = 0;
    $total_general = 0;
    $delivery = "default";
    $metodo_pago = "default";

    while ($fila = mysqli_fetch_assoc($consulta)) {
        $detalle = $fila['producto'] . " x" . $fila['cantidad'] . " - $" . number_format($fila['precio'], 2) . " = $" . number_format($fila['total'], 2);
        $productos_array[] = $detalle;

        $cantidad_total += intval($fila['cantidad']);
        $total_general += floatval($fila['total']);

        $delivery = $fila['delivery'];
        $metodo_pago = $fila['metodo_pago'];
    }

    if (count($productos_array) > 0) {
        $productos_implode = mysqli_real_escape_string($conexion, implode("\n", $productos_array));

        $result = mysqli_query($conexion, "
            INSERT INTO pedidos (usuario, producto, cantidad, precio, total, estado, delivery, metodo_pago, fecha, hora)
            VALUES ('$usuario_listo','$productos_implode','$cantidad_total','$total_general','$total_general','10','$delivery','$metodo_pago','$fecha','$hora')
        ");

        mysqli_query($conexion, "UPDATE pedidos SET estado='100' WHERE usuario='$usuario_listo' AND estado!='2' AND estado!='10'");
        mysqli_query($conexion, "DELETE FROM pedidos WHERE usuario='$usuario_listo' AND estado='10'");
    }

    exit("ok");
}

// eliminar producto (estado 2)
if (!empty($_POST['eliminar_producto_estado']) && !empty($_POST['usuario_eliminar']) && !empty($_POST['producto_eliminar'])) {
    $usuario_eliminar = limpiar($conexion, $_POST['usuario_eliminar']);
    $producto_eliminar = limpiar($conexion, $_POST['producto_eliminar']);
    mysqli_query($conexion, "UPDATE pedidos SET estado='2' WHERE usuario='$usuario_eliminar' AND producto='$producto_eliminar' AND estado!='2' AND estado!='10' AND estado!='100'");
    exit("ok");
}

// actualizar cantidad y total por id
if (!empty($_POST['actualizar_cantidad']) && !empty($_POST['id_pedido_cantidad']) && !empty($_POST['nueva_cantidad'])) {
    $idPedido = intval($_POST['id_pedido_cantidad']);
    $nuevaCantidad = floatval($_POST['nueva_cantidad']);

    $consultaPedido = mysqli_query($conexion, "SELECT precio FROM pedidos WHERE id='$idPedido' LIMIT 1");
    if ($filaPedido = mysqli_fetch_assoc($consultaPedido)) {
        $precioPedido = floatval($filaPedido['precio']);
        $nuevoTotal = $precioPedido * $nuevaCantidad;
        mysqli_query($conexion, "UPDATE pedidos SET cantidad='$nuevaCantidad', total='$nuevoTotal' WHERE id='$idPedido'");
    }

    exit("ok");
}

// actualizar tipo de entrega
if (!empty($_POST['actualizar_delivery']) && !empty($_POST['usuario_delivery']) && !empty($_POST['tipo_delivery'])) {
    $usuarioDelivery = limpiar($conexion, $_POST['usuario_delivery']);
    $tipoDelivery = limpiar($conexion, $_POST['tipo_delivery']);
    mysqli_query($conexion, "UPDATE pedidos SET delivery='$tipoDelivery' WHERE usuario='$usuarioDelivery' AND estado!='2' AND estado!='10'");
    exit("ok");
}

// actualizar método de pago
if (
    isset($_POST['actualizar_metodo_pago']) &&
    isset($_POST['usuario_metodo_pago']) &&
    isset($_POST['metodo_pago_valor']) &&
    $_POST['usuario_metodo_pago'] != "" &&
    $_POST['metodo_pago_valor'] != ""
) {
    $usuarioMetodo = ucfirst(limpiar($conexion, $_POST['usuario_metodo_pago']));
    $metodoPago = limpiar($conexion, $_POST['metodo_pago_valor']);

    $metodoPagoActual = "";
    $consultaMetodoActual = mysqli_query($conexion, "
        SELECT metodo_pago
        FROM pedidos
        WHERE usuario='$usuarioMetodo' AND estado!='2' AND estado!='10'
        ORDER BY id DESC
        LIMIT 1
    ");

    if ($consultaMetodoActual && mysqli_num_rows($consultaMetodoActual) > 0) {
        $filaMetodoActual = mysqli_fetch_assoc($consultaMetodoActual);
        $metodoPagoActual = trim($filaMetodoActual['metodo_pago']);
    }

    mysqli_query($conexion, "
        UPDATE pedidos
        SET metodo_pago='$metodoPago'
        WHERE usuario='$usuarioMetodo' AND estado!='2' AND estado!='10'
    ");

    if ($metodoPago == "fiado" && $metodoPagoActual != "fiado") {
        $saldoPendiente = isset($_POST['saldo_pendiente_pago']) ? floatval($_POST['saldo_pendiente_pago']) : 0;
        $fechaCredito = isset($_POST['fecha_credito']) ? limpiar($conexion, $_POST['fecha_credito']) : "";

        if ($fechaCredito == "") {
            $fechaCredito = $fecha;
        }

        if ($saldoPendiente > 0) {
            $buscarSaldo = mysqli_query($conexion, "
                SELECT id, saldo_pendiente
                FROM saldo_pendiente
                WHERE usuario='$usuarioMetodo'
                ORDER BY id DESC
                LIMIT 1
            ");

            if ($buscarSaldo && mysqli_num_rows($buscarSaldo) > 0) {
                $datoSaldo = mysqli_fetch_assoc($buscarSaldo);
                $saldoActual = floatval($datoSaldo['saldo_pendiente']);
                $nuevoSaldo = $saldoActual + $saldoPendiente;

                mysqli_query($conexion, "
                    UPDATE saldo_pendiente
                    SET saldo_pendiente='$nuevoSaldo',
                        accion='1',
                        fecha='$fecha',
                        hora='$hora'
                    WHERE id='" . $datoSaldo['id'] . "'
                ");
            } else {
                mysqli_query($conexion, "
                    INSERT INTO saldo_pendiente (usuario, saldo_pendiente, accion, fecha, hora)
                    VALUES ('$usuarioMetodo', '$saldoPendiente', '1', '$fecha', '$hora')
                ");
            }

            $conceptoHistorial = "Pedido fiado desde pedidos";

            mysqli_query($conexion, "
                INSERT INTO historial_credito (usuario, saldo, saldo_contable, concepto, fecha)
                VALUES ('$usuarioMetodo', '$saldoPendiente', '$saldoPendiente', '$conceptoHistorial', '$fechaCredito')
            ");
        }
    }

    exit("ok");
}

// vaciar tabla
if (!empty($_POST['tabla_pedidos'])) {
    mysqli_query($conexion, "TRUNCATE TABLE pedidos");
    exit("ok");
}


?>




<?php
/// buscar usuario en saldopendiente

if ($_POST["buscar_usuario"] != "" && $_POST["buscar_deudor"] != "") {



    //$buscar_usuario=mysqli_query($conexion,"SELECT * FROM saldo_pendiente WHERE usuario='$_POST[buscar_deudor]'");
    $buscar_usuario = mysqli_query($conexion, "SELECT * FROM saldo_pendiente WHERE usuario like '%" . $_POST['buscar_deudor'] . "%'");


    while ($usuario = mysqli_fetch_array($buscar_usuario)) {

        echo "<br>" . $usuario['usuario'];

        ?>

        <button onClick="elegir_usuario('<?php echo $usuario['usuario'] ?>')">
            Seleccionar
        </button>

        <p style="color:red">Saldo pendiente de pago : <?php echo " $ " . $usuario['saldo_pendiente']; ?></p>
        <?php

    }
} else {
    echo "";
}


?>

<?php
///buscar producto
if ($_POST["buscar_producto"] != "") {




    //$buscar_producto_menu=mysqli_query($conexion,"SELECT * FROM bodega WHERE producto like '%".$_POST['buscar_producto']."%'");
    $buscar_producto_menu = mysqli_query($conexion, "SELECT * FROM menu WHERE producto like '%" . $_POST['buscar_producto'] . "%'");

    while ($producto = mysqli_fetch_array($buscar_producto_menu)) {

        ?>
        <!--<ul onClick="add_list('<?php // echo //$producto[producto]                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             ?>','<?php //echo $producto[precio]                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             ?>')" style="text-align:center">-->
      <ul style="text-align:center; padding:0; margin:0;">
    <li style="
        list-style: none; 
        display:inline-block; 
        margin:15px; 
        padding:15px; 
        border-radius:12px; 
        width:160px; 
        background: #f9f9f9; 
        box-shadow: 0 4px 8px rgba(0,0,0,0.1); 
        transition: transform 0.2s, box-shadow 0.2s;
        font-family: Arial, sans-serif;
    " 
    onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.2)';" 
    onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.1)';">
        
        <a id='<?php echo str_replace(' ', '', $producto['producto'] . "img") ?>'>
            <img style='width:120px; height:120px; border-radius:10px; object-fit:cover;' src="../imagenes/<?php echo $producto['img'] ?>">
        </a>
        
        <p style="margin:10px 0 5px 0; font-weight:bold; font-size:16px;"><?php echo $producto['producto']; ?></p>
        <p style="margin:5px 0; color:#555; font-size:14px;"><?php echo " $ " . $producto['precio']; ?></p>
        
        <!-- Input para cantidad -->
        <input type="number" 
               id="cantidad_<?php echo str_replace(' ', '', $producto['producto']) ?>" 
               min="1" value="1" 
               style="width:60px; text-align:center; margin-bottom:10px; padding:4px; border:1px solid #ccc; border-radius:6px;">
        
        <button onclick="ingresar('<?php echo $producto['producto'] ?>','<?php echo $producto['precio'] ?>')" 
                style="width:100%; padding:8px; border:none; border-radius:6px; background-color:#4CAF50; color:white; font-weight:bold; cursor:pointer; transition: background-color 0.2s;">
            Agregar
        </button>
    </li>
</ul>

        <?php



    }


}

?>
