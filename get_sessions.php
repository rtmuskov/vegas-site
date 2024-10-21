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

$user_id = $_SESSION['user_id'];

// Получение текущих сессий пользователя
$sql = "SELECT session_id, ip_address, login_time FROM user_sessions WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$sessions = [];
while ($row = $result->fetch_assoc()) {
    $sessions[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode(['sessions' => $sessions]);
?>
