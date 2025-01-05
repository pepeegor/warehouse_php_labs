<div class="navbar">
    <div class="navbar-left">
        <a href="/warehouse/index.php" class="logo">Склад</a>
    </div>
    <div class="navbar-right">
        <ul class="nav-links">
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <li><a href="/warehouse/worker/">Панель управления</a></li>
                <?php else: ?>
                    <li><a href="/warehouse/user/">Продукты</a></li>
                    <li><a href="/warehouse/user/orders.php">Мои заказы</a></li>
                <?php endif; ?>
                <li><a href="/warehouse/logout.php">Выйти</a></li>
            <?php else: ?>
                <li><a href="/warehouse/login.php">Войти</a></li>
                <li><a href="/warehouse/register.php">Регистрация</a></li>
            <?php endif; ?>
        </ul>
    </div>
</div>