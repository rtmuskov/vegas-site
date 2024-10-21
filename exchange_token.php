<?php
require 'db_connect.php'; // Подключение к базе данных
error_log('Script executed: ' . $_SERVER['SCRIPT_NAME']);
error_log('POST parameters: ' . print_r($_POST, true)); // Логирование POST параметров

// Устанавливаем заголовок Content-Type для JSON
header('Content-Type: application/json');

// Функция для отправки JSON-ответа
function send_response($status, $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit();
}

// Проверяем наличие параметра 'code' в POST
if (!isset($_POST['code'])) {
    error_log('Error: Authorization code is missing'); // Логирование ошибки отсутствия кода
    send_response('error', 'Authorization code is missing');
}

// Получаем значение authorization code из POST
$code = $_POST['code'];

// Параметры вашего приложения VK
$client_id = '51887563';  
$client_secret = '5kwJ83mHBl6ilZneNlG5';  
$redirect_uri = 'https://vegaswishlist.ru/profile';  

// Формируем URL для обмена code на access_token
$token_url = 'https://oauth.vk.com/access_token?' . http_build_query([
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'redirect_uri' => $redirect_uri,
    'code' => $code
]);

// Инициализируем cURL-сессию
$ch = curl_init($token_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

// Логирование ответа от VK API
if (curl_errno($ch)) {
    error_log('Curl error: ' . curl_error($ch)); // Логирование cURL ошибок
}

curl_close($ch);

// Преобразуем JSON-ответ в ассоциативный массив
$token_data = json_decode($response, true);

// Логирование полного ответа VK API
error_log('VK API response: ' . $response);

// Проверяем, успешно ли был обменян code на access_token
if (isset($token_data['access_token'])) {
    $access_token = $token_data['access_token'];
    $vk_id = $token_data['user_id'];

    // Запрос на получение информации о пользователе
    $user_info_url = 'https://api.vk.com/method/users.get?' . http_build_query([
        'access_token' => $access_token,
        'v' => '5.131'
    ]);

    // Получаем информацию о пользователе
    $user_info_response = file_get_contents($user_info_url);
    $user_info_data = json_decode($user_info_response, true);

    // Логирование ответа с информацией о пользователе
    error_log('User info response: ' . $user_info_response);

    if (isset($user_info_data['response'][0])) {
        $user = $user_info_data['response'][0];
        $name = $user['first_name'] . ' ' . $user['last_name'];

        // Проверка существования пользователя в базе данных
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE vk_id = ?");
        $stmt_check->bind_param("i", $vk_id);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            // Пользователь уже существует, обновляем информацию
            $stmt_update = $conn->prepare("UPDATE users SET name = ? WHERE vk_id = ?");
            $stmt_update->bind_param("si", $name, $vk_id);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            // Пользователь не существует, добавляем новую запись
            $stmt_insert = $conn->prepare("INSERT INTO users (vk_id, name) VALUES (?, ?)");
            $stmt_insert->bind_param("is", $vk_id, $name);
            $stmt_insert->execute();
            $stmt_insert->close();
        }

        // Отправляем успешный JSON-ответ с данными пользователя
        send_response('success', ['access_token' => $access_token, 'vk_id' => $vk_id, 'name' => $name]);
    } else {
        // Ошибка при получении данных пользователя
        error_log('Error: Failed to retrieve user information from VK API');
        send_response('error', 'Failed to retrieve user information from VK API');
    }
} else {
    // Логирование ошибки при обмене кода на токен
    error_log('Error: Failed to exchange authorization code for access token: ' . $token_data['error_description']);
    send_response('error', 'Failed to exchange authorization code for access token: ' . $token_data['error_description']);
}

// Закрываем соединение с базой данных
$conn->close();
?>
