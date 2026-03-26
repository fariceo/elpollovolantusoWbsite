<?php
// pedidos.php
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar Pedidos</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <script>
        // =========================
        // CARGAR LISTA DE PEDIDOS
        // =========================
        function mostrar_lista_pedidos() {
            $("#lista_pedidos").html("<p>Cargando pedidos...</p>");

            $.ajax({
                type: "POST",
                url: "pedidos_lista.php",
                success: function(result) {
                    $("#lista_pedidos").html(result);
                },
                error: function(xhr, status, error) {
                    console.log("Error cargando pedidos:", error);
                    console.log(xhr.responseText);
                    $("#lista_pedidos").html("<p>Error al cargar pedidos</p>");
                }
            });
        }

        // =========================
        // BUSCADOR
        // =========================
        function perderFocoproducto() {
            setTimeout(() => {
                $("#ventana_buscador").html("").fadeOut();
                $("#ventana_usuario").html("");
            }, 200);
        }

        function buscar_producto() {
            var valor = $("#producto").val().trim();

            if (valor !== "") {
                $.ajax({
                    type: "POST",
                    url: "pedidos_acciones.php",
                    data: {
                        buscar_producto: valor
                    },
                    success: function(result) {
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

        function buscar_usuario() {
            
            var usuario = $("#usuario").val().trim();
            console.log("Buscando usuario:", usuario);
            $.ajax({
                type: "POST",
                url: "pedidos_acciones.php",
                data: {
                    buscar_deudor: usuario,
                    buscar_usuario: 1
                },
                success: function(result) {
                    $("#ventana_usuario").html(result);
                }
            });
        }

        function elegir_usuario(e) {
            $("#usuario").val(e);
            $("#ventana_usuario").html("");
        }

        // =========================
        // INSERTAR PRODUCTO
        // =========================
        function ingresar(producto, precio) {
            if ($("#usuario").val() != "") {
                var idCantidad = "cantidad_" + producto.replace(/\s/g, '').replace(/[^\w]/g, '');
                var cantidad = $("#" + idCantidad).val();

                if (cantidad <= 0 || cantidad === "" || isNaN(cantidad)) {
                    alert("Ingrese una cantidad válida");
                    return;
                }

                var total = parseFloat(cantidad) * parseFloat(precio);

                $.ajax({
                    type: "POST",
                    url: "pedidos_acciones.php",
                    data: {
                        accion: "insertar",
                        usuario: $("#usuario").val(),
                        producto: producto,
                        cantidad: cantidad,
                        precio: precio,
                        total: total
                    },
                    success: function(result) {
    try {
        let data = (typeof result === "string") ? JSON.parse(result) : result;

        if (data.success) {
            alert("Insertado correctamente");
            $("#producto").val("");
            $("#ventana_buscador").html("").fadeOut();
            mostrar_lista_pedidos();
        } else {
            alert("Error: " + (data.message || "No se pudo insertar"));
        }
    } catch (e) {
        console.log("Respuesta inválida:", result);
        alert("Error procesando respuesta:\n\n" + result);
    }
},
error: function(xhr, status, error) {
    console.log("Error AJAX:", error);
    console.log(xhr.responseText);
},
                    error: function(xhr, status, error) {
                        console.log("Error AJAX:", error);
                        console.log(xhr.responseText);
                    }
                });

            } else {
                alert("Introducir Usuario");
                $("#usuario").focus();
            }
        }

        // =========================
        // PEDIDO LISTO
        // =========================
        function listo(usuario, fecha, delivery, metodo_pago, grupoId) {
            if (!confirm("¿Marcar pedido listo y registrar venta de " + usuario + "?")) return;

            $.ajax({
                type: "POST",
                url: "pedidos_acciones.php",
                data: {
                    accion: "registrar_venta_y_listo",
                    usuario: usuario,
                    fecha: fecha,
                    delivery: delivery,
                    metodo_pago: metodo_pago
                },
                success: function(result) {
                    try {
                        let data = JSON.parse(result);
                        if (data.success) {
                            $("#grupo_" + grupoId).fadeOut(300, function() {
                                $(this).remove();
                            });
                        } else {
                            alert(data.message || "No se pudo marcar como listo");
                        }
                    } catch (e) {
                        console.log(result);
                        alert("Error procesando respuesta");
                    }
                }
            });
        }

        // =========================
        // VACIAR PEDIDOS
        // =========================
        function truncate() {
            if (!confirm("¿Seguro que quieres borrar todos los pedidos?")) return;

            $.ajax({
                type: "POST",
                url: "pedidos_acciones.php",
                data: {
                    accion: "truncate"
                },
                success: function(result) {
                    try {
                        let data = JSON.parse(result);
                        if (data.success) {
                            $("#lista_pedidos").html("<p style='margin-top:20px;'>No hay pedidos registrados.</p>");
                        } else {
                            alert(data.message || "No se pudo vaciar");
                        }
                    } catch (e) {
                        console.log(result);
                        alert("Error procesando respuesta");
                    }
                }
            });
        }

        // =========================
        // MÉTODO DE PAGO
        // =========================
        function cambiar_metodo_pago(usuario, fecha, delivery, metodo, grupoId) {
            let saldoPendiente = 0;

            if (metodo === "fiado") {
                saldoPendiente = prompt("Saldo pendiente", "0");
                if (saldoPendiente == null || saldoPendiente.trim() === "") return;
                if (isNaN(saldoPendiente) || parseFloat(saldoPendiente) < 0) {
                    alert("Saldo pendiente inválido");
                    return;
                }
            }

            $.ajax({
                type: "POST",
                url: "pedidos_acciones.php",
                data: {
                    accion: "actualizar_metodo_pago",
                    usuario: usuario,
                    fecha: fecha,
                    delivery: delivery,
                    metodo_pago: metodo,
                    saldo_pendiente: saldoPendiente
                },
                success: function(result) {
                    try {
                        let data = JSON.parse(result);

                        if (data.success) {
                            actualizarResumenGrupo(grupoId, data.subtotal, data.extra_delivery, data.total_general, data.fecha);
                        } else {
                            alert(data.message || "No se pudo cambiar método de pago");
                        }
                    } catch (e) {
                        console.log(result);
                        alert("Error procesando respuesta");
                    }
                }
            });
        }

        // =========================
        // CAMBIAR DELIVERY
        // =========================
        function cambiar_delivery(usuario, fecha, metodo_pago, delivery, grupoId) {
            $.ajax({
                type: "POST",
                url: "pedidos_acciones.php",
                data: {
                    accion: "actualizar_delivery",
                    usuario: usuario,
                    fecha: fecha,
                    metodo_pago: metodo_pago,
                    delivery: delivery
                },
                success: function(result) {
                    try {
                        let data = JSON.parse(result);

                        if (data.success) {
                            actualizarResumenGrupo(grupoId, data.subtotal, data.extra_delivery, data.total_general, data.fecha);
                        } else {
                            alert(data.message || "No se pudo cambiar delivery");
                        }
                    } catch (e) {
                        console.log(result);
                        alert("Error procesando respuesta");
                    }
                }
            });
        }

        // =========================
        // ELIMINAR PRODUCTO
        // =========================
        function eliminar_producto(id, grupoId) {
            if (!confirm("¿Eliminar este producto del pedido?")) return;

            $.ajax({
                type: "POST",
                url: "pedidos_acciones.php",
                data: {
                    accion: "eliminar_producto",
                    id_pedido: id
                },
                success: function(result) {
                    try {
                        let data = JSON.parse(result);

                        if (data.success) {
                            $("#fila_" + id).remove();

                            if ($("#grupo_" + grupoId + " tbody tr").length === 0) {
                                $("#grupo_" + grupoId).fadeOut(300, function() {
                                    $(this).remove();
                                });
                            } else {
                                actualizarResumenGrupo(grupoId, data.subtotal, data.extra_delivery, data.total_general, data.fecha);
                            }
                        } else {
                            alert(data.message || "No se pudo eliminar");
                        }
                    } catch (e) {
                        console.log(result);
                        alert("Error procesando respuesta");
                    }
                }
            });
        }

        // =========================
        // EDITAR CANTIDAD
        // =========================
        function editar_cantidad(id, precioActual, grupoId) {
            var nuevaCantidad = prompt("Nueva cantidad:");

            if (nuevaCantidad == null || nuevaCantidad.trim() === "") return;
            if (isNaN(nuevaCantidad) || parseFloat(nuevaCantidad) <= 0) {
                alert("Cantidad inválida");
                return;
            }

            $.ajax({
                type: "POST",
                url: "pedidos_acciones.php",
                data: {
                    accion: "editar_cantidad",
                    id_pedido: id,
                    nueva_cantidad: nuevaCantidad
                },
                success: function(result) {
                    try {
                        let data = JSON.parse(result);

                        if (data.success) {
                            $("#fila_" + id).html(`
                                <td>${data.producto}</td>
                                <td>${data.cantidad}</td>
                                <td>$ ${parseFloat(data.precio).toFixed(2)}</td>
                                <td>$ ${parseFloat(data.total).toFixed(2)}</td>
                                <td>
                                    <button class='btn-edit-product' onclick="editar_cantidad('${id}', '${data.precio}', '${grupoId}')">Editar</button>
                                    <button class='btn-delete-product' onclick="eliminar_producto('${id}', '${grupoId}')">Eliminar</button>
                                </td>
                            `);

                            actualizarResumenGrupo(grupoId, data.subtotal, data.extra_delivery, data.total_general, data.fecha);
                        } else {
                            alert(data.message || "No se pudo editar");
                        }
                    } catch (e) {
                        console.log(result);
                        alert("Error procesando respuesta");
                    }
                }
            });
        }

        // =========================
        // CAMBIAR FECHA
        // =========================
        function cambiar_fecha(usuario, fechaActual, delivery, metodo_pago, grupoId) {
            var nuevaFecha = prompt("Nueva fecha (YYYY-MM-DD)", fechaActual);

            if (nuevaFecha == null || nuevaFecha.trim() === "") return;

            $.ajax({
                type: "POST",
                url: "pedidos_acciones.php",
                data: {
                    accion: "cambiar_fecha",
                    usuario: usuario,
                    fecha_actual: fechaActual,
                    nueva_fecha: nuevaFecha,
                    delivery: delivery,
                    metodo_pago: metodo_pago
                },
                success: function(result) {
                    try {
                        let data = JSON.parse(result);

                        if (data.success) {
                            actualizarResumenGrupo(grupoId, data.subtotal, data.extra_delivery, data.total_general, data.fecha);
                        } else {
                            alert(data.message || "No se pudo cambiar fecha");
                        }
                    } catch (e) {
                        console.log(result);
                        alert("Error procesando respuesta");
                    }
                }
            });
        }

        // =========================
        // ACTUALIZAR RESUMEN VISUAL
        // =========================
        function actualizarResumenGrupo(grupoId, subtotal, extraDelivery, totalGeneral, fecha) {
            $("#subtotal_" + grupoId).text(parseFloat(subtotal).toFixed(2));
            $("#delivery_" + grupoId).text(parseFloat(extraDelivery).toFixed(2));
            $("#total_" + grupoId).text(parseFloat(totalGeneral).toFixed(2));
            $("#miniinfo_" + grupoId).text(`Fecha: ${fecha} | Total: $ ${parseFloat(totalGeneral).toFixed(2)}`);
            $("#fecha_" + grupoId).text(fecha);
        }

        // =========================
        // ACORDEÓN
        // =========================
        function toggleAccordion(id, element) {
            const contenidoActual = document.getElementById(id);
            const todos = document.querySelectorAll('.accordion-content');
            const iconos = document.querySelectorAll('.accordion-icon');
            const headers = document.querySelectorAll('.accordion-header');

            todos.forEach(function(item) {
                if (item.id !== id) item.style.display = 'none';
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

        // =========================
        // BUSCADOR COLAPSABLE
        // =========================
        function abrirBuscador() {
            const modulo = document.getElementById('searchModule');
            const icono = document.getElementById('searchModuleIcon');
            const wrapper = document.querySelector('.search-wrapper');

            if (modulo && !modulo.classList.contains('open')) {
                modulo.classList.add('open');
            }

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

        function cerrarBuscador() {
            const modulo = document.getElementById('searchModule');
            const icono = document.getElementById('searchModuleIcon');

            if (modulo) modulo.classList.remove('open');
            if (icono) icono.textContent = '+';
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
            margin-bottom: 0;
        }

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

        .search-module {
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            padding: 25px;
            margin-bottom: 0;
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



        .accordion-card {
            margin-bottom: 16px;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
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
            display: none;
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

        .order-table th,
        .order-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #eef2f7;
            text-align: center;
            font-size: 14px;
        }

        .btn-delete-product,
        .btn-edit-product,
        .btn-danger,
        .btn-success {
            color: #ffffff;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
            margin: 2px;
        }

        .btn-delete-product {
            background: #ef4444;
        }

        .btn-edit-product {
            background: #2563eb;
        }

        .btn-danger {
            background: #dc2626;
            padding: 12px 22px;
            font-size: 15px;
            display: block;
            margin: 20px auto;
        }

        .btn-success {
            background: #16a34a;
        }

        .summary-box,
        .delivery-box,
        .payment-box {
            margin-top: 16px;
            padding: 14px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
        }

        .summary-box p {
            margin: 4px 0;
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

        .item-buscado,
        .item-usuario {
            padding: 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        .item-buscado:last-child,
        .item-usuario:last-child {
            border-bottom: none;
        }

        .item-buscado button,
        .item-usuario button {
            margin-top: 8px;
            padding: 6px 10px;
            border: none;
            border-radius: 6px;
            background: #2563eb;
            color: white;
            cursor: pointer;
        }

        .item-buscado input {
            width: 80px;
            padding: 5px;
            margin-top: 5px;
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
        }


        /* =========================
   TARJETA BONITA DE USUARIO
========================= */

.resultado-usuario-card {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 18px 18px;
    border-radius: 20px;
    background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
    border: 1px solid #e5e7eb;
    box-shadow: 
        0 10px 25px rgba(0, 0, 0, 0.05),
        0 2px 8px rgba(0, 0, 0, 0.03);
    cursor: pointer;
    overflow: hidden;
    transition: all 0.28s ease;
}

.resultado-usuario-card:hover {
    transform: translateY(-3px) scale(1.01);
    border-color: #93c5fd;
    box-shadow: 
        0 18px 38px rgba(37, 99, 235, 0.14),
        0 4px 14px rgba(37, 99, 235, 0.08);
    background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
}

.usuario-card-glow {
    position: absolute;
    top: -40px;
    right: -40px;
    width: 110px;
    height: 110px;
    background: radial-gradient(circle, rgba(59,130,246,0.18) 0%, rgba(59,130,246,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}

.resultado-usuario-left {
    display: flex;
    align-items: center;
    gap: 14px;
    z-index: 1;
}

.resultado-avatar {
    width: 56px;
    height: 56px;
    border-radius: 18px;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    font-weight: 800;
    box-shadow: 0 10px 22px rgba(37, 99, 235, 0.28);
    flex-shrink: 0;
}

.resultado-avatar span {
    transform: translateY(-1px);
}

.resultado-info {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.resultado-nombre {
    font-size: 17px;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.2px;
}

.resultado-sub {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #6b7280;
    font-weight: 500;
}

.estado-dot {
    width: 9px;
    height: 9px;
    background: #22c55e;
    border-radius: 50%;
    box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.15);
    flex-shrink: 0;
}

.resultado-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 6px;
    z-index: 1;
}

.saldo-label {
    font-size: 12px;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.8px;
}

.resultado-saldo {
    background: linear-gradient(135deg, #dcfce7, #f0fdf4);
    color: #166534;
    font-size: 16px;
    font-weight: 800;
    padding: 9px 14px;
    border-radius: 999px;
    border: 1px solid #bbf7d0;
    min-width: 95px;
    text-align: center;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.7);
}

/* Responsive */
@media (max-width: 600px) {
    .resultado-usuario-card {
        flex-direction: column;
        align-items: flex-start;
        gap: 14px;
    }

    .resultado-right {
        width: 100%;
        align-items: flex-start;
    }

    .resultado-saldo {
        min-width: auto;
    }
}

/*buscar producto*/
/* =========================
   RESULTADOS BONITOS DE PRODUCTOS
========================= */

#ventana_buscador {
    display: flex;
    flex-direction: column;
    gap: 14px;
    width: 100%;
}

.resultado-producto-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
    border: 1px solid #e5e7eb;
    border-radius: 22px;
    padding: 16px;
    box-shadow:
        0 10px 24px rgba(0, 0, 0, 0.05),
        0 3px 10px rgba(0, 0, 0, 0.03);
    transition: all 0.25s ease;
    overflow: hidden;
    position: relative;
}

.resultado-producto-card:hover {
    transform: translateY(-3px);
    border-color: #93c5fd;
    box-shadow:
        0 18px 34px rgba(37, 99, 235, 0.12),
        0 6px 14px rgba(37, 99, 235, 0.06);
    background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
}

.producto-card-top {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 16px;
}

.producto-imagen-wrap {
    width: 72px;
    height: 72px;
    min-width: 72px;
    border-radius: 18px;
    overflow: hidden;
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.7);
}

.producto-imagen {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.producto-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.producto-nombre {
    font-size: 17px;
    font-weight: 800;
    color: #111827;
    line-height: 1.2;
}

.producto-sub {
    font-size: 13px;
    color: #6b7280;
    font-weight: 500;
}

.producto-precio {
    background: linear-gradient(135deg, #dbeafe, #eff6ff);
    color: #1d4ed8;
    font-size: 16px;
    font-weight: 800;
    padding: 9px 14px;
    border-radius: 999px;
    border: 1px solid #bfdbfe;
    min-width: 90px;
    text-align: center;
    white-space: nowrap;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.7);
}

.producto-card-bottom {
    display: flex;
    justify-content: space-between;
    align-items: end;
    gap: 14px;
    flex-wrap: wrap;
}

.cantidad-box {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.cantidad-box label {
    font-size: 13px;
    font-weight: 700;
    color: #374151;
}

.cantidad-box input {
    width: 110px;
    padding: 11px 12px;
    border: 1px solid #d1d5db;
    border-radius: 14px;
    outline: none;
    font-size: 15px;
    background: #fff;
    transition: all 0.2s ease;
}

.cantidad-box input:focus {
    border-color: #60a5fa;
    box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.15);
}

.btn-agregar-producto {
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: #ffffff;
    border: none;
    padding: 12px 18px;
    border-radius: 14px;
    font-size: 14px;
    font-weight: 800;
    cursor: pointer;
    min-width: 130px;
    transition: all 0.22s ease;
    box-shadow: 0 10px 20px rgba(22, 163, 74, 0.22);
}

.btn-agregar-producto:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 26px rgba(22, 163, 74, 0.28);
}

.btn-agregar-producto:active {
    transform: scale(0.98);
}

/* =========================
   ESTADOS VACÍOS
========================= */

.empty-state {
    text-align: center;
    padding: 28px 18px;
    border: 1px dashed #d1d5db;
    border-radius: 18px;
    background: #f9fafb;
}

.empty-icon {
    font-size: 30px;
    margin-bottom: 10px;
}

.empty-title {
    font-size: 16px;
    font-weight: 800;
    color: #111827;
    margin-bottom: 4px;
}

.empty-text {
    font-size: 14px;
    color: #6b7280;
}

/* Responsive */
@media (max-width: 600px) {
    .producto-card-top {
        align-items: flex-start;
    }

    .producto-card-bottom {
        flex-direction: column;
        align-items: stretch;
    }

    .cantidad-box input,
    .btn-agregar-producto {
        width: 100%;
    }

    .producto-precio {
        align-self: flex-start;
    }
} 

/* =========================
   SEARCH WRAPPER BONITO
========================= */

.search-wrapper {
    width: 100%;
    margin-bottom: 24px;
    border-radius: 28px;
    overflow: hidden;
    background: linear-gradient(135deg, #f8fbff 0%, #eef6ff 45%, #fdfdfd 100%);
    border: 1px solid rgba(191, 219, 254, 0.55);
    box-shadow:
        0 18px 40px rgba(37, 99, 235, 0.08),
        0 4px 14px rgba(0, 0, 0, 0.04),
        inset 0 1px 0 rgba(255,255,255,0.65);
    position: relative;
}

/* brillo decorativo */
.search-wrapper::before {
    content: "";
    position: absolute;
    top: -70px;
    right: -70px;
    width: 220px;
    height: 220px;
    background: radial-gradient(circle, rgba(59,130,246,0.13) 0%, rgba(59,130,246,0) 72%);
    border-radius: 50%;
    pointer-events: none;
}

.search-wrapper::after {
    content: "";
    position: absolute;
    bottom: -80px;
    left: -80px;
    width: 240px;
    height: 240px;
    background: radial-gradient(circle, rgba(16,185,129,0.08) 0%, rgba(16,185,129,0) 72%);
    border-radius: 50%;
    pointer-events: none;
}

/* =========================
   HEADER DEL MÓDULO
========================= */

.search-module-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    background: rgba(255, 255, 255, 0.55);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
    cursor: pointer;
    position: relative;
    z-index: 2;
    transition: all 0.25s ease;
}

.search-module-header:hover {
    background: rgba(255, 255, 255, 0.72);
}

.page-title {
    text-align: left;
    font-size: 26px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
    letter-spacing: -0.4px;
}

#searchModuleIcon {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: white;
    font-size: 24px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 22px rgba(37, 99, 235, 0.22);
    transition: all 0.25s ease;
}

.search-module-header:hover #searchModuleIcon {
    transform: scale(1.05) rotate(3deg);
}

/* =========================
   CONTENIDO DEL MÓDULO
========================= */

.search-module {
    background: transparent;
    padding: 26px;
    margin-bottom: 0;
    position: relative;
    z-index: 2;
}

.collapsible-manual {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.45s ease, padding 0.45s ease;
    padding: 0 20px;
}

.collapsible-manual.open {
    max-height: 2200px;
    padding: 20px;
}

.search-grid {
    display: grid;
    grid-template-columns: 1fr 1.15fr;
    gap: 22px;
    align-items: start;
}

/* =========================
   CARD DE INPUTS
========================= */

.field-card {
    background: rgba(255, 255, 255, 0.72);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(226, 232, 240, 0.85);
    border-radius: 24px;
    padding: 24px;
    box-shadow:
        0 10px 26px rgba(0, 0, 0, 0.04),
        inset 0 1px 0 rgba(255,255,255,0.7);
}

.field-card label {
    display: block;
    font-size: 14px;
    font-weight: 800;
    color: #1e293b;
    margin-bottom: 10px;
    letter-spacing: 0.2px;
}

.field-card input {
    width: 100%;
    padding: 14px 16px;
    border: 1px solid #dbe4f0;
    border-radius: 16px;
    outline: none;
    font-size: 15px;
    background: #ffffff;
    color: #111827;
    transition: all 0.22s ease;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.7);
}

.field-card input::placeholder {
    color: #94a3b8;
}

.field-card input:focus {
    border-color: #60a5fa;
    box-shadow:
        0 0 0 4px rgba(96, 165, 250, 0.15),
        0 8px 20px rgba(59, 130, 246, 0.08);
    transform: translateY(-1px);
}

/* =========================
   CARD DE RESULTADOS
========================= */

.search-results-box {
    background: rgba(255, 255, 255, 0.78);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(226, 232, 240, 0.85);
    border-radius: 24px;
    min-height: 260px;
    padding: 22px;
    box-shadow:
        0 10px 26px rgba(0, 0, 0, 0.04),
        inset 0 1px 0 rgba(255,255,255,0.7);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.results-title {
    display: block;
    margin-bottom: 16px;
    font-size: 14px;
    font-weight: 800;
    color: #334155;
    border-bottom: 1px solid #edf2f7;
    padding-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.6px;
}

#ventana_usuario,
#ventana_buscador {
    width: 100%;
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* =========================
   RESPONSIVE
========================= */

@media (max-width: 900px) {
    .search-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {
    .search-wrapper {
        border-radius: 22px;
    }

    .search-module-header {
        padding: 16px 18px;
    }

    .page-title {
        font-size: 22px;
    }

    .search-module {
        padding: 18px;
    }

    .field-card,
    .search-results-box {
        padding: 18px;
        border-radius: 20px;
    }

    #searchModuleIcon {
        width: 38px;
        height: 38px;
        font-size: 20px;
    }
}
    </style>
</head>

<body onload="mostrar_lista_pedidos()">

    <div class="main-panel">
        <div class="topbar">
            <div class="topbar-left">
                <a href="admin">
                    <img src="../imagenes/logo.jpeg" style="height:55px; width:55px;">
                </a>
                <div class="topbar-text">
                    <h2>Panel de Pedidos</h2>
                    <p>Módulo para agregar y visualizar pedidos</p>
                </div>
            </div>

            <div class="topbar-actions">
                <a href="../asi_sistema/info/pagos">
                    <img src="../imagenes/pago.png" alt="Pagos">
                </a>

                <a href="../asi_sistema/info/info_ventas.php">
                    <img src="https://elpollovolantuso.com/imagenes/historial.png" alt="Historial">
                </a>
            </div>
        </div>

        <div class="search-wrapper">
            <div class="search-module-header" onclick="toggleSearchModule()">
                <h3 class="page-title">Ingresar Pedidos</h3>
                <span id="searchModuleIcon">+</span>
            </div>

            <div class="search-module collapsible-manual" id="searchModule">
                <div class="search-grid">
                    <div class="field-card">
                        <label for="usuario">Usuario</label>
                        <input type="text"
                            id="usuario"
                            onkeyup="abrirBuscador(); buscar_usuario()"
                            onfocus="abrirBuscador()"
                            onclick="abrirBuscador()"
                            onblur="perderFocoproducto()"
                            placeholder="Escribe el nombre del usuario">

                        <br><br>

                        <label for="producto">Producto</label>
                        <input type="text"
                            id="producto"
                            onkeyup="abrirBuscador(); buscar_producto()"
                            onfocus="abrirBuscador()"
                            onclick="abrirBuscador()"
                            placeholder="Busca un producto">
                    </div>

                    <div class="search-results-box" style="text-align:center">
                        <span class="results-title">Resultados de búsqueda</span>
                        <div id="ventana_usuario"></div>
                        
                        <div id="ventana_buscador" style=" background:#fff; border:1px solid #ccc; z-index:1000;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div style="text-align:center" id="lista_pedidos"></div>

        <button class="btn-danger" onClick="truncate()">Vaciar pedidos</button>
    </div>

</body>

</html>