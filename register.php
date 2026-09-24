<?php
// Подключаем базу данных
require_once 'config/db.php';

// Начинаем сессию (нужна для сохранения данных пользователя)
session_start();

// Если пользователь уже вошел, перенаправляем в кабинет
if (isset($_SESSION['user_id'])) {
    header('Location: cabinet.php');
    exit;
}

$errors = []; // Массив для ошибок

// Проверяем, была ли отправлена форма
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы и убираем лишние пробелы
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $fio = trim($_POST['fio'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // --- ВАЛИДАЦИЯ ---

    // 1. Логин: минимум 6 символов, только латиница и цифры
    if (strlen($login) < 6) {
        $errors['login'] = 'Логин должен содержать минимум 6 символов.';
    } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $login)) {
        $errors['login'] = 'Логин может содержать только латинские буквы и цифры.';
    } else {
        // Проверка уникальности логина
        $stmt = $pdo->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            $errors['login'] = 'Такой логин уже занят.';
        }
    }

    // 2. Пароль: минимум 8 символов
    if (strlen($password) < 8) {
        $errors['password'] = 'Пароль должен содержать минимум 8 символов.';
    }

    // 3. ФИО, телефон, email — обязательные
    if (empty($fio)) $errors['fio'] = 'Введите ФИО.';
    if (empty($phone)) $errors['phone'] = 'Введите телефон.';
    if (empty($email)) $errors['email'] = 'Введите email.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Некорректный email.';
    }

    // --- ЕСЛИ ОШИБОК НЕТ, ДОБАВЛЯЕМ В БАЗУ ---
    if (empty($errors)) {
        // Хешируем пароль (безопасность!)
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (login, password, fio, phone, email, role) VALUES (?, ?, ?, ?, ?, 'user')");
        $stmt->execute([$login, $hashedPassword, $fio, $phone, $email]);

        // Перенаправляем на вход
        header('Location: login.php?registered=1');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - Банкетам.Нет</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Регистрация</h4>
                    </div>
                    <div class="card-body">

                        <!-- Вывод общих ошибок -->
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                Пожалуйста, исправьте ошибки ниже.
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <!-- Логин -->
                            <div class="mb-3">
                                <label for="login" class="form-label">Логин (латиница и цифры, мин. 6 символов):</label>
                                <input type="text" name="login" id="login" class="form-control <?= isset($errors['login']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($login ?? '') ?>" required>
                                <?php if (isset($errors['login'])): ?>
                                    <div class="invalid-feedback"><?= $errors['login'] ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Пароль -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Пароль (мин. 8 символов):</label>
                                <input type="password" name="password" id="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" required>
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback"><?= $errors['password'] ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- ФИО -->
                            <div class="mb-3">
                                <label for="fio" class="form-label">ФИО:</label>
                                <input type="text" name="fio" id="fio" class="form-control <?= isset($errors['fio']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($fio ?? '') ?>" required>
                                <?php if (isset($errors['fio'])): ?>
                                    <div class="invalid-feedback"><?= $errors['fio'] ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Телефон -->
                            <div class="mb-3">
                                <label for="phone" class="form-label">Телефон:</label>
                                <input type="tel" name="phone" id="phone" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($phone ?? '') ?>" placeholder="+7 (999) 123-45-67" required>
                                <?php if (isset($errors['phone'])): ?>
                                    <div class="invalid-feedback"><?= $errors['phone'] ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail:</label>
                                <input type="email" name="email" id="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($email ?? '') ?>" required>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?= $errors['email'] ?></div>
                                <?php endif; ?>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
                        </form>

                        <p class="mt-3 text-center">Уже зарегистрированы? <a href="login.php">Войти</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>