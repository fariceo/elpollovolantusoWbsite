<?php
session_start();
include("conexion.php");
//nuevo
// Obtener categoría aleatoria
$categoriaAleatoria = "";
$result = mysqli_query($conexion, "SELECT DISTINCT categoria FROM menu ORDER BY RAND() LIMIT 1");
if ($row = mysqli_fetch_assoc($result)) {
  $categoriaAleatoria = $row['categoria'];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Menú del Restaurante</title>
  <link rel="stylesheet" href="css/index.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


  <script>

    const categoriaInicial = <?= json_encode($categoriaAleatoria ?: '') ?>;
  </script>


  <script src="js/index.js" defer></script>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body data-usuario="<?= isset($_SESSION['usuario']) ? htmlspecialchars($_SESSION['usuario']) : '' ?>">

  <h2 id="textoEncabezado">
    <a href="admin.php"><img id="imgLogo" src="imagenes/logo.jpeg"></a>
    Menú del Restaurante
  </h2>

  
 
  
  <!-- Contenedor botones laterales -->
<div class="botones-laterales">
  <button id="btnMenuHamburguesa">
    <i class="fas fa-utensils"></i>
  </button>

   <!-- Botón lupa -->
  <button id="btnBuscar" style="margin-left:10px; font-size:20px; cursor:pointer;">
  <i class="fas fa-search"></i>
</button>



</div>

  <!-- Input de búsqueda oculto -->
  <div id="contenedorBusqueda">
    <input type="text" id="inputBuscar" placeholder="Escribe el nombre del producto">
  </div>



  <?php if (isset($_SESSION['usuario'])): ?>
    <!-- Usuario logueado -->
    <div class="iconos">
      <a id="cerrarSesion" href="sesion/cerrar_sesion.php">👤 <?= htmlspecialchars($_SESSION['usuario']) ?></a>
      <input type="hidden" id="pedidoId" value="<?= htmlspecialchars($_SESSION['usuario']) ?>">
    </div>

    <?php
    $consulta = $conexion->prepare("SELECT COUNT(*) AS cantidad FROM pedidos WHERE usuario = ? AND estado != 2");
    $consulta->bind_param("s", $_SESSION['usuario']);
    $consulta->execute();
    $res = $consulta->get_result()->fetch_assoc();
    $pendientes = $res['cantidad'];
    ?>

    <div class="carrito">
      <div id="n_productos"><?= $pendientes ?></div>
      <a href="asi_sistema/info/carrito/carrito.php">🛒</a>
    </div>

  <?php else: ?>

    <!-- Formulario para ingresar usuario -->
    <div class="login-id">

      <input type="text" id="pedidoId" placeholder="Ingrese tu ID">
      <button id="guardarUsuario">Intro</button>
      <div id="errorPedidoId"></div>
    </div>

  <?php endif; ?>

  <!-- Menu lateral -->
  <div id="menuLateral">
    <button id="cerrarMenu">&times; Cerrar</button>
    <ul id="listaCategorias"></ul>
  </div>



  <!-- Tabla -->
  <div class="tabla-wrapper" id="zonaProductos">
    <table id="tablaMenu">
        <tbody></tbody>
    </table>
</div>

  <!-- Modal cantidad -->
  <div id="modalCantidad" style="display: none; position: fixed; top: 0; left: 0;
       width: 100%; height: 100%; background: rgba(0,0,0,0.7);
       justify-content: center; align-items: center; z-index: 9999;">

    <div class="modal-inner"
      style="background: white; padding: 20px; border-radius: 10px; text-align: center; width: 300px; max-width: 90%;">
      <h3 id="tituloProducto" style="margin-bottom: 10px;"></h3>
      <p>Precio: $<span id="precioProducto"></span></p>

      <input type="number" id="inputCantidad" value="1" min="1" style="width: 80px; padding: 5px; font-size: 16px;">

      <div style="margin-top: 15px;">
        <button id="btnConfirmarCantidad"
          style="margin-right: 10px; padding: 8px 16px; background: #28a745; color: white; border: none; border-radius: 5px;">
          ✅ Confirmar
        </button>

        <button id="btnCancelarCantidad"
          style="padding: 8px 16px; background: #dc3545; color: white; border: none; border-radius: 5px;">
          ❌ Cancelar
        </button>
      </div>
    </div>
  </div>





  <style>
    /* Contenedor fijo con transición para ocultar */
    #contactoFijo {
      position: fixed;
      bottom: 20px;
      right: 20px;
      display: flex;
      flex-direction: row;
      gap: 10px;
      z-index: 9999;
      transition: transform 0.3s ease, opacity 0.3s ease;
    }

    #contactoFijo.oculto {
      transform: translateY(100px);
      opacity: 0;
      pointer-events: none;
    }
  </style>


  <!-- MODAL DETALLES PRODUCTO -->

  <div id="modalDetalles" style="
    display:none;
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.7);
    justify-content:center;
    align-items:center;
    z-index:99999;
">

    <div style="
        background:white;
        width:90%;
        max-width:420px;
        height:570px;             /* <-- TAMAÑO FIJO */
        padding:20px;
        border-radius:15px;
        text-align:center;
        position:relative;
        box-sizing:border-box;
        overflow:hidden;          /* Evita que el contenido empuje el tamaño */
    ">

      <!-- BOTÓN CERRAR CIRCULAR -->
      <span id="cerrarDetalles" style="
                position:absolute;
                top:10px;
                right:10px;
                width:38px;
                height:38px;
                background:#ff1a1a;
                color:white;
                border-radius:50%;
                display:flex;
                justify-content:center;
                align-items:center;
                font-size:20px;
                font-weight:bold;
                cursor:pointer;
                box-shadow:0 0 10px rgba(0,0,0,0.5);
                transition:0.2s;
            " onmouseover="this.style.background='#ff4d4d'; this.style.transform='scale(1.12)';"
        onmouseout="this.style.background='#ff1a1a'; this.style.transform='scale(1)';">✖</span>

      <!-- IMAGEN AJUSTADA UNIFORME -->
      <img id="detalleImg" src="" style="
                width:100%;
                height:240px;
                object-fit:cover;      /* Ajusta siempre igual */
                border-radius:12px;
                margin-bottom:15px;
            ">

      <h2 id="detalleNombre"></h2>

      <p id="detalleDescripcion"
        style="font-size:15px; margin:10px 0; padding:0 10px; max-height:90px; overflow-y:auto;">
      </p>

      <p style="font-size:16px; margin-top:15px;">
        <strong>⏱ Tiempo de elaboración:</strong>
        <span id="detalleTiempo"></span>
      </p>

    </div>
  </div>
  <!---Footer-->
  <!-- Modal Información -->
  <div id="modalInfo" class="modal-info">
    <div class="modal-contenido">
      <span id="cerrarInfo" class="cerrar-info">&times;</span>

      <h2>Información</h2>

      <p><strong>📍 Dirección:</strong> Ciudad, Ecuador</p>
      <p><strong>⏰ Horario:</strong> 10:00 AM – 10:00 PM</p>
      <p><strong>📞 Teléfono:</strong> 098 177 0519</p>
      <p><strong>📧 Correo:</strong> info@elpollovolantuso.com</p>

      <p style="margin-top: 10px; font-size: 14px; opacity: 0.8;">
        © <span id="year"></span> El Pollo Volantuso — Todos los derechos reservados.
      </p>
      <script>
        document.getElementById("year").textContent = new Date().getFullYear();
      </script>

    </div>
  </div>
  <footer id="footerSitio">
    <div class="footer-contenido">
      <div class="footer-logo">
        <h2>El Pollo Volantuso</h2>
        <p>Comida al carbón • Delivery • Take Away</p>
      </div>

      <div class="footer-contacto">
        <p>Teléfono: <a href="tel:+593981770519">098 177 0519</a></p>
        <p>Email: <a href="mailto:contacto@elpollovolantuso.com">contacto@elpollovolantuso.com</a></p>
      </div>

      <div class="footer-redes">
        <a href="https://wa.me/593981770519" target="_blank" title="WhatsApp">
          <i class="fab fa-whatsapp"></i>
        </a>
        <a href="https://m.me/tuPagina" target="_blank" title="Messenger">
          <i class="fab fa-facebook-messenger"></i>
        </a>
        <a href="#" target="_blank" title="Instagram">
          <i class="fab fa-instagram"></i>
        </a>
      </div>

      <p class="footer-copy">
        © <span id="footerYear"></span> Todos los derechos reservados.
      </p>
    </div>
  </footer>

  <script>
               // Actualizar automáticamente el año
               document.getElementById("footerYear").textContent = new Date().getFullYear();
  </script>



</body>

</html>