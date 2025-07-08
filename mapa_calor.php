<?php
header('Content-Type: application/json');

$tipo = $_GET['tipo'];

$conn = pg_connect("host=127.0.0.1 port=25432 dbname=Proy_Sevilla user=user password=user");

if (!$conn) {
    http_response_code(500);
    echo json_encode(["error" => "No se pudo conectar a la base de datos"]);
    exit;
}

if ($tipo === 'todos') {
    $query = "SELECT ST_Y(geom) AS lat, ST_X(geom) AS lon FROM reportes_eventos WHERE geom IS NOT NULL";
} else {
    $query = "SELECT ST_Y(geom) AS lat, ST_X(geom) AS lon FROM reportes_eventos WHERE tipo_evento = $1 AND geom IS NOT NULL";
}

$result = ($tipo === 'todos') ? pg_query($conn, $query) : pg_query_params($conn, $query, array($tipo));

if (!$result) {
    http_response_code(500);
    echo json_encode(["error" => "Error en la consulta"]);
    exit;
}

$puntos = [];
while ($row = pg_fetch_assoc($result)) {
    $puntos[] = [(float)$row['lat'], (float)$row['lon'], 0.8];
}

echo json_encode($puntos);
?>
