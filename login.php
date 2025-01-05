<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$username = $password = "";
$username_err = $password_err = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'];
  $password = $_POST['password'];

  // Валидация имени пользователя
  if (empty(trim($username))) {
    $username_err = "Пожалуйста, введите имя пользователя.";
  }

  // Валидация пароля
  if (empty(trim($password))) {
    $password_err = "Пожалуйста, введите пароль.";
  }

  // Если нет ошибок валидации, проверяем учетные данные
  if (empty($username_err) && empty($password_err)) {
    $sql = "SELECT * FROM users WHERE username = ?";

    if ($stmt = mysqli_prepare($conn, $sql)) {
      mysqli_stmt_bind_param($stmt, "s", $param_username);
      $param_username = $username;

      if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) == 1) {
          mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $role);
          if (mysqli_stmt_fetch($stmt)) {
            if (password_verify($password, $hashed_password)) {
              $_SESSION['user_id'] = $id;
              $_SESSION['username'] = $username;
              $_SESSION['role'] = $role;

              if ($role == 'worker') {
                header('Location: worker/');
              } else {
                header('Location: user/');
              }
              exit;
            } else {
              $password_err = "Неверный пароль.";
            }
          }
        } else {
          $username_err = "Пользователь не найден.";
        }
      } else {
        echo "Ошибка: " . $stmt->error;
      }
      mysqli_stmt_close($stmt);
    }
  }
  mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Вход</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/login.css">
</head>

<body>

  <?php include 'includes/navbar.php'; ?>

  <div class="container">
    <h2>Вход</h2>

    <form method="post">
      <div>
        <label for="username">Имя пользователя:</label>
        <input type="text" id="username" name="username" maxlength="20" value="<?php echo $username; ?>">
        <?php if (!empty($username_err)): ?>
          <div class="error-message"><?php echo $username_err; ?></div>
        <?php endif; ?>
      </div>
      <div>
        <label for="password">Пароль:</label>
        <input type="password" id="password" name="password" maxlength="100">
        <?php if (!empty($password_err)): ?>
          <div class="error-message"><?php echo $password_err; ?></div>
        <?php endif; ?>
      </div>
      <button type="submit">Войти</button>
    </form>
  </div>

</body>

</html>