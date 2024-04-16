<?php
// Подключаемся к базе данных
$mysqli = new mysqli("db", "root", "examplepassword", "mydb");

// Проверяем соединение с базой данных
if ($mysqli->connect_error) {
    die("Ошибка подключения к базе данных: " . $mysqli->connect_error);
}

// Определяем переменные для сообщений об ошибках и успехе
$error = $success = "";

// Обработка отправки формы регистрации
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Проверяем, что поля формы не пустые
    if (!empty($_POST['username']) && !empty($_POST['password'])) {
        $username = $mysqli->real_escape_string($_POST['username']);
        $password = $_POST['password'];

        // Проверяем, не существует ли уже пользователь с таким именем
        $query_check_user = "SELECT * FROM users WHERE username = '$username'";
        $result_check_user = $mysqli->query($query_check_user);

        if ($result_check_user->num_rows == 0) {
            // Регистрируем нового пользователя
            $query_register_user = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
            if ($mysqli->query($query_register_user)) {
                $success = "Пользователь успешно зарегистрирован!";
            } else {
                $error = "Ошибка регистрации пользователя: " . $mysqli->error;
            }
        } else {
            $error = "Пользователь с таким именем уже существует.";
        }
    } else {
        $error = "Пожалуйста, заполните все поля.";
    }
}

// Закрываем соединение с базой данных
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
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
        <h2>Регистрация нового пользователя</h2>
        <?php if (!empty($error)) { ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php } ?>
        <?php if (!empty($success)) { ?>
            <p style="color:green;"><?php echo $success; ?></p>
            <a href="login.php">Войти</a>
        <?php } ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="username">Имя пользователя:</label><br>
            <input type="text" id="username" name="username"><br><br>
            <label for="password">Пароль:</label><br>
            <input type="password" id="password" name="password"><br><br>
            <input type="submit" value="Зарегистрироваться">
        </form>
    </div>
</body>
</html>
