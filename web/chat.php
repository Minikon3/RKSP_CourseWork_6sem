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

// Обработка отправки сообщения
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message']) && isset($_GET['chat_id'])) {
    $user_id = $_SESSION['user_id'];
    $chat_id = $_GET['chat_id'];
    $message = $mysqli->real_escape_string($_POST['message']);

    // Вставляем сообщение в базу данных
    $query_insert = "INSERT INTO messages (chat_id, sender_id, message) VALUES ('$chat_id', '$user_id', '$message')";
    $result_insert = $mysqli->query($query_insert);

    if (!$result_insert) {
        echo "Ошибка: " . $mysqli->error;
    }
}

// Получаем список чатов для текущего пользователя
$user_id = $_SESSION['user_id'];
$query = "SELECT chats.id, chats.name FROM chats
          INNER JOIN chat_members ON chats.id = chat_members.chat_id
          WHERE chat_members.user_id = $user_id";
$result = $mysqli->query($query);

// Получаем сообщения для выбранного чата (если он выбран)
$chat_messages = [];
if (isset($_GET['chat_id'])) {
    $chat_id = $_GET['chat_id'];
    $query_messages = "SELECT messages.id, messages.sender_id, messages.message, messages.timestamp, users.username 
                       FROM messages
                       INNER JOIN users ON messages.sender_id = users.id
                       WHERE messages.chat_id = $chat_id
                       ORDER BY messages.timestamp";
    $result_messages = $mysqli->query($query_messages);
    while ($row = $result_messages->fetch_assoc()) {
        $chat_messages[] = $row;
    }
}

// Закрываем соединение с базой данных
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Чат</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #414a4c;
            color: #fff; /* белый текст */
            padding: 20px;
        }
        a {
            color: #fff; /* цвет ссылок */
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
        }
        a:hover {
            text-decoration: underline;
        }
        h2 {
            margin-bottom: 20px;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        .chat-list {
            flex: 1;
            padding: 10px;
            border-right: 1px solid #ccc;
            margin-right: 20px;
        }
        .chat-list h3 {
            margin-bottom: 10px;
        }
        .chat-list ul {
            list-style-type: none;
            padding: 0;
        }
        .chat-list ul li {
            margin-bottom: 5px;
        }
        .chat-messages {
            flex: 2;
            padding: 10px;
        }
        .chat-messages h3 {
            margin-bottom: 10px;
        }
        .chat-messages ul {
            list-style-type: none;
            padding: 0;
        }
        .chat-messages ul li {
            margin-bottom: 5px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            background-color: #534b4f; /* цвет кнопок */
            color: #fff; /* белый текст */
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #414a4c; /* основной цвет при наведении */
        }
    </style>
</head>
<body>
    <a href="logout.php">Выйти</a>
    <h2>Чат</h2>
    <div class="container">
        <div class="chat-list">
            <h3>Список чатов</h3>
            <ul>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <li><a href="?chat_id=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a></li>
                <?php } ?>
            </ul>
            <a href="createchat.php">Создать чат</a>
            <a href="manage_chats.php">Управление чатами</a>
        </div>
        <div class="chat-messages">
            <h3>Сообщения</h3>
            <?php if (isset($_GET['chat_id'])) { ?>
            <ul id="message-list">
                <?php foreach ($chat_messages as $message) { ?>
                    <li><strong><?php echo $message['username']; ?>:</strong> <?php echo $message['message']; ?> (<?php echo $message['timestamp']; ?>)</li>
                <?php } ?>
            </ul>
            <form id="message-form">
                <label for="message">Введите сообщение:</label><br>
                <textarea id="message" name="message" rows="4" cols="50"></textarea><br>
                <input type="submit" value="Отправить">
            </form>
            <?php } else { ?>
                <div id="no-chat-selected">
                    <p>Выберите чат для просмотра сообщений.</p>
                </div>
            <?php } ?>
        </div>
    </div>
    <script>

        $(document).ready(function(){
            // Функция для обновления сообщений
            function updateMessages() {
                var chatId = "<?php echo isset($_GET['chat_id']) ? $_GET['chat_id'] : ''; ?>";
                $.ajax({
                    url: "get_messages.php?chat_id=" + chatId,
                    type: "GET",
                    success: function(data) {
                        $("#message-list").html(data);
                    }
                });
            }

            // Вызываем функцию обновления сообщений каждые 5 секунд
            setInterval(updateMessages, 5000);

            // Обработка отправки сообщения
            $("#message-form").submit(function(event) {
                event.preventDefault();
                var formData = $(this).serialize();
                $.ajax({
                    url: "send_message.php?chat_id=<?php echo isset($_GET['chat_id']) ? $_GET['chat_id'] : ''; ?>",
                    type: "POST",
                    data: formData,
                    success: function(data) {
                        // После успешной отправки сообщения можно обновить список сообщений
                        updateMessages();
                    }
                });
                document.getElementById("message").value = "";
            });
        });
    </script>
</body>
</html>
