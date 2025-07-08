<?php
header('Content-Type: application/json');

// Conectar a PostgreSQL
$conexion = pg_connect("host=127.0.0.1 port=25432 dbname=Proy_Sevilla user=user password=user");

if (!$conexion) {
  echo json_encode(["success" => false, "mensaje" => "Error al conectar a la base de datos."]);
  exit;
}

// Leer datos JSON del visor
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
  echo json_encode(["success" => false, "mensaje" => "No se recibieron datos."]);
  exit;
}

$id = $data["id"];
$nombre = $data["nombre"];
$tipo_evento = $data["tipo_evento"];
$numero_heridos = $data["numero_heridos"];
$gravedad = $data["gravedad"];
$latitud = $data["latitud"];
$longitud = $data["longitud"];
$viviendas_afectadas = $data["viviendas_afectadas"];
$gravedad_fenomeno = $data["gravedad_fenomeno"];

// Preparar consulta UPDATE con PostGIS
$query = "
UPDATE reportes_eventos SET
  nombre = $1,
  tipo_evento = $2,
  numero_heridos = $3,
  gravedad = $4,
  latitud = $5,
  longitud = $6,
  viviendas_afectadas = $7,
  gravedad_fenomeno = $8,
  geom = ST_SetSRID(ST_MakePoint($6, $5), 4326)
WHERE id = $9
";

$params = [
  $nombre,
  $tipo_evento,
  $numero_heridos,
  $gravedad,
  $latitud,
  $longitud,
  $viviendas_afectadas,
  $gravedad_fenomeno,
  $id
];

$result = pg_query_params($conexion, $query, $params);

if ($result) {
  echo json_encode(["success" => true, "mensaje" => "Evento editado correctamente."]);
} else {
  echo json_encode(["success" => false, "mensaje" => pg_last_error($conexion)]);
}

pg_close($conexion);
?>
