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
    echo json_encode(["error" => "No se pudo conectar a la BDS"]);
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
    // Intentar leer JSON bruto
    $raw = trim(file_get_contents('php://input'));
    $input = null;
    if ($raw !== '') {
        $input = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $input = null; // JSON inválido -> fallback a $_POST
        }
    }

    // Si no llegó JSON, usar $_POST (formularios)
    if (!$input) {
        $input = $_POST;
    }

    // Aceptar tanto "username" como "usuario" como nombre del campo
    $usuario = $input['username'] ?? $input['usuario'] ?? null;
    $password = $input['password'] ?? null;

    if (!$usuario || !$password) {
        http_response_code(400);
        echo json_encode(["error" => "Datos incompletos o en formato incorrecto. Se requieren 'username' (o 'usuario') y 'password'."]);
        exit();
    }

    // En producción usar password_hash()
    $stmt = $db->prepare('INSERT INTO usuarios (usuario, password) VALUES (:u, :p)');
    $stmt->bindValue(':u', $usuario, SQLITE3_TEXT);
    $stmt->bindValue(':p', $password, SQLITE3_TEXT);

    $res = $stmt->execute();
    if ($res !== false) {
        // devolver id de fila para confirmar que quedó en esta BD
        $last = $db->lastInsertRowID();
        echo json_encode(["mensaje" => "Usuario guardado con éxito", "rowid" => $last, "db" => $dbPath]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Error al guardar"]);
    }

} elseif ($metodo === 'GET') {
    // Modo debug: ?debug=1 devuelve ruta de BD y conteo
    if (isset($_GET['debug']) && $_GET['debug']) {
        $count = $db->querySingle('SELECT COUNT(*) FROM usuarios');
        echo json_encode(["db" => $dbPath, "count" => $count]);
        exit();
    }

    $resultados = $db->query('SELECT usuario, password FROM usuarios ORDER BY rowid DESC');
    if ($resultados === false) {
        http_response_code(500);
        echo json_encode(["error" => "Error en la consulta"]);
        exit();
    }

    $listaUsuarios = [];
    while ($fila = $resultados->fetchArray(SQLITE3_ASSOC)) {
        $listaUsuarios[] = $fila;
    }

    echo json_encode($listaUsuarios);
} else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
}
?>