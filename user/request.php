<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Получаем количество продукта на складе
    $sql = "SELECT quantity FROM products WHERE id = $product_id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $product_quantity = $row['quantity'];

    // Проверяем, достаточно ли продукта на складе
    if ($quantity > $product_quantity) {
        $error = "Недостаточно продукта на складе.";
    } else {
        // Проверяем, существует ли уже заявка на этот продукт от этого пользователя
        $sql = "SELECT * FROM requests WHERE user_id = $user_id AND product_id = $product_id AND status = 'pending'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $error = "Вы уже создали заявку на этот продукт.";
        } else {
            // Создаем новую заявку
            $sql = "INSERT INTO requests (user_id, product_id, status, quantity) VALUES ($user_id, $product_id, 'pending', $quantity)";

            if ($conn->query($sql) === TRUE) {
                $success = "Заявка успешно создана.";
            } else {
                $error = "Ошибка: " . $sql . "<br>" . $conn->error;
            }
        }
    }
} else {
    header('Location: ../user/'); // Или на главную страницу user
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Заявка на продукт</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/request.css">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container">
    <h2>Заявка на продукт</h2>

    <?php if (isset($success)): ?>
        <div class="success-message">
            <p><?php echo $success; ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="error-message">
            <p><?php echo $error; ?></p>
        </div>
    <?php endif; ?>

    <a href="../user/" class="button">Вернуться к списку продуктов</a>
</div>

</body>
</html>