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
        ? "<img src='$ruta' style='width:150px; height:120px; object-fit:cover; border-radius:10px;'>"
        : "<div style='width:150px; height:120px; background:#eee; display:flex; align-items:center; justify-content:center; border-radius:10px;'>Sin imagen</div>";

    echo "
    <tr>
        <td style='padding:10px;'>
            <div style='
                text-align:center; 
                border:2px solid #ffc107; 
                border-radius:10px; 
                padding:10px; 
                width:170px; 
                background:white; 
                transition: transform 0.3s, box-shadow 0.3s;
                cursor:pointer;
                box-shadow:2px 2px 6px rgba(0,0,0,0.1);
            ' 
            onmouseover='this.style.transform=\"translateY(-5px)\"; this.style.boxShadow=\"4px 4px 12px rgba(0,0,0,0.2)\";' 
            onmouseout='this.style.transform=\"translateY(0)\"; this.style.boxShadow=\"2px 2px 6px rgba(0,0,0,0.1)\";'>
                <h4 style='margin:5px 0; color:#ff9800;'>$p</h4>
                $imgTag
                <p style='margin:8px 0; font-weight:bold; color:#4caf50;'>$$pr</p>
                <button class='agregarBtn' data-producto='$p' data-precio='$pr' 
                    style='
                        color:#ffc107; 
                        background:white; 
                        border:2px solid #ffc107; 
                        padding:5px 10px; 
                        border-radius:5px; 
                        cursor:pointer;
                        font-weight:bold;
                        transition: all 0.3s;
                    '
                    onmouseover='this.style.background=\"#ffc107\"; this.style.color=\"white\"; this.style.boxShadow=\"0 0 10px #ffc107\";'
                    onmouseout='this.style.background=\"white\"; this.style.color=\"#ffc107\"; this.style.boxShadow=\"none\";'
                >
                    ➕ 🛒 Añadir
                </button>
            </div>
        </td>
    </tr>
    ";
}
?>