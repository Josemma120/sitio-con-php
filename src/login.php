<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// 1. CONEXIÓN A LA BASE DE DATOS
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

// 2. SOLO ACEPTAR PETICIONES POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    exit();
}

// 3. LEER DATOS DE ENTRADA (JSON o FORM)
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

// 4. CONSULTAR LA BASE DE DATOS
// ¡ADVERTENCIA! Esto es inseguro para producción. Deberías usar password_hash() y password_verify().
$stmt = $db->prepare('SELECT COUNT(*) as count FROM usuarios WHERE usuario = :u AND password = :p');
$stmt->bindValue(':u', $usuario, SQLITE3_TEXT);
$stmt->bindValue(':p', $password, SQLITE3_TEXT);

$resultado = $stmt->execute();
$fila = $resultado->fetchArray(SQLITE3_ASSOC);

if ($fila && $fila['count'] > 0) {
    // Usuario y contraseña correctos
    echo json_encode(["success" => true, "message" => "Login exitoso"]);
} else {
    // Credenciales incorrectas
    http_response_code(401); // 401 Unauthorized
    echo json_encode(["success" => false, "message" => "Usuario o contraseña incorrectos."]);
}
?>