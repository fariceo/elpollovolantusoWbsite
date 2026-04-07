<?php
include("../conexion.php");

// Recibir categoría o nombre desde POST
$c = $conexion->real_escape_string($_POST['categoria'] ?? '');
$nombre = $conexion->real_escape_string($_POST['nombre'] ?? '');

// Construir SQL según lo que venga
if ($nombre) {
    $sql = "SELECT producto, precio, detalles, img 
            FROM menu 
            WHERE producto LIKE '%$nombre%' 
            ORDER BY producto ASC";
} else if ($c) {
    $sql = "SELECT producto, precio, detalles, img 
            FROM menu 
            WHERE categoria='$c'";
} else {
    $sql = "SELECT producto, precio, detalles, img 
            FROM menu 
            LIMIT 10";
}

$res = $conexion->query($sql);

// Generar filas de tabla
while ($f = $res->fetch_assoc()) {
    $p = htmlspecialchars($f['producto']);
    $det = htmlspecialchars($f['detalles']);
    $pr = floatval($f['precio']);
    $ruta = "../imagenes/" . $f['img'];

    $imgTag = file_exists($ruta)
        ? "<img src='$ruta' alt='$p' class='producto-img'>"
        : "<div class='producto-img sin-imagen'>Sin imagen</div>";

    echo "
<tr>
    <td style='padding:10px;'>
        <div class='producto-card'>
            $imgTag
            <h4 class='producto-titulo'>$p</h4>
            <p class='producto-precio'>$$pr</p>
            <button class='agregarBtn' data-producto='$p' data-precio='$pr'>
                ➕ 🛒 Añadir
            </button>
        </div>
    </td>
</tr>
";
}
?>