<?php
header('Content-Type: application/json; charset=utf-8');

$conn = pg_connect("host=127.0.0.1 port=25432 dbname=Proy_Sevilla user=user password=user");
if (!$conn) {
    echo json_encode(["error" => "No se pudo conectar a la base de datos."]);
    exit;
}

$sql = "SELECT id, nombre, tipo_evento, numero_heridos, gravedad, viviendas_afectadas, gravedad_fenomeno, fecha_reporte,
               ST_AsGeoJSON(geom) AS geometry
        FROM reportes_eventos
        WHERE geom IS NOT NULL";

$result = pg_query($conn, $sql);
$features = [];

while ($row = pg_fetch_assoc($result)) {
    $geometry = json_decode($row['geometry']);
    unset($row['geometry']);

    $features[] = [
        "type" => "Feature",
        "geometry" => $geometry,
        "properties" => $row
    ];
}

$geojson = [
    "type" => "FeatureCollection",
    "features" => $features
];

echo json_encode($geojson, JSON_UNESCAPED_UNICODE);
?>
