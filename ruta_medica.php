<?php
header('Content-Type: application/json');

// Leer los datos JSON del cuerpo de la solicitud
$datos = json_decode(file_get_contents('php://input'), true);

// Validar si llegaron los datos correctamente
if (!$datos || !isset($datos['lat']) || !isset($datos['lng']) || !isset($datos['id'])) {
    echo json_encode(["error" => "Datos incompletos"]);
    exit;
}

$lat = $datos['lat'];
$lng = $datos['lng'];
$id_centro = $datos['id'];

// Conexión a PostgreSQL
$host = "127.0.0.1";
$port = "25432";
$dbname = "Proy_Sevilla";
$user = "user";
$password = "user";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conn) {
    echo json_encode(["error" => "No se pudo conectar a la base de datos"]);
    exit;
}

// Ejecutar la función SQL
$query = "SELECT ST_AsGeoJSON(geom) as geojson FROM calcular_ruta_mas_corta($1, $2, $3)";
$result = pg_query_params($conn, $query, array($lat, $lng, $id_centro));

if (!$result) {
    echo json_encode(["error" => "Error en la consulta"]);
    exit;
}

// Construir GeoJSON completo
$features = [];
while ($row = pg_fetch_assoc($result)) {
    $geom = json_decode($row['geojson'], true);
    $features[] = [
        "type" => "Feature",
        "geometry" => $geom,
        "properties" => new stdClass()
    ];
}

echo json_encode([
    "type" => "FeatureCollection",
    "features" => $features
]);

pg_close($conn);
?>
