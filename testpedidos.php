<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script>

        //buscar producbbbto
        function perderFocoproducto() {
            $("#ventana_buscador").html("");
        }
function buscar_producto() {
    var valor = $("#producto").val().trim();

    if(valor !== "") {
        $.ajax({
            type: "POST",
            url: "asi_sistema/info/procesar2.php",
            data: { buscar_producto: valor },
            success: function(result) {
                if(result.trim() !== "") {
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
            // alert(e + f);
            var cantidad = prompt("Cantidad");
        }
        /*
        
                function outFocus() {
                    $("#ventana_usuario").html("");
                }*/

        function alPerderFoco() {
            //$("#ventana_usuario").html("");
        }
        function buscar_usuario() {

            //alert($("#producto").val());
            var usuario = $("#usuario").val();

            // var texto = $('#texto').text();
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


            // Asignar el evento onblur dentro del evento onkeyup
            document.getElementById("#usuario").onblur = function () {
                alPerderFoco();
            }
        }










        function elegir_usuario(e) {

            $("#usuario").val(e);

            $.ajax({

                type: "POST",
                url: "asi_sistema/info/procesar2.php",
                // data: { buscar_deudor: $("#usuario").val(), buscar_usuario: "", pedidos: 1, buscar_usuario_pedido: 2 },
                data: { buscar_deudor: $("#usuario").val(), buscar_usuario: "" },
                success: function (result) {
                    $("#ventana_usuario").html(result);
                    $("#ventana_usuario").html("");

                }
            });
        }
        /*
        function mostrar_lista_pedidos() {
    
        $.ajax({
        type: "POST",
        url: "asi_sistema/info/procesar.php",
        data: { mostrar_lista_pedidos: 1 },
        success: function (result) {
        $("body").html(result);
        }
    
        });
        }*/

      function ingresar(producto, precio) {
    // Verificar que el usuario esté ingresado
    if ($("#usuario").val() != "") {

        // Obtener la cantidad del input correspondiente al producto
        var cantidad = $("#cantidad_" + producto.replace(/\s/g, '')).val();

        if (cantidad <= 0) {
            alert("Ingrese una cantidad válida");
            return;
        }

        var total = cantidad * precio;

        // Insertar pedido
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
            success: function(result) {
                $("body").html(result); // Actualiza la lista de pedidos
            }
        });

        // Restar stock
        $.ajax({
            type: "POST",
            url: "asi_sistema/info/procesar2.php",
            data: { producto: producto, cantidad: cantidad, restar_stock: 1 },
            success: function(result) {
                // Aquí puedes agregar feedback si quieres
            }
        });

    } else {
        alert("Introducir Usuario");
        $("#usuario").focus();
    }
}



        function metodo_pago(e, f, g, h) {



            //fiado
            if (e == 2) {

                alert(" metodo pago " + e + " id : " + f + " usuario : " + g + " fecha : " + h)
                var saldo_pendiente = prompt("Saldo pendiente");

                //if(saldo_pendiente!=NULL && saldo_pendiente!="" && !isNam(saldo_pendiente)){
                $.ajax({

                    type: "POST",
                    url: "asi_sistema/info/procesar.php",
                    data: { metodo_pago: e, id_metodo_pago: f, saldo_pendiente_pago: saldo_pendiente, credito_usuario: g, fecha: h },
                    success: function (result) {
                        // $("body").html(result);
                    }
                });



            }
            //efectivo
            if (e == 1) {
                // alert(" metodo pago " + e + " id : " + f + " usuario : " + g + " fecha : " + h)
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

        function listo(e, f) {

            //alert(e + f);
            $.ajax({

                type: "POST",
                url: "asi_sistema/info/procesar.php",
                data: { i: 1, mostrar_lista_pedidos: 1, usuario_pedido: e, productos_pedido: f, cobrar: 1, usuario: 1, negocio: f },
                success: function (result) {
                    //$("body").html(result);
                    // $("#lista_pedidos").html(result);
                    // $("body").html(result);
                }
            });
            $.ajax({

                type: "POST",
                url: "testpedidos.php",
                data: { i: 1 },
                success: function (result) {
                    $("body").html(result);
                    // $("#lista_pedidos").html(result);
                    // $("body").html(result);
                }
            });


            $.ajax({

                type: "POST",
                url: "asi_sistema/info/procesar2.php",
                data: { pedido_listo: 1, usuario_pedido: e },
                success: function (result) {
                    //$("#lista_pedidos").html(result);
                    //$("#lista_pedidos").html(result);
                }
            });
            //mostrar_lista_pedidos();

        }


        function cambiar_fecha(e) {

            // Crear un objeto Date que representa la fecha y hora actuales
            const hoy = new Date();
            // Obtener el año, mes y día
            const año = hoy.getFullYear(); // Obtiene el año completo (ej: 2024)
            const mes = String(hoy.getMonth() + 1).padStart(2, '0'); // Obtiene el mes (0-11) y se suma 1. padStart asegura 2 dígitos
            const día = String(hoy.getDate()).padStart(2, '0'); // Obtiene el día del mes (1-31) y asegura 2 dígitos

            // Formatear la fecha en 'YYYY-MM-DD'
            const fechaFormateada = `${año}-${mes}-${día}`;

            //console.log(fechaFormateada); // Ejemplo de salida: 2024-10-04

            var fecha = prompt("cambiar fecha " + e, fechaFormateada);

            $.ajax({

                type: "POST",
                url: "testpedidos.php",
                data: { fecha: fecha, id_fecha: e },
                success: function (result) {

                    $("body").html(result);
                }
            })
        }



        //vaciar la tabla pedidos
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
</head>

<style>
    div {
        display: inline-table;
    }
</style>

<body onload="mostrar_lista_pedidos()">

    <a href="admin">
        <img src="imagenes/logo.jpeg" style="height: 50px;width: 50px">
    </a>
    <br>

    <br>
    <a href="asi_sistema/info/pagos"><img src="imagenes/pago.png" style="width: 40px;height: 40px"></a>

    <a href="asi_sistema/info/info_ventas.php"><img src="https://elpollovolantuso.com/imagenes/historial.png" style="width: 40px;height: 40px"></a>


    <h3 style="text-align:center">Ingresar Pedidos</h3>
    <br>

    <?php
    include("conexion.php");

    ?>
    <!--fecha-->
    <?php
    ini_set('date.timezone', 'America/Guayaquil');


    //echo date("F l h:i");
    

    setlocale(LC_ALL, "es_ES");
    strftime("%A %d de %B del %Y");

    //$fecha=strftime("%A %d de %B del %Y");
    $fecha = date("Y-m-d");
    //$fecha='2020-01-13';					
    $hora = date("G:i");



    ?>

    <?php
    /*acciones de funciones*/


    ///cambiar fecha
    if ($_POST['id_fecha'] != "") {

        $fecha_actual = mysqli_query($conexion, "UPDATE pedidos SET fecha='$_POST[fecha]' WHERE usuario='$_POST[id_fecha]'");
    }


    //insertar producto en carrito
    if ($_POST['producto'] != "") {

        $usuario = ucfirst($_POST['usuario']);
        $insertar_pedido = mysqli_query($conexion, "INSERT INTO pedidos (`usuario`,`producto`,`cantidad`,`precio`,`total`,`estado`,`delivery`,`metodo_pago`,`fecha`,`hora`) VALUES ('$usuario','$_POST[producto]','$_POST[cantidad]','$_POST[precio]','$_POST[total]','0','default','default','$fecha','$hora'
        )");
    }

    //Vaciar tabla pedidos
    
    if ($_POST['tabla_pedidos'] != "") {

        $truncatetable = mysqli_query($conexion, "TRUNCATE TABLE pedidos");
    }
    ?>

    <br>
<div style="display:flex; justify-content:center; align-items:flex-start; gap:30px; margin-top:30px;">
    
    <!-- Contenedor de Usuario -->
    <div style="background:#f0f4f8; padding:20px; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.1); width:250px; text-align:center;">
        <p style="font-weight:bold; color:#333;">Usuario</p>
        <?php if ($_POST['usuario'] == "") { ?>
            <input type="text" id="usuario" onKeyup="buscar_usuario()" 
                   style="width:90%; padding:8px; border:1px solid #ccc; border-radius:6px; text-align:center; margin-bottom:15px;">
        <?php } else { ?>
            <input type="text" id="usuario" onKeyup="buscar_usuario()" 
                   value="<?php echo $_POST['usuario'] ?>" onblur="perderFocoproducto()" 
                   style="width:90%; padding:8px; border:1px solid #ccc; border-radius:6px; text-align:center; margin-bottom:15px;">
        <?php } ?>

        <p style="font-weight:bold; color:#333;">Producto</p>
        <input type="text" id="producto" onKeyup="buscar_producto()" 
               style="width:90%; padding:8px; border:1px solid #ccc; border-radius:6px; text-align:center;">
    </div>

    <!-- Ventanas de búsqueda -->
  

        <div id="ventana_buscador" 
             style="display:none">
        </div>
    </div>

</div>








    <div style="text-align:center" id="lista_pedidos"></div>

<?php
$musuario = mysqli_query($conexion, "SELECT DISTINCT usuario FROM pedidos WHERE estado!='2'");
while ($usuario = mysqli_fetch_array($musuario)) {

    echo "<hr style='border:1px solid #ccc; margin:20px 0;'>";
    echo "<h3 style='color:#1a73e8; text-align:center; margin-bottom:10px;'>" . $usuario['usuario'] . "</h3>";

    // Agrupar productos repetidos y sumar cantidades y totales
    $buscar_compra = mysqli_query($conexion, "
        SELECT producto, precio, SUM(cantidad) as cantidad_total, SUM(total) as total_producto
        FROM pedidos 
        WHERE estado!=2 AND usuario='".$usuario['usuario']."'
        GROUP BY producto, precio
    ");

    $total_usuario = 0;
    $productos = [];
    while ($compra = mysqli_fetch_array($buscar_compra)) {
        $productos[] = $compra;
        $total_usuario += $compra['total_producto'];
    }
    ?>

    <table style="margin:auto; border-collapse: collapse; width: 60%; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <thead style="background-color: #f2f2f2; color: #333; text-align: center;">
            <tr>
                <th style="padding: 10px; border-bottom: 1px solid #ddd;">Producto</th>
                <th style="padding: 10px; border-bottom: 1px solid #ddd;">Cantidad</th>
                <th style="padding: 10px; border-bottom: 1px solid #ddd;">Precio</th>
                <th style="padding: 10px; border-bottom: 1px solid #ddd;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $compra): ?>
            <tr style="text-align:center; border-bottom:1px solid #eee;">
                <td style="padding: 8px;"><?php echo $compra['producto'] ?></td>
                <td style="padding: 8px;"><?php echo $compra['cantidad_total'] ?></td>
                <td style="padding: 8px;"><?php echo "$ " . number_format($compra['precio'], 2) ?></td>
                <td style="padding: 8px;"><?php echo "$ " . number_format($compra['total_producto'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background-color:#f9f9f9; font-weight:bold;">
                <td colspan="3" style="text-align:right; padding:10px; border-top:2px solid #ddd;">Total de la compra:</td>
                <td style="padding:10px; border-top:2px solid #ddd; color:#1a73e8;">
                    <?php echo "$ " . number_format($total_usuario, 2); ?>
                </td>
            </tr>
        </tfoot>
    </table>

<?php
}
?>

    <hr>

    <br><br>
    <div style="text-align: center">
        <button onClick="truncate()">Borrar tablas</button>
        <br>

    </div>

</body>

</html>




<!--probando codigo--->