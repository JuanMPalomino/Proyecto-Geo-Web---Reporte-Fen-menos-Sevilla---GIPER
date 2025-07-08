<?php
header('Content-Type: application/json');

$host = "127.0.0.1";
$port = "25432";
$dbname = "Proy_Sevilla";
$user = "user";
$password = "user";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    http_response_code(500);
    echo json_encode([
        "error" => "No se pudo conectar a la base de datos",
        "detalle" => pg_last_error()
    ]);
    exit;
}

$query = "
SELECT jsonb_build_object(
    'type',     'FeatureCollection',
    'features', jsonb_agg(feature)
)
FROM (
  SELECT jsonb_build_object(
    'type',       'Feature',
    'geometry',   ST_AsGeoJSON(geom)::jsonb,
    'properties', to_jsonb(row) - 'geom'
  ) AS feature
  FROM (
    SELECT * FROM reportes_eventos
  ) row
) features;
";

$result = pg_query($conn, $query);

if (!$result) {
    http_response_code(500);
    echo json_encode([
        "error" => "Error en la consulta SQL",
        "detalle" => pg_last_error($conn)
    ]);
    exit;
}

$row = pg_fetch_row($result);

if (!$row || !$row[0]) {
    http_response_code(500);
    echo json_encode(["error" => "La consulta no devolvió resultados"]);
    exit;
}

echo $row[0];
?>
