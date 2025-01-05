<?php

session_start();

function isLoggedIn() {
  return isset($_SESSION['user_id']);
}

function isAdmin() {
  return isLoggedIn() && $_SESSION['role'] === 'worker';
}

?>