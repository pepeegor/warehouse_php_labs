<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Обработка удаления продукта
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = $_POST['delete_id'];

    // Сначала удаляем все заявки на этот продукт
    $sql = "DELETE FROM requests WHERE product_id = $id";
    $conn->query($sql);

    // Затем удаляем сам продукт
    $sql = "DELETE FROM products WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        // Можно добавить сообщение об успешном удалении
    } else {
        $error = "Ошибка: " . $sql . "<br>" . $conn->error;
    }
}

// Обработка изменения количества продукта
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id']) && isset($_POST['new_quantity'])) {
    $product_id = $_POST['product_id'];
    $new_quantity = $_POST['new_quantity'];

    // Обновляем количество продукта в базе данных
    $sql = "UPDATE products SET quantity = $new_quantity WHERE id = $product_id";

    if ($conn->query($sql) === TRUE) {
        // Можно добавить сообщение об успешном изменении количества
    } else {
        $error = "Ошибка: " . $sql . "<br>" . $conn->error;
    }
}

// Запрос продуктов из базы данных
$worker_id = $_SESSION['user_id'];
$sql = "SELECT * FROM products WHERE worker_id = $worker_id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Панель управления - Продукты</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/products.css">
</head>

<body>

    <?php include '../includes/navbar.php'; ?>

    <div class="container">
        <h2>Панель управления - Продукты</h2>

        <div class="product-actions">
            <a href="add_product.php" class="button">Добавить продукт</a>
            <a href="requests.php" class="button">Просмотреть заказы</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Описание</th>
                    <th>Вес</th>
                    <th>Цвет</th>
                    <th>Цена</th>
                    <th>Количество</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row["id"]; ?></td>
                            <td><?php echo $row["name"]; ?></td>
                            <td><?php echo $row["description"]; ?></td>
                            <td><?php echo $row["weight"]; ?></td>
                            <td><?php echo $row["color"]; ?></td>
                            <td><?php echo $row["price"]; ?></td>
                            <td><?php echo $row["quantity"]; ?></td>
                            <td>
                                <a href='edit_product.php?id=<?php echo $row["id"]; ?>' class="action-button">Редактировать</a>
                                <form method='post' style='display:inline;'
                                    onsubmit="return confirm('Вы уверены, что хотите удалить этот продукт?');">
                                    <input type='hidden' name='delete_id' value='<?php echo $row["id"]; ?>'>
                                    <button type='submit' class="action-button danger-button">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">Нет продуктов.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>

</html>