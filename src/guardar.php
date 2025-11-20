<?php
// Habilitar la visualización de errores para depuración
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Configurar encabezados para recibir JSON y caracteres especiales
header('Content-Type: application/json; charset=utf-8');

// 1. CONEXIÓN Y CREACIÓN AUTOMÁTICA
try {
    $dbPath = __DIR__ . '/../usuarios.db';
    $db = new SQLite3($dbPath);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "No se pudo conectar a la BD"]);
    exit();
}

// 2. CREAR LA TABLA SI NO EXISTE (solo usuario y password)
$queryTabla = "CREATE TABLE IF NOT EXISTS usuarios (
    usuario TEXT NOT NULL,
    password TEXT NOT NULL
)";
$db->exec($queryTabla);

// 3. MANEJAR LAS PETICIONES (GET y POST)
$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'POST') {
    // --- CREAR USUARIO ---
    $input = json_decode(trim(file_get_contents('php://input')), true);
    if (!$input) {
        $input = $_POST; // Fallback para datos de formulario
    }

    $usuario = $input['username'] ?? $input['usuario'] ?? null;
    $password = $input['password'] ?? null;

    if (!$usuario || !$password) {
        http_response_code(400);
        echo json_encode(["error" => "Datos incompletos. Se requieren 'username' y 'password'."]);
        exit();
    }

    // ¡ADVERTENCIA! En producción se debe usar password_hash()
    $stmt = $db->prepare('INSERT INTO usuarios (usuario, password) VALUES (:u, :p)');
    $stmt->bindValue(':u', $usuario, SQLITE3_TEXT);
    $stmt->bindValue(':p', $password, SQLITE3_TEXT);

    $res = $stmt->execute();
    if ($res !== false) {
        $last = $db->lastInsertRowID();
        echo json_encode(["mensaje" => "Usuario guardado con éxito", "rowid" => $last, "db" => $dbPath]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Error al guardar el usuario"]);
    }

} elseif ($metodo === 'GET') {
    // --- LEER USUARIOS ---
    // Modo debug: ?debug=1
    if (isset($_GET['debug']) && $_GET['debug']) {
        $count = $db->querySingle('SELECT COUNT(*) FROM usuarios');
        echo json_encode(["db" => $dbPath, "count" => $count]);
        exit();
    }

    $resultados = $db->query('SELECT usuario, password FROM usuarios ORDER BY rowid DESC');
    $listaUsuarios = [];
    while ($fila = $resultados->fetchArray(SQLITE3_ASSOC)) {
        $listaUsuarios[] = $fila;
    }
    echo json_encode($listaUsuarios);

} elseif ($metodo === 'DELETE') {
    // --- ELIMINAR USUARIO ---
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['username'])) {
        http_response_code(400);
        echo json_encode(["error" => "Nombre de usuario no proporcionado."]);
        exit();
    }

    $usuario = $input['username'];

    $stmt = $db->prepare('DELETE FROM usuarios WHERE usuario = :u');
    $stmt->bindValue(':u', $usuario, SQLITE3_TEXT);

    $res = $stmt->execute();
    if ($res !== false) {
        if ($db->changes() > 0) {
            echo json_encode(["mensaje" => "Usuario eliminado con éxito"]);
        } else {
            http_response_code(404); // Not Found
            echo json_encode(["error" => "Usuario no encontrado"]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Error al eliminar el usuario"]);
    }

} else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
}
?>