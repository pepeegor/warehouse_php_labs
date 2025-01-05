<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$worker_id = $_SESSION['user_id'];

// Получаем текущие веса из базы данных
$sql = "SELECT * FROM weights WHERE worker_id = $worker_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $weights = $result->fetch_assoc();
} else {
    // Если весов нет, создаем новую запись с дефолтными значениями
    $sql = "INSERT INTO weights (worker_id) VALUES ($worker_id)";
    $conn->query($sql);
    $weights = [
        'price_weight' => 0.33,
        'weight_weight' => 0.33,
        'color_weight' => 0.33,
        'history_weight' => 0.5, // Добавляем history_weight с дефолтным значением
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $price_weight = $_POST['price_weight'];
    $weight_weight = $_POST['weight_weight'];
    $color_weight = $_POST['color_weight'];
    $history_weight = $_POST['history_weight']; // Получаем history_weight из формы

    // Обновляем веса в базе данных
    $sql = "UPDATE weights SET 
            price_weight = $price_weight, 
            weight_weight = $weight_weight, 
            color_weight = $color_weight,
            history_weight = $history_weight  
            WHERE worker_id = $worker_id";
    if ($conn->query($sql) === TRUE) {
        $success = "Веса успешно обновлены.";
    } else {
        $error = "Ошибка: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Настройка весов</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/weights.css">
</head>

<body>

    <?php include '../includes/navbar.php'; ?>

    <div class="container">
        <h2>Настройка весов</h2>

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

        <form method="post">
            <div>
                <label for="price_weight">Вес параметра Цена (0-1):</label>
                <input type="number" id="price_weight" name="price_weight"
                    value="<?php echo $weights['price_weight']; ?>" min="0" max="1" step="0.01" required maxlength="3">
            </div>
            <div>
                <label for="weight_weight">Вес параметра Вес (0-1):</label>
                <input type="number" id="weight_weight" name="weight_weight"
                    value="<?php echo $weights['weight_weight']; ?>" min="0" max="1" step="0.01" required maxlength="3">
            </div>
            <div>
                <label for="color_weight">Вес параметра Цвет (0-1):</label>
                <input type="number" id="color_weight" name="color_weight"
                    value="<?php echo $weights['color_weight']; ?>" min="0" max="1" step="0.01" required maxlength="3">
            </div>
            <div>
                <label for="history_weight">Вес истории заявок (0-1):</label>
                <input type="number" id="history_weight" name="history_weight"
                    value="<?php echo $weights['history_weight']; ?>" min="0" max="1" step="0.01" required
                    maxlength="3">
            </div>
            <button type="submit">Сохранить</button>
        </form>
    </div>

</body>

</html>