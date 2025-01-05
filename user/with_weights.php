<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// Функция для нормализации значения в диапазоне от 0 до 1
function normalize($value, $min, $max)
{
    return ($value - $min) / ($max - $min);
}

// Обработка поиска
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    // Запрос продуктов из базы данных с поиском
    $sql = "SELECT p.*
             FROM products p
             WHERE (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
} else {
    // Запрос продуктов из базы данных без поиска
    $sql = "SELECT p.*
             FROM products p";
}
$result = $conn->query($sql);

// Получаем все продукты
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// Получаем ID работника для каждого продукта
$worker_ids = [];
foreach ($products as $product) {
    $worker_ids[$product['id']] = $product['worker_id'];
}

// Получаем веса для каждого работника
$weights = [];
foreach ($worker_ids as $product_id => $worker_id) {
    $sql = "SELECT * FROM weights WHERE worker_id = $worker_id";
    $result = $conn->query($sql);
    // Проверяем, есть ли результаты
    if ($result && $result->num_rows > 0) {
        $weights[$product_id] = $result->fetch_assoc();
    } else {
        // Если весов нет, используй дефолтные значения
        $weights[$product_id] = [
            'price_weight' => 0.33,
            'weight_weight' => 0.33,
            'color_weight' => 0.33,
            'history_weight' => 0.5,
        ];
    }
}

// --- Расчет рейтинга с учетом истории заявок ---

// Получаем историю заявок пользователя
$user_id = $_SESSION['user_id'];
$sql = "SELECT product_id FROM requests WHERE user_id = $user_id";
$result = $conn->query($sql);
$user_history = [];
while ($row = $result->fetch_assoc()) {
    $user_history[] = $row['product_id'];
}

// Рассчитываем рейтинг для каждого продукта
$product_ratings = [];
foreach ($products as $key => $product) { // Используем $key в качестве индекса
    $rating_history = 0;

    // Рейтинг истории (учитываем количество заявок)
    $sql = "SELECT COUNT(*) AS request_count 
             FROM requests 
             WHERE user_id = $user_id AND product_id = " . $product['id'];
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $rating_history = $row['request_count'];

    // Нормализация 
    $price_normalized = normalize($product['price'], 0, 1000);
    $weight_normalized = normalize($product['weight'], 0, 10);
    $color_normalized = normalize(strlen($product['color']), 0, 20);

    // Расчет рейтинга с учетом весов (без popularity)
    $final_rating = 0;
    $final_rating += $weights[$product['id']]['price_weight'] * $price_normalized;
    $final_rating += $weights[$product['id']]['weight_weight'] * $weight_normalized;
    $final_rating += $weights[$product['id']]['color_weight'] * $color_normalized;
    $final_rating += $weights[$product['id']]['history_weight'] * $rating_history;

    $product_ratings[$key] = $final_rating; // Используем $key в качестве индекса
}

// Сортируем продукты по рейтингу в порядке убывания
arsort($product_ratings);

// Получаем отсортированный список продуктов
$sorted_products = [];
foreach ($product_ratings as $key => $rating) {
    // Добавляем рейтинг к массиву продукта
    $products[$key]['rating'] = $rating; 
    $sorted_products[] = $products[$key]; // Используем $key для доступа к продукту
}
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
                <input type="text" name="search" placeholder="Поиск..." maxlength="255"
                    value="<?php echo isset($search) ? $search : ''; ?>">
                <button type="submit">Найти</button>
            </form>
        </div>
        <br>

        <div class="products-grid">
            <?php if (count($sorted_products) > 0): ?>
                <?php foreach ($sorted_products as $row): ?>
                    <div class="product-card">
                        <div class="product-header">
                            <h3><?php echo $row['name'] ?? ''; ?></h3>
                        </div>
                        <div class="product-body">
                            <?php if (isset($row['image'])): ?>
                                <img src="../<?php echo $row['image']; ?>" alt="<?php echo $row['name'] ?? ''; ?>">
                            <?php endif; ?>
                            <p><strong>Описание:</strong> <?php echo $row['description'] ?? ''; ?></p>
                            <p><strong>Количество на складе:</strong> <?php echo $row['quantity'] ?? ''; ?></p>
                            <p><strong>Вес:</strong> <?php echo $row['weight'] ?? ''; ?> кг</p>
                            <p><strong>Цвет:</strong> <?php echo $row['color'] ?? ''; ?></p>
                            <p><strong>Цена:</strong> <?php echo $row['price'] ?? ''; ?></p>
                            <p><strong>Рейтинг:</strong> <?php echo $row['rating'] ?? ''; ?></p> 
                        </div>
                        <div class="product-actions">
                            <form method="post" action="request.php">
                                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                <label for="quantity">Запрашиваемое количество:</label>
                                <input type="number" name="quantity" value="1" min="1">
                                <br>
                                <?php if (isset($_GET['error']) && $_GET['product_id'] == $row['id']): ?>
                                    <div class="error"><?php echo htmlspecialchars($_GET['error']); ?></div>
                                <?php endif; ?>
                                <button type="submit" class="button">Запросить</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Нет продуктов.</p>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>