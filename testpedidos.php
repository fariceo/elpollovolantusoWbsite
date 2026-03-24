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

                if (cantidad <= 0) {
                    alert("Ingrese una cantidad válida");
                    return;
                }

                var total = cantidad * precio;

                $.ajax({
                    type: "POST",
                    url: "testpedidos.php",
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

        function metodo_pago(e, f, g, h) {

            if (e == 2) {
                alert(" metodo pago " + e + " id : " + f + " usuario : " + g + " fecha : " + h);
                var saldo_pendiente = prompt("Saldo pendiente");

                $.ajax({
                    type: "POST",
                    url: "asi_sistema/info/procesar.php",
                    data: { metodo_pago: e, id_metodo_pago: f, saldo_pendiente_pago: saldo_pendiente, credito_usuario: g, fecha: h },
                    success: function (result) {
                    }
                });
            }

            if (e == 1) {
                $.ajax({
                    type: "POST",
                    url: "asi_sistema/info/procesar.php",
                    data: { metodo_pago: e, credito_usuario: g, id_metodo_pago: f },
                    success: function (result) {
                    }
                });
            }

            $.ajax({
                type: "POST",
                url: "testpedidos.php",
                data: { metodo_pago: e, id_metodo_pago: f },
                success: function (result) {
                    $("body").html(result);
                }
            });
        }

        function listo(usuario, total) {
            if (!confirm("¿Marcar pedido listo y registrar venta de " + usuario + "?")) {
                return;
            }

            $.ajax({
                type: "POST",
                url: "asi_sistema/info/procesar.php",
                data: {
                    i: 1,
                    mostrar_lista_pedidos: 1,
                    usuario_pedido: usuario,
                    productos_pedido: total,
                    cobrar: 1,
                    usuario: 1,
                    negocio: total
                },
                success: function (result) {
                }
            });

            $.ajax({
                type: "POST",
                url: "asi_sistema/info/procesar2.php",
                data: { pedido_listo: 1, usuario_pedido: usuario },
                success: function (result) {
                }
            });

            $.ajax({
                type: "POST",
                url: "testpedidos.php",
                data: {
                    pedido_listo_usuario: 1,
                    usuario_pedido_listo: usuario
                },
                success: function (result) {
                    $("body").html(result);
                }
            });
        }

        function eliminar_producto(usuario, producto) {
            if (!confirm("¿Cambiar estado a 2 para este producto?\n\nUsuario: " + usuario + "\nProducto: " + producto)) {
                return;
            }

            $.ajax({
                type: "POST",
                url: "testpedidos.php",
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
                url: "testpedidos.php",
                data: { fecha: fecha, id_fecha: usuario },
                success: function (result) {
                    $("body").html(result);
                }
            })
        }

        function truncate() {
            $.ajax({
                type: "POST",
                url: "testpedidos.php",
                data: { tabla_pedidos: 1 },
                success: function (result) {
                    $("body").html(result);
                }
            });
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

        .btn-delete-product {
            background: #ef4444;
            color: #ffffff;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        .btn-delete-product:hover {
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

        <h3 class="page-title">Ingresar Pedidos</h3>

        <?php
        include("conexion.php");
        ?>

        <?php
        ini_set('date.timezone', 'America/Guayaquil');

        setlocale(LC_ALL, "es_ES");
        strftime("%A %d de %B del %Y");

        $fecha = date("Y-m-d");
        $hora = date("G:i");
        ?>

        <?php
        if ($_POST['id_fecha'] != "") {
            $usuario_fecha = mysqli_real_escape_string($conexion, $_POST['id_fecha']);
            $nueva_fecha = mysqli_real_escape_string($conexion, $_POST['fecha']);
            $fecha_actual = mysqli_query($conexion, "UPDATE pedidos SET fecha='$nueva_fecha' WHERE usuario='$usuario_fecha' AND estado!='2'");
        }

        if ($_POST['producto'] != "") {
            $usuario = ucfirst($_POST['usuario']);
            $insertar_pedido = mysqli_query($conexion, "INSERT INTO pedidos (`usuario`,`producto`,`cantidad`,`precio`,`total`,`estado`,`delivery`,`metodo_pago`,`fecha`,`hora`) VALUES ('$usuario','$_POST[producto]','$_POST[cantidad]','$_POST[precio]','$_POST[total]','0','default','default','$fecha','$hora')");
        }

        if ($_POST['pedido_listo_usuario'] != "" && $_POST['usuario_pedido_listo'] != "") {
            $usuario_listo = mysqli_real_escape_string($conexion, $_POST['usuario_pedido_listo']);
            $actualizar_pedido = mysqli_query($conexion, "UPDATE pedidos SET estado='2' WHERE usuario='$usuario_listo' AND estado!='2'");
        }

        if ($_POST['eliminar_producto_estado'] != "" && $_POST['usuario_eliminar'] != "" && $_POST['producto_eliminar'] != "") {
            $usuario_eliminar = mysqli_real_escape_string($conexion, $_POST['usuario_eliminar']);
            $producto_eliminar = mysqli_real_escape_string($conexion, $_POST['producto_eliminar']);
            $eliminar_producto = mysqli_query($conexion, "UPDATE pedidos SET estado='2' WHERE usuario='$usuario_eliminar' AND producto='$producto_eliminar' AND estado!='2'");
        }

        if ($_POST['tabla_pedidos'] != "") {
            $truncatetable = mysqli_query($conexion, "TRUNCATE TABLE pedidos");
        }
        ?>

        <div class="search-module">
            <div class="search-grid">
                <div class="field-card">
                    <label for="usuario">Usuario</label>
                    <?php if ($_POST['usuario'] == "") { ?>
                        <input type="text" id="usuario" onKeyup="buscar_usuario()" placeholder="Escribe el nombre del usuario">
                    <?php } else { ?>
                        <input type="text" id="usuario" onKeyup="buscar_usuario()" value="<?php echo $_POST['usuario'] ?>" onblur="perderFocoproducto()" placeholder="Escribe el nombre del usuario">
                    <?php } ?>

                    <br><br>

                    <label for="producto">Producto</label>
                    <input type="text" id="producto" onKeyup="buscar_producto()" placeholder="Busca un producto">
                </div>

                <div class="search-results-box">
                    <span class="results-title">Resultados de búsqueda</span>
                    <div id="ventana_usuario"></div>
                    <div id="ventana_buscador"></div>
                </div>
            </div>
        </div>

        <div style="text-align:center" id="lista_pedidos"></div>

        <div class="orders-section">
            <?php
            $musuario = mysqli_query($conexion, "SELECT DISTINCT usuario FROM pedidos WHERE estado!='2'");
            while ($usuario = mysqli_fetch_array($musuario)) {

                $usuario_nombre = $usuario['usuario'];

                $consulta_fecha = mysqli_query($conexion, "
                    SELECT fecha, hora
                    FROM pedidos
                    WHERE estado!='2' AND usuario='$usuario_nombre'
                    ORDER BY id DESC
                    LIMIT 1
                ");
                $datos_fecha = mysqli_fetch_array($consulta_fecha);
                $fecha_pedido = $datos_fecha['fecha'] ?? '';
                $hora_pedido = $datos_fecha['hora'] ?? '';

                $buscar_compra = mysqli_query($conexion, "
                    SELECT producto, precio, SUM(cantidad) as cantidad_total, SUM(total) as total_producto
                    FROM pedidos 
                    WHERE estado!=2 AND usuario='$usuario_nombre'
                    GROUP BY producto, precio
                ");

                $total_usuario = 0;
                $productos = [];
                while ($compra = mysqli_fetch_array($buscar_compra)) {
                    $productos[] = $compra;
                    $total_usuario += $compra['total_producto'];
                }

                echo "<div class='order-card'>";
                echo "<h3 class='order-user-title'>" . $usuario_nombre . "</h3>";
                ?>

                <div class="order-meta">
                    <div class="order-date" onclick="cambiar_fecha('<?php echo $usuario_nombre; ?>','<?php echo $fecha_pedido; ?>')">
                        Fecha: <?php echo $fecha_pedido; ?> | Hora: <?php echo $hora_pedido; ?>
                    </div>

                    <div class="order-ready">
                        <label class="ready-label">
                            <input type="checkbox" onchange="listo('<?php echo $usuario_nombre; ?>','<?php echo $total_usuario; ?>')">
                            <span>Pedido listo / Registrar venta</span>
                        </label>
                    </div>
                </div>

                <table class="order-table">
                    <thead>
                        <tr>
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
                                <td><?php echo $compra['producto'] ?></td>
                                <td><?php echo $compra['cantidad_total'] ?></td>
                                <td><?php echo "$ " . number_format($compra['precio'], 2) ?></td>
                                <td><?php echo "$ " . number_format($compra['total_producto'], 2) ?></td>
                                <td>
                                    <button class="btn-delete-product"
                                        onclick="eliminar_producto('<?php echo addslashes($usuario_nombre); ?>','<?php echo addslashes($compra['producto']); ?>')">
                                        -
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="order-total-row">
                            <td colspan="4" style="text-align:right;">Total de la compra:</td>
                            <td class="order-total-amount">
                                <?php echo "$ " . number_format($total_usuario, 2); ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>

                <?php
                echo "</div>";
            }
            ?>
        </div>

        <div class="actions-panel">
            <button class="btn-danger" onClick="truncate()">Borrar tablas</button>
        </div>
    </div>

</body>

</html>