<?php

session_start();

// Уничтожаем все данные сессии
session_destroy();

// Перенаправляем пользователя на главную страницу или страницу входа
header('Location: index.php'); 
exit;

?>