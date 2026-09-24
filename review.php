<?php
require_once 'config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = (int)($_POST['booking_id'] ?? 0);
    $review = trim($_POST['review'] ?? '');

    if ($booking_id > 0 && !empty($review)) {
        // Проверяем: заявка принадлежит текущему пользователю И статус "Банкет завершен"
        $stmt = $pdo->prepare("SELECT id FROM bookings WHERE id = ? AND user_id = ? AND status = 'Банкет завершен'");
        $stmt->execute([$booking_id, $_SESSION['user_id']]);

        if ($stmt->fetch()) {
            $update = $pdo->prepare("UPDATE bookings SET review = ? WHERE id = ?");
            $update->execute([$review, $booking_id]);
        }
    }
}

header('Location: cabinet.php?review_saved=1');
exit;
?>