<?php
// Cabecera para recibir JSON
header('Content-Type: application/json');

// Obtener el cuerpo de la solicitud
$input = json_decode(file_get_contents('php://input'), true);

// Verificar que llegó un ID
if (!isset($input['id'])) {
    echo json_encode(["error" => "ID no proporcionado"]);
    exit;
}

$id = intval($input['id']); // Sanitización simple

// Conexión a la base de datos
$conn = pg_connect("host=127.0.0.1 port=25432 dbname=Proy_Sevilla user=user password=user");

if (!$conn) {
    echo json_encode(["error" => "Error de conexión a la base de datos"]);
    exit;
}

// Ejecutar eliminación
$query = "DELETE FROM reportes_eventos WHERE id = $1";
$result = pg_query_params($conn, $query, [$id]);

if ($result) {
    echo json_encode(["status" => "OK"]);
} else {
    echo json_encode(["error" => "No se pudo eliminar el registro"]);
}

pg_close($conn);
?>
