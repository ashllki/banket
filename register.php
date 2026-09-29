<?php
require_once 'config/db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: cabinet.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $fio = trim($_POST['fio'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Логин
    if (strlen($login) < 6) {
        $errors['login'] = 'Логин должен содержать минимум 6 символов.';
    } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $login)) {
        $errors['login'] = 'Только латинские буквы и цифры.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            $errors['login'] = 'Такой логин уже занят.';
        }
    }

    // Пароль
    if (strlen($password) < 8) {
        $errors['password'] = 'Пароль должен содержать минимум 8 символов.';
    }

    // ФИО, телефон, email
    if (empty($fio)) $errors['fio'] = 'Введите ФИО.';
    if (empty($phone)) $errors['phone'] = 'Введите телефон.';
    if (empty($email)) {
        $errors['email'] = 'Введите email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Некорректный email.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO users (login, password, fio, phone, email, role) VALUES (?, ?, ?, ?, ?, 'user')");
        $stmt->execute([$login, $password, $fio, $phone, $email]);
        header('Location: login.php?registered=1');
        exit;
    }
}
?>

<?php require_once 'includes/header.php'; ?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-card__header">
            <span class="auth-icon">📝</span>
            <h4>Регистрация</h4>
            <p>Банкетам.Нет</p>
        </div>

        <div class="auth-card__body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">Пожалуйста, исправьте ошибки ниже.</div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="login" class="form-label">Логин (мин. 6 символов)</label>
                    <input type="text" name="login" id="login" class="form-control <?= isset($errors['login']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($login ?? '') ?>" required>
                    <?php if (isset($errors['login'])): ?>
                        <div class="invalid-feedback"><?= $errors['login'] ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Пароль (мин. 8 символов)</label>
                    <input type="password" name="password" id="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" required>
                    <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback"><?= $errors['password'] ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="fio" class="form-label">ФИО</label>
                    <input type="text" name="fio" id="fio" class="form-control <?= isset($errors['fio']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($fio ?? '') ?>" required>
                    <?php if (isset($errors['fio'])): ?>
                        <div class="invalid-feedback"><?= $errors['fio'] ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Телефон</label>
                    <input type="tel" name="phone" id="phone" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($phone ?? '') ?>" placeholder="+7 (999) 123-45-67" required>
                    <?php if (isset($errors['phone'])): ?>
                        <div class="invalid-feedback"><?= $errors['phone'] ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" name="email" id="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($email ?? '') ?>" required>
                    <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback"><?= $errors['email'] ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="auth-btn auth-btn--register">Зарегистрироваться</button>
            </form>
        </div>

        <div class="auth-card__footer">
            Уже зарегистрированы? <a href="login.php">Войти</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>