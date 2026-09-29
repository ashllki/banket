<?php
require_once 'config/db.php';
session_start();

// Защита: если не авторизован — на вход
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Получаем заявки пользователя
$stmt = $pdo->prepare("
    SELECT b.*, r.name AS room_name 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC
");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();

// Вспомогательная функция для цвета статуса
function statusBadge($status) {
    if ($status === 'Новая') return 'bg-warning text-dark';
    if ($status === 'Банкет назначен') return 'bg-info text-dark';
    if ($status === 'Банкет завершен') return 'bg-success';
    return 'bg-secondary';
}
?>

<?php require_once 'includes/header.php'; ?>

<!-- СЛАЙДЕР -->
<div id="banquetSlider" class="carousel slide mb-5" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#banquetSlider" data-bs-slide-to="0" class="active"></button>
        <button type="button" data-bs-target="#banquetSlider" data-bs-slide-to="1"></button>
        <button type="button" data-bs-target="#banquetSlider" data-bs-slide-to="2"></button>
        <button type="button" data-bs-target="#banquetSlider" data-bs-slide-to="3"></button>
    </div>
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="img/1.webp?v=2" class="d-block w-100" alt="Шикарный зал">
            <div class="carousel-caption d-none d-md-block">
                <h5>Шикарный зал</h5>
                <p>Идеально для больших торжеств</p>
            </div>
        </div>
        <div class="carousel-item">
            <img src="img/2.jpg?v=2" class="d-block w-100" alt="Летняя веранда">
            <div class="carousel-caption d-none d-md-block">
                <h5>Летняя веранда</h5>
                <p>Свежий воздух и приятная атмосфера</p>
            </div>
        </div>
        <div class="carousel-item">
            <img src="img/3.jpg?v=2" class="d-block w-100" alt="Уютный ресторан">
            <div class="carousel-caption d-none d-md-block">
                <h5>Уютный ресторан</h5>
                <p>Изысканная кухня и обслуживание</p>
            </div>
        </div>
        <div class="carousel-item">
            <img src="img/4.jpg?v=2" class="d-block w-100" alt="Закрытая веранда">
            <div class="carousel-caption d-none d-md-block">
                <h5>Закрытая веранда</h5>
                <p>Уют на первом месте</p>
            </div>
        </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#banquetSlider" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#banquetSlider" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
    </button>
</div>

<!-- Кнопка создания заявки -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Мои заявки</h3>
    <a href="booking.php" class="btn btn-success">+ Создать заявку</a>
</div>

<!-- Уведомление об успехе -->
<?php if (isset($_GET['created'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        Заявка успешно создана! Ожидайте подтверждения администратора.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['review_saved'])): ?>
    <div class="alert alert-info alert-dismissible fade show">
        Спасибо за отзыв!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- ТАБЛИЦА ЗАЯВОК -->
<div class="card shadow-sm">
    <div class="card-body">
        <?php if (empty($bookings)): ?>
            <p class="text-muted text-center my-4">У вас пока нет заявок. Создайте первую!</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>№</th>
                            <th>Помещение</th>
                            <th>Дата начала</th>
                            <th>Оплата</th>
                            <th>Статус</th>
                            <th>Отзыв</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $i => $b): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($b['room_name']) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($b['start_date'])) ?></td>
                                <td><?= htmlspecialchars($b['payment_method']) ?></td>
                                <td>
                                    <span class="badge <?= statusBadge($b['status']) ?>">
                                        <?= htmlspecialchars($b['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($b['status'] === 'Банкет завершен' && empty($b['review'])): ?>
                                        <!-- Можно оставить отзыв -->
                                        <button class="btn btn-sm btn-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#reviewModal"
                                                data-booking-id="<?= $b['id'] ?>">
                                            Оставить отзыв
                                        </button>
                                    <?php elseif (!empty($b['review'])): ?>
                                        <span class="text-success small">✓ Отзыв оставлен</span>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled>Оставить отзыв</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- МОДАЛКА ОТЗЫВА -->
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="review.php">
                <div class="modal-header">
                    <h5 class="modal-title">Отзыв об услуге</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="booking_id" id="modalBookingId">
                    <div class="mb-3">
                        <label for="reviewText" class="form-label">Ваш отзыв:</label>
                        <textarea class="form-control" name="review" id="reviewText" rows="4" required placeholder="Напишите, что вам понравилось..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Отправить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Скрипт: подставляем ID заявки в модалку -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    var reviewModal = document.getElementById('reviewModal');
    reviewModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var bookingId = button.getAttribute('data-booking-id');
        document.getElementById('modalBookingId').value = bookingId;
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>