<?php
include("../conexion.php");
//header('Content-Type: application/json');

// Función helper para responder JSON

// Función helper para responder JSON
function responder($data){
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}


// =========================
// INSERTAR PEDIDO
// =========================
if(isset($_POST['accion']) && $_POST['accion']=="insertar"){
    $usuario=mysqli_real_escape_string($conexion,$_POST['usuario']);
    $producto=mysqli_real_escape_string($conexion,$_POST['producto']);
    $cantidad=floatval($_POST['cantidad']);
    $precio=floatval($_POST['precio']);
    $total=floatval($_POST['total']);
    $fecha=date('Y-m-d'); $hora=date('H:i:s');

    $sql="INSERT INTO pedidos(usuario,producto,cantidad,precio,total,estado,delivery,metodo_pago,fecha,hora) 
          VALUES('$usuario','$producto','$cantidad','$precio','$total','0','0','efectivo','$fecha','$hora')";
    if(mysqli_query($conexion,$sql)) responder(['success'=>true]);
    else responder(['success'=>false,'message'=>'No se pudo insertar el pedido']);
}

// =========================
// BUSCAR PRODUCTOS
// =========================
if(isset($_POST['buscar_producto'])){
    $buscar=mysqli_real_escape_string($conexion,$_POST['buscar_producto']);
    $sql="SELECT * FROM menu WHERE producto LIKE '%$buscar%' ORDER BY producto ASC LIMIT 10";
    $result=mysqli_query($conexion,$sql);
    if(mysqli_num_rows($result)>0){
        while($row=mysqli_fetch_assoc($result)){
            $producto=htmlspecialchars($row['producto']);
            $precio=number_format($row['precio'],2);
            $idCantidad="cantidad_".preg_replace("/[^\w]/","",$producto);
            echo "
                    <span>$producto - $$precio</span><br>
                    <input type='number' min='1' id='$idCantidad' value='1'><br>
                    <button onclick=\"ingresar('$producto','$precio')\">Agregar</button>
                 ";
        }
    } else echo "<div>No se encontraron productos</div>";
    exit;
}

// =========================
// =========================
// BUSCAR USUARIOS (solo saldo_pendiente)
// =========================
if (isset($_POST['buscar_usuario']) && isset($_POST['buscar_deudor'])) {
    $buscar = trim($_POST['buscar_deudor']);

    if ($buscar === '') {
        echo "<div class='empty-state'>Escribe un nombre para buscar</div>";
        exit;
    }

    $buscar = mysqli_real_escape_string($conexion, $buscar);

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
// ELIMINAR PEDIDO
// =========================
if(isset($_POST['accion']) && $_POST['accion']=="eliminar_producto" && isset($_POST['id_pedido'])){
    $id=intval($_POST['id_pedido']);
    if(mysqli_query($conexion,"DELETE FROM pedidos WHERE id='$id'")) responder(['success'=>true]);
    else responder(['success'=>false,'message'=>'No se pudo eliminar']);
}

// =========================
// EDITAR CANTIDAD
// =========================
if(isset($_POST['accion']) && $_POST['accion']=="editar_cantidad"){
    $id=intval($_POST['id_pedido']);
    $nuevaCantidad=floatval($_POST['nueva_cantidad']);
    $sql=mysqli_query($conexion,"SELECT * FROM pedidos WHERE id='$id'");
    if($row=mysqli_fetch_assoc($sql)){
        $precio=$row['precio'];
        $total=$precio*$nuevaCantidad;
        mysqli_query($conexion,"UPDATE pedidos SET cantidad='$nuevaCantidad',total='$total' WHERE id='$id'");
        responder([
            'success'=>true,
            'producto'=>$row['producto'],
            'cantidad'=>$nuevaCantidad,
            'precio'=>$precio,
            'total'=>$total
        ]);
    } else responder(['success'=>false,'message'=>'Pedido no encontrado']);
}

// =========================
// ACTUALIZAR MÉTODO DE PAGO
// =========================
if(isset($_POST['accion']) && $_POST['accion']=="actualizar_metodo_pago"){
    $usuario=mysqli_real_escape_string($conexion,$_POST['usuario']);
    $fecha=mysqli_real_escape_string($conexion,$_POST['fecha']);
    $delivery=intval($_POST['delivery']);
    $metodo=mysqli_real_escape_string($conexion,$_POST['metodo_pago']);
    $saldo_pendiente=floatval($_POST['saldo_pendiente'] ?? 0);

    mysqli_query($conexion,"UPDATE pedidos SET metodo_pago='$metodo' WHERE usuario='$usuario' AND fecha='$fecha' AND delivery='$delivery'");
    if($metodo=="fiado" && $saldo_pendiente>0){
        $sql=mysqli_query($conexion,"SELECT saldo_pendiente FROM saldo_pendiente WHERE usuario='$usuario'");
        if(mysqli_num_rows($sql)>0){
            mysqli_query($conexion,"UPDATE saldo_pendiente SET saldo_pendiente='$saldo_pendiente' WHERE usuario='$usuario'");
        } else mysqli_query($conexion,"INSERT INTO saldo_pendiente(usuario,saldo_pendiente) VALUES('$usuario','$saldo_pendiente')");
    }
    responder(['success'=>true]);
}

// =========================
// ACTUALIZAR DELIVERY
// =========================
if(isset($_POST['accion']) && $_POST['accion']=="actualizar_delivery"){
    $usuario=mysqli_real_escape_string($conexion,$_POST['usuario']);
    $fecha=mysqli_real_escape_string($conexion,$_POST['fecha']);
    $metodo=mysqli_real_escape_string($conexion,$_POST['metodo_pago']);
    $delivery=intval($_POST['delivery']);
    mysqli_query($conexion,"UPDATE pedidos SET delivery='$delivery' WHERE usuario='$usuario' AND fecha='$fecha' AND metodo_pago='$metodo'");
    responder(['success'=>true]);
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
    $usuario=mysqli_real_escape_string($conexion,$_POST['usuario']);
    $fecha=mysqli_real_escape_string($conexion,$_POST['fecha']);
    $delivery=intval($_POST['delivery']);
    $metodo=mysqli_real_escape_string($conexion,$_POST['metodo_pago']);

    $sql=mysqli_query($conexion,"SELECT * FROM pedidos WHERE usuario='$usuario' AND fecha='$fecha' AND delivery='$delivery' AND metodo_pago='$metodo'");
    $pedidos=[];
    $total_general=0;
    while($row=mysqli_fetch_assoc($sql)){
        $pedidos[]=['producto'=>$row['producto'],'cantidad'=>$row['cantidad'],'precio'=>$row['precio'],'total'=>$row['total']];
        $total_general+=$row['total'];
    }
    if(count($pedidos)>0){
        // Insertar venta en tabla ventas
        $productos_json=mysqli_real_escape_string($conexion,json_encode($pedidos));
        mysqli_query($conexion,"INSERT INTO ventas(usuario,producto,total,estado,delivery,metodo_pago,fecha,hora) 
            VALUES('$usuario','$productos_json','$total_general','0','$delivery','$metodo','$fecha',NOW())");
        // Marcar pedidos como listos (estado=2)
        mysqli_query($conexion,"UPDATE pedidos SET estado='2' WHERE usuario='$usuario' AND fecha='$fecha' AND delivery='$delivery' AND metodo_pago='$metodo'");
        responder(['success'=>true]);
    } else responder(['success'=>false,'message'=>'No hay pedidos para registrar']);
}

// =========================
// CAMBIAR FECHA PEDIDO
// =========================
if(isset($_POST['accion']) && $_POST['accion']=="cambiar_fecha"){
    $usuario=mysqli_real_escape_string($conexion,$_POST['usuario']);
    $fecha_actual=mysqli_real_escape_string($conexion,$_POST['fecha_actual']);
    $nueva_fecha=mysqli_real_escape_string($conexion,$_POST['nueva_fecha']);
    $delivery=intval($_POST['delivery']);
    $metodo_pago=mysqli_real_escape_string($conexion,$_POST['metodo_pago']);

    mysqli_query($conexion,"UPDATE pedidos SET fecha='$nueva_fecha' WHERE usuario='$usuario' AND fecha='$fecha_actual' AND delivery='$delivery' AND metodo_pago='$metodo_pago'");
    responder(['success'=>true]);
}
?>