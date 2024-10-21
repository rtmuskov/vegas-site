<?php
session_start();

// Проверка, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit();
}

// Подключение к базе данных
$db_host = 'localhost';
$db_username = 'u2565237_default';
$db_password = '5TJuL3Vlh1fme5f4';
$db_name = 'u2565237_default';

$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Проверка подключения
if ($conn->connect_error) {
    die("Connection failed: " . htmlspecialchars($conn->connect_error));
}

$ip_address = $_SERVER['REMOTE_ADDR'];
$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$session_id = $input['session_id'];

// Удаление сессии
$sql = "DELETE FROM user_sessions WHERE user_id = ? AND session_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $session_id);

$response = [];
if ($stmt->execute()) {
    $response['success'] = true;
    $response['ip_address'] = $ip_address; // Добавляем IP-адрес в ответ
} else {
    $response['success'] = false;
    $response['error'] = $stmt->error;
}

$stmt->close();
$conn->close();

// Возвращаем JSON-ответ об успешном завершении сессии
echo json_encode($response);
?>
