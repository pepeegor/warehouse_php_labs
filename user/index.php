<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// Обработка поиска
if (isset($_GET['search'])) {
    $search = $_GET['search'];

    // Запрос продуктов из базы данных с поиском
    $sql = "SELECT p.*
            FROM products p
            LEFT JOIN requests r ON p.id = r.product_id
            WHERE p.name LIKE '%$search%' OR p.description LIKE '%$search%' 
            GROUP BY p.id
            ORDER BY p.id";
} else {
    // Запрос продуктов из базы данных без поиска
    $sql = "SELECT p.*
            FROM products p
            LEFT JOIN requests r ON p.id = r.product_id
            GROUP BY p.id
            ORDER BY p.id";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Продукты</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/user_products.css">
</head>

<body>

    <?php include '../includes/navbar.php'; ?>

    <div class="container">
        <h2>Продукты</h2>

        <div class="search-form">
            <form method="get">
                <input type="text" name="search" placeholder="Поиск..."
                    value="<?php echo isset($search) ? $search : ''; ?>">
                <button type="submit">Найти</button>
            </form>
        </div>
        <br>

        <div class="products-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="product-card">
                        <div class="product-header">
                            <h3><?php echo $row['name']; ?></h3>
                        </div>
                        <div class="product-body">
                            <?php if ($row['image']): ?>
                                <img src="../<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                            <?php endif; ?>
                            <p><strong>Описание:</strong> <?php echo $row['description']; ?></p>
                            <p><strong>Количество на складе:</strong> <?php echo $row['quantity']; ?></p>
                            <p><strong>Вес:</strong> <?php echo $row['weight']; ?> кг</p>
                            <p><strong>Цвет:</strong> <?php echo $row['color']; ?></p>
                            <p><strong>Цена:</strong> <?php echo $row['price']; ?></p>
                        </div>
                        <div class="product-actions">
                            <form method="post" action="request.php">
                                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                <label for="quantity">Запрашиваемое количество:</label>
                                <input type="number" name="quantity" value="1" min="1" max="<?php echo $row['quantity']; ?>">
                                <br>
                                <button type="submit" class="button">Запросить</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Нет продуктов.</p>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>