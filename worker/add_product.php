<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];
    $weight = $_POST['weight'];
    $color = $_POST['color'];
    $price = $_POST['price'];

    // Валидация данных
    $errors = [];

    if (empty($name)) {
        $errors[] = "Название не может быть пустым.";
    } elseif (strlen($name) > 255) { 
        $errors[] = "Название не должно превышать 255 символов.";
    }

    if (empty($description)) {
        $errors[] = "Описание не может быть пустым.";
    } elseif (strlen($description) > 1000) { 
        $errors[] = "Описание не должно превышать 1000 символов.";
    }

    if (!is_numeric($quantity) || $quantity < 0) {
        $errors[] = "Количество должно быть неотрицательным числом.";
    }

    if (!is_numeric($weight) || $weight < 0) {
        $errors[] = "Вес должен быть неотрицательным числом.";
    }

    if (empty($color)) {
        $errors[] = "Цвет не может быть пустым.";
    } elseif (strlen($color) > 50) { 
        $errors[] = "Цвет не должен превышать 50 символов.";
    }

    if (!is_numeric($price) || $price < 0) {
        $errors[] = "Цена должна быть неотрицательным числом.";
    }

    // Если ошибок нет, обрабатываем загрузку изображения и добавляем продукт
    if (empty($errors)) {
        // Обработка загрузки изображения (если есть)
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "../uploads/";
            $target_file = $target_dir . basename($_FILES["image"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

            // Проверка типа файла 
            if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
            && $imageFileType != "gif" ) {
                $errors[] = "Извините, разрешены только JPG, JPEG, PNG и GIF файлы.";
            } else {
                // Перемещение загруженного файла в папку uploads
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image = "uploads/" . basename($_FILES["image"]["name"]); // Путь к изображению для базы данных
                } else {
                    $errors[] = "Извините, произошла ошибка при загрузке файла.";
                }
            }
        } else {
            $image = null; // Если изображение не загружено
        }

        // Если после обработки изображения нет ошибок, добавляем продукт
        if (empty($errors)) {
            // Получаем ID работника из сессии
            $worker_id = $_SESSION['user_id'];

            // SQL-запрос с добавлением новых полей
            $sql = "INSERT INTO products (name, description, image, worker_id, quantity, weight, color, price) 
                    VALUES ('$name', '$description', '$image', $worker_id, $quantity, $weight, '$color', $price)";

            if ($conn->query($sql) === TRUE) {
                header('Location: index.php'); // Перенаправляем на страницу списка продуктов после добавления
                exit;
            } else {
                $errors[] = "Ошибка: " . $sql . "<br>" . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Добавить продукт</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/add_product.css">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container">
    <h2>Добавить продукт</h2>

    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div>
            <label for="name">Название:</label>
            <input type="text" id="name" name="name" maxlength="255" required> 
        </div>
        <div>
            <label for="description">Описание:</label>
            <textarea id="description" name="description" maxlength="1000"></textarea> 
        </div>
        <div>
            <label for="quantity">Количество:</label>
            <input type="number" id="quantity" name="quantity" value="0" min="0" required>
        </div>
        <div>
            <label for="weight">Вес (кг):</label>
            <input type="number" id="weight" name="weight" value="0" min="0" step="0.01" required>
        </div>
        <div>
            <label for="color">Цвет:</label>
            <input type="text" id="color" name="color" maxlength="50" required> 
        </div>
        <div>
            <label for="price">Цена:</label>
            <input type="number" id="price" name="price" value="0" min="0" step="0.01" required>
        </div>
        <div>
            <label for="image">Изображение:</label>
            <input type="file" id="image" name="image">
        </div>
        <button type="submit">Добавить</button>
    </form>
</div>

</body>
</html>