<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Получение отклоненных заявок из базы данных
$worker_id = $_SESSION['user_id'];
$sql = "SELECT r.*, p.name AS product_name, u.username AS user_name 
        FROM requests r
        JOIN products p ON r.product_id = p.id
        JOIN users u ON r.user_id = u.id
        WHERE p.worker_id = $worker_id AND r.status = 'rejected'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Отклоненные заявки</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/rejected_requests.css"> 
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container"> 
  <h2>Отклоненные заказы</h2>

  <a href="requests.php" class="button">Вернуться к списку заказов</a> 

  <div class="requests-grid"> 
    <?php if ($result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): ?>
        <div class="request-card">
          <div class="request-header">
            <h3>Заказ #<?php echo $row['id']; ?></h3>
            <span class="order-status rejected">Отклонена</span> 
          </div>
          <div class="request-body">
            <p><strong>Продукт:</strong> <?php echo $row['product_name']; ?></p>
            <p><strong>Пользователь:</strong> <?php echo $row['user_name']; ?></p>
            <p><strong>Количество:</strong> <?php echo $row['quantity']; ?></p>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>Нет отклоненных заявок.</p>
    <?php endif; ?>
  </div>
</div>

</body>
</html>