<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar Pedidos</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script>

        function mostrar_lista_pedidos() {
        }

        //buscar producto
        function perderFocoproducto() {
            $("#ventana_buscador").html("").fadeOut();
        }

        function buscar_producto() {
            var valor = $("#producto").val().trim();

            if (valor !== "") {
                $.ajax({
                    type: "POST",
                    url: "asi_sistema/info/procesar2.php",
                    data: { buscar_producto: valor },
                    success: function (result) {
                        if (result.trim() !== "") {
                            $("#ventana_buscador").html(result).fadeIn();
                        } else {
                            $("#ventana_buscador").fadeOut().html("");
                        }
                    }
                });
            } else {
                $("#ventana_buscador").fadeOut().html("");
            }
        }

        function add_list(e, f) {
            var cantidad = prompt("Cantidad");
        }

        function alPerderFoco() {
            // $("#ventana_usuario").html("");
        }

        function buscar_usuario() {
            var usuario = $("#usuario").val();

            var textoConMayuscula = usuario.charAt(0).toUpperCase() + usuario.slice(1);
            $('#usuario').val(textoConMayuscula);

            $.ajax({
                type: "POST",
                url: "asi_sistema/info/procesar2.php",
                data: { buscar_deudor: usuario, buscar_usuario: 1 },
                success: function (result) {
                    $("#ventana_usuario").html(result);
                }
            });

            var inputUsuario = document.getElementById("usuario");
            if (inputUsuario) {
                inputUsuario.onblur = function () {
                    alPerderFoco();
                }
            }
        }

        function elegir_usuario(e) {
            $("#usuario").val(e);

            $.ajax({
                type: "POST",
                url: "asi_sistema/info/procesar2.php",
                data: { buscar_deudor: $("#usuario").val(), buscar_usuario: "" },
                success: function (result) {
                    $("#ventana_usuario").html(result);
                    $("#ventana_usuario").html("");
                }
            });
        }

        function ingresar(producto, precio) {
            if ($("#usuario").val() != "") {
                var cantidad = $("#cantidad_" + producto.replace(/\s/g, '')).val();

                if (cantidad <= 0 || cantidad === "" || isNaN(cantidad)) {
                    alert("Ingrese una cantidad válida");
                    return;
                }

                var total = parseFloat(cantidad) * parseFloat(precio);

                $.ajax({
                    type: "POST",
                    url: "pedidos.php",
                    data: {
                        usuario: $("#usuario").val(),
                        producto: producto,
                        cantidad: cantidad,
                        precio: precio,
                        total: total,
                        i: 1
                    },
                    success: function (result) {
                        $("body").html(result);
                    }
                });

                $.ajax({
                    type: "POST",
                    url: "asi_sistema/info/procesar2.php",
                    data: { producto: producto, cantidad: cantidad, restar_stock: 1 },
                    success: function (result) {
                    }
                });

            } else {
                alert("Introducir Usuario");
                $("#usuario").focus();
            }
        }

      function listo(usuario, total) {
    if (!confirm("¿Marcar pedido listo y registrar venta de " + usuario + "?")) return;

    $.ajax({
        type: "POST",
        url: "pedidos.php",
        data: {
            registrar_venta_y_listo: 1,
            usuario_pedido_listo: usuario,
            total_general_venta: total
        },
        success: function (result) {
            //alert(result);
            location.reload(); // recarga la página para reflejar cambios
        }
    });
}

        function eliminar_producto(usuario, producto) {
            if (!confirm("¿Cambiar estado a 2 para este producto?\n\nUsuario: " + usuario + "\nProducto: " + producto)) {
                return;
            }

            $.ajax({
                type: "POST",
                url: "pedidos.php",
                data: {
                    eliminar_producto_estado: 1,
                    usuario_eliminar: usuario,
                    producto_eliminar: producto
                },
                success: function (result) {
                    $("body").html(result);
                }
            });
        }

        function cambiar_fecha(usuario, fechaActual) {
            const hoy = new Date();
            const año = hoy.getFullYear();
            const mes = String(hoy.getMonth() + 1).padStart(2, '0');
            const día = String(hoy.getDate()).padStart(2, '0');
            const fechaFormateada = `${año}-${mes}-${día}`;

            var fechaBase = fechaActual && fechaActual !== "" ? fechaActual : fechaFormateada;
            var fecha = prompt("Cambiar fecha de " + usuario, fechaBase);

            if (fecha == null || fecha.trim() === "") {
                return;
            }

            $.ajax({
                type: "POST",
                url: "pedidos.php",
                data: { fecha: fecha, id_fecha: usuario },
                success: function (result) {
                    $("body").html(result);
                }
            })
        }

        function truncate() {
            $.ajax({
                type: "POST",
                url: "pedidos.php",
                data: { tabla_pedidos: 1 },
                success: function (result) {
                    $("body").html(result);
                }
            });
        }

        function actualizar_cantidad_input(id, precio, input) {
            var nuevaCantidad = input.value;

            if (nuevaCantidad === "" || isNaN(nuevaCantidad) || parseFloat(nuevaCantidad) <= 0) {
                alert("Ingrese una cantidad válida");
                input.focus();
                return;
            }

            $.ajax({
                type: "POST",
                url: "pedidos.php",
                data: {
                    actualizar_cantidad: 1,
                    id_pedido_cantidad: id,
                    nueva_cantidad: nuevaCantidad
                },
                success: function (result) {
                    $("body").html(result);
                }
            });
        }

        function cambiar_delivery(usuario, tipo) {
            $.ajax({
                type: "POST",
                url: "pedidos.php",
                data: {
                    actualizar_delivery: 1,
                    usuario_delivery: usuario,
                    tipo_delivery: tipo
                },
                success: function (result) {
                    $("body").html(result);
                }
            });
        }

        function cambiar_metodo_pago(usuario, metodo, total, fecha) {
            if (metodo === "fiado") {
                var saldoPendiente = prompt("Saldo pendiente", total);

                if (saldoPendiente == null || saldoPendiente.trim() === "") {
                    return;
                }

                if (isNaN(saldoPendiente) || parseFloat(saldoPendiente) < 0) {
                    alert("Saldo pendiente inválido");
                    return;
                }

                $.ajax({
                    type: "POST",
                    url: "pedidos.php",
                    data: {
                        actualizar_metodo_pago: 1,
                        usuario_metodo_pago: usuario,
                        metodo_pago_valor: metodo,
                        saldo_pendiente_pago: saldoPendiente,
                        fecha_credito: fecha
                    },
                    success: function (result) {
                        $("body").html(result);
                    }
                });
            } else {
                $.ajax({
                    type: "POST",
                    url: "pedidos.php",
                    data: {
                        actualizar_metodo_pago: 1,
                        usuario_metodo_pago: usuario,
                        metodo_pago_valor: metodo
                    },
                    success: function (result) {
                        $("body").html(result);
                    }
                });
            }
        }

    </script>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            background: #f4f7fb;
            color: #1f2937;
            padding: 20px;
        }

        a {
            text-decoration: none;
        }

        .main-panel {
            max-width: 1200px;
            margin: 0 auto;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topbar-left img {
            border-radius: 10px;
            object-fit: cover;
        }

        .topbar-text h2 {
            font-size: 22px;
            color: #111827;
            margin-bottom: 4px;
        }

        .topbar-text p {
            font-size: 13px;
            color: #6b7280;
        }

        .topbar-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .topbar-actions img {
            width: 42px;
            height: 42px;
            padding: 8px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .topbar-actions img:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
        }

        .page-title {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 25px;
        }

        .search-module {
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            padding: 25px;
            margin-bottom: 30px;
        }

        .search-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            align-items: start;
        }

        .field-card {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 18px;
        }

        .field-card label {
            display: block;
            font-size: 14px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 8px;
        }

        .field-card input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            outline: none;
            font-size: 15px;
            background: #fff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .field-card input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .search-results-box {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            min-height: 180px;
            padding: 15px;
            box-shadow: inset 0 0 0 1px #f3f4f6;
        }

        .results-title {
            display: block;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: bold;
            color: #374151;
        }

        #ventana_buscador,
        #ventana_usuario {
            width: 100%;
        }

        #ventana_buscador {
            display: none;
        }

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

        .order-total-row td {
            background: #f8fafc;
            font-weight: bold;
            border-top: 2px solid #dbeafe;
        }

        .order-total-amount {
            color: #2563eb;
            font-weight: bold;
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

        .actions-panel {
            text-align: center;
            margin-top: 30px;
        }

        .btn-danger {
            background: #dc2626;
            color: white;
            border: none;
            padding: 12px 22px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 15px;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
            transition: transform 0.2s ease, opacity 0.2s ease;
        }

        .btn-danger:hover {
            transform: translateY(-1px);
            opacity: 0.92;
        }

        hr {
            border: none;
            height: 1px;
            background: #e5e7eb;
            margin: 18px 0;
        }

        @media (max-width: 768px) {
            .search-grid {
                grid-template-columns: 1fr;
            }

            .page-title {
                font-size: 22px;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .topbar-actions {
                width: 100%;
                justify-content: flex-start;
            }

            .order-meta {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>




</head>

<body onload="mostrar_lista_pedidos()">

    <div class="main-panel">
        <div class="topbar">
            <div class="topbar-left">
                <a href="admin">
                    <img src="imagenes/logo.jpeg" style="height:55px; width:55px;">
                </a>
                <div class="topbar-text">
                    <h2>Panel de Pedidos</h2>
                    <p>Módulo para agregar y visualizar pedidos</p>
                </div>
            </div>

            <div class="topbar-actions">
                <a href="asi_sistema/info/pagos">
                    <img src="imagenes/pago.png" alt="Pagos">
                </a>

                <a href="asi_sistema/info/info_ventas.php">
                    <img src="https://elpollovolantuso.com/imagenes/historial.png" alt="Historial">
                </a>
            </div>
        </div>

       

        <?php
        error_reporting(0);
        include("conexion.php");
        ini_set('date.timezone', 'America/Guayaquil');

        setlocale(LC_ALL, "es_ES");
        strftime("%A %d de %B del %Y");

        $fecha = date("Y-m-d");
        $hora = date("G:i:s");

        function limpiar($conexion, $valor) {
            return mysqli_real_escape_string($conexion, trim($valor));
        }

        // cambiar fecha por usuario
        if ($_POST['id_fecha'] != "") {
            $usuario_fecha = limpiar($conexion, $_POST['id_fecha']);
            $nueva_fecha = limpiar($conexion, $_POST['fecha']);
            mysqli_query($conexion, "UPDATE pedidos SET fecha='$nueva_fecha' WHERE usuario='$usuario_fecha' AND estado!='2' AND estado!='10'");
        }

        // insertar pedido
        if ($_POST['producto'] != "") {
            $usuario = ucfirst(limpiar($conexion, $_POST['usuario']));
            $producto = limpiar($conexion, $_POST['producto']);
            $cantidad = floatval($_POST['cantidad']);
            $precio = floatval($_POST['precio']);
            $total = floatval($_POST['total']);

            mysqli_query($conexion, "INSERT INTO pedidos (`usuario`,`producto`,`cantidad`,`precio`,`total`,`estado`,`delivery`,`metodo_pago`,`fecha`,`hora`) VALUES ('$usuario','$producto','$cantidad','$precio','$total','0','default','default','$fecha','$hora')");
        }

     if (!empty($_POST['registrar_venta_y_listo']) && !empty($_POST['usuario_pedido_listo'])) {
    $usuario_listo = ucfirst(limpiar($conexion, $_POST['usuario_pedido_listo']));
    $total_general_venta = isset($_POST['total_general_venta']) ? floatval($_POST['total_general_venta']) : 0;

    // Obtener los pedidos pendientes del usuario
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
        $detalle = $fila['producto'] . " x" . $fila['cantidad'] . " - $" . number_format($fila['precio'],2) . " = $" . number_format($fila['total'],2);
        $productos_array[] = $detalle;

        $cantidad_total += intval($fila['cantidad']);
        $total_general += floatval($fila['total']);

        // Tomar delivery y metodo_pago del primer pedido
        $delivery = $fila['delivery'];
        $metodo_pago = $fila['metodo_pago'];
    }

    if (count($productos_array) > 0) {
        // Agrupar todos los productos en un solo string
        $productos_implode = mysqli_real_escape_string($conexion, implode("\n", $productos_array));

        // Insertar en tabla ventas
       $result= mysqli_query($conexion, "
            INSERT INTO pedidos (usuario, producto, cantidad, precio, total, estado, delivery, metodo_pago, fecha, hora)
            VALUES ('$usuario_listo','$productos_implode','$cantidad_total','$total_general','$total_general','10','$delivery','$metodo_pago','$fecha','$hora')
        ");
        // Cambiar estado de los pedidos a 10 (ya registrados)
        mysqli_query($conexion, "UPDATE pedidos SET estado='10' WHERE usuario='$usuario_listo' AND estado!='2' AND estado!='10'");

        //ahora borra el pedido igresado en pedidos y mediante un trigger registra la venta en una sola fila en la tabla ventas.

        $registrar_venta=mysqli_query($conexion,"DELETE FROM pedidos WHERE usuario='$usuario_listo' AND estado='10'");

        
    }

    echo "✅ Pedido listo / Registrar venta realizado para $usuario_listo";

 

if (!$result) {
    echo "Error MySQL: " . mysqli_error($conexion);
} else {
    echo "Insert exitoso!";
}
}
        // eliminar producto (estado 2)
        if ($_POST['eliminar_producto_estado'] != "" && $_POST['usuario_eliminar'] != "" && $_POST['producto_eliminar'] != "") {
            $usuario_eliminar = limpiar($conexion, $_POST['usuario_eliminar']);
            $producto_eliminar = limpiar($conexion, $_POST['producto_eliminar']);
            mysqli_query($conexion, "UPDATE pedidos SET estado='2' WHERE usuario='$usuario_eliminar' AND producto='$producto_eliminar' AND estado!='2' AND estado!='10'");
        }

        // actualizar cantidad y total por id
        if ($_POST['actualizar_cantidad'] != "" && $_POST['id_pedido_cantidad'] != "" && $_POST['nueva_cantidad'] != "") {
            $idPedido = intval($_POST['id_pedido_cantidad']);
            $nuevaCantidad = floatval($_POST['nueva_cantidad']);

            $consultaPedido = mysqli_query($conexion, "SELECT precio FROM pedidos WHERE id='$idPedido' LIMIT 1");
            if ($filaPedido = mysqli_fetch_assoc($consultaPedido)) {
                $precioPedido = floatval($filaPedido['precio']);
                $nuevoTotal = $precioPedido * $nuevaCantidad;
                mysqli_query($conexion, "UPDATE pedidos SET cantidad='$nuevaCantidad', total='$nuevoTotal' WHERE id='$idPedido'");
            }
        }

        // actualizar tipo de entrega por usuario
        if ($_POST['actualizar_delivery'] != "" && $_POST['usuario_delivery'] != "" && $_POST['tipo_delivery'] != "") {
            $usuarioDelivery = limpiar($conexion, $_POST['usuario_delivery']);
            $tipoDelivery = limpiar($conexion, $_POST['tipo_delivery']);
            mysqli_query($conexion, "UPDATE pedidos SET delivery='$tipoDelivery' WHERE usuario='$usuarioDelivery' AND estado!='2' AND estado!='10'");
        }

        // actualizar metodo de pago + fiado
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
        }

        // vaciar tabla
        if ($_POST['tabla_pedidos'] != "") {
            mysqli_query($conexion, "TRUNCATE TABLE pedidos");
        }
        ?>
<div class="search-wrapper">
    <div class="search-module-header" onclick="toggleSearchModule()">
         <h3 class="page-title">Ingresar Pedidos</h3>
        <span id="searchModuleIcon">+</span>
    </div>

    <div class="search-module collapsible-manual" id="searchModule">
        <div class="search-grid">
            <div class="field-card">
                <label for="usuario">Usuario</label>
                <?php if ($_POST['usuario'] == "") { ?>
                    <input type="text"
                           id="usuario"
                           onkeyup="abrirBuscador(); buscar_usuario()"
                           onfocus="abrirBuscador()"
                           onclick="abrirBuscador()"
                           placeholder="Escribe el nombre del usuario">
                <?php } else { ?>
                    <input type="text"
                           id="usuario"
                           onkeyup="abrirBuscador(); buscar_usuario()"
                           onfocus="abrirBuscador()"
                           onclick="abrirBuscador()"
                           value="<?php echo $_POST['usuario'] ?>"
                           onblur="perderFocoproducto()"
                           placeholder="Escribe el nombre del usuario">
                <?php } ?>

                <br><br>

                <label for="producto">Producto</label>
                <input type="text"
                       id="producto"
                       onkeyup="abrirBuscador(); buscar_producto()"
                       onfocus="abrirBuscador()"
                       onclick="abrirBuscador()"
                       placeholder="Busca un producto">
            </div>

            <div class="search-results-box">
                <span class="results-title">Resultados de búsqueda</span>
                <div id="ventana_usuario"></div>
                <div id="ventana_buscador"></div>
            </div>
        </div>
    </div>
</div>

<style>
    .search-wrapper {
        width: 100%;
        margin-bottom: 16px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
    }

    .search-module-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 16px;
        background: #f9fafb;
        cursor: pointer;
        font-weight: 600;
        border-bottom: 1px solid #e5e7eb;
    }

    .search-module-header:hover {
        background: #f3f4f6;
    }

    .collapsible-manual {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.5s ease, padding 0.5s ease;
        padding: 0 15px;
    }

    .collapsible-manual.open {
        max-height: 2000px;
        padding: 15px;
    }
</style>

<script>
function abrirBuscador() {
    const modulo = document.getElementById('searchModule');
    const icono = document.getElementById('searchModuleIcon');
    const wrapper = document.querySelector('.search-wrapper');

    if (modulo && !modulo.classList.contains('open')) {
        modulo.classList.add('open');
    }

    if (icono) {
        icono.textContent = '−';
    }

    if (wrapper) {
        setTimeout(function() {
            wrapper.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }, 50);
    }
}

function cerrarBuscador() {
    const modulo = document.getElementById('searchModule');
    const icono = document.getElementById('searchModuleIcon');

    if (modulo) {
        modulo.classList.remove('open');
    }

    if (icono) {
        icono.textContent = '+';
    }
}

function toggleSearchModule() {
    const modulo = document.getElementById('searchModule');
    const icono = document.getElementById('searchModuleIcon');
    const wrapper = document.querySelector('.search-wrapper');

    if (!modulo) return;

    if (modulo.classList.contains('open')) {
        modulo.classList.remove('open');
        if (icono) icono.textContent = '+';
    } else {
        modulo.classList.add('open');
        if (icono) icono.textContent = '−';

        if (wrapper) {
            setTimeout(function() {
                wrapper.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 50);
        }
    }
}

window.addEventListener('DOMContentLoaded', function() {
    cerrarBuscador();
});
</script>

        <div style="text-align:center" id="lista_pedidos"></div>
<h3 style="text-align:center">Ordenes</h3>
        <div class="orders-section">
    <?php
    $musuario = mysqli_query($conexion, "SELECT DISTINCT usuario FROM pedidos WHERE estado!='2' AND estado!='10' ORDER BY id DESC");
    $index_acordeon = 0;

    while ($usuario = mysqli_fetch_array($musuario)) {

        $usuario_nombre = $usuario['usuario'];

        $consulta_fecha = mysqli_query($conexion, "
            SELECT fecha, hora, delivery, metodo_pago
            FROM pedidos
            WHERE estado!='2' AND estado!='10' AND usuario='$usuario_nombre'
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
            WHERE estado!='2' AND estado!='10' AND usuario='$usuario_nombre'
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

        $accordion_id = "order_content_" . $index_acordeon;
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
                                        style="width: 70px; padding: 6px; text-align:center; border:1px solid #d1d5db; border-radius:8px;"
                                    >
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
</style>

        <div class="actions-panel">
            <button class="btn-danger" onClick="truncate()">Borrar tablas</button>
        </div>
    

</body>

</html>

