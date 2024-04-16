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

$user_id = $_SESSION['user_id'];
$chat_id = $mysqli->real_escape_string($_GET['chat_id']);
$message = $mysqli->real_escape_string($_POST['message']);

// Вставляем сообщение в базу данных
$query_insert = "INSERT INTO messages (chat_id, sender_id, message) VALUES ('$chat_id', '$user_id', '$message')";
$result_insert = $mysqli->query($query_insert);

if (!$result_insert) {
    exit("Ошибка: " . $mysqli->error);
}

// Закрываем соединение с базой данных
$mysqli->close();

// Возвращаем успешный ответ
echo "Сообщение успешно отправлено.";
?>
