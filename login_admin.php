<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: text/html; charset=UTF-8");

// Parámetros de conexión
$host = "127.0.0.1";
$port = "25432";
$dbname = "Proy_Sevilla";
$user = "user";
$password = "user";

// Leer JSON enviado
$input = json_decode(file_get_contents("php://input"), true);
$usuario = $input["usuario"] ?? '';
$clave = $input["clave"] ?? '';

// Conexión a PostgreSQL
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    echo "<span style='color:red;'>❌ Error de conexión a la base de datos.</span>";
    exit;
}

// Consulta segura
$query = "SELECT * FROM admins WHERE usuario = $1 AND contraseña = $2";
$result = pg_query_params($conn, $query, array($usuario, $clave));

if ($result && pg_num_rows($result) > 0) {
    echo "<span style='color:green;'>✅ Acceso correcto. Bienvenido.</span>";
} else {
    echo "<span style='color:red;'>❌ Usuario o contraseña incorrectos.</span>";
}

pg_close($conn);
?>
