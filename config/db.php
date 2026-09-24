<?php
// Настройки подключения к базе данных
$host = 'localhost';
$dbname = 'banket_db';
$username = 'root';       // Стандартный логин для XAMPP/OpenServer
$password = '';           // Стандартный пароль (пустой)

try {
    // Создаем подключение через PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Включаем режим ошибок, чтобы видеть проблемы
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Данные будут возвращаться как ассоциативные массивы
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>