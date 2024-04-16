<?php
session_start();

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Подключаемся к базе данных
$mysqli = new mysqli("db", "root", "examplepassword", "mydb");

// Проверяем соединение с базой данных
if ($mysqli->connect_error) {
    die("Ошибка подключения к базе данных: " . $mysqli->connect_error);
}

// Получаем идентификатор текущего пользователя
$user_id = $_SESSION['user_id'];

// Получаем список чатов, в которых пользователь является администратором
$query_chats = "SELECT id, name FROM chats WHERE admin_id = $user_id";
$result_chats = $mysqli->query($query_chats);

// Получаем список всех пользователей
$query_users = "SELECT id, username FROM users";
$result_users = $mysqli->query($query_users);

// Обработка удаления чата
if (isset($_POST['delete_chat'])) {
    $chat_id = $_POST['delete_chat'];
    // Удаляем чат и связанные с ним сообщения и участников
    $query_delete_chat = "DELETE FROM chats WHERE id = $chat_id";
    $query_delete_messages = "DELETE FROM messages WHERE chat_id = $chat_id";
    $query_delete_members = "DELETE FROM chat_members WHERE chat_id = $chat_id";
    $mysqli->query($query_delete_messages);
    $mysqli->query($query_delete_members);
    $mysqli->query($query_delete_chat);
    // Перезагружаем страницу после удаления чата
    header("Location: manage_chats.php");
    exit();
}

// Обработка добавления пользователя в чат
if (isset($_POST['add_user'])) {
    $chat_id = $_POST['chat_id'];
    $user_id_to_add = $_POST['user_id'];
    // Проверяем, что пользователь существует
    $query_check_user = "SELECT * FROM users WHERE id = $user_id_to_add";
    $result_check_user = $mysqli->query($query_check_user);
    if ($result_check_user->num_rows > 0) {
        // Проверяем, что пользователь не присутствует уже в чате
        $query_check_membership = "SELECT * FROM chat_members WHERE user_id = $user_id_to_add AND chat_id = $chat_id";
        $result_check_membership = $mysqli->query($query_check_membership);
        if ($result_check_membership->num_rows == 0) {
            // Добавляем пользователя в чат
            $query_add_user = "INSERT INTO chat_members (user_id, chat_id) VALUES ($user_id_to_add, $chat_id)";
            $mysqli->query($query_add_user);
            $message = "Пользователь добавлен в чат";
        } else {
            $error = "Пользователь уже присутствует в чате";
        }
    } else {
        $error = "Пользователь с указанным ID не существует";
    }
}

// Обработка удаления пользователя из чата
if (isset($_POST['remove_user'])) {
    $chat_id = $_POST['chat_id'];
    $user_id_to_remove = $_POST['user_id'];
    // Проверяем, что пользователь присутствует в чате
    $query_check_membership = "SELECT * FROM chat_members WHERE user_id = $user_id_to_remove AND chat_id = $chat_id";
    $result_check_membership = $mysqli->query($query_check_membership);
    if ($result_check_membership->num_rows > 0) {
        // Удаляем пользователя из чата
        $query_remove_user = "DELETE FROM chat_members WHERE user_id = $user_id_to_remove AND chat_id = $chat_id";
        $mysqli->query($query_remove_user);
        $message = "Пользователь удалён из чата";
    } else {
        $error = "Пользователь уже отсутствует в чате";
    }
}

// Обработка изменения названия чата
if (isset($_POST['change_chat_name'])) {
    $chat_id = $_POST['chat_id'];
    $new_name = $_POST['new_name'];
    // Обновляем название чата
    $query_change_name = "UPDATE chats SET name = '$new_name' WHERE id = $chat_id";
    $mysqli->query($query_change_name);
    // Перезагружаем страницу после изменения названия чата
    header("Location: manage_chats.php");
    exit();
}

// Закрываем соединение с базой данных
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Управление чатами</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #414a4c;
            color: #fff;
            padding: 20px;
        }
        h2, h3 {
            margin-bottom: 20px;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            margin-bottom: 20px;
        }
        button {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            background-color: #534b4f;
            color: #fff;
            cursor: pointer;
        }
        button:hover {
            background-color: #414a4c;
        }
        select {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
            background-color: #fff;
            color: #414a4c;
        }
        input[type="text"] {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
            background-color: #fff;
            color: #414a4c;
        }
        .error, .message {
            margin-top: 5px;
            color: red;
        }
    </style>
</head>
<body>
    <h2>Управление чатами</h2>
    <h3>Мои чаты:</h3>
    <ul>
        <?php while ($row = $result_chats->fetch_assoc()) { ?>
            <li>
                <?php echo $row['name']; ?>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="delete_chat" value="<?php echo $row['id']; ?>">
                    <button type="submit">Удалить чат</button>
                </form>
                <!-- Форма для изменения названия чата -->
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="chat_id" value="<?php echo $row['id']; ?>">
                    <input type="text" name="new_name" placeholder="Новое название">
                    <button type="submit" name="change_chat_name">Изменить название</button>
                </form>
                <!-- Форма для добавления пользователя в чат -->
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="chat_id" value="<?php echo $row['id']; ?>">
                    <select name="user_id">
                        <?php mysqli_data_seek($result_users, 0); ?>
                        <?php while ($user_row = $result_users->fetch_assoc()) { ?>
                            <option value="<?php echo $user_row['id']; ?>"><?php echo $user_row['username']; ?></option>
                        <?php } ?>
                    </select>
                    <button type="submit" name="add_user">Добавить пользователя</button>
                    <?php if (isset($error) && isset($_POST['add_user']) && $_POST['chat_id'] == $row['id']) { ?>
                        <p><?php echo $error; ?></p>
                    <?php } elseif (isset($message) && isset($_POST['add_user']) && $_POST['chat_id'] == $row['id']) { ?>
                        <p><?php echo $message; ?></p>
                    <?php } ?>
                </form>
                <!-- Форма для удаления пользователя из чата -->
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="chat_id" value="<?php echo $row['id']; ?>">
                    <select name="user_id">
                        <?php mysqli_data_seek($result_users, 0); ?>
                        <?php while ($user_row = $result_users->fetch_assoc()) { ?>
                            <option value="<?php echo $user_row['id']; ?>"><?php echo $user_row['username']; ?></option>
                        <?php } ?>
                    </select>
                    <button type="submit" name="remove_user">Удалить пользователя</button>
                    <?php if (isset($error) && isset($_POST['remove_user']) && $_POST['chat_id'] == $row['id']) { ?>
                        <p><?php echo $error; ?></p>
                    <?php } elseif (isset($message) && isset($_POST['remove_user']) && $_POST['chat_id'] == $row['id']) { ?>
                        <p><?php echo $message; ?></p>
                    <?php } ?>
                </form>
            </li>
        <?php } ?>
    </ul>
</body>
</html>
