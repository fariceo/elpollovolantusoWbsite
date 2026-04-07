<?php

session_start();
include("../../conexion.php");


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


/*cambiar el saldo de la tienda*/
if ($_POST['cambiar_saldo_negocio'] != "") {

	$departamento = $_POST['departamento'];

	$actualzar_saldo = mysqli_query($conexion, "UPDATE finanzas SET $departamento='$_POST[cambiar_saldo_negocio]'");

}


/*suma saldo de la tienda proveniente de pedidos u ordenes*/
if ($_POST['usuario'] != "" && $_POST['cobrar'] != "") {




	$finanzas = mysqli_query($conexion, "SELECT * FROM finanzas");

	while ($muestra_finanzas = mysqli_fetch_array($finanzas)) {


		$negocio_ = $muestra_finanzas['negocio'];
		$diezmo_ = $muestra_finanzas['diezmo'];
		$beneficio_ = $muestra_finanzas['beneficio'];
		$salud_ = $muestra_finanzas['salud'];
		$deudas_ = $muestra_finanzas['deudas'];
		$s_basicos_ = $muestra_finanzas['basicos'];
		$impuestos_ = $muestra_finanzas['impuestos'];
	}

	$_POST['diezmo'];
	$_POST['negocio'];
	//UPDATE `finanzas` SET `negocio` = '0',`diezmo`='0',`beneficio`='0',`salud`='0',`deudas`='0',`basicos`='0',`impuestos`='0' WHERE `finanzas`.`id` = 1;
	/*repartod de beneficios en porcentajetes*/

	//25% para el negocio
	$n = round($_POST['negocio'] * 25 / 100, 2);
	$actualizar_dinero_negocio = $n + $negocio_;

	//10% Diezmo
	$di = round($_POST['negocio'] * 10 / 100, 2);
	//$actualizar_dinero_diezmo=round($_POST['diezmo']+$diezmo_,2);
	$actualizar_dinero_diezmo = $di + $diezmo_;

	//20% beneficio
	$b = round($_POST['negocio'] * 20 / 100, 2);
	//$actualizar_dinero_beneficio=round($_POST['diezmo']+$beneficio_,2);
	$actualizar_dinero_beneficio = $b + $beneficio_;

	//10% Salud
	$s = round($_POST['negocio'] * 10 / 100, 2);
	//$actualizar_dinero_salud=round($_POST['diezmo']+$salud_,2);
	$actualizar_dinero_salud = $s + $salud_;

	//10% Deudas
	$de = round($_POST['negocio'] * 10 / 100, 2);
	//$actualizar_dinero_deudas=round($_POST['diezmo']+$deudas_,2);
	$actualizar_dinero_deudas = $de + $deudas_;

	//10% Servicios basicos
	$s_b = round($_POST['negocio'] * 10 / 100, 2);
	//$actualizar_dinero_s_basicos=round($_POST['diezmo']+$s_basicos_,2);
	$actualizar_dinero_s_basicos = $s_b + $s_basicos_;

	//%15 impuestos
	$im = round($_POST['negocio'] * 15 / 100, 2);
	$actualizar_dinero_impuestos = $im + $impuestos_;

	$actualizar_finanzas = mysqli_query($conexion, "UPDATE finanzas SET negocio='$actualizar_dinero_negocio',diezmo='$actualizar_dinero_diezmo',beneficio='$actualizar_dinero_beneficio',salud='$actualizar_dinero_salud',deudas='$actualizar_dinero_deudas',basicos='$actualizar_dinero_s_basicos',impuestos='$actualizar_dinero_impuestos'");

}

/*cobrar saldo del pedido pendiente de pago*/

if ($_POST['cantidad_pendiente'] != "") {

	//actualiza el estado
	$actualizar_saldo_pendiente = mysqli_query($conexion, "UPDATE ventas SET estado=4 WHERE usuario='$_POST[usuario]' AND fecha='$_POST[fecha_pendiente]' AND id='$_POST[id]'");

	//actualiza saldo pendiente de pago	


}

/*pedido pendiente de pago*/


if ($_POST['pendiente_pago'] != "") {

	//actualiza el estado
	$actualizar_saldo_pendiente = mysqli_query($conexion, "UPDATE ventas SET estado=0 WHERE usuario='$_POST[usuario]' AND fecha='$_POST[fecha_pendiente]'");

	//actualiza saldo pendiente de pago


}

/*eliminar_venta---------eliminara del registro de la tabla ventas en phpmyadmin*/
if ($_POST['eliminar_venta'] != "") {

	$eliminar_venta = mysqli_query($conexion, "DELETE FROM ventas WHERE usuario='$_POST[usuario]' AND fecha='$_POST[fecha]' AND id='$_POST[id]'");
}

/*insertar_deuda*/

if ($_POST['insertar_deuda'] == 1) {

	$deuda = mysqli_query($conexion, "INSERT INTO `saldo_pendiente` (`usuario`, `saldo_pendiente`,`accion`,`fecha`,`hora`) VALUES ('$_POST[usuario]','1','0', '$_POST[fecha]','$_POST[hora]')");
}

/*cambiar saldo deuda cliente*/

if ($_POST['cobrado'] != "") {

	$cobrar_saldo_deuda = mysqli_query($conexion, "UPDATE saldo_pendiente SET saldo_pendiente='$_POST[cantidad]',fecha='$_POST[fecha]',hora='$_POST[hora]' WHERE usuario='$_POST[usuario]'");
}


/*Puntos acumulados del cliente*/



if ($_POST['puntos'] != "") {

	//saldo actual

	$mostrar_puntos = mysqli_query($conexion, "SELECT * FROM saldo_pendiente");

	$_POST['negocio'];
	$porcentaje_puntos = $_POST['negocio'] * 5 / 100;

	$porcentaje = round($porcentaje_puntos, 2);


	while ($puntos = mysqli_fetch_array($mostrar_puntos)) {

		$actualizar_mercoins = $puntos['saldo_pendiente'] + $porcentaje;

		if ($puntos['usuario'] == "$_POST[usuario]") {

			//$actualizar_puntos=mysqli_query($conexion,"UPDATE saldo_pendiente SET saldo_pendiente='$actualizar_mercoins',fecha='$fecha',hora='$hora' WHERE usuario='$_POST[usuario]'");
		} else {

			//$crear_saldo=mysqli_query($conexion,"INSERT INTO `saldo_pendiente` (`usuario`,`saldo_pendiente`,`accion`,`fecha`,`hora`) VALUES ('$_POST[usuario]','$porcentaje','0', '$fecha','$hora')");
		}

	}

	//actualizar saldo




}

//cambiar fecha ventas 

if ($_POST['cambiar_fecha_id'] != "") {

	$cambiar_fecha_ventas = mysqli_query($conexion, "UPDATE ventas SET fecha='$_POST[nueva_fecha]' WHERE id='$_POST[cambiar_fecha_id]' AND usuario='$_POST[usuario]' AND producto='$_POST[producto]'");

}


// finanzas






/*historial de ventas, quien o que producto*/




if ($_POST['buscar'] != "") {



	?>

	<table>
		<tr>
			<td style="width: 100px">
				<h3>Usuario</h3>
			</td>
			<td style="width: 100px">
				<h3>producto</h3>
			</td>
			<td>
				<h3>cant</h3>
			</td>


		</tr>
	</table> <!--buscar informacion de ventas--->
<?php
		$buscar = mysqli_query($conexion, "SELECT * FROM ventas WHERE producto like '%" . $_POST['buscar'] . "%' || usuario like '%" . $_POST['buscar'] . "%' ORDER BY fecha DESC");

		//$query = sprintf("SELECT * FROM ventas WHERE usuario, LIKE %s",  GetSQLValueString("%" . $busqueda . "%", "text"));
	
		//SELECT column1, column2, ..FROM table_name WHERE columnN LIKE pattern;
	







		while ($muestra_busqueda = mysqli_fetch_array($buscar)) {

			?>




<table>
	<tr>

		<td style="width: 100px"
			onClick="cambiar_nombre_usuario('procesar.php','<?php echo $muestra_busqueda['usuario'] ?>')">
			<?php echo $muestra_busqueda['usuario']; ?>
		</td>
		<td style="width: 20px">
			<?php echo $muestra_busqueda['producto']; ?>
		</td>
	</tr>
	<tr>
		<td style="width: 100px"
			onClick="cambiar_fecha('<?php echo $muestra_busqueda['id'] ?>','<?php echo $muestra_busqueda['usuario'] ?>','<?php echo $muestra_busqueda['producto'] ?>')">
			<?php echo $muestra_busqueda['fecha']; ?>
		</td>
		<td style="width: 100px">
			<?php echo $muestra_busqueda['cantidad']; ?>
		</td>
		<td style="width: 100px">
			<?php echo "$ " . $muestra_busqueda['total']; ?>
		</td>
	</tr>

	<!--metodo_pago-->

	<tr style="background:
#BDBDBD">
	<td style="width: 100px; color: <?php echo ($muestra_busqueda['metodo_pago'] == 'default') ? 'green' : 'red'; ?>">
		<?php echo ($muestra_busqueda['metodo_pago'] == 'default') ? 'Efectivo' : 'Fiado'; ?>
	</td>
</tr>
		</table>

		<?php
		}


}
?>


<?php

/*busqueda de deudores*/

if ($_POST['buscar_deudor'] != "") {


	$buscar_deudor = $_POST['buscar_deudor'];

	// Prevenir inyecciones SQL utilizando consultas preparadas o escapando los datos
	$buscar_deudor = mysqli_real_escape_string($conexion, $buscar_deudor);

	$buscar_deudor = mysqli_query($conexion, "SELECT * FROM saldo_pendiente WHERE usuario like '%" . $_POST['buscar_deudor'] . "%' ORDER BY fecha DESC");
	//$buscar_deudor = mysqli_query($conexion, "SELECT * FROM saldo_pendiente WHERE usuario='$_POST[buscar_deudor]'");


	while ($busqueda_deudor = mysqli_fetch_array($buscar_deudor)) {




		?>



		<!--busqueda de usuarios existente para ingresar pedidos--->
		

<?php

				if ($_POST['buscar_usuario'] == 1) {

					if ($_POST['buscar_deudor'] == $busqueda_deudor['usuario']) {
						echo "hay usuario";
					} else {
						echo "no hay ";
					}
					?>



<button onClick="elegir_usuario('<?php echo $busqueda_deudor['usuario'] ?>')">
	<?php echo $busqueda_deudor['usuario']; ?>
</button>
<p>Seleccionar usuario</p>

$ <p style="color:red">
	<?php echo $busqueda_deudor['saldo_pendiente']; ?>
</p>

<?php } ?>

<!--busqueda de deudores en pagos.php--->

<?php if ($_POST['buscar_usuario'] == 0 && $_POST['buscar_usuario_pedido'] != 2) { ?>

<div style="display: flex; align-items: center; justify-content: space-between; background: #fffdf7; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 12px 18px; margin: 8px auto; max-width: 600px; flex-wrap: wrap; transition: transform 0.2s;">
    
    <!-- Usuario -->
    <p 
        style="flex: 1; min-width: 150px; font-weight: 600; color: #333; cursor: pointer; margin: 6px 0;"
        onclick="ver_historial_credito('<?php echo $busqueda_deudor['usuario'] ?>')">
        👤 <?php echo ucfirst($busqueda_deudor['usuario']); ?>
    </p>

    <!-- Saldo pendiente -->
    <p 
        style="width: 100px; text-align: center; color: <?php echo ($busqueda_deudor['saldo_pendiente'] > 0) ? '#e67e22' : '#27ae60'; ?>; font-weight: bold; cursor: pointer;"
        onclick="actualizar_cantidad('<?php echo $busqueda_deudor['usuario'] ?>')">
        💰 <?php echo "$ " . number_format($busqueda_deudor['saldo_pendiente'], 2); ?>
    </p>

    <!-- Acción -->
    <p 
        style="width: 100px; text-align: center; font-weight: 600; cursor: pointer;"
        onclick="accion('<?php echo $busqueda_deudor['usuario'] ?>')">
        <?php
        if ($busqueda_deudor['accion'] == 0) {
            echo "<span style='color: #2980b9;'>🔵 Sin acción</span>";
        } elseif ($busqueda_deudor['accion'] == 1) {
            echo "<span style='color: #27ae60;'>🟢 Cobrar</span>";
        } elseif ($busqueda_deudor['accion'] == 2) {
            echo "<span style='color: #e74c3c;'>🔴 Pagar</span>";
        }
        ?>
    </p>

    <!-- Eliminar deuda -->
    <p style="width: 60px; text-align: center; margin: 6px 0;">
        <button 
            style="background: #ff4d4d; border: none; color: white; font-weight: bold; border-radius: 8px; padding: 6px 10px; cursor: pointer; transition: background 0.3s;"
            onmouseover="this.style.background='#d93636'"
            onmouseout="this.style.background='#ff4d4d'"
            onclick="eliminar_deuda('<?php echo $busqueda_deudor['id'] ?>')">
            ❌
        </button>
    </p>
</div>




		<?php } ?>

		<?php




		//echo $_POST['pedidos'];
		//////
	}
}
?>

<!---sorteo--->

<?php




//$t=mysql_query("SELECT * FROM ventas WHERE cat=4 and id=FLOOR(RAND()*((SELECT max(id) FROM mitabla WHERE cat=4)-1+1)+1) LIMIT 1");


if ($_POST['sortear'] != "") {

	$mostrar_usuarios = mysqli_query($conexion, "SELECT * FROM ventas");

	while ($usuarios = mysqli_fetch_array($mostrar_usuarios)) {

		$rand = rand(1, $usuarios['id']);

	}

	//usuario_aleatorio
	$mostrar_usuarios_aleatorio = mysqli_query($conexion, "SELECT * FROM ventas where id='$rand'");


	$usuario_aleatorio = mysqli_fetch_assoc($mostrar_usuarios_aleatorio);

	echo $usuario_aleatorio['usuario'];


}



?>




<?php

if ($_POST['buscar_producto'] != "") {

	//$buscar_producto_menu=mysqli_query($conexion,"SELECT * FROM bodega WHERE producto like '%".$_POST['buscar_producto']."%'");
	$buscar_producto_menu = mysqli_query($conexion, "SELECT * FROM menu WHERE producto like '%" . $_POST['buscar_producto'] . "%'");

	while ($producto = mysqli_fetch_array($buscar_producto_menu)) {

		?>
<!--<ul onClick="add_list('<?php // echo //$producto[producto]                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             ?>','<?php //echo $producto[precio]                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             ?>')" style="text-align:center">-->
		<ul style="text-align:center">
			<li style="list-style: none"><a id='<?php echo str_replace(' ', '', $producto['producto'] . "img") ?>'><img
						style='width:100px;height:100px' src="imagenes/<?php echo $producto['img'] ?>"></a>
				<p>
					<?php echo $producto['producto']; ?>
				</p>
				<button
					onClick="ingresar('<?php echo $producto[producto] ?>','<?php echo $producto[precio] ?>')">agregar</button>
			</li>

		</ul>
		<?php



	}


}



?>



<!--metodo de pago-->

<?php

if ($_POST['id_metodo_pago'] != "") {







	//efectivo
	if ($_POST['metodo_pago'] == 1) {
		$pago = 'efectivo';

		$m = mysqli_query($conexion, "UPDATE pedidos SET metodo_pago='$pago' WHERE usuario='$_POST[credito_usuario]'");

	}

	//fiado
	if ($_POST['metodo_pago'] == 2) {
		$pago = 'fiado';
		$m = mysqli_query($conexion, "UPDATE pedidos SET metodo_pago='$pago' WHERE usuario='$_POST[credito_usuario]'  AND id='$_POST[id_metodo_pago]'");

	}
	// comprueba si usuario tiene deuda, sino abre credito	
	$muestra_saldo_pendiente = mysqli_query($conexion, "SELECT * FROM saldo_pendiente WHERE usuario='$_POST[credito_usuario]'");

	while ($saldo_pendiente = mysqli_fetch_array($muestra_saldo_pendiente)) {

		if ($saldo_pendiente['usuario'] == $_POST['credito_usuario']) {
			$usuario_deuda = 1;
			$credito = $saldo_pendiente['saldo_pendiente'];

		} else {
			//abre credito
			$usuario_deuda = 0;

		}

	}


	if ($usuario_deuda == 0) {

		$crea_credito_usuario = mysqli_query($conexion, "INSERT INTO `saldo_pendiente` (`usuario`,`saldo_pendiente`,`accion`,`fecha`,`hora`) VALUES ('$_POST[credito_usuario]','$_POST[saldo_pendiente_pago]','1','$fecha','$hora')");
		$insertar_historial = mysqli_query($conexion, "INSERT INTO `historial_credito` (`usuario`,`saldo`,`saldo_contable`,`concepto`,`fecha`) VALUES ('$_POST[credito_usuario]','$_POST[saldo_pendiente_pago]','$_POST[saldo_pendiente_pago]','fiado','$_POST[fecha]')");

	}
	if ($usuario_deuda == 1) {

		$credito = $credito + $_POST['saldo_pendiente_pago'];


		//aumenta el credito del usuario a la deuda del cliente
		$aumento_credito = mysqli_query($conexion, "UPDATE saldo_pendiente SET saldo_pendiente='$credito' WHERE usuario='$_POST[credito_usuario]'");
		$insertar_historial = mysqli_query($conexion, "INSERT INTO `historial_credito` (`usuario`,`saldo`,`saldo_contable`,`concepto`,`fecha`) VALUES ('$_POST[credito_usuario]','$_POST[saldo_pendiente_pago]','$credito','fiado','$_POST[fecha]')");

	}
	//registra en historial de credito 



	//registra Y ACTUALIZA metodo de pago en la tabla pedidos 
	//$metodo_pago = mysqli_query($conexion, "UPDATE pedidos SET metodo_pago='$pago' WHERE id='$_POST[id_metodo_pago]'");


}


?>






<!---actualiza la deuda de usuario y registra transaccion-->
<?php
if ($_POST['actualizar_cantidad'] != "") {


	$actualizar_deuda = mysqli_query($conexion, "UPDATE saldo_pendiente SET saldo_pendiente='$_POST[actualizar_cantidad]' WHERE usuario='$_POST[usuario]'");

	?>

	<a href="pagos.php"><img src="../../imagenes/logo.jpeg" style="height: 50px;width: 50px"></a>
	<?php

	///historial de transacciones

	$buscar_historial_credito = mysqli_query($conexion, "SELECT * FROM historial_credito WHERE usuario='$_POST[usuario]' ORDER BY id DESC");


	//usuario saldo fecha

	?>
	<br>
	<h3>Usuario</h3>
	<a style='width:200px'>
		<?php echo $_POST['usuario'] ?>
	</a>
	<table>

		<tr>
			<td>

				<?php
				//buscar pedidos de clientes segun fecha dada
				if ($_POST["buscar_pedido"] != "") {

					$buscar_pedido = mysqli_query($conexion, "SELECT * FROM ventas WHERE usuario='$_POST[usuario]' AND fecha='$_POST[buscar_pedido]'");

					while ($pedido = mysqli_fetch_array($buscar_pedido)) {
						?>
						<br>
						<table>
							<tr>
								<td style="background:#F3E2A9">Pedido
									<?php echo $count_pedido += count($pedido['producto']) ?>:
								</td>
								<td>
									<?php echo $pedido['producto'] ?>
								</td>
							</tr>
						</table>
						<?php
					}
					?>


					<hr>
					<br>
					<?php
				}
				?>
			</td>
		</tr>

	</table>

	<?php
	while ($historial_credito = mysqli_fetch_array($buscar_historial_credito)) {
		?>


<table>

			<tr>
				<?php
				if ($historial_credito['saldo'] != 0) {
					?>
					<td style="opacity: 0.9;color:silver">Saldo Fiado : </td>
					<td style="opacity: 0.9;color:silver">Fecha de consumo</td>

					<td style="opacity: 0.9;color:silver">Deuda</td>
				</tr>
				<tr>
					<td style='width:50px'>
						<?php echo "$ " . $historial_credito['saldo'] ?>
					</td>
				<?php } else { ?>
					<td style='width:50px;color:red'>
						<?php echo "$ " . $historial_credito['saldo'] ?>
					</td>
				<?php } ?>
				<td style='width:200px'
					onClick="buscar_pedido('<?php echo $historial_credito['usuario'] ?>','<?php echo $historial_credito['fecha'] ?>')">
					<?php echo $historial_credito['fecha'] ?>
				</td>
				<td style='color:blue'><?php echo $historial_credito['saldo_contable'] ?></td>
				
			</tr>
<tr><td><?php echo "Concepto : ".$historial_credito['concepto'];?></td></tr>

		</table>


		<?php


	}

}
?>


<!---cambiar nombre de usuario--->
<?php
if ($_POST['antiguo_nombre'] != "") {

	$nuevo_nombre_usuario = mysqli_query($conexion, "UPDATE ventas SET usuario='$_POST[cambiar_nombre_usuario]' WHERE usuario='$_POST[antiguo_nombre]'");

	//48.85+13.5=262.35
}
?>


<!--sorteo--->
<?php
///


?>
<?php

////primer premio
/*
$buscar_premio = mysqli_query($conexion, "SELECT FROM temp_sorteo");

while ($premio = mysqli_fetch_all($buscar_premio)) {

	echo "<br>" . $premio['id'];
}*/

////usuarios registrados en base datos para sorteo
$buscar_usuarios = mysqli_query($conexion, "SELECT * FROM ventas");
while ($usuario_aleatorio = mysqli_fetch_array($buscar_usuarios)) {

	$usuario_aleatorio['usuario'];

	$n_usuario += count($usuario_aleatorio['usuario']);


	///

	if ($_POST['numero'] == $n_usuario && $_POST['ganador'] == "") {

		echo $usuario_aleatorio['usuario'];
		//$usuario = $usuario_aleatorio['usuario'];
		//echo "<script>$(\"#usuario\").val($usuario)</script>";
		$ganador = mysqli_query($conexion, "UPDATE `temp_sorteo` SET `usuario_ganador` = '$usuario_aleatorio[usuario]' WHERE `temp_sorteo`.`id` = 1");




	}
	//if ($_POST['numero'] == $n_usuario && $_POST['ganador'] != "") {


}


echo "<script>$(\"#n_usuarios\").val($n_usuario)</script>";
if ($_POST['numero'] == 1) {
	//echo $usuario = $usuario_aleatorio['usuario'];
	//$ganador = mysqli_query($conexion, "INSERT INTO `temp_sorteo` (`usuario_ganador`,`premio`,`fecha`) VALUES ('$usuario','pollo hornado','2023-08-26')");
	$buscar_ganador = mysqli_query($conexion, "SELECT * FROM temp_sorteo");

	while ($ganador = mysqli_fetch_array($buscar_ganador)) {
		echo "<br>" . $ganador["usuario_ganador"];

		echo "<h4>¡ En Hora buena !</h4>";
	}
}
?>


<!---muestra el menu del restaurante en indexbbbbbbbb-->
<div style="overflow-y: scroll;height: 550px" id="contenedor">




	<?php

	if ($_POST['categoria'] == "") {
		$muestra_menu = mysqli_query($conexion, "SELECT * FROM menu WHERE categoria='$categoriasMenu[$menu_aleatorio]' AND estado='1'");
	} else {

		$muestra_menu = mysqli_query($conexion, "SELECT * FROM menu WHERE categoria='$_POST[categoria]' AND estado!=0");
	}
	?>


	<?php
	while ($menu = mysqli_fetch_array($muestra_menu)) {


		?>

		<table>


		</table>


		<li style="text-align: center">
			<hr>
			<h3>
				<?php echo $menu['producto']; ?>
			</h3>

			<p>

				<a id='<?php echo str_replace(' ', '', $menu['producto'] . "img") ?>'><img
						src="imagenes/<?php echo $menu['img'] ?>" onClick="detalles('<?php echo $menu[producto] ?>')"></a>
			</p>

			<!---detalles del plato-->
			<p style="width: 150px;margin-right: auto;margin-left: auto">
				<?php

				///
				$test += count($menu['producto']);

				echo $menu['detalles'];

				?>

			</p>



			<p style="width: 50px;">
				<?php echo "$ " . $menu['precio'] ?>
			</p>
			<button id='<?php echo str_replace(' ', '', $menu['producto']) ?>' style="color: green"
				onClick="agregar('<?php echo $menu['producto'] ?>','<?php echo $_SESSION['usuario'] ?>','<?php echo $menu['precio'] ?>','<?php echo $_POST['categoria'] ?>')">Agregar</button>





			<!--dvvvddvdvdvd-->

			<?php
			if ($_SESSION['usuario'] == 'volantuso') {



				?>
				<button
					onClick="eliminar_producto('<?php echo $menu['id'] ?>','<?php echo $menu[seccion] ?>','<?php echo $menu['img'] ?>')"
					style="color: red">x</button>

			<?php } ?>







			<?php


			$pedidos = mysqli_query($conexion, "SELECT * FROM pedidos WHERE producto='$menu[producto]' AND usuario='$_SESSION[usuario]' AND estado!='2'");
			while ($pedidos = mysqli_fetch_array($pedidos)) {


				if ($menu['producto'] == $pedidos['producto']) {
					echo "<br><a style='color:red'>Agregado</a>";

					?>

					<!--condicion para poder quitar producto-->
					<?php


					?>

					<!--<button style="color: red;margin-left: 50px;" onClick="quitar_producto('<?php //echo $muestra_pedidos['id'] ?>','<?php //echo $_SESSION[usuario] ?>','<?php //echo $_POST[categoria] ?>')">X</button>-->


					<?php


					?>




					<?php
					$t = str_replace(' ', '', $menu['producto'] . "img");



					echo "<script> 
					$('#$t').css('opacity','0.3')
					</script>";

					$s = str_replace(' ', '', $menu['producto']);
					echo "<script> 
					$('#$s').css('display','none');
					</script>";

				} else {

				}
				?>






				<?php


			}

			include("receta.php");

	}

	?>
	</li>





</div>




<?php
/////aqui
/*
if (isset($_POST['usuario']) && !empty(trim($_POST['usuario']))) {
    $_SESSION['usuario'] = trim($_POST['usuario']);
    echo "✅ Usuario guardado correctamente.";
} else {
    echo "⚠️ Ingrese un nombre válido.";
}*/
if (isset($_POST['usuario']) && !empty(trim($_POST['usuario']))) {
    // Si NO hay un usuario en sesión, entonces se permite guardar
    if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
        $_SESSION['usuario'] = trim($_POST['usuario']);
        echo "✅ Usuario guardado correctamente.";
    } 
}
?>

<!--cerrsar session_destroy-->
<?php


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cerrar') {
    session_unset();
    session_destroy();
    echo "Sesión cerrada correctamente.";
} else {
    echo "Acción no permitida.";
}
?>
