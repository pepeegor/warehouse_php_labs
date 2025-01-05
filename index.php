<?php require_once 'includes/auth.php'; ?>

<!DOCTYPE html>
<html>

<head>
  <title>Система управления складом</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/index.css">
</head>

<body>

  <?php include 'includes/navbar.php'; ?>

  <div class="container">
    <header>
      <h1>Система управления складом</h1>
      <p>Добро пожаловать в нашу систему управления складом! Здесь вы можете управлять своими продуктами, отслеживать
        заказы и оптимизировать работу склада.</p>
    </header>

    <?php if (!isLoggedIn()): ?>
      <section class="login-section">
        <h2>Вход не выполнен</h2>
        <p>Войдите в систему или зарегистрируйтесь, чтобы получить доступ к функциям управления складом.</p>
        <a href="login.php" class="button">Войти</a>
        <a href="register.php" class="button">Регистрация</a>
      </section>
    <?php endif; ?>

    <main>
      <section class="features">
        <h2>Возможности системы</h2>
        <ul>
          <li>Управление продуктами: добавление, редактирование, удаление продуктов.</li>
          <li>Управление заказами: просмотр, обработка заявок.</li>
        </ul>
      </section>

      <section class="benefits">
        <h2>Преимущества</h2>
        <ul>
          <li>Оптимизация работы склада.</li>
          <li>Улучшение контроля за запасами.</li>
          <li>Повышение эффективности обработки заказов.</li>
          <li>Удобный и интуитивно понятный интерфейс.</li>
        </ul>
      </section>
    </main>

    <footer>
      <p>&copy; 2023 Система управления складом</p>
    </footer>
  </div>

</body>

</html>