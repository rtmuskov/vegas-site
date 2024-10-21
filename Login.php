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
    $email = htmlspecialchars($_POST['Email']);
    $password = $_POST['Password'];

    // Подготовка и выполнение запроса
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($user_id, $hashed_password);
        $stmt->fetch();
        $stmt->close(); // Закрываем первый запрос перед выполнением нового
        
        if ($user_id && password_verify($password, $hashed_password)) {
            // Успешный вход
            session_start();
            $_SESSION['user_id'] = $user_id;

            // Сохранение информации о сессии
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $stmt_session = $conn->prepare("INSERT INTO user_sessions (user_id, ip_address) VALUES (?, ?)");
            if ($stmt_session) {
                $stmt_session->bind_param("is", $user_id, $ip_address);
                if ($stmt_session->execute()) {
                    $stmt_session->close();
                    header('Location: profile'); 
                    exit();
                } else {
                    $stmt_session->close();
                    header("Location: login.html?error=" . urlencode("Ошибка сохранения сессии: " . $stmt_session->error));
                    exit();
                }
            } else {
                header("Location: login.html?error=" . urlencode("Ошибка подготовки запроса: " . $conn->error));
                exit();
            }
        } else {
            // Неверные учетные данные
            header("Location: login.html?error=" . urlencode("Неверный email или пароль"));
            exit();
        }
    } else {
        $error = $conn->error;
        header("Location: login.html?error=" . urlencode("Ошибка подготовки запроса: " . $error));
        exit();
    }
}

$conn->close();
?>
