<?php
ob_start(); // evita basura accidental antes del JSON

include("../conexion.php");

header('Content-Type: application/json; charset=UTF-8');
mysqli_set_charset($conexion, "utf8mb4");

// ocultar warnings/notices en salida JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);
// =========================
// RESPUESTA JSON
// =========================
function responder($data){
    if (ob_get_length()) {
        ob_clean(); // limpia cualquier salida accidental
    }

    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// =========================
// HELPERS
// =========================
function limpiar($conexion, $valor){
    return mysqli_real_escape_string($conexion, trim($valor ?? ''));
}

function normalizarDelivery($valor){
    $valor = trim(strtolower($valor));

    if($valor == "local") return "consumo en tienda";
    if($valor == "tienda") return "consumo en tienda";
    if($valor == "consumo") return "consumo en tienda";
    if($valor == "recoger") return "recoger en tienda";

    return $valor;
}

function obtenerResumenGrupo($conexion, $usuario, $fecha){
    $usuario = limpiar($conexion, $usuario);
    $fecha   = limpiar($conexion, $fecha);

    $sql = mysqli_query($conexion, "SELECT * FROM pedidos WHERE usuario='$usuario' AND fecha='$fecha' AND estado='0'");
    
    $subtotal = 0;
    $cantidad_total_productos = 0;
    $delivery_actual = "consumo en tienda";
    $metodo_pago_actual = "efectivo";

    while($row = mysqli_fetch_assoc($sql)){
        $subtotal += floatval($row['total']);
        $cantidad_total_productos += floatval($row['cantidad']);

        if (!empty($row['delivery'])) {
            $delivery_actual = normalizarDelivery($row['delivery']);
        }

        if (!empty($row['metodo_pago'])) {
            $metodo_pago_actual = trim(strtolower($row['metodo_pago']));
        }
    }

    // Reglas de recargo
    $extra_delivery = 0;
    $concepto_bandejas = 0;

    if($delivery_actual == "delivery"){
        $extra_delivery = 2.00;
        $concepto_bandejas = $cantidad_total_productos * 0.25;
    } elseif($delivery_actual == "recoger en tienda"){
        $extra_delivery = 0;
        $concepto_bandejas = $cantidad_total_productos * 0.25;
    } else {
        $extra_delivery = 0;
        $concepto_bandejas = 0;
    }

    $total_general = $subtotal + $extra_delivery + $concepto_bandejas;

    return [
        'subtotal' => round($subtotal, 2),
        'cantidad_total_productos' => round($cantidad_total_productos, 2),
        'extra_delivery' => round($extra_delivery, 2),
        'concepto_bandejas' => round($concepto_bandejas, 2),
        'total_general' => round($total_general, 2),
        'delivery_actual' => $delivery_actual,
        'metodo_pago_actual' => $metodo_pago_actual,
        'fecha' => $fecha
    ];
}

// =========================
// INSERTAR PEDIDO
// =========================
if(isset($_POST['accion']) && $_POST['accion']=="insertar"){
    $usuario  = limpiar($conexion, $_POST['usuario']);
    $producto = limpiar($conexion, $_POST['producto']);
    $cantidad = floatval($_POST['cantidad'] ?? 0);
    $precio   = floatval($_POST['precio'] ?? 0);
    $total    = floatval($_POST['total'] ?? 0);
    $fecha    = date('Y-m-d');
    $hora     = date('H:i:s');

    if($usuario == "" || $producto == "" || $cantidad <= 0){
        responder(['success'=>false,'message'=>'Datos inválidos']);
    }

    $sql = "INSERT INTO pedidos(usuario,producto,cantidad,precio,total,estado,delivery,metodo_pago,fecha,hora) 
            VALUES('$usuario','$producto','$cantidad','$precio','$total','0','consumo en tienda','efectivo','$fecha','$hora')";

    if(mysqli_query($conexion,$sql)){
        $resumen = obtenerResumenGrupo($conexion, $usuario, $fecha);
        responder(array_merge(['success'=>true], $resumen));
    } else {
        responder(['success'=>false,'message'=>'No se pudo insertar el pedido: '.mysqli_error($conexion)]);
    }
}

// =========================
// BUSCAR PRODUCTOS
// =========================
if (isset($_POST['buscar_producto'])) {
    header('Content-Type: text/html; charset=UTF-8');

    $buscar = limpiar($conexion, $_POST['buscar_producto']);

    $sql = "SELECT * FROM menu 
            WHERE producto LIKE '%$buscar%' 
            ORDER BY producto ASC 
            LIMIT 10";

    $result = mysqli_query($conexion, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        echo "<div class='results-header'>Productos encontrados</div>";

        while ($row = mysqli_fetch_assoc($result)) {
            $producto = htmlspecialchars($row['producto'], ENT_QUOTES, 'UTF-8');
            $precioRaw = (float)$row['precio'];
            $precio = number_format($precioRaw, 2);
            $img = !empty($row['img']) ? htmlspecialchars($row['img'], ENT_QUOTES, 'UTF-8') : "default.png";
            $rutaImagen = "../imagenes/" . $img;

            $idCantidad = "cantidad_" . preg_replace("/[^\w]/", "", $producto);

            echo "
            <div class='resultado-producto-card'>
                <div class='producto-card-top'>
                    <div class='producto-imagen-wrap'>
                        <img src='$rutaImagen' alt='$producto' class='producto-imagen'
                             onerror=\"this.src='../imagenes/default.png'\" />
                    </div>

                    <div class='producto-info'>
                        <div class='producto-nombre'>$producto</div>
                        <div class='producto-sub'>Disponible para pedido</div>
                    </div>

                    <div class='producto-precio'>$$precio</div>
                </div>

                <div class='producto-card-bottom'>
                    <div class='cantidad-box'>
                        <label for='$idCantidad'>Cantidad</label>
                        <input type='number' min='1' id='$idCantidad' value='1'>
                    </div>

                    <button class='btn-agregar-producto' onclick=\"ingresar('$producto','$precioRaw')\">
                        Agregar
                    </button>
                </div>
            </div>";
        }
    } else {
        echo "
        <div class='empty-state'>
            <div class='empty-icon'>🍽️</div>
            <div class='empty-title'>Producto no encontrado</div>
            <div class='empty-text'>No existe en el menú</div>
        </div>";
    }
    exit;
}

// =========================
// BUSCAR USUARIOS
// =========================
if (isset($_POST['buscar_usuario']) && isset($_POST['buscar_deudor'])) {
    header('Content-Type: text/html; charset=UTF-8');

    $buscar = trim($_POST['buscar_deudor']);

    if ($buscar === '') {
        echo "<div class='empty-state'>Escribe un nombre para buscar</div>";
        exit;
    }

    $buscar = limpiar($conexion, $buscar);

    $sql = "SELECT usuario, saldo_pendiente 
            FROM saldo_pendiente 
            WHERE TRIM(usuario) LIKE '%$buscar%' 
            ORDER BY usuario ASC 
            LIMIT 10";

    $result = mysqli_query($conexion, $sql);

    if (!$result) {
        echo "<div class='error-state'>Error SQL: " . mysqli_error($conexion) . "</div>";
        exit;
    }

    if (mysqli_num_rows($result) > 0) {
        echo "<div class='results-header'>Usuarios encontrados</div>";

        while ($row = mysqli_fetch_assoc($result)) {
            $usuario = htmlspecialchars($row['usuario'], ENT_QUOTES, 'UTF-8');
            $saldo = number_format((float)$row['saldo_pendiente'], 2);

            echo "
            <div class='resultado-usuario-card' onclick=\"elegir_usuario('$usuario')\">
                <div class='usuario-card-glow'></div>

                <div class='resultado-usuario-left'>
                    <div class='resultado-avatar'>
                        <span>" . strtoupper(substr($usuario, 0, 1)) . "</span>
                    </div>

                    <div class='resultado-info'>
                        <div class='resultado-nombre'>$usuario</div>
                        <div class='resultado-sub'>
                            <span class='estado-dot'></span>
                            Cliente con saldo pendiente
                        </div>
                    </div>
                </div>

                <div class='resultado-right'>
                    <div class='saldo-label'>Saldo</div>
                    <div class='resultado-saldo'>$$saldo</div>
                </div>
            </div>";
        }
    } else {
        echo "
        <div class='empty-state'>
            <div class='empty-icon'>🔍</div>
            <div class='empty-title'>Usuario no encontrado</div>
            <div class='empty-text'>No existe en saldo_pendiente</div>
        </div>";
    }

    exit;
}

// =========================
// ELIMINAR PRODUCTO
// =========================
if(isset($_POST['accion']) && $_POST['accion']=="eliminar_producto" && isset($_POST['id_pedido'])){
    $id = intval($_POST['id_pedido']);

    $q = mysqli_query($conexion, "SELECT usuario, fecha FROM pedidos WHERE id='$id'");
    if(!$q || mysqli_num_rows($q) == 0){
        responder(['success'=>false,'message'=>'Pedido no encontrado']);
    }

    $row = mysqli_fetch_assoc($q);
    $usuario = $row['usuario'];
    $fecha   = $row['fecha'];

    if(mysqli_query($conexion,"DELETE FROM pedidos WHERE id='$id'")){
        $resumen = obtenerResumenGrupo($conexion, $usuario, $fecha);
        responder(array_merge(['success'=>true], $resumen));
    } else {
        responder(['success'=>false,'message'=>'No se pudo eliminar']);
    }
}

// =========================
// EDITAR CANTIDAD
// =========================
if(isset($_POST['accion']) && $_POST['accion']=="editar_cantidad"){
    $id = intval($_POST['id_pedido'] ?? 0);
    $nuevaCantidad = floatval($_POST['nueva_cantidad'] ?? 0);

    if($id <= 0 || $nuevaCantidad <= 0){
        responder([
            'success' => false,
            'message' => 'Cantidad o ID inválido'
        ]);
    }

    $sql = mysqli_query($conexion, "SELECT * FROM pedidos WHERE id='$id' LIMIT 1");

    if(!$sql){
        responder([
            'success' => false,
            'message' => 'Error al consultar pedido: ' . mysqli_error($conexion)
        ]);
    }

    if($row = mysqli_fetch_assoc($sql)){
        $precio  = floatval($row['precio']);
        $total   = round($precio * $nuevaCantidad, 2);
        $usuario = $row['usuario'];
        $fecha   = $row['fecha'];

        $update = mysqli_query($conexion, "UPDATE pedidos 
                                           SET cantidad='$nuevaCantidad', total='$total' 
                                           WHERE id='$id'");

        if(!$update){
            responder([
                'success' => false,
                'message' => 'Error al actualizar: ' . mysqli_error($conexion)
            ]);
        }

        $resumen = obtenerResumenGrupo($conexion, $usuario, $fecha);

        responder(array_merge([
            'success'  => true,
            'producto' => $row['producto'],
            'cantidad' => $nuevaCantidad,
            'precio'   => $precio,
            'total'    => $total
        ], $resumen));
    } else {
        responder([
            'success' => false,
            'message' => 'Pedido no encontrado'
        ]);
    }
}

// =========================
// ACTUALIZAR MÉTODO DE PAGO
// =========================
if(isset($_POST['accion']) && $_POST['accion']=="actualizar_metodo_pago"){
    $usuario = limpiar($conexion, $_POST['usuario']);
    $fecha   = limpiar($conexion, $_POST['fecha']);
    $metodo  = limpiar($conexion, $_POST['metodo_pago']);
    $saldo_pendiente = floatval($_POST['saldo_pendiente'] ?? 0);

    mysqli_query($conexion,"UPDATE pedidos SET metodo_pago='$metodo' WHERE usuario='$usuario' AND fecha='$fecha' AND estado='0'");

    if($metodo=="fiado" && $saldo_pendiente > 0){
        $sql = mysqli_query($conexion,"SELECT saldo_pendiente FROM saldo_pendiente WHERE usuario='$usuario'");
        if($sql && mysqli_num_rows($sql)>0){
            mysqli_query($conexion,"UPDATE saldo_pendiente SET saldo_pendiente='$saldo_pendiente' WHERE usuario='$usuario'");
        } else {
            mysqli_query($conexion,"INSERT INTO saldo_pendiente(usuario,saldo_pendiente) VALUES('$usuario','$saldo_pendiente')");
        }
    }

    $resumen = obtenerResumenGrupo($conexion, $usuario, $fecha);
    responder(array_merge(['success'=>true], $resumen));
}

// =========================
// ACTUALIZAR DELIVERY
// =========================
if(isset($_POST['accion']) && $_POST['accion']=="actualizar_delivery"){
    $usuario  = limpiar($conexion, $_POST['usuario']);
    $fecha    = limpiar($conexion, $_POST['fecha']);
    $delivery = normalizarDelivery($_POST['delivery'] ?? '');

    mysqli_query($conexion,"UPDATE pedidos SET delivery='$delivery' WHERE usuario='$usuario' AND fecha='$fecha' AND estado='0'");

    $resumen = obtenerResumenGrupo($conexion, $usuario, $fecha);
    responder(array_merge(['success'=>true], $resumen));
}

// =========================
// TRUNCATE PEDIDOS
// =========================
if(isset($_POST['accion']) && $_POST['accion']=="truncate"){
    mysqli_query($conexion,"DELETE FROM pedidos");
    responder(['success'=>true]);
}

// =========================
// REGISTRAR PEDIDO LISTO
// =========================
if(isset($_POST['accion']) && $_POST['accion']=="registrar_venta_y_listo"){
    $usuario = limpiar($conexion, $_POST['usuario']);
    $fecha   = limpiar($conexion, $_POST['fecha']);

    $resumen = obtenerResumenGrupo($conexion, $usuario, $fecha);

    $sql = mysqli_query($conexion,"SELECT * FROM pedidos WHERE usuario='$usuario' AND fecha='$fecha' AND estado='0'");
    $pedidos = [];
    while($row = mysqli_fetch_assoc($sql)){
        $pedidos[] = [
            'producto'=>$row['producto'],
            'cantidad'=>$row['cantidad'],
            'precio'=>$row['precio'],
            'total'=>$row['total']
        ];
    }

    if(count($pedidos) > 0){
        $productos_json = mysqli_real_escape_string($conexion, json_encode($pedidos, JSON_UNESCAPED_UNICODE));
        $delivery = $resumen['delivery_actual'];
        $metodo   = $resumen['metodo_pago_actual'];
        $total_general = $resumen['total_general'];

        mysqli_query($conexion,"INSERT INTO ventas(usuario,producto,total,estado,delivery,metodo_pago,fecha,hora) 
            VALUES('$usuario','$productos_json','$total_general','0','$delivery','$metodo','$fecha',NOW())");

        mysqli_query($conexion,"UPDATE pedidos SET estado='2' WHERE usuario='$usuario' AND fecha='$fecha' AND estado='0'");

        responder(['success'=>true]);
    } else {
        responder(['success'=>false,'message'=>'No hay pedidos para registrar']);
    }
}

// =========================
// CAMBIAR FECHA PEDIDO
// =========================
if(isset($_POST['accion']) && $_POST['accion']=="cambiar_fecha"){
    $usuario     = limpiar($conexion, $_POST['usuario']);
    $fechaActual = limpiar($conexion, $_POST['fecha_actual']);
    $nuevaFecha  = limpiar($conexion, $_POST['nueva_fecha']);

    mysqli_query($conexion,"UPDATE pedidos SET fecha='$nuevaFecha' WHERE usuario='$usuario' AND fecha='$fechaActual' AND estado='0'");

    $resumen = obtenerResumenGrupo($conexion, $usuario, $nuevaFecha);
    responder(array_merge(['success'=>true], $resumen));
}

// Si no coincide ninguna acción
responder(['success'=>false,'message'=>'Acción no válida o faltan parámetros']);
?>