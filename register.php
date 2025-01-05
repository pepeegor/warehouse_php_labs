<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$username = $password = $confirm_password = "";
$username_err = $password_err = $confirm_password_err = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'];
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];

  // Валидация имени пользователя
  if (strlen($username) < 4) {
    $username_err = "Имя пользователя должно быть не менее 4 символов.";
  } elseif (strlen($username) > 20) {
    $username_err = "Имя пользователя должно быть не более 20 символов.";
  } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $username_err = "Имя пользователя может содержать только латинские буквы, цифры и знак подчеркивания.";
  }

  // Валидация пароля
  if (strlen($password) < 6) {
    $password_err = "Пароль должен быть не менее 6 символов.";
  } elseif ($password !== $confirm_password) {
    $confirm_password_err = "Пароли не совпадают.";
  }

  // Проверка на существование пользователя с таким же именем (только если нет ошибок в имени пользователя)
  if (empty($username_err)) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      $username_err = "Пользователь с таким именем уже существует.";
    }
    $stmt->close();
  }

  // Если ошибок нет, регистрируем пользователя
  if (empty($username_err) && empty($password_err) && empty($confirm_password_err)) {
    $password = password_hash($password, PASSWORD_DEFAULT);
    $role = 'user';

    $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $password, $role);

    if ($stmt->execute()) {
      header('Location: login.php');
      exit;
    } else {
      // Обработка ошибки вставки данных
      echo "Ошибка: " . $stmt->error;
    }
    $stmt->close();
  }
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Регистрация</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/register.css">
</head>

<body>

  <?php include 'includes/navbar.php'; ?>

  <div class="container">
    <h2>Регистрация</h2>

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
        <input type="password" id="password" name="password" maxlength="100" value="<?php echo $password; ?>">
        <?php if (!empty($password_err)): ?>
          <div class="error-message"><?php echo $password_err; ?></div>
        <?php endif; ?>
      </div>
      <div>
        <label for="confirm_password">Подтвердите пароль:</label>
        <input type="password" id="confirm_password" name="confirm_password" maxlength="100"
          value="<?php echo $confirm_password; ?>">
        <?php if (!empty($confirm_password_err)): ?>
          <div class="error-message"><?php echo $confirm_password_err; ?></div>
        <?php endif; ?>
      </div>
      <button type="submit">Зарегистрироваться</button>
    </form>
  </div>

</body>

</html>