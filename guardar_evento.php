<?php
header('Content-Type: application/json');

// Conexión a la base de datos
$host = "127.0.0.1";
$port = "25432";
$dbname = "Proy_Sevilla";
$user = "user";
$password = "user";

$conexion = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conexion) {
    echo json_encode(["error" => "Error al conectar a la base de datos."]);
    exit;
}

// Leer datos enviados (si los hay)
$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    $lat = $data['latitud'];
    $lng = $data['longitud'];
    $nombre = $data['nombre'];
    $evento = $data['tipo_evento'];
    $heridos = $data['numero_heridos'];
    $gravedad = $data['gravedad'];
    $viviendasAfectadas = $data['viviendas_afectadas'];
    $gravedadFenomeno = $data['gravedad_fenomeno'];

    $query = "INSERT INTO reportes_eventos (
        nombre, tipo_evento, numero_heridos, gravedad,
        latitud, longitud, viviendas_afectadas, gravedad_fenomeno, geom
    ) VALUES (
        $1, $2, $3, $4, $5, $6, $7, $8,
        ST_SetSRID(ST_MakePoint($6, $5), 4326)
    )";

    $params = array($nombre, $evento, $heridos, $gravedad, $lat, $lng, $viviendasAfectadas, $gravedadFenomeno);

    $result = pg_query_params($conexion, $query, $params);

    if (!$result) {
        echo json_encode(["error" => pg_last_error($conexion)]);
        pg_close($conexion);
        exit;
    }
}

// Después de insertar, devolver los eventos como GeoJSON
$queryGeo = "
SELECT 
    id,
    nombre,
    tipo_evento,
    numero_heridos,
    gravedad,
    viviendas_afectadas,
    gravedad_fenomeno,
    ST_AsGeoJSON(geom)::json AS geometry
FROM reportes_eventos
WHERE geom IS NOT NULL;
";

$result = pg_query($conexion, $queryGeo);
$features = [];

while ($row = pg_fetch_assoc($result)) {
    $geometry = $row['geometry'];
    unset($row['geometry']);

    $features[] = [
        "type" => "Feature",
        "geometry" => json_decode($geometry),
        "properties" => $row
    ];
}

$geojson = [
    "type" => "FeatureCollection",
    "features" => $features
];

echo json_encode($geojson);
pg_close($conexion);
?>

