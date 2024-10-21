function validation() {
    var form = document.Formfill;
    var username = form.Username.value;
    var email = form.Email.value;
    var password = form.Password.value;
    var cPassword = form.cPassword.value;

    if (username === "") {
        document.getElementById("result").innerHTML = "Введите имя пользователя*";
        return false;
    } else if (username.length < 6) {
        document.getElementById("result").innerHTML = "Имя пользователя должно быть не менее 6 знаков*";
        return false;
    } else if (email === "") {
        document.getElementById("result").innerHTML = "Введите ваш Email*";
        return false;
    } else if (password === "") {
        document.getElementById("result").innerHTML = "Введите ваш пароль*";
        return false;
    } else if (password !== cPassword) {
        document.getElementById("result").innerHTML = "Пароль не совпадает*";
        return false;
    } else if (cPassword === "") {
        document.getElementById("result").innerHTML = "Введите подтверждение пароля*";
        return false;
    } else if (password.length < 6) {
        document.getElementById("result").innerHTML = "Пароль должен быть не менее 6 знаков*";
        return false;
    } else {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "registration.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        var formData = "Username=" + encodeURIComponent(username) +
                       "&Email=" + encodeURIComponent(email) +
                       "&Password=" + encodeURIComponent(password) +
                       "&cPassword=" + encodeURIComponent(cPassword);
        xhr.send(formData);

        xhr.onload = function() {
            console.log("Response status: " + xhr.status); // Логирование статуса ответа
            console.log("Response text: " + xhr.responseText); // Логирование текста ответа
            
            if (xhr.status === 200) {
                var response = xhr.responseText;
                
                // Дополнительно можно проверить, содержит ли ответ ожидаемое сообщение
                if (response.includes("успешно") || response.includes("success")) {
                    document.getElementById("result").innerHTML = response;
                    document.getElementById("result").style.color = "green";
                    popup.classList.add("open-slide");

                    // Redirect to profile.html after successful registration
                    window.location.href = 'profile.html';
                } else {
                    document.getElementById("result").innerHTML = response;
                    document.getElementById("result").style.color = "red";
                }
            } else {
                console.error("Error: " + xhr.responseText);
                document.getElementById("result").innerHTML = "Ошибка при регистрации. Попробуйте еще раз.";
                document.getElementById("result").style.color = "red";
            }
        };

        xhr.onerror = function() {
            console.error("Request failed");
            document.getElementById("result").innerHTML = "Ошибка сети. Попробуйте еще раз.";
            document.getElementById("result").style.color = "red";
        };

        return false;
    }
}

var popup = document.getElementById('popup');

function CloseSlide() {
    popup.classList.remove("open-slide");
}
