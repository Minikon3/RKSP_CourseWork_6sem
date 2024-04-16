<?php
session_start();
// Удаляем все данные сессии
session_destroy();
// Перенаправляем пользователя на страницу входа
header("Location: login.php");
exit();
?>
