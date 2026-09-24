<?php
require_once 'config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Получаем список помещений из базы
$rooms = $pdo->query("SELECT * FROM rooms ORDER BY name")->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = (int)($_POST['room_id'] ?? 0);
    $start_date = $_POST['start_date'] ?? '';
    $payment_method = trim($_POST['payment_method'] ?? '');

    if ($room_id <= 0) $errors[] = 'Выберите помещение.';
    if (empty($start_date)) $errors[] = 'Укажите дату начала.';
    if (empty($payment_method)) $errors[] = 'Выберите способ оплаты.';

    // Проверка: дата не в прошлом
    if (!empty($start_date) && strtotime($start_date) < time()) {
        $errors[] = 'Дата начала не может быть в прошлом.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, room_id, start_date, payment_method, status) VALUES (?, ?, ?, ?, 'Новая')");
        $stmt->execute([$_SESSION['user_id'], $room_id, $start_date, $payment_method]);
        header('Location: cabinet.php?created=1');
        exit;
    }
}
?>

<?php require_once 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Новая заявка на банкет</h4>
            </div>
            <div class="card-body">

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="room" class="form-label">Выберите помещение:</label>
                        <select class="form-select" name="room_id" id="room" required>
                            <option value="" disabled selected>-- Нажмите, чтобы выбрать --</option>
                            <?php foreach ($rooms as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= (isset($_POST['room_id']) && $_POST['room_id'] == $r['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($r['name']) ?> (<?= number_format($r['price'], 0, '.', ' ') ?> руб.)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="date" class="form-label">Дата и время начала банкета:</label>
                        <input type="datetime-local" class="form-control" name="start_date" id="date" required value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>">
                        <div class="form-text">Формат: ДД.ММ.ГГГГ ЧЧ:ММ</div>
                    </div>

                    <div class="mb-3">
                        <label for="payment" class="form-label">Способ оплаты:</label>
                        <select class="form-select" name="payment_method" id="payment" required>
                            <option value="" disabled selected>-- Выберите способ --</option>
                            <option value="QR-код" <?= (($_POST['payment_method'] ?? '') === 'QR-код') ? 'selected' : '' ?>>Предоплата по QR-коду</option>
                            <option value="Карта МИР" <?= (($_POST['payment_method'] ?? '') === 'Карта МИР') ? 'selected' : '' ?>>Оплата картой МИР</option>
                            <option value="Постоплата" <?= (($_POST['payment_method'] ?? '') === 'Постоплата') ? 'selected' : '' ?>>Постоплата в офисе организации</option>
                        </select>   
                    </div>

                    <button type="submit" class="btn btn-success w-100">Отправить заявку</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>