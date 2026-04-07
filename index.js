// ==============================
//      INDEX.JS COMPLETO
// ==============================

window.addEventListener('DOMContentLoaded', () => {
    const modalCant = document.getElementById('modalCantidad');
    const tituloProducto = document.getElementById('tituloProducto');
    const precioProducto = document.getElementById('precioProducto');
    const inputCantidad = document.getElementById('inputCantidad');
    let productoSeleccionado = {};

    // USUARIO DESDE SESIÓN PHP
    const usuarioSesion = document.body.dataset.usuario || "";


    // Mostrar productos por categoría
    // Cargar categorías
    function cargarCategorias() {
        
        fetch('index/obtener_categorias.php')
            .then(res => res.json())
            .then(data => {
                const lista = document.getElementById('listaCategorias');
                lista.innerHTML = '';
                data.forEach(categoria => {
                    const li = document.createElement('li');
                    li.innerHTML = `<button>${categoria}</button>`;
                    li.onclick = () => mostrarPorCategoria(categoria);
                    lista.appendChild(li);
                });

                // Intentar mostrar categoría inicial aleatoria primero
                mostrarPorCategoria(categoriaInicial, data);
            });


    }

    // Mostrar productos por categoría
    function mostrarPorCategoria(categoria) {
    const zonaProductos = document.getElementById('zonaProductos');
    const tbody = document.querySelector('#tablaMenu tbody');
    const encabezado = document.getElementById('textoEncabezado');

    if (!zonaProductos || !tbody || !encabezado) return;

    // Limpiar tabla antes de cargar
    tbody.innerHTML = '';

    // Subir scroll al inicio de productos
    const alturaHeader = encabezado.offsetHeight + 20;
    const posicionTabla = zonaProductos.offsetTop - alturaHeader;

    window.scrollTo({
        top: posicionTabla,
        behavior: 'smooth'
    });

    // Cargar productos de la categoría
    $.post('index/buscar_por_categoria.php', { categoria: categoria }, (html) => {
        tbody.innerHTML = html;

        // Reiniciar scroll interno de la tabla si existe
        zonaProductos.scrollTop = 0;
    }).fail(() => {
        tbody.innerHTML = '<tr><td colspan="5">Error al cargar productos.</td></tr>';
    });
}


    // ================================
    // MODAL DETALLES DEL PRODUCTO
    // ================================

    document.addEventListener('click', e => {

        const img = e.target.closest('.productoImagen');
        if (!img) return;

        const modal = document.getElementById('modalDetalles');

        document.getElementById('detalleNombre').textContent = img.dataset.producto;
        document.getElementById('detalleDescripcion').textContent = img.dataset.detalles;
        document.getElementById('detalleTiempo').textContent = img.dataset.tiempo;

        if (img.dataset.img) {
            document.getElementById('detalleImg').src = "imagenes/" + img.dataset.img;
        } else {
            document.getElementById('detalleImg').src = "";
        }

        modal.style.display = "flex";
    });

    // Cerrar modal al hacer clic en X
    document.getElementById('cerrarDetalles').onclick = () => {
        document.getElementById('modalDetalles').style.display = "none";
    };

    // Cerrar modal al hacer clic fuera de la ventana
    document.getElementById('modalDetalles').addEventListener('click', e => {
        if (e.target.id === "modalDetalles") {
            e.target.style.display = "none";
        }
    });

    // CLICK EN BOTÓN "AGREGAR"
    document.addEventListener('click', e => {
        const btn = e.target.closest('.agregarBtn');
        if (btn) {

            if (!usuarioSesion) {
                // Mostrar alerta
                alert("Debe ingresar un ID de pedido para agregar productos.");

                // Obtener el input del ID
                const inputPedido = document.getElementById('pedidoId');
                if (inputPedido) {
                    // Poner foco
                    inputPedido.focus();
                    // Cambiar color de borde o fondo para resaltar
                    inputPedido.style.border = '2px solid red';
                    inputPedido.style.backgroundColor = '#ffe6e6';

                    // Opcional: quitar el color cuando el usuario escriba
                    inputPedido.addEventListener('input', () => {
                        inputPedido.style.border = '';
                        inputPedido.style.backgroundColor = '';
                    }, { once: true });
                }

                return;
            }

            productoSeleccionado = {
                producto: btn.dataset.producto,
                precio: parseFloat(btn.dataset.precio)
            };

            tituloProducto.textContent = productoSeleccionado.producto;
            precioProducto.textContent = productoSeleccionado.precio.toFixed(2);
            modalCant.style.display = 'flex';
        }
    });


    // CONFIRMAR CANTIDAD
    document.getElementById('btnConfirmarCantidad').addEventListener('click', () => {

        if (!usuarioSesion) {
            alert("Debe ingresar un ID de pedido antes de agregar.");
            modalCant.style.display = 'none';
            return;
        }

        const cantidad = parseInt(inputCantidad.value);

        if (!cantidad || cantidad < 1) {
            alert("Debe ingresar una cantidad válida");
            return;
        }

        const total = cantidad * productoSeleccionado.precio;

        fetch('index/procesar_index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                usuario: usuarioSesion,
                ...productoSeleccionado,
                cantidad,
                total
            })
        })
            .then(res => res.json())
            .then(data => {
                alert(data.mensaje);
                modalCant.style.display = 'none';

                // ==========================
                // ACTUALIZAR CONTADOR DE CARRITO
                // ==========================
                const contador = document.getElementById('n_productos');
                if (contador) {
                    // Aumentar en la cantidad agregada
                    let actual = parseInt(contador.textContent) || 0;
                    contador.textContent = actual + cantidad;
                }
            });
    });


    // CANCELAR
    document.getElementById('btnCancelarCantidad').addEventListener('click', () => {
        modalCant.style.display = 'none';
    });

    // Menú lateral
document.getElementById('btnMenuHamburguesa').addEventListener('click', function (e) {
    e.stopPropagation();
    document.getElementById('menuLateral').style.display = 'block';
    document.getElementById('botones-laterales').style.display = 'none';
});

document.getElementById('cerrarMenu').addEventListener('click', function (e) {
    e.stopPropagation();
    document.getElementById('menuLateral').style.display = 'none';
});

// Evita que clics dentro del menú lo cierren
document.getElementById('menuLateral').addEventListener('click', function (e) {
    e.stopPropagation();
});

// Cerrar al hacer clic fuera
document.addEventListener('click', function () {
    document.getElementById('menuLateral').style.display = 'none';
});

    cargarCategorias();
});


// =====================================
//   LOGIN DE USUARIO (ID DEL PEDIDO)
// =====================================

document.addEventListener("DOMContentLoaded", () => {
    const sesionUsuario = document.body.dataset.usuario;

    if (sesionUsuario) {
        const loginForm = document.querySelector(".login-id");
        if (loginForm) loginForm.style.display = "none";
    }

    const guardarBtn = document.getElementById("guardarUsuario");
    const inputPedido = document.getElementById("pedidoId");

    // ENTER para guardar usuario
    if (inputPedido) {
        inputPedido.addEventListener("keypress", function (e) {
            if (e.key === "Enter") {
                guardarBtn.click();
            }
        });
    }

    // CLICK en botón
    if (guardarBtn) {
        guardarBtn.addEventListener("click", () => {
            const usuarioVal = inputPedido.value.trim();

            if (!usuarioVal) {
                document.getElementById("errorPedidoId").innerText = "Debes ingresar un ID válido.";
                return;
            }

            fetch("index/guardar_usuario.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ usuario: usuarioVal })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        document.getElementById("errorPedidoId").innerText = data.mensaje;
                    }
                });
        });
    }
});



///modal de busqueda


// modal de busqueda

window.addEventListener('DOMContentLoaded', () => {
    const inputBuscar = document.getElementById('inputBuscar');
    const zonaProductos = document.getElementById('zonaProductos');
    const tbody = document.querySelector('#tablaMenu tbody');
    const encabezado = document.getElementById('textoEncabezado');

    if (!inputBuscar || !zonaProductos || !tbody || !encabezado) return;

    inputBuscar.addEventListener('input', () => {
        const nombre = inputBuscar.value.trim();

        if (nombre.length === 0) {
            tbody.innerHTML = '';
            return;
        }

        const alturaHeader = encabezado.offsetHeight + 20;
        const posicionTabla = zonaProductos.offsetTop - alturaHeader;

        window.scrollTo({
            top: posicionTabla,
            behavior: 'smooth'
        });

        $.post('index/buscar_por_categoria.php', { nombre: nombre }, (html) => {
            tbody.innerHTML = html;
        }).fail(() => {
            tbody.innerHTML = '<tr><td colspan="5">Error al buscar productos.</td></tr>';
        });
    });
});

window.addEventListener('DOMContentLoaded', () => {
    const btnBuscar = document.getElementById("btnBuscar");
    const contenedorBusqueda = document.getElementById("contenedorBusqueda");
    const inputBuscar = document.getElementById("inputBuscar");

    if (btnBuscar && contenedorBusqueda && inputBuscar) {
        // Mostrar / ocultar al pulsar lupa
        btnBuscar.addEventListener("click", function (e) {
            e.stopPropagation();
            contenedorBusqueda.classList.toggle("mostrar");

            if (contenedorBusqueda.classList.contains("mostrar")) {
                inputBuscar.focus();
            } else {
                inputBuscar.value = "";
            }
        });

        // Evitar que se cierre al hacer clic dentro del input
        contenedorBusqueda.addEventListener("click", function (e) {
            e.stopPropagation();
        });

        // Ocultar si se hace clic fuera
        document.addEventListener("click", function () {
            contenedorBusqueda.classList.remove("mostrar");
            inputBuscar.value = ""; // limpiar texto
        });
    }
});

/*agranda la imagen al pasar por el centro de la pantalla*/

function detectarProductoEnCentro() {
    const cards = document.querySelectorAll('.producto-card');
    const centroPantalla = window.innerHeight / 2;

    cards.forEach(card => {
        const rect = card.getBoundingClientRect();
        const centroCard = rect.top + rect.height / 2;

        const distancia = Math.abs(centroPantalla - centroCard);

        if (distancia < 120) {
            card.classList.add('enfocada');
        } else {
            card.classList.remove('enfocada');
        }
    });
}

// Ejecutar al cargar
window.addEventListener('load', detectarProductoEnCentro);

// Ejecutar al hacer scroll
window.addEventListener('scroll', detectarProductoEnCentro);

// Ejecutar al cambiar tamaño de pantalla
window.addEventListener('resize', detectarProductoEnCentro);