<?php
header('Content-Type: application/json');

$host = "127.0.0.1";
$port = "25432";
$dbname = "Proy_Sevilla";
$user = "user";
$password = "user";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conn) {
    echo json_encode(["error" => "No se pudo conectar a la base de datos."]);
    exit;
}

$tabla = $_GET['tabla'] ?? '';
$tipo = $_GET['tipo'] ?? '';

function getData($conn, $sql) {
    $res = pg_query($conn, $sql);
    $labels = [];
    $data = [];
    while ($row = pg_fetch_row($res)) {
        $labels[] = strtoupper(trim($row[0] ?? 'DESCONOCIDO'));
        $data[] = (int)$row[1];
    }
    return [$labels, $data];
}

function generarGrafico($tipo, $titulo, $etiquetaCampo, $labels, $data, $orientacion = 'vertical', $porcentual = false) {
    if ($porcentual) {
        $total = array_sum($data);
        $data = array_map(fn($v) => round(($v / $total) * 100, 2), $data);
    }

    $tipoGrafico = $tipo === 'pastel' ? 'pie' : 'bar';

    return [
        "type" => $tipoGrafico,
        "data" => [
            "labels" => $labels,
            "datasets" => [[
                "label" => $etiquetaCampo,
                "data" => $data,
                "backgroundColor" => $tipoGrafico === 'pie'
                    ? [ "#FF6384", "#36A2EB", "#FFCE56", "#8E44AD", "#2ECC71", "#E67E22", "#1ABC9C" ]
                    : "rgba(54, 162, 235, 0.6)"
            ]]
        ],
        "options" => [
            "indexAxis" => $tipoGrafico === 'bar' && $orientacion === 'horizontal' ? 'y' : 'x',
            "responsive" => true,
            "plugins" => [
                "title" => [
                    "display" => true,
                    "text" => $titulo
                ],
                "legend" => [
                    "display" => $tipoGrafico === 'pie',
                    "position" => "bottom"
                ]
            ],
            "scales" => $tipoGrafico === 'bar'
                ? [
                    "x" => [
                        "title" => [
                            "display" => true,
                            "text" => $orientacion === 'vertical' ? strtoupper($etiquetaCampo) : "VALOR"
                        ]
                    ],
                    "y" => [
                        "title" => [
                            "display" => true,
                            "text" => $orientacion === 'vertical' ? "VALOR" : strtoupper($etiquetaCampo)
                        ]
                    ]
                ]
                : []
        ]
    ];
}

// CENTROS MÉDICOS
if ($tabla === 'centros_medicos') {
    switch ($tipo) {
        case 'nivel':
            [$labels, $data] = getData($conn, "SELECT nivel, COUNT(*) FROM centros_medicos GROUP BY nivel");
            echo json_encode(generarGrafico('bar', 'NIVEL DE ATENCIÓN', 'NIVEL', $labels, $data, 'horizontal'));
            break;
        case 'naturaleza':
            [$labels, $data] = getData($conn, "SELECT naturaleza, COUNT(*) FROM centros_medicos GROUP BY naturaleza");
            echo json_encode(generarGrafico('bar', 'NATURALEZA DEL CENTRO MÉDICO', 'NATURALEZA', $labels, $data));
            break;
        case 'regimen':
            [$labels, $data] = getData($conn, "SELECT regimen, COUNT(*) FROM centros_medicos GROUP BY regimen");
            $total = array_sum($data);
            $porcentajes = array_map(fn($v) => round(($v / $total) * 100, 2), $data);
            echo json_encode(generarGrafico('pastel', 'RÉGIMEN DE AFILIACIÓN (PORCENTAJE)', 'RÉGIMEN', $labels, $porcentajes));
            break;
        default:
            echo json_encode(["error" => "Tipo de gráfico inválido para centros_medicos."]);
    }
    exit;
}

// REPORTES DE EVENTOS
if ($tabla === 'reportes_eventos') {
    switch ($tipo) {
        case 'tipo_evento':
            [$labels, $data] = getData($conn, "SELECT tipo_evento, COUNT(*) FROM reportes_eventos GROUP BY tipo_evento");
            echo json_encode(generarGrafico('bar', 'TIPOS DE EVENTOS REPORTADOS', 'TIPO DE EVENTO', $labels, $data));
            break;
        case 'heridos_por_tipo':
            [$labels, $data] = getData($conn, "SELECT tipo_evento, SUM(numero_heridos) FROM reportes_eventos GROUP BY tipo_evento");
            echo json_encode(generarGrafico('bar', 'HERIDOS POR TIPO DE EVENTO', 'FENOMENO', $labels, $data, 'horizontal'));
            break;
        case 'gravedad':
            [$labels, $data] = getData($conn, "SELECT gravedad, COUNT(*) FROM reportes_eventos WHERE gravedad IS NOT NULL GROUP BY gravedad");
            echo json_encode(generarGrafico('bar', 'GRAVEDAD DE LOS HERIDOS', 'GRAVEDAD', $labels, $data));
            break;
        case 'viviendas_afectadas':
            [$labels, $data] = getData($conn, "SELECT tipo_evento, SUM(viviendas_afectadas) FROM reportes_eventos GROUP BY tipo_evento");
            echo json_encode(generarGrafico('pastel', 'VIVIENDAS AFECTADAS POR TIPO DE EVENTO', 'TIPO DE EVENTO', $labels, $data));
            break;
        case 'gravedad_fenomeno':
            [$labels, $data] = getData($conn, "SELECT gravedad_fenomeno, COUNT(*) FROM reportes_eventos GROUP BY gravedad_fenomeno");
            echo json_encode(generarGrafico('bar', 'GRAVEDAD DEL FENÓMENO NATURAL', 'GRAVEDAD', $labels, $data, 'horizontal'));
            break;
        default:
            echo json_encode(["error" => "Tipo de gráfico inválido para reportes_eventos."]);
    }
    exit;
}

echo json_encode(["error" => "Tabla o tipo de gráfico no válido."]);



