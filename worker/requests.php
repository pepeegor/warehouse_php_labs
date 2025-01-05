<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Обработка действий с заявками
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $status = 'approved';
        // Получаем количество из заявки
        $sql = "SELECT quantity FROM requests WHERE id = $request_id";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $request_quantity = $row['quantity'];

        // Получаем ID продукта из заявки
        $sql = "SELECT product_id FROM requests WHERE id = $request_id";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $product_id = $row['product_id'];

        // Вычитаем количество из продукта
        $sql = "UPDATE products SET quantity = quantity - $request_quantity WHERE id = $product_id";
        $conn->query($sql);

    } elseif ($action === 'reject') {
        $status = 'rejected';
    } else {
        $error = "Неверное действие.";
    }

    if (isset($status)) {
        $sql = "UPDATE requests SET status = '$status' WHERE id = $request_id";
        if ($conn->query($sql) === TRUE) {
            // Можно добавить сообщение об успешном изменении статуса
        } else {
            $error = "Ошибка: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Получение заявок из базы данных
$worker_id = $_SESSION['user_id'];
$sql = "SELECT r.*, p.name AS product_name, u.username AS user_name 
        FROM requests r
        JOIN products p ON r.product_id = p.id
        JOIN users u ON r.user_id = u.id
        WHERE p.worker_id = $worker_id AND r.status = 'pending'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Заявки</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/requests.css">
</head>

<body>

    <?php include '../includes/navbar.php'; ?>

    <div class="container">
        <h2>Заказы</h2>

        <div class="requests-actions">
            <a href="approved_requests.php" class="button">Принятые заказы</a>
            <a href="rejected_requests.php" class="button">Отклоненные заказы</a>
        </div>

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
                            <span class="order-status <?php echo $row['status']; ?>">
                                <?php
                                switch ($row['status']) {
                                    case 'pending':
                                        echo "Ожидает";
                                        break;
                                    case 'approved':
                                        echo "Принята";
                                        break;
                                    case 'rejected':
                                        echo "Отклонена";
                                        break;
                                }
                                ?>
                            </span>
                        </div>
                        <div class="request-body">
                            <p><strong>Продукт:</strong> <?php echo $row['product_name']; ?></p>
                            <p><strong>Пользователь:</strong> <?php echo $row['user_name']; ?></p>
                            <p><strong>Количество:</strong> <?php echo $row['quantity']; ?></p>
                        </div>
                        <div class="request-actions">
                            <form method="post" style="display: inline-block;">
                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="button success-button">Принять</button>
                            </form>
                            <form method="post" style="display: inline-block;">
                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="button danger-button">Отказать</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Нет заказов.</p>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>