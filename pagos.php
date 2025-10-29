<!doctype html>
<html>

<head>

	<title>Pagos</title>
	<meta charset="UTF-8">

	<meta charset="UTF-8" name="viewport" content="width=device-width">

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
	<script>


		$(document).ready(function () { });



		function actualizar_cantidad(e, f, g) {
			//alert(e+f+g)
			var cantidad = prompt("actualiza la cantidad por pagar");

			var calculo = parseFloat(cantidad) + parseFloat(f);
			calculo = parseFloat(calculo);

			$.ajax({

				type: "POST",
				url: "pagos.php",
				data: { algo: 1 },
				success: function (result) {

					//$("body").html(result);

				}

			});


			
			//alert(e+cantidad+g)
			$.ajax({

				type: "POST",
				url: "historial_credito.php",
				//data: { usuario_deudor: e, saldo: calculo, fecha: g, saldo_contable: calculo, fio: 1 },
				data: { usuario_deudor: e, saldo: cantidad, fecha: g, fio: 1 },
				success: function (result) {
					$(".expositor").html(result);

				}

			});

			/*
			$.ajax({

				type: "POST",
				url: "procesar.php",
				data: { usuario: e, actualizar_cantidad: calculo, buscar_deudor: e, buscar_usuario: 1 },
				success: function (result) {
					//$(".expositor").html(result);

				}

			});
*/

			///enviar notificacion de correo del evento 
			$.ajax({

				type: "POST",
				url: "../notificaciones.php",
				data: { usuario_deudor: e, saldo: cantidad, fecha: g, fio: 1 },
				success: function (result) {

					//$("body").html(result);
					//$(".expositor").html(result);

					//$(".expositor").html(result);
				}

			});



		}

		function eliminar_deuda(e, f, g) {

			//alert(e)
			var confirmacion = window.confirm('Estas seguro de eliminar deuda a ' + f + ' ?');


			if (confirmacion == true) {
				$.ajax({

					type: "POST",
					url: "pagos.php",
					data: { peticion: "credito", id_deuda: e ,eliminar_deuda_usuario:f},
					success: function (result) {

						$("body").html(result);
					}

				});



				$.ajax({

					type: "POST",
					url: "historial_credito.php",
					data: { usuario_deudor: f, saldo: 0, fecha: g, fio: 1 },
					success: function (result) {
						//$(".expositor").html(result);

					}

				});

				$.ajax({

					type: "POST",
					url: "procesar.php",
					data: { buscar_deudor: "" },
					success: function (result) {

						//$("body").load("pagos.php");

					}

				});

			}
		}


		function nueva_deuda(e, f) {



			if ($("#deudor").val() != "") {
				var cantidad = prompt("cantidad");

				if (!isNaN(cantidad) && cantidad != "" && cantidad != null) {
					$.ajax({

						type: "POST",
						url: "pagos.php",
						data: { peticion: "credito", cantidad_deuda: cantidad, usuario_deudor: $("#deudor").val() },
						success: function (result) {

							$("body").html(result);
						}

					});

				}

				$.ajax({

					type: "POST",
					url: "procesar.php",
					data: { buscar_deudor: e },
					success: function (result) {

						//$(".expositor").html(result);
					}

				});

				$.ajax({

					type: "POST",
					url: "historial_credito.php",
					data: { usuario_deudor: $("#deudor").val(), saldo: cantidad, fecha: e, saldo_contable: cantidad },
					success: function (result) {


					}

				});


			} else {

				alert("el usuario deudor esta vacio");
			}






		}

		function accion(e) {
			var accion = prompt("Accion a realizar// insertar 1 para cobrar , 2 para pagar.");

			if (accion == 1 || accion == 2 && accion != "" && accion != null && !isNaN(accion)) {
				$.ajax({

					type: "POST",
					url: "pagos.php",
					data: { peticion: "credito", accion: accion, usuario_deudor: e },
					success: function (result) {

						$("body").html(result);
					}

				});

			} else {
				alert("introduce un valor valido. 1 para cobrar , 2 para pagar");
			}


			$.ajax({

				type: "POST",
				url: "procesar.php",
				data: { buscar_deudor: e },
				success: function (result) {

					$(".expositor").html(result);
				}

			});
		}


		function ver_historial_credito(e) {
			//alert(e)
			$.ajax({

				type: "POST",
				url: "procesar.php",
				data: { usuario: e, actualizar_cantidad: 'algo' },
				success: function (result) {

					$("body").html(result);
				}

			});
		}



		function buscar_deudor() {




			$.ajax({
				type: "POST",
				url: "procesar.php",
				data: { buscar_deudor: $("#buscar_deudor").val(), buscar_usuario: 0 },

				success: function (result) {

					$(".expositor").html(result);
				}

			});



		}

		function buscar_pedido(e, f) {
			//alert(e+f)
			$.ajax({
				type: "POST",
				url: "procesar.php",
				data: { usuario: e, buscar_pedido: f, actualizar_cantidad: "algo" },

				success: function (result) {

					$("body").html(result);
				}

			});

		}
		function actualizar_deuda_pendiente(e, f) {
			//alert(e + f)
			var actualizar_deuda_pagar = prompt("deuda");
			$.ajax({

				type: "POST",
				url: "historial_credito.php",
				data: { usuario_deudor: e, saldo_corregir: actualizar_deuda_pagar, id_deuda: f, corregir_deuda: 1 },
				success: function (result) {
					$("body").html(result);

				}

			});

			//ver_historial_credito(usuario_deudor);


		}



	</script>


	<title>administracion</title>


	<style>
		table {
			//display: inline-table;


		}

		.tabla_menu td {
			border: groove;
			background: green;
		}

		.expositor {
			width: 350px;
			height: 100%;
			background: #F5F7D2;
			margin: auto;

		}
	</style>
</head>

<?php

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
$hora = date("G:i:s");



?>

<body>

	<!--logo--->

	<a href="../../admin"><img src="../../imagenes/logo.jpeg" style="height:50px;width:50px"></a>
	<!--acciones de sistema-->
	<div>

		<?php



		//eliminar deuda
		if ($_POST['id_deuda'] != "") {

			$eliminar_deuda = mysqli_query($conexion, "DELETE FROM saldo_pendiente WHERE id='$_POST[id_deuda]'");

			//registro la transaccion del pago total

			//historial credito
			$registro_transaccion=mysqli_query($conexion,"INSERT INTO `historial_credito` ('usuario','saldo','saldo_contable','fecha') VALUES ('$_POST[eliminar_deuda_usuario]','0','0','$fecha')");

			//ventas
			$historial_ventas=mysqli_query($conexion,"INSERT INTO ventas ('usuario','producto','cantidad','precio','total','estado','delivery','metodo_pago','fecha','hora') VALUES ('$_POST[eliminar_deuda_usuario]','Deuda pagada','0','$_POST[saldo_pendiente]','$_POST[saldo_pendiente]','0','default','elimina deuda','$fecha','$hora')");


		}

		/*nuevo deudor*/
		echo $_POST['cantidad_deuda'];
		if ($_POST["usuario_deudor"] != "") {

			$nueva_deuda = mysqli_query($conexion, "INSERT INTO `saldo_pendiente` (`usuario`,`saldo_pendiente`,`accion`,`fecha`,`hora`) VALUES('$_POST[usuario_deudor]','$_POST[cantidad_deuda]','0','$fecha','$hora')");

		}

		if ($_POST['accion'] != "") {

			$cambiar_accion = mysqli_query($conexion, "UPDATE saldo_pendiente SET accion='$_POST[accion]' WHERE usuario='$_POST[usuario_deudor]'");
		}





		?>

	</div>


	<!--informacion de dinero del negocio-->
	<div>

		<?php

		$mostrar_caja = mysqli_query($conexion, "SELECT * FROM finanzas");

		while ($caja = mysqli_fetch_array($mostrar_caja)) {

			$saldo = $caja['negocio'];
		}


		?>
		<p>Saldo : $
			<?php echo $saldo; ?>
			<!---buscador-->
			<img src="../../imagenes/lupa.png" style="width: 20px;height: 20px"><input type="text" id="buscar_deudor"
				placeholder="Buscar" onKeyUp="buscar_deudor()" value="<?php echo $_POST['buscar_deudor'] ?>" />
		</p>
	</div>


	<a href="../../pedidos.php"><img src="../../imagenes/orden.png" style="height:30px;width:30px"></a>

	<a style="color: blue;margin-left: 25px" href="info_ventas"> <a href="info_ventas.php"><img
				src="../../imagenes/historial.png" style="height:30px;width:30px"></a></a>
	<!--saldo por cobrar-->

	<?php

	$muestra_saldo_cobrar = mysqli_query($conexion, "SELECT * FROM saldo_pendiente");

	while ($saldo_pendiente = mysqli_fetch_array($muestra_saldo_cobrar)) {


		if ($saldo_pendiente['accion'] == 1) {

			$saldo_cobrar_ += $saldo_pendiente['saldo_pendiente'];
		}

		if ($saldo_pendiente['accion'] == 2) {

			$saldo_pagar_ += $saldo_pendiente['saldo_pendiente'];
		}

	}



	echo "<a style='color: #AABE2B' href='asi_sistema/info/administracion_financiera'\">cobrar $ " . $saldo_cobrar_ . "</a> / ";
	echo " <a style='color:red'  href='asi_sistema/info/administracion_financiera'\">Deuda ---  $ " . $saldo_pagar_ . "</a> / ";

	?>
	<br>
	<hr>

	<h3 style="text-align: center;color:white;background:black">Cobros & Pagos</h3>

	<hr>
	<div class="expositor">





		<!--credito-->

		<?php

		?>

		<?php
		$credito = mysqli_query($conexion, "SELECT * FROM saldo_pendiente ORDER BY saldo_pendiente DESC");

		while ($muestra_credito = mysqli_fetch_array($credito)) {

			?>

			<table>
				<tr style="padding-top: 35px">
					<td style="width: 75px;" onClick="ver_historial_credito('<?php echo $muestra_credito['usuario'] ?>')">
						<?php echo $muestra_credito['usuario']; ?>
					</td>

					<!--actualizar saldo de deuda-->
					<td style="width: 50px;"
						onClick="actualizar_cantidad('<?php echo $muestra_credito['usuario'] ?>','<?php echo $muestra_credito['saldo_pendiente'] ?>','<?php echo $fecha ?>')">
						<?php echo " $ " . $muestra_credito['saldo_pendiente']; ?>
					</td>

					<!--accion-->
					<td style="width: 50px" onClick="accion('<?php echo $muestra_credito['usuario'] ?>')">
						<?php

						if ($muestra_credito['accion'] == 0) {
							echo "<a style='color:blue'>No accion</a>";
						}



						if ($muestra_credito['accion'] == 1) {
							echo "<a style='color:green'>Cobrar</a>";
						}

						if ($muestra_credito['accion'] == 2) {
							echo "<a style='color:red'>Pagar</a>";
						}
						?>


					</td>
					<!--eliminar_deudas-->
					<td style="width:50px;padding-top: 20px"><button style="color: red;margin-left: 50px;"
							onClick="eliminar_deuda('<?php echo $muestra_credito['id'] ?>','<?php echo $muestra_credito['usuario'] ?>','<?php echo $muestra_credito['saldo_pendiente']?>')">X</button>
					</td>

				</tr>


			</table>




			<?php
		}

		echo "<table><tr><td><input type='text' id='deudor'/></td><td><button onClick=\"nueva_deuda('$fecha')\">Intro</button></td></tr></table>";


		?>


	</div>



</body>

</html>
