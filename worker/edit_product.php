<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Получаем данные продукта из базы данных
    $sql = "SELECT * FROM products WHERE id = $id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        $error = "Продукт не найден.";
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

        // Если ошибок нет, обрабатываем загрузку изображения и обновляем продукт
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
                        // Обновляем путь к изображению в базе данных
                        $sql = "UPDATE products SET image = '$image' WHERE id = $id";
                        $conn->query($sql); 
                    } else {
                        $errors[] = "Извините, произошла ошибка при загрузке файла.";
                    }
                }
            }

            // Если после обработки изображения нет ошибок, обновляем продукт
            if (empty($errors)) {
                // Обновляем данные продукта в базе данных (включая новые поля)
                $sql = "UPDATE products SET 
                        name = '$name', 
                        description = '$description', 
                        quantity = $quantity, 
                        weight = $weight, 
                        color = '$color', 
                        price = $price 
                        WHERE id = $id"; 

                if ($conn->query($sql) === TRUE) {
                    header('Location: index.php'); // Перенаправляем на страницу списка продуктов
                    exit;
                } else {
                    $errors[] = "Ошибка: " . $sql . "<br>" . $conn->error;
                }
            }
        }
    }
} else {
    header('Location: index.php'); // Перенаправляем на страницу списка продуктов, если не передан ID продукта
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Редактировать продукт</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/edit_product.css"> 
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container"> 
    <h2>Редактировать продукт</h2>

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
            <input type="text" id="name" name="name" value="<?php echo $product['name']; ?>" maxlength="255" required>
        </div>
        <div>
            <label for="description">Описание:</label>
            <textarea id="description" name="description" maxlength="1000"><?php echo $product['description']; ?></textarea>
        </div>
        <div>
            <label for="quantity">Количество:</label>
            <input type="number" id="quantity" name="quantity" value="<?php echo $product['quantity']; ?>" min="0" required>
        </div>
        <div>
            <label for="weight">Вес (кг):</label>
            <input type="number" id="weight" name="weight" value="<?php echo $product['weight']; ?>" min="0" step="0.01" required>
        </div>
        <div>
            <label for="color">Цвет:</label>
            <input type="text" id="color" name="color" value="<?php echo $product['color']; ?>" maxlength="50" required>
        </div>
        <div>
            <label for="price">Цена:</label>
            <input type="number" id="price" name="price" value="<?php echo $product['price']; ?>" min="0" step="0.01" required>
        </div>
        <div>
            <label for="image">Изображение:</label>
            <input type="file" id="image" name="image">
            <?php if ($product['image']): ?>
                <p>Текущее изображение: <img src="../<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" width="100"></p>
            <?php endif; ?>
        </div>
        <button type="submit">Сохранить</button>
    </form>
</div>

</body>
</html>