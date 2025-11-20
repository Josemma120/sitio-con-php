<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// 1. Conexión a la base de datos
try {
    $dbPath = __DIR__ . '/../usuarios.db';
    if (!file_exists($dbPath)) {
        throw new Exception("La base de datos no existe.");
    }
    $db = new SQLite3($dbPath);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error del servidor: " . $e->getMessage()]);
    exit();
}

// 2. Aceptar solo peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    exit();
}

// 3. Leer datos de entrada (acepta JSON y form-urlencoded)
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$usuario = $input['username'] ?? null;
$password = $input['password'] ?? null;

if (!$usuario || !$password) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Faltan el usuario o la contraseña."]);
    exit();
}

// 4. Consultar la base de datos
// ¡ADVERTENCIA! Guardar contraseñas en texto plano es inseguro. Usar password_hash() y password_verify().
$stmt = $db->prepare('SELECT COUNT(*) as count FROM usuarios WHERE usuario = :u AND password = :p');
$stmt->bindValue(':u', $usuario, SQLITE3_TEXT);
$stmt->bindValue(':p', $password, SQLITE3_TEXT);

$resultado = $stmt->execute();
$fila = $resultado->fetchArray(SQLITE3_ASSOC);

if ($fila && $fila['count'] > 0) {
    echo json_encode(["success" => true, "message" => "Login exitoso"]);
} else {
    http_response_code(401); // 401 Unauthorized
    echo json_encode(["success" => false, "message" => "Usuario o contraseña incorrectos."]);
}
?>