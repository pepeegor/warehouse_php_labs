<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Обработка отмены принятой заявки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];

    if ($action === 'reject') {
        // Получаем количество из заявки
        $sql = "SELECT quantity, product_id FROM requests WHERE id = $request_id";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $request_quantity = $row['quantity'];
        $product_id = $row['product_id'];

        // Возвращаем количество к продукту
        $sql = "UPDATE products SET quantity = quantity + $request_quantity WHERE id = $product_id";
        $conn->query($sql);

        // Изменяем статус заявки на "rejected"
        $sql = "UPDATE requests SET status = 'rejected' WHERE id = $request_id";
        if ($conn->query($sql) === TRUE) {
            // Можно добавить сообщение об успешной отмене заявки
        } else {
            $error = "Ошибка: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Получение принятых заявок из базы данных
$worker_id = $_SESSION['user_id'];
$sql = "SELECT r.*, p.name AS product_name, u.username AS user_name 
        FROM requests r
        JOIN products p ON r.product_id = p.id
        JOIN users u ON r.user_id = u.id
        WHERE p.worker_id = $worker_id AND r.status = 'approved'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Принятые заявки</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/approved_requests.css">
</head>

<body>

    <?php include '../includes/navbar.php'; ?>

    <div class="container">
        <h2>Принятые заказы</h2>

        <a href="requests.php" class="button">Вернуться к списку заказов</a>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <p><?php echo $error; ?></p>
            </div>
        <?php endif; ?>

        <div class="requests-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="request-card">
                        <div class="request-header">
                            <h3>Заказ #<?php echo $row['id']; ?></h3>
                            <span class="order-status approved">Принята</span>
                        </div>
                        <div class="request-body">
                            <p><strong>Продукт:</strong> <?php echo $row['product_name']; ?></p>
                            <p><strong>Пользователь:</strong> <?php echo $row['user_name']; ?></p>
                            <p><strong>Количество:</strong> <?php echo $row['quantity']; ?></p>
                        </div>
                        <div class="request-actions">
                            <form method="post" style="display: inline-block;">
                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="button danger-button">Отклонить</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Нет принятых заказов.</p>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>