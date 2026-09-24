<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Банкетам.Нет</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="cabinet.php">Банкетам.Нет</a>
            <div class="ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="text-white me-3 d-none d-md-inline">Привет, <b><?= htmlspecialchars($_SESSION['user_fio']) ?></b>!</span>
                    <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                        <a href="admin.php" class="btn btn-warning btn-sm me-2">Админ-панель</a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn btn-outline-light btn-sm">Выйти</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-light btn-sm">Войти</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="container mt-4">