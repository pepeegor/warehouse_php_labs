<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Получение заказов пользователя из базы данных
$sql = "SELECT r.*, p.name AS product_name 
        FROM requests r
        JOIN products p ON r.product_id = p.id
        WHERE r.user_id = $user_id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Мои заказы</title>
  <link rel="stylesheet" href="../css/style.css"> 
  <link rel="stylesheet" href="../css/orders.css"> 
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container"> 
  <h2>Мои заказы</h2>

  <div class="orders">
    <?php if ($result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): ?>
        <div class="order-card">
          <div class="order-header">
            <h3>Заказ #<?php echo $row['id']; ?></h3> 
            <span class="order-status <?php echo $row['status']; ?>"> 
              <?php echo $row['status']; ?> 
            </span>
          </div>
          <div class="order-body">
            <p><strong>Продукт:</strong> <?php echo $row['product_name']; ?></p>
            <p><strong>Количество:</strong> <?php echo $row['quantity']; ?></p>
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