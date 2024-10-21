<?php
session_start();

// Проверка, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}
// Сохранение IP-адреса пользователя в сессию
$_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
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
$conn->set_charset("utf8");

// Получение данных пользователя
$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['profile-name']);
    $email = htmlspecialchars($_POST['profile-email']);
    $current_password = $_POST['profile-password'];
    $new_password = $_POST['new-password'];
    $confirm_password = $_POST['confirm-password'];
    
    $errors = [];
    $success = "";

    // Проверка текущего пароля
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($stored_password);
    $stmt->fetch();
    $stmt->close();

    if (password_verify($current_password, $stored_password)) {
        // Проверка нового пароля
        if (!empty($new_password)) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            } else {
                $errors[] = "Новые пароли не совпадают.";
            }
        }

        if (empty($errors)) {
            if (!empty($new_password)) {
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
                $stmt->bind_param("sssi", $name, $email, $hashed_password, $user_id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssi", $name, $email, $user_id);
            }

            if ($stmt->execute()) {
                $success = "Изменения сохранены!";
            } else {
                $errors[] = "Ошибка сохранения изменений: " . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        }
    } else {
        $errors[] = "Неверный текущий пароль.";
    }
}

// Получение актуальных данных пользователя из базы данных
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($name, $email);
    $stmt->fetch();
    $stmt->close();
} else {
    die("Ошибка выполнения запроса: " . htmlspecialchars($conn->error));
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мой профиль</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Стили для модального окна */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    
    <div class="profile-container">
        <a href="https://vegaswishlist.ru" class="home-link">
            <img src="favicon.ico" alt="Главная страница" class="home-icon small-icon">
        </a>
        <h1>Мой профиль</h1>
        <form id="profile-form" method="POST">
            <label for="profile-name">Имя:</label>
            <input type="text" id="profile-name" name="profile-name" value="<?php echo htmlspecialchars($name); ?>">
            
            <label for="profile-email">Электронная почта:</label>
            <input type="email" id="profile-email" name="profile-email" value="<?php echo htmlspecialchars($email); ?>">
            
            <label for="profile-password">Текущий пароль:</label>
            <input type="password" id="profile-password" name="profile-password" required>
            
            <label for="new-password">Новый пароль:</label>
            <input type="password" id="new-password" name="new-password">
            
            <label for="confirm-password">Подтвердите новый пароль:</label>
            <input type="password" id="confirm-password" name="confirm-password">
            
            <button type="submit">Сохранить изменения</button>
        </form>
        
        <?php if (!empty($success)): ?>
            <p><?php echo $success; ?></p>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        
        <h2>Мои списки желаемых товаров</h2>
        <ul>
            <li>Список 1</li>
            <li>Список 2</li>
            <!-- Добавьте больше списков по мере необходимости -->
        </ul>
        
        <h2>Настройки уведомлений</h2>
        <label for="notifications">Уведомлять о новых предложениях:</label>
        <input type="checkbox" id="notifications" name="notifications" checked>
        
        <h2>Безопасность аккаунта</h2>
        <label for="2fa">Двухфакторная аутентификация:</label>
        <input type="checkbox" id="2fa" name="2fa">
        
        <button type="button" onclick="manageSessions()">Управление сессиями</button>
    </div>
    
    <!-- Модальное окно для управления сессиями -->
    <div id="sessions-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Управление сессиями</h2>
            <div id="sessions-list">
                <!-- Сессии будут загружены здесь -->
            </div>
        </div>
    </div>
    <script>
        async function sendToken(silentToken) {
        console.log('Отправка silent token:', silentToken);

        try {
            const response = await fetch('https://vegaswishlist.ru/exchange_token.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({ 'silent_token': silentToken })
            });

            if (!response.ok) {
                throw new Error(`Ошибка HTTP: ${response.status}`);
            }

            const data = await response.json();
            console.log('Ответ от сервера:', data);

            if (data.status === 'success') {
                // Отобразить информацию о пользователе
                document.getElementById('profile-info').innerText = `Добро пожаловать, ${data.message.name}`;
            } else {
                // Показать сообщение об ошибке
                alert('Ошибка: ' + data.message);
            }
        } catch (error) {
            console.error('Ошибка при отправке запроса:', error);
            alert('Ошибка при отправке запроса. Попробуйте снова позже.');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Захват payload из URL
        const urlParams = new URLSearchParams(window.location.search);
        const payloadParam = urlParams.get('payload');

        if (payloadParam) {
            try {
                const payload = JSON.parse(decodeURIComponent(payloadParam));
                const silentToken = payload.token;

                if (silentToken) {
                    console.log('Silent token найден:', silentToken);
                    console.log('Отправка silent token на сервер:', silentToken);
                    sendToken(silentToken);
                } else {
                    console.error('Silent token отсутствует в payload');
                }
            } catch (error) {
                console.error('Ошибка парсинга payload:', error);
            }
        } else {
            console.error('Payload отсутствует в URL');
        }
    });
        
    
        function manageSessions() {
            var modal = document.getElementById("sessions-modal");
            var sessionsList = document.getElementById("sessions-list");

            // Очистка предыдущих данных
            sessionsList.innerHTML = '';

            // Запрос на сервер для получения текущих сессий
            fetch('get_sessions.php')
                .then(response => response.json())
                .then(data => {
                    if (data.sessions) {
                        data.sessions.forEach(session => {
                            var sessionDiv = document.createElement('div');
                            sessionDiv.textContent = `IP: ${session.ip_address}, Время входа: ${session.login_time}`;
                            var endSessionButton = document.createElement('button');
                            endSessionButton.textContent = 'Завершить сессию';
                            endSessionButton.onclick = () => endSession(session.session_id);
                            sessionDiv.appendChild(endSessionButton);
                            sessionsList.appendChild(sessionDiv);
                            
                        });
                    } else {
                        sessionsList.textContent = 'Нет активных сессий.';
                    }
                });

            modal.style.display = "block";
        }

        function endSession(sessionId) {
            fetch('end_session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ session_id: sessionId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    manageSessions(); // Обновить список сессий
                    if (data.ip_address === "<?php echo $_SESSION['ip_address']; ?>") {
                        window.location.href = 'login'; // Перенаправление на страницу входа
                    }
                } else {
                    alert('Ошибка при завершении сессии.');
                }
            });
        }

        function closeModal() {
            document.getElementById("sessions-modal").style.display = "none";
        }

        window.onclick = function(event) {
            var modal = document.getElementById("sessions-modal");
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const code = urlParams.get('code');  // Получаем код из URL

    if (code) {
        sendCodeToServer(code);  // Отправляем код на сервер
    }
});

// Функция для отправки кода на сервер
async function sendCodeToServer(code) {
    console.log('Отправка кода:', code);

    try {
        const response = await fetch('https://vegaswishlist.ru/exchange_token.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({ 'code': code })  // Передаём код
        });

        const data = await response.json();
        console.log('Ответ сервера:', data);

        if (data.status === 'success') {
            // Если обмен токенов успешен, перенаправляем пользователя
            window.location.href = 'https://vegaswishlist.ru/dashboard';  // Перенаправление
        } else {
            alert('Ошибка: ' + data.message);  // Обрабатываем ошибку
        }
    } catch (error) {
        console.error('Ошибка при отправке кода на сервер:', error);
    }
}
</script>
</body>
</html>
