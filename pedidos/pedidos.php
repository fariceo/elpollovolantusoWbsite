<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar Pedidos</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

    <script>

        let acordeonAbiertoId = null;
        let acordeonAbiertoScroll = 0;
        function mostrar_lista_pedidos() {
    guardarEstadoAccordion();

    $("#contenedor_lista_pedidos").load("pedidos_lista.php", function() {
        restaurarEstadoAccordion();
        actualizarContadorOrdenes();
    });
}

function guardarEstadoAccordion() {
    const abierto = document.querySelector('.accordion-content[style*="display: block"]');

    if (abierto) {
        acordeonAbiertoId = abierto.id;
        acordeonAbiertoScroll = window.scrollY;
    } else {
        acordeonAbiertoId = null;
    }
}

function restaurarEstadoAccordion() {
    if (acordeonAbiertoId) {
        const contenido = document.getElementById(acordeonAbiertoId);

        if (contenido) {
            contenido.style.display = 'block';

            const header = contenido.previousElementSibling;
            if (header && header.classList.contains('accordion-header')) {
                header.classList.add('active');

                const icono = header.querySelector('.accordion-icon');
                if (icono) {
                    icono.textContent = '−';
                }
            }

            window.scrollTo({
                top: acordeonAbiertoScroll,
                behavior: 'auto'
            });
        }
    }
}

function actualizarContadorOrdenes() {
    const contenedor = document.getElementById("contenedor_lista_pedidos");
    const badge = document.getElementById("contadorOrdenes");

    if (!contenedor || !badge) return;

    // Cuenta acordeones / bloques de pedidos visibles
    let totalOrdenes = contenedor.querySelectorAll('.accordion-header').length;

    // Si no existen acordeones, intenta contar tarjetas alternativas
    if (totalOrdenes === 0) {
        totalOrdenes = contenedor.querySelectorAll('.pedido-card, .pedido-item, .orden-card').length;
    }

    badge.textContent = totalOrdenes;

    if (totalOrdenes > 0) {
        badge.classList.add("activo");
    } else {
        badge.classList.remove("activo");
    }
}

        // buscar producto
        function perderFocoproducto() {
            $("#ventana_buscador").html("").fadeOut();
        }

        function buscar_producto() {
            var valor = $("#producto").val().trim();

            if (valor !== "") {
                $.ajax({
                    type: "POST",
                    url: "pedidos_acciones.php",
                    data: { buscar_producto: valor },
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

        function add_list(e, f) {
            var cantidad = prompt("Cantidad");
        }

        function alPerderFoco() {}

        function buscar_usuario() {
            var usuario = $("#usuario").val();

            var textoConMayuscula = usuario.charAt(0).toUpperCase() + usuario.slice(1);
            $('#usuario').val(textoConMayuscula);

            $.ajax({
                type: "POST",
                url: "pedidos_acciones.php",
                data: { buscar_deudor: usuario, buscar_usuario: 1 },
                success: function(result) {
                    $("#ventana_usuario").html(result);
                }
            });

            var inputUsuario = document.getElementById("usuario");
            if (inputUsuario) {
                inputUsuario.onblur = function() {
                    alPerderFoco();
                }
            }
        }

        function elegir_usuario(e) {
            $("#usuario").val(e);

            $.ajax({
                type: "POST",
                url: "pedidos_acciones.php",
                data: { buscar_deudor: $("#usuario").val(), buscar_usuario: "" },
                success: function(result) {
                    $("#ventana_usuario").html("");
                }
            });
        }

        function ingresar(producto, precio) {
    const usuario = $("#usuario").val().trim();

    if (usuario === "") {
        alert("Introducir Usuario");
        $("#usuario").focus();
        return;
    }

    var cantidad = $("#cantidad_" + producto.replace(/\s/g, '')).val();

    if (cantidad <= 0 || cantidad === "" || isNaN(cantidad)) {
        alert("Ingrese una cantidad válida");
        return;
    }

    var total = parseFloat(cantidad) * parseFloat(precio);

    $.ajax({
        type: "POST",
        url: "pedidos_acciones.php",
        data: {
            usuario: usuario,
            producto: producto,
            cantidad: cantidad,
            precio: precio,
            total: total,
            i: 1
        },
        success: function(result) {
            mostrar_lista_pedidos();
            $("#producto").val("");
            $("#ventana_buscador").html("").fadeOut();
            abrirOrdersModule();
        }
    });

    $.ajax({
        type: "POST",
        url: "pedidos_acciones.php",
        data: { producto: producto, cantidad: cantidad, restar_stock: 1 },
        success: function(result) {}
    });
}

        function listo(usuario, total) {
            if (!confirm("¿Marcar pedido listo y registrar venta de " + usuario + "?")) return;

            $.ajax({
                type: "POST",
                url: "pedidos_acciones.php",
                data: {
                    registrar_venta_y_listo: 1,
                    usuario_pedido_listo: usuario,
                    total_general_venta: total
                },
                success: function(result) {
                    mostrar_lista_pedidos();
                }
            });
        }

        function eliminar_producto(usuario, producto) {
            if (!confirm("¿Cambiar estado a 2 para este producto?\n\nUsuario: " + usuario + "\nProducto: " + producto)) {
                return;
            }

            $.ajax({
                type: "POST",
                url: "pedidos_acciones.php",
                data: {
                    eliminar_producto_estado: 1,
                    usuario_eliminar: usuario,
                    producto_eliminar: producto
                },
                success: function(result) {
                    mostrar_lista_pedidos();
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
                url: "pedidos_acciones.php",
                data: { fecha: fecha, id_fecha: usuario },
                success: function(result) {
                    mostrar_lista_pedidos();
                }
            })
        }

        function truncate() {
            if (!confirm("¿Seguro que deseas borrar todos los pedidos?")) return;

            $.ajax({
                type: "POST",
                url: "pedidos_acciones.php",
                data: { tabla_pedidos: 1 },
                success: function(result) {
                    mostrar_lista_pedidos();
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
                url: "pedidos_acciones.php",
                data: {
                    actualizar_cantidad: 1,
                    id_pedido_cantidad: id,
                    nueva_cantidad: nuevaCantidad
                },
                success: function(result) {
                    mostrar_lista_pedidos();
                }
            });
        }

        function cambiar_delivery(usuario, tipo) {
            $.ajax({
                type: "POST",
                url: "pedidos_acciones.php",
                data: {
                    actualizar_delivery: 1,
                    usuario_delivery: usuario,
                    tipo_delivery: tipo
                },
                success: function(result) {
                    mostrar_lista_pedidos();
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
                    url: "pedidos_acciones.php",
                    data: {
                        actualizar_metodo_pago: 1,
                        usuario_metodo_pago: usuario,
                        metodo_pago_valor: metodo,
                        saldo_pendiente_pago: saldoPendiente,
                        fecha_credito: fecha
                    },
                    success: function(result) {
                        mostrar_lista_pedidos();
                    }
                });
            } else {
                $.ajax({
                    type: "POST",
                    url: "pedidos_acciones.php",
                    data: {
                        actualizar_metodo_pago: 1,
                        usuario_metodo_pago: usuario,
                        metodo_pago_valor: metodo
                    },
                    success: function(result) {
                        mostrar_lista_pedidos();
                    }
                });
            }
        }

        function abrirBuscador() {
    const searchModule = document.getElementById('searchModule');
    const searchIcon = document.getElementById('searchModuleIcon');
    const searchWrapper = document.querySelector('.search-wrapper');

    const ordersModule = document.getElementById('ordersModule');
    const ordersIcon = document.getElementById('ordersModuleIcon');

    // Abrir buscador
    if (searchModule && !searchModule.classList.contains('open')) {
        searchModule.classList.add('open');
    }

    if (searchIcon) {
        searchIcon.textContent = '−';
    }

    // Cerrar órdenes
    if (ordersModule) {
        ordersModule.classList.remove('open');
    }

    if (ordersIcon) {
        ordersIcon.textContent = '+';
    }

    if (searchWrapper) {
        setTimeout(function() {
            searchWrapper.scrollIntoView({
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

function abrirOrdersModule() {
    const ordersModule = document.getElementById('ordersModule');
    const ordersIcon = document.getElementById('ordersModuleIcon');
    const ordersWrapper = document.querySelector('.orders-wrapper');

    const searchModule = document.getElementById('searchModule');
    const searchIcon = document.getElementById('searchModuleIcon');

    // Abrir órdenes
    if (ordersModule && !ordersModule.classList.contains('open')) {
        ordersModule.classList.add('open');
    }

    if (ordersIcon) {
        ordersIcon.textContent = '−';
    }

    // Cerrar buscador
    if (searchModule) {
        searchModule.classList.remove('open');
    }

    if (searchIcon) {
        searchIcon.textContent = '+';
    }

    if (ordersWrapper) {
        setTimeout(function() {
            ordersWrapper.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }, 50);
    }
}

function cerrarOrdersModule() {
    const ordersModule = document.getElementById('ordersModule');
    const ordersIcon = document.getElementById('ordersModuleIcon');

    if (ordersModule) {
        ordersModule.classList.remove('open');
    }

    if (ordersIcon) {
        ordersIcon.textContent = '+';
    }
}

function toggleSearchModule() {
    const searchModule = document.getElementById('searchModule');
    const searchIcon = document.getElementById('searchModuleIcon');
    const searchWrapper = document.querySelector('.search-wrapper');

    const ordersModule = document.getElementById('ordersModule');
    const ordersIcon = document.getElementById('ordersModuleIcon');

    if (!searchModule) return;

    if (searchModule.classList.contains('open')) {
        searchModule.classList.remove('open');
        if (searchIcon) searchIcon.textContent = '+';
    } else {
        // Abrir buscador
        searchModule.classList.add('open');
        if (searchIcon) searchIcon.textContent = '−';

        // Cerrar órdenes
        if (ordersModule) ordersModule.classList.remove('open');
        if (ordersIcon) ordersIcon.textContent = '+';

        if (searchWrapper) {
            setTimeout(function() {
                searchWrapper.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 50);
        }
    }
}

function toggleOrdersModule() {
    const ordersModule = document.getElementById('ordersModule');
    const ordersIcon = document.getElementById('ordersModuleIcon');
    const ordersWrapper = document.querySelector('.orders-wrapper');

    const searchModule = document.getElementById('searchModule');
    const searchIcon = document.getElementById('searchModuleIcon');

    if (!ordersModule) return;

    if (ordersModule.classList.contains('open')) {
        ordersModule.classList.remove('open');
        if (ordersIcon) ordersIcon.textContent = '+';
    } else {
        // Abrir órdenes
        ordersModule.classList.add('open');
        if (ordersIcon) ordersIcon.textContent = '−';

        // Cerrar buscador
        if (searchModule) searchModule.classList.remove('open');
        if (searchIcon) searchIcon.textContent = '+';

        if (ordersWrapper) {
            setTimeout(function() {
                ordersWrapper.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 50);
        }
    }
}

window.addEventListener('DOMContentLoaded', function() {
    abrirBuscador();
    cerrarOrdersModule();
});
    </script>

  <style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        font-family: "Inter", Arial, Helvetica, sans-serif;
    }

    :root {
        --bg-main: linear-gradient(135deg, #eef2ff 0%, #f8fafc 45%, #ecfeff 100%);
        --card-bg: rgba(255, 255, 255, 0.92);
        --card-border: rgba(255, 255, 255, 0.65);
        --text-main: #0f172a;
        --text-soft: #64748b;
        --primary: #2563eb;
        --primary-hover: #1d4ed8;
        --success: #10b981;
        --danger: #dc2626;
        --danger-hover: #b91c1c;
        --shadow-sm: 0 8px 20px rgba(15, 23, 42, 0.06);
        --shadow-md: 0 16px 40px rgba(15, 23, 42, 0.10);
        --shadow-lg: 0 20px 55px rgba(37, 99, 235, 0.12);
        --radius-xl: 24px;
        --radius-lg: 18px;
        --radius-md: 14px;
        --radius-sm: 10px;
    }

    body {
        background: var(--bg-main);
        background-attachment: fixed;
        color: var(--text-main);
        padding: 24px 16px 40px;
        min-height: 100vh;
    }

    a {
        text-decoration: none;
    }

    .main-panel {
        max-width: 1250px;
        margin: 0 auto;
    }

    /* =========================
       TOPBAR
    ========================= */
    .topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 28px;
        flex-wrap: wrap;
        gap: 18px;
        padding: 22px 24px;
        background: rgba(255, 255, 255, 0.78);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.65);
        border-radius: 26px;
        box-shadow: var(--shadow-md);
    }

    .topbar-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .topbar-left img {
        border-radius: 18px;
        object-fit: cover;
        box-shadow: 0 8px 22px rgba(0, 0, 0, 0.14);
        border: 3px solid rgba(255, 255, 255, 0.9);
    }

    .topbar-text h2 {
        font-size: 28px;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 4px;
        letter-spacing: -0.4px;
    }

    .topbar-text p {
        font-size: 14px;
        color: var(--text-soft);
    }

    .topbar-actions {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .topbar-actions a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .topbar-actions img {
        width: 52px;
        height: 52px;
        padding: 10px;
        background: rgba(255, 255, 255, 0.92);
        border-radius: 16px;
        box-shadow: var(--shadow-sm);
        border: 1px solid rgba(226, 232, 240, 0.8);
        transition: transform 0.22s ease, box-shadow 0.22s ease, background 0.22s ease;
    }

    .topbar-actions img:hover {
        transform: translateY(-4px) scale(1.03);
        box-shadow: var(--shadow-lg);
        background: #ffffff;
    }

    .page-title {
        text-align: center;
        font-size: 22px;
        font-weight: 800;
        color: var(--text-main);
        margin: 0;
        letter-spacing: -0.4px;
    }

    /* =========================
       WRAPPERS / MODULOS
    ========================= */
    .search-wrapper,
    .orders-wrapper {
        width: 100%;
        margin-bottom: 22px;
        border-radius: var(--radius-xl);
        overflow: hidden;
        background: rgba(255, 255, 255, 0.82);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.65);
        box-shadow: var(--shadow-md);
        transition: transform 0.22s ease, box-shadow 0.22s ease;
    }

    .search-wrapper:hover,
    .orders-wrapper:hover {
        transform: translateY(-2px);
        box-shadow: 0 22px 55px rgba(15, 23, 42, 0.12);
    }

    .search-module-header,
    .orders-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 22px;
        background: linear-gradient(135deg, rgba(248, 250, 252, 0.96), rgba(241, 245, 249, 0.88));
        cursor: pointer;
        font-weight: 700;
        border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        transition: background 0.25s ease;
    }

    .search-module-header:hover,
    .orders-header:hover {
        background: linear-gradient(135deg, rgba(241, 245, 249, 1), rgba(226, 232, 240, 0.92));
    }

    .search-module-header span,
    .orders-header span {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        color: var(--primary);
        border-radius: 999px;
        font-size: 24px;
        font-weight: bold;
        box-shadow: 0 6px 18px rgba(37, 99, 235, 0.12);
        border: 1px solid rgba(226, 232, 240, 0.9);
        flex-shrink: 0;
    }

    .search-module,
    .orders-module {
        background: transparent;
    }

    .collapsible-manual {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.55s ease, padding 0.4s ease;
        padding: 0 18px;
    }

    .collapsible-manual.open {
        max-height: 3000px;
        padding: 22px 18px 22px;
    }

    /* =========================
       GRID / FORMULARIO
    ========================= */
    .search-grid {
        display: grid;
        grid-template-columns: 1.05fr 1fr;
        gap: 24px;
        align-items: start;
    }

    .field-card,
    .search-results-box {
        background: rgba(255, 255, 255, 0.88);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 22px;
        padding: 22px;
        box-shadow: var(--shadow-sm);
    }

    .field-card {
        position: relative;
        overflow: hidden;
    }

    .field-card::before {
        content: "";
        position: absolute;
        top: -60px;
        right: -60px;
        width: 150px;
        height: 150px;
        background: radial-gradient(circle, rgba(37, 99, 235, 0.10), transparent 70%);
        pointer-events: none;
    }

    .field-card label {
        display: block;
        font-size: 13px;
        font-weight: 800;
        color: #334155;
        margin-bottom: 10px;
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }

    .field-card input {
        width: 100%;
        padding: 15px 16px;
        border: 1.5px solid #dbe3ee;
        border-radius: 16px;
        outline: none;
        font-size: 15px;
        background: #ffffff;
        color: #0f172a;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.18s ease;
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.03);
    }

    .field-card input::placeholder {
        color: #94a3b8;
    }

    .field-card input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 5px rgba(37, 99, 235, 0.12);
        transform: translateY(-1px);
    }

    .search-results-box {
        min-height: 240px;
        position: relative;
    }

    .results-title {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
        font-size: 13px;
        font-weight: 800;
        color: #334155;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        background: #eff6ff;
        color: #1d4ed8;
        padding: 8px 14px;
        border-radius: 999px;
        border: 1px solid #dbeafe;
    }

    #ventana_buscador,
    #ventana_usuario {
        width: 100%;
    }

    #ventana_buscador {
        display: none;
    }

    /* =========================
       CONTENEDORES DINÁMICOS
    ========================= */
    #ventana_usuario,
    #ventana_buscador,
    #lista_pedidos,
    #contenedor_lista_pedidos {
        border-radius: 18px;
    }

    #ventana_usuario > *,
    #ventana_buscador > *,
    #contenedor_lista_pedidos > * {
        animation: fadeSlideIn 0.25s ease;
    }

    @keyframes fadeSlideIn {
        from {
            opacity: 0;
            transform: translateY(8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* =========================
       LISTA PEDIDOS
    ========================= */
    #lista_pedidos {
        margin-bottom: 14px;
    }

    #contenedor_lista_pedidos {
        background: rgba(248, 250, 252, 0.82);
        border: 1px dashed rgba(148, 163, 184, 0.35);
        border-radius: 22px;
        padding: 18px;
        min-height: 180px;
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    /* =========================
       BOTÓN BORRAR
    ========================= */
    .actions-panel {
        text-align: center;
        margin-top: 32px;
    }

    .btn-danger {
        background: linear-gradient(135deg, var(--danger), #ef4444);
        color: white;
        border: none;
        padding: 14px 26px;
        border-radius: 16px;
        cursor: pointer;
        font-size: 15px;
        font-weight: 800;
        letter-spacing: 0.2px;
        box-shadow: 0 14px 30px rgba(220, 38, 38, 0.28);
        transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
    }

    .btn-danger:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 38px rgba(220, 38, 38, 0.34);
        opacity: 0.98;
    }

    .btn-danger:active {
        transform: scale(0.98);
    }

    /* =========================
       SCROLLBAR
    ========================= */
    ::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }

    ::-webkit-scrollbar-track {
        background: #e2e8f0;
        border-radius: 20px;
    }

    ::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #94a3b8, #64748b);
        border-radius: 20px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, #64748b, #475569);
    }

    .badge-ordenes {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 30px;
    height: 30px;
    padding: 0 10px;
    margin-left: 10px;
    border-radius: 999px;
    font-size: 14px;
    font-weight: 800;
    background: #e2e8f0;
    color: #334155;
    border: 1px solid #cbd5e1;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
    transition: all 0.25s ease;
    vertical-align: middle;
}

.badge-ordenes.activo {
    background: linear-gradient(135deg, #f59e0b, #f97316);
    color: white;
    border-color: transparent;
    box-shadow: 0 8px 20px rgba(249, 115, 22, 0.28);
    animation: pulseBadge 1.8s infinite;
}

@keyframes pulseBadge {
    0% {
        transform: scale(1);
        box-shadow: 0 8px 20px rgba(249, 115, 22, 0.28);
    }
    50% {
        transform: scale(1.06);
        box-shadow: 0 10px 24px rgba(249, 115, 22, 0.38);
    }
    100% {
        transform: scale(1);
        box-shadow: 0 8px 20px rgba(249, 115, 22, 0.28);
    }
}

    /* =========================
       RESPONSIVE
    ========================= */
    @media (max-width: 992px) {
        .search-grid {
            grid-template-columns: 1fr;
        }

        .topbar-text h2 {
            font-size: 24px;
        }
    }

    @media (max-width: 768px) {
        body {
            padding: 16px 12px 30px;
        }

        .topbar {
            flex-direction: column;
            align-items: flex-start;
            padding: 18px;
            border-radius: 22px;
        }

        .topbar-left {
            width: 100%;
        }

        .topbar-actions {
            width: 100%;
            justify-content: flex-start;
            flex-wrap: wrap;
        }

        .topbar-actions img {
            width: 48px;
            height: 48px;
        }

        .page-title {
            font-size: 20px;
        }

        .search-module-header,
        .orders-header {
            padding: 16px 18px;
        }

        .field-card,
        .search-results-box {
            padding: 18px;
            border-radius: 18px;
        }

        .field-card input {
            padding: 14px 14px;
            font-size: 15px;
        }

        .collapsible-manual.open {
            padding: 18px 14px 18px;
        }

        #contenedor_lista_pedidos {
            padding: 14px;
            border-radius: 18px;
        }

        .btn-danger {
            width: 100%;
            max-width: 340px;
        }
    }

    @media (max-width: 480px) {
        .topbar-left {
            align-items: flex-start;
        }

        .topbar-text h2 {
            font-size: 22px;
            line-height: 1.15;
        }

        .topbar-text p {
            font-size: 13px;
        }

        .page-title {
            font-size: 18px;
        }

        .search-module-header span,
        .orders-header span {
            width: 30px;
            height: 30px;
            font-size: 20px;
        }
    }
</style>
</head>

<body onload="mostrar_lista_pedidos()">

    <div class="main-panel">
        <div class="topbar">
            <div class="topbar-left">
                <a href="../admin">
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

                    <div class="search-results-box">
                        <span class="results-title">Resultados de búsqueda</span>
                        <div id="ventana_usuario"></div>
                        <div id="ventana_buscador"></div>
                    </div>
                </div>
            </div>
        </div>

      <div class="orders-wrapper">
   <div class="orders-header" onclick="toggleOrdersModule()">
    <h3 class="page-title">
        Órdenes 
        <span id="contadorOrdenes" class="badge-ordenes">0</span>
    </h3>
    <span id="ordersModuleIcon">+</span>
</div>

    <div class="orders-module collapsible-manual" id="ordersModule">
        <div style="text-align:center" id="lista_pedidos"></div>
        <div id="contenedor_lista_pedidos"></div>
    </div>
</div>
        <div class="actions-panel">
            <button class="btn-danger" onClick="truncate()">Borrar tablas</button>
        </div>
    </div>

</body>

</html>