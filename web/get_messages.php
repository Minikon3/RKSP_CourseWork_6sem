<?php
session_start();

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id']) || !isset($_GET['chat_id'])) {
    exit("Ошибка: Пользователь не авторизован или не выбран чат.");
}

// Подключаемся к базе данных
$mysqli = new mysqli("db", "root", "examplepassword", "mydb");

// Проверяем соединение с базой данных
if ($mysqli->connect_error) {
    exit("Ошибка подключения к базе данных: " . $mysqli->connect_error);
}

$chat_id = $mysqli->real_escape_string($_GET['chat_id']);

// Получаем сообщения для выбранного чата
$query_messages = "SELECT messages.id, messages.sender_id, messages.message, messages.timestamp, users.username 
                   FROM messages
                   INNER JOIN users ON messages.sender_id = users.id
                   WHERE messages.chat_id = $chat_id
                   ORDER BY messages.timestamp";
$result_messages = $mysqli->query($query_messages);

// Формируем список сообщений в HTML-формате
$message_list = '';
while ($row = $result_messages->fetch_assoc()) {
    $message_list .= '<li><strong>' . $row['username'] . ':</strong> ' . $row['message'] . ' (' . $row['timestamp'] . ')</li>';
}

// Закрываем соединение с базой данных
$mysqli->close();

// Возвращаем список сообщений
echo $message_list;
?>
