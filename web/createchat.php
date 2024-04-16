<?php
session_start();

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Получаем идентификатор текущего пользователя
$user_id = $_SESSION['user_id'];

// Подключаемся к базе данных
$mysqli = new mysqli("db", "root", "examplepassword", "mydb");

// Проверяем соединение с базой данных
if ($mysqli->connect_error) {
    die("Ошибка подключения к базе данных: " . $mysqli->connect_error);
}

// Получаем список всех пользователей для выбора, исключая текущего пользователя
$query_users = "SELECT id, username FROM users WHERE id != $user_id";
$result_users = $mysqli->query($query_users);

// Обработка отправки формы создания чата
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Проверяем, были ли выбраны пользователи для добавления в чат
    if (!empty($_POST['users']) && !empty($_POST['chat_name'])) {
        $chat_name = $mysqli->real_escape_string($_POST['chat_name']);

        // Вставляем новый чат в таблицу chats
        $query_insert_chat = "INSERT INTO chats (name, admin_id) VALUES ('$chat_name', '$user_id')";
        $result_insert_chat = $mysqli->query($query_insert_chat);

        // Получаем ID только что созданного чата
        $chat_id = $mysqli->insert_id;

        // Добавляем создателя чата в новый чат
        $query_insert_member = "INSERT INTO chat_members (user_id, chat_id) VALUES ('$user_id', '$chat_id')";
        $result_insert_member = $mysqli->query($query_insert_member);

        // Добавляем выбранных пользователей в новый чат
        foreach ($_POST['users'] as $user_id) {
            $query_insert_member = "INSERT INTO chat_members (user_id, chat_id) VALUES ('$user_id', '$chat_id')";
            $result_insert_member = $mysqli->query($query_insert_member);
        }

        // Перенаправляем пользователя на страницу чата с новым чатом
        header("Location: chat.php?chat_id=$chat_id");
        exit();
    } else {
        $error = "Пожалуйста, выберите название чата и добавьте пользователей.";
    }
}

// Закрываем соединение с базой данных
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Создать новый чат</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #414a4c;
            color: #fff;
            padding: 20px;
        }
        h2 {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"],
        input[type="checkbox"],
        input[type="submit"] {
            margin-bottom: 10px;
        }
        input[type="text"],
        input[type="checkbox"] {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            background-color: #fff;
            color: #414a4c;
        }
        input[type="submit"] {
            background-color: #534b4f;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #414a4c;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h2>Создать новый чат</h2>
    <?php if(isset($error)) { ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php } ?>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="chat_name">Название чата:</label><br>
        <input type="text" id="chat_name" name="chat_name"><br><br>
        <label for="users">Пользователи:</label><br>
        <?php while ($row = $result_users->fetch_assoc()) { ?>
            <input type="checkbox" id="user_<?php echo $row['id']; ?>" name="users[]" value="<?php echo $row['id']; ?>">
            <label for="user_<?php echo $row['id']; ?>"><?php echo $row['username']; ?></label><br>
        <?php } ?>
        <br>
        <input type="submit" value="Создать чат">
    </form>
</body>
</html>
