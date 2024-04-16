<?php
session_start();

// Проверяем, была ли отправлена форма
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Проверяем, что поля логина и пароля были заполнены
    if (!empty($_POST['username']) && !empty($_POST['password'])) {
        // Подключаемся к базе данных
        $mysqli = new mysqli("db", "root", "examplepassword", "mydb");

        // Проверяем соединение с базой данных
        if ($mysqli->connect_error) {
            die("Ошибка подключения к базе данных: " . $mysqli->connect_error);
        }

        // Экранируем введенные данные, чтобы предотвратить SQL-инъекции
        $username = $mysqli->real_escape_string($_POST['username']);
        $password = $mysqli->real_escape_string($_POST['password']);

        // Выполняем запрос к базе данных для проверки пользователя
        $query = "SELECT id, username FROM users WHERE username='$username' AND password='$password'";
        $result = $mysqli->query($query);

        // Если найден пользователь с введенными учетными данными
        if ($result->num_rows == 1) {
            // Устанавливаем сессионную переменную для авторизации пользователя
            $row = $result->fetch_assoc();
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];

            // Перенаправляем пользователя на главную страницу или другую защищенную страницу
            header("Location: chat.php");
            exit();
        } else {
            // Если пользователь с введенными учетными данными не найден
            $error = "Неправильное имя пользователя или пароль.";
        }

        // Закрываем соединение с базой данных
        $mysqli->close();
    } else {
        $error = "Пожалуйста, заполните все поля.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #414a4c;
            color: #fff; /* белый текст */
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            max-width: 400px; /* Ширина контейнера */
            width: 100%;
        }
        h2 {
            color: #fff; /* белый текст */
        }
        label {
            display: block;
            margin-bottom: 10px;
        }
        input[type="text"],
        input[type="password"],
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            background-color: #534b4f; /* цвет кнопки */
            color: #fff; /* белый текст */
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #414a4c; /* основной цвет при наведении */
        }
        p.error {
            color: red;
        }
        a {
            color: #fff; /* цвет ссылок */
            text-decoration: none;
            margin-bottom: 20px;
            display: block;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="register.php">Регистрация</a>
        <h2>Вход</h2>
        <?php if(isset($error)) { ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php } ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="username">Имя пользователя:</label><br>
            <input type="text" id="username" name="username"><br>
            <label for="password">Пароль:</label><br>
            <input type="password" id="password" name="password"><br><br>
            <input type="submit" value="Войти">
        </form>
    </div>
</body>
</html>