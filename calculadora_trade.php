<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculadora Trading</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #0f172a;
            color: white;
            margin: 0;
            padding: 20px;
        }

        .contenedor {
            max-width: 1100px;
            margin: auto;
            background: #1e293b;
            padding: 25px;
            border-radius: 18px;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
        }

        h1 {
            text-align: center;
            margin-bottom: 25px;
            color: #38bdf8;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
        }

        .campo {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 8px;
            font-weight: bold;
            color: #cbd5e1;
        }

        input, select {
            padding: 12px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            outline: none;
            background: #334155;
            color: white;
        }

        input:focus, select:focus {
            box-shadow: 0 0 0 2px #38bdf8;
        }

        .resultado {
            margin-top: 30px;
            background: #0f172a;
            border-radius: 16px;
            padding: 20px;
        }

        .resultado h2 {
            color: #22c55e;
            margin-bottom: 20px;
        }

        .resultado-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
        }

        .card {
            background: #1e293b;
            padding: 18px;
            border-radius: 14px;
            border: 1px solid #334155;
        }

        .card h3 {
            margin: 0 0 10px;
            color: #38bdf8;
            font-size: 16px;
        }

        .card p {
            margin: 0;
            font-size: 22px;
            font-weight: bold;
        }

        .ganancia {
            color: #22c55e;
        }

        .perdida {
            color: #ef4444;
        }

        .neutral {
            color: #facc15;
        }

        .nota {
            margin-top: 20px;
            font-size: 14px;
            color: #94a3b8;
            line-height: 1.6;
        }

        .alerta {
            margin-top: 20px;
            padding: 15px;
            border-radius: 12px;
            font-weight: bold;
            display: none;
        }

        .alerta.ok {
            background: rgba(34,197,94,0.15);
            color: #22c55e;
            border: 1px solid #22c55e;
        }

        .alerta.error {
            background: rgba(239,68,68,0.15);
            color: #ef4444;
            border: 1px solid #ef4444;
        }

        .subtitulo {
            margin-top: 8px;
            text-align: center;
            color: #94a3b8;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="contenedor">
    <h1>📈 Calculadora de Trading</h1>
    <div class="subtitulo">Simula tu operación con apalancamiento, volumen, inversión y riesgo</div>

    <div class="grid">
        <div class="campo">
            <label for="par">Par a operar</label>
            <select id="par">
                <option value="EURUSD">EURUSD</option>
                <option value="USDJPY">USDJPY</option>
                <option value="AUDCAD">AUDCAD</option>
                <option value="GBPUSD">GBPUSD</option>
                <option value="USDCAD">USDCAD</option>
                <option value="NZDUSD" selected>NZDUSD</option>
            </select>
        </div>

        <div class="campo">
            <label for="entrada">Precio de Entrada</label>
            <input type="number" id="entrada" step="0.00001" placeholder="Ej: 0.57770">
        </div>

        <div class="campo">
            <label for="tp">Take Profit</label>
            <input type="number" id="tp" step="0.00001" placeholder="Ej: 0.57955">
        </div>

        <div class="campo">
            <label for="sl">Stop Loss</label>
            <input type="number" id="sl" step="0.00001" placeholder="Ej: 0.57587">
        </div>

        <div class="campo">
            <label for="volumen">Volumen</label>
            <input type="number" id="volumen" step="1" value="3000" min="1">
        </div>

        <div class="campo">
            <label for="apalancamiento">Apalancamiento</label>
            <select id="apalancamiento">
                <option value="50">1:50</option>
                <option value="100">1:100</option>
                <option value="200">1:200</option>
                <option value="500">1:500</option>
                <option value="1000">1:1000</option>
                <option value="2000">1:2000</option>
                <option value="3000" selected>1:3000</option>
                <option value="5000">1:5000</option>
            </select>
        </div>

        <div class="campo">
            <label for="inversion">Inversión usada ($) <small>(mínimo $1)</small></label>
            <input type="number" id="inversion" step="0.01" min="1" value="1">
        </div>

        <div class="campo">
            <label for="capitalTotal">Monto total de la cuenta ($)</label>
            <input type="number" id="capitalTotal" step="0.01" min="1" placeholder="Ej: 100">
        </div>
    </div>

    <div id="mensajeMargen" class="alerta"></div>

    <div class="resultado">
        <h2>Resultados</h2>
        <div class="resultado-grid">
            <div class="card">
                <h3>Pips TP</h3>
                <p id="pipsTp">0</p>
            </div>

            <div class="card">
                <h3>Pips SL</h3>
                <p id="pipsSl">0</p>
            </div>

            <div class="card">
                <h3>Valor por Pip</h3>
                <p id="valorPip">$0.00</p>
            </div>

            <div class="card">
                <h3>Ganancia Estimada</h3>
                <p id="ganancia" class="ganancia">$0.00</p>
            </div>

            <div class="card">
                <h3>Pérdida Estimada</h3>
                <p id="perdida" class="perdida">$0.00</p>
            </div>

            <div class="card">
                <h3>Margen Requerido</h3>
                <p id="margen" class="neutral">$0.00</p>
            </div>

            <div class="card">
                <h3>Riesgo / Beneficio</h3>
                <p id="rr" class="neutral">0</p>
            </div>

            <div class="card">
                <h3>% Riesgo</h3>
                <p id="porcentajeRiesgo" class="perdida">0%</p>
            </div>

            <div class="card">
                <h3>% Ganancia</h3>
                <p id="porcentajeGanancia" class="ganancia">0%</p>
            </div>

            <div class="card">
                <h3>Capital Restante</h3>
                <p id="capitalRestante" class="neutral">$0.00</p>
            </div>

            <div class="card">
                <h3>Estado del Margen</h3>
                <p id="estadoMargen" class="neutral">-</p>
            </div>
        </div>

        <div class="nota">
            <strong>Nota:</strong> Esta calculadora usa una aproximación educativa para estimar pips, valor por pip y margen. 
            En cuentas reales, el broker puede variar ligeramente los cálculos según el instrumento, spread, comisión, tamaño contractual y tipo de cuenta.
        </div>
    </div>
</div>

<script>
    const par = document.getElementById("par");
    const entrada = document.getElementById("entrada");
    const tp = document.getElementById("tp");
    const sl = document.getElementById("sl");
    const volumen = document.getElementById("volumen");
    const apalancamiento = document.getElementById("apalancamiento");
    const inversion = document.getElementById("inversion");
    const capitalTotal = document.getElementById("capitalTotal");

    const pipsTp = document.getElementById("pipsTp");
    const pipsSl = document.getElementById("pipsSl");
    const valorPip = document.getElementById("valorPip");
    const ganancia = document.getElementById("ganancia");
    const perdida = document.getElementById("perdida");
    const margen = document.getElementById("margen");
    const rr = document.getElementById("rr");
    const porcentajeRiesgo = document.getElementById("porcentajeRiesgo");
    const porcentajeGanancia = document.getElementById("porcentajeGanancia");
    const capitalRestante = document.getElementById("capitalRestante");
    const estadoMargen = document.getElementById("estadoMargen");
    const mensajeMargen = document.getElementById("mensajeMargen");

    function obtenerTamanoPip(parSeleccionado) {
        return parSeleccionado.includes("JPY") ? 0.01 : 0.0001;
    }

    function calcularValorPip(parSeleccionado, volumenValor, precioEntrada) {
        let pipSize = obtenerTamanoPip(parSeleccionado);

        // Aproximación:
        // JPY: pip value cambia con el precio
        if (parSeleccionado.includes("JPY")) {
            return (volumenValor * pipSize) / precioEntrada;
        } else {
            return volumenValor * pipSize;
        }
    }

    function limpiarResultados() {
        pipsTp.textContent = "0";
        pipsSl.textContent = "0";
        valorPip.textContent = "$0.00";
        ganancia.textContent = "$0.00";
        perdida.textContent = "$0.00";
        margen.textContent = "$0.00";
        rr.textContent = "0";
        porcentajeRiesgo.textContent = "0%";
        porcentajeGanancia.textContent = "0%";
        capitalRestante.textContent = "$0.00";
        estadoMargen.textContent = "-";
        mensajeMargen.style.display = "none";
        mensajeMargen.className = "alerta";
    }

    function calcular() {
        let parSeleccionado = par.value;
        let entradaValor = parseFloat(entrada.value);
        let tpValor = parseFloat(tp.value);
        let slValor = parseFloat(sl.value);
        let volumenValor = parseFloat(volumen.value);
        let apalancamientoValor = parseFloat(apalancamiento.value);
        let inversionValor = parseFloat(inversion.value) || 0;
        let capitalTotalValor = parseFloat(capitalTotal.value) || 0;

        if (!entradaValor || !tpValor || !slValor || !volumenValor || !apalancamientoValor) {
            limpiarResultados();
            return;
        }

        // mínimo inversión = 1
        if (inversionValor < 1) {
            inversionValor = 1;
            inversion.value = 1;
        }

        let pipSize = obtenerTamanoPip(parSeleccionado);

        let pipsTakeProfit = Math.abs(tpValor - entradaValor) / pipSize;
        let pipsStopLoss = Math.abs(entradaValor - slValor) / pipSize;

        let valorPorPip = calcularValorPip(parSeleccionado, volumenValor, entradaValor);

        let gananciaEstimada = pipsTakeProfit * valorPorPip;
        let perdidaEstimada = pipsStopLoss * valorPorPip;

        let margenRequerido = (entradaValor * volumenValor) / apalancamientoValor;

        let riesgoBeneficio = perdidaEstimada > 0 ? (gananciaEstimada / perdidaEstimada) : 0;

        let porcentajeRiesgoValor = capitalTotalValor > 0 ? (perdidaEstimada / capitalTotalValor) * 100 : 0;
        let porcentajeGananciaValor = capitalTotalValor > 0 ? (gananciaEstimada / capitalTotalValor) * 100 : 0;

        let capitalRestanteValor = capitalTotalValor > 0 ? (capitalTotalValor - margenRequerido) : 0;

        let margenDisponible = inversionValor >= margenRequerido;

        pipsTp.textContent = pipsTakeProfit.toFixed(2) + " pips";
        pipsSl.textContent = pipsStopLoss.toFixed(2) + " pips";
        valorPip.textContent = "$" + valorPorPip.toFixed(4);
        ganancia.textContent = "$" + gananciaEstimada.toFixed(2);
        perdida.textContent = "$" + perdidaEstimada.toFixed(2);
        margen.textContent = "$" + margenRequerido.toFixed(2);
        rr.textContent = riesgoBeneficio.toFixed(2);

        porcentajeRiesgo.textContent = porcentajeRiesgoValor.toFixed(2) + "%";
        porcentajeGanancia.textContent = porcentajeGananciaValor.toFixed(2) + "%";
        capitalRestante.textContent = capitalTotalValor > 0 ? "$" + capitalRestanteValor.toFixed(2) : "$0.00";

        estadoMargen.textContent = margenDisponible ? "✅ Sí alcanza" : "❌ No alcanza";
        estadoMargen.style.color = margenDisponible ? "#22c55e" : "#ef4444";

        // Mensaje visual
        mensajeMargen.style.display = "block";
        if (margenDisponible) {
            mensajeMargen.className = "alerta ok";
            mensajeMargen.innerHTML = `✅ Tu inversión de <strong>$${inversionValor.toFixed(2)}</strong> sí alcanza para abrir esta operación.`;
        } else {
            mensajeMargen.className = "alerta error";
            mensajeMargen.innerHTML = `❌ Tu inversión de <strong>$${inversionValor.toFixed(2)}</strong> NO alcanza para cubrir el margen requerido de <strong>$${margenRequerido.toFixed(2)}</strong>.`;
        }
    }

    [par, entrada, tp, sl, volumen, apalancamiento, inversion, capitalTotal].forEach(elemento => {
        elemento.addEventListener("input", calcular);
        elemento.addEventListener("change", calcular);
    });

    calcular();
</script>

</body>
</html>