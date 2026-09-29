<?php
require_once 'config/db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: cabinet.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login) || empty($password)) {
        $error = 'Заполните все поля.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch();

        if ($user && $password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_fio'] = $user['fio'];
            $_SESSION['user_role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header('Location: admin.php');
            } else {
                header('Location: cabinet.php');
            }
            exit;
        } else {
            $error = 'Неправильный логин или пароль.';
        }
    }
}
?>

<?php require_once 'includes/header.php'; ?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-card__header">
            <span class="auth-icon">🍷</span>
            <h4>Вход в личный кабинет</h4>
            <p>Банкетам.Нет</p>
        </div>

        <div class="auth-card__body">
            <?php if (isset($_GET['registered'])): ?>
                <div class="alert alert-success">Регистрация прошла успешно! Войдите.</div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="login" class="form-label">Логин</label>
                    <input type="text" name="login" id="login" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Пароль</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <button type="submit" class="auth-btn auth-btn--login">Войти</button>
            </form>
        </div>

        <div class="auth-card__footer">
            Еще не зарегистрированы? <a href="register.php">Регистрация</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>