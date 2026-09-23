<?php
header('Content-Type: application/json; charset=utf-8');

$servername = 'localhost';
$dbusername = 'root';
$dbpassword = '';
$dbname = 'parkside_db';

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Adatbázis csatlakozási hiba: ' . $conn->connect_error,
    ]);
    exit;
}
//ha meg a tabla nem letezik akkor letrehozza
$createTableSql = "
    CREATE TABLE IF NOT EXISTS felhasznalok (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        username VARCHAR(100) NOT NULL UNIQUE,
        email VARCHAR(150) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL
    )
";

if (!$conn->query($createTableSql)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Tábla létrehozása sikertelen: ' . $conn->error,
    ]);
    $conn->close();
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = $method === 'POST' ? $_POST : $_GET;
$action = $input['action'] ?? '';

if ($action === '' && !empty($input['username']) && !empty($input['password'])) {
    $action = 'login';
}
// Mit jelenet a ??:The `??` operator in PHP is called the "null coalescing operator." It is used to check if a variable is set and is not null. If the variable is set and not null, it returns its value; otherwise, it returns the value on the right side of the operator.
if ($action === 'register') {
    $name = trim((string)($input['name'] ?? 'nincs név'));
    $username = trim((string)($input['username'] ?? ''));
    $email = trim((string)($input['email'] ?? ''));
    $password = (string)($input['password'] ?? '');

    if ($name === '' || $username === '' || $email === '' || $password === '') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Minden mező kitöltése kötelező.',
        ]);
        $conn->close();
        exit;
    }

    $stmt = $conn->prepare('SELECT id FROM felhasznalok WHERE username = ? OR email = ?');
    $stmt->bind_param('ss', $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt->close();
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'A felhasználónév vagy az email cím már foglalt.',
        ]);
        $conn->close();
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $insertStmt = $conn->prepare('INSERT INTO felhasznalok (name, username, email, password) VALUES (?, ?, ?, ?)');
    $insertStmt->bind_param('ssss', $name, $username, $email, $hashedPassword);

    if ($insertStmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Sikeres regisztráció!',
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Regisztráció sikertelen: ' . $insertStmt->error,
        ]);
    }

    $insertStmt->close();
    $conn->close();
    exit;
}

if ($action === 'login' || $action === '') {
    $loginValue = trim((string)($input['loginUser'] ?? $input['username'] ?? ''));
    $password = (string)($input['password'] ?? '');

    if ($loginValue === '' || $password === '') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Add meg a felhasználónevet/emailt és a jelszót.',
        ]);
        $conn->close();
        exit;
    }

    $stmt = $conn->prepare('SELECT id, name, username, email, password FROM felhasznalok WHERE username = ? OR email = ? LIMIT 1');
    $stmt->bind_param('ss', $loginValue, $loginValue);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Hibás felhasználónév/email vagy jelszó.',
        ]);
        $conn->close();
        exit;
    }

    $user = $result->fetch_assoc();
    if (!password_verify($password, $user['password'])) {
        $stmt->close();
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Hibás felhasználónév/email vagy jelszó.',
        ]);
        $conn->close();
        exit;
    }

    $stmt->close();
    echo json_encode([
        'success' => true,
        'message' => 'Sikeres bejelentkezés!',
        'user' => [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'username' => $user['username'],
            'email' => $user['email'],
        ],
    ]);
    $conn->close();
    exit;
}

http_response_code(400);
echo json_encode([
    'success' => false,
    'message' => 'Érvénytelen kérés.',
]);
$conn->close();
?>