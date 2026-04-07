<?php

session_start();

include("../conexion.php");

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

if ($_POST['pedido_listo'] != "" && $_POST['usuario_pedido'] != "") {
    //if ($usuario['usuario'] == $compra['usuario']) {
    //$t = "<script>$(\"#$id2\").val()</script>";
    //echo $compra['usuario'] . $t . $totalfinal


    $mostrar_pedido = mysqli_query($conexion, "SELECT * FROM pedidos WHERE usuario='$_POST[usuario_pedido]' AND estado!='2'");
    $t[0];
    while ($pedido = mysqli_fetch_array($mostrar_pedido)) {


        $t[] = "$pedido[producto]" . " x " . "$pedido[cantidad]" . " = $ " . "$pedido[total]";
        $quitar_pedidos_usuario_delista = mysqli_query($conexion, "UPDATE pedidos SET estado='2' WHERE usuario='$pedido[usuario]' AND estado!='2'");
        $total_pedido += $pedido['total'];
    }
    $m = implode(" ", $t);
    /*
    echo "<h3>Pedido Ingresado</h3>";
    echo $m;*/

    $insertar_pedido = mysqli_query($conexion, "INSERT INTO pedidos (`usuario`,`producto`,`cantidad`,`precio`,`total`,`estado`,`delivery`,`metodo_pago`,`fecha`,`hora`) VALUES ('$_POST[usuario_pedido]','$m','1','1','$total_pedido','0','default','default','$fecha','$hora')");


    //$venta = mysqli_query($conexion, "DELETE FROM pedidos WHERE usuario='$_POST[usuario_pedido]' AND estado !='2'");
    $venta = mysqli_query($conexion, "DELETE FROM pedidos WHERE usuario='$_POST[usuario_pedido]' AND estado !='2'");

}

/*
if ($_POST['eliminar_pedido_usuario'] != "") {
    $venta = mysqli_query($conexion, "DELETE FROM pedidos WHERE usuario='$_POST[eliminar_pedido_usuario]' AND estado !='2'");

}


*/



?>




<?php
if ($_POST['restar_stock'] != "") {


    $buscar_menu = mysqli_query($conexion, "SELECT * FROM bodega");

    echo $producto = $_POST['producto'];
    while ($menu = mysqli_fetch_array($buscar_menu)) {

        echo "<br><a style='color:blue'>" . $menu['producto'] . "</a><br>";


        //convierte a una array la relacion que tiene con producto
        $array_relacion = explode(",", $menu['relacion']);



        for ($i = 0; $i < count($array_relacion); ++$i) {

            if ($array_relacion[$i] == $producto) {

                echo "<a style='color:red'>" . $array_relacion[$i] . "</a>";
                //restamos de bodega las porciones en stock del producto consumido
                $cantidad = $menu['porciones'] - $_POST['cantidad'];

                $producto_stock = $menu['producto'];
                $restar_stock = mysqli_query($conexion, "UPDATE bodega SET porciones='$cantidad' WHERE producto='$producto_stock'");
            } else {

                echo "<a style='color:silver'>" . $array_relacion[$i] . "</a>";
            }



        }



    }


}


////relacion [producto]
?>


<?php
if ($_POST["agregar_relacion"] != "") {
    $agregar_relacion = mysqli_query($conexion, "UPDATE bodega SET relacion='$_POST[agregar_relacion]' WHERE producto='$_POST[producto]'");

}
?>


<!--Tiempo aproximado de preparacion-->
<?php
if ($_POST['tiempo_aprox_id'] != "") {

    $cambiar_tiempo_aproximado = mysqli_query($conexion, "UPDATE menu SET tiempo_Aprox='$_POST[tiempo_aprox]' WHERE id='$_POST[tiempo_aprox_id]'");


}
?>

<!--descripcion del plato en el menu-->
<?php
if ($_POST['id_detalles_plato'] != "") {
    $descripcion = mysqli_query($conexion, "UPDATE menu SET detalles='$_POST[detalles]' WHERE id='$_POST[id_detalles_plato]'");

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
            <img style='width:120px; height:120px; border-radius:10px; object-fit:cover;' src="imagenes/<?php echo $producto['img'] ?>">
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


<!--consulta de articulos en carrito-->
<?php
if($_POST['articulos']!=""){

    $buscar_productos_pedidos=mysqli_query($conexion,"SELECT  COUNT(producto) as n_articulos FROM pedidos WHERE usuario='$_POST[articulos_usuario]'");

    $productos_pedidos=mysqli_fetch_assoc( $buscar_productos_pedidos);

    echo "<a style='color:red'>+ ".$productos_pedidos["n_articulos"]."</a>" ;
}
?>


<?php
//...rest of your code
/*
if (isset($_POST['get_descripcion_plato'])) {
    $platoId = $_POST['get_descripcion_plato'];

    $detalle_plato = mysqli_query($conexion, "SELECT * FROM menu WHERE id='$platoId'");
    $d = mysqli_fetch_assoc($detalle_plato);
    print_r($d['detalles']);
    exit;
}
//...rest of your code

*/

//...rest of your code

if (isset($_POST['imageName'])) {
    $imageName = $_POST['imageName'];

    // Query the database by image name
    $detalle_plato = mysqli_query($conexion, "SELECT detalles, img FROM menu WHERE img='$imageName'");
    $d = mysqli_fetch_assoc($detalle_plato);

    if ($d) {
        // Prepare the data to be sent as a JSON response
        $response = array(
            'descripcion' => $d['detalles'],
            'imagen' => 'imagenes/' . $d['img']
        );
    } else {
        // Handle the case where no data is found
        $response = array(
            'descripcion' => 'Description not found',
            'imagen' => 'imagenes/default.jpg' // Default image if not found
        );
    }

    // Set the content type to JSON
    header('Content-Type: application/json');
    //send the data as json
    echo json_encode($response);
    exit;
}

?>