<?php

// Определение переменных для подключения к базе данных
$db_host = 'localhost';
$db_username = 'u2565237_default';
$db_password = '5TJuL3Vlh1fme5f4';
$db_name = 'u2565237_default';

// Подключение к базе данных
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);
// Проверка подключения
if ($conn->connect_error) {
    die("Connection failed: " . htmlspecialchars($conn->connect_error));
}
$conn->set_charset("utf8");
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['Username']); 
    $email = htmlspecialchars($_POST['Email']);
    $password = $_POST['Password'];
    $cPassword = $_POST['cPassword'];

    // Проверка соответствия паролей
    if ($password !== $cPassword) {
        header("Location: register.html?error=" . urlencode("Пароли не совпадают"));
        exit();
    }

    // Хэширование пароля
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Подготовка и выполнение запроса
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sss", $name, $email, $hashed_password);
        if ($stmt->execute()) {
            header('Location: login');
            exit();
        } else {
            $error = $stmt->error;
            header("Location: register?error=" . urlencode("Ошибка выполнения запроса: " . $error));
            exit();
        }
        $stmt->close();
    } else {
        $error = $conn->error;
        header("Location: register?error=" . urlencode("Ошибка подготовки запроса: " . $error));
        exit();
    }
}

$conn->close();
?>
