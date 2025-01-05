<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "warehouse";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
  die("Ошибка подключения: " . $conn->connect_error);
}

?>