<?php
require_once 'config/db.php';
session_start();

// Защита: только админ
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

// --- ОБРАБОТКА СМЕНЫ СТАТУСА ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status'])) {
    $booking_id = (int)($_POST['booking_id'] ?? 0);
    $new_status = $_POST['new_status'] ?? '';

    $allowed = ['Новая', 'Банкет назначен', 'Банкет завершен'];
    if ($booking_id > 0 && in_array($new_status, $allowed)) {
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $booking_id]);
    }
    header('Location: admin.php?' . http_build_query($_GET));
    exit;
}

// --- ФИЛЬТРЫ ---
$search = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'created_at';
$order = $_GET['order'] ?? 'DESC';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 5;
$offset = ($page - 1) * $perPage;

// Разрешённые поля для сортировки (защита от SQL-инъекций)
$allowedSort = ['id', 'fio', 'room_name', 'start_date', 'status', 'created_at'];
if (!in_array($sort, $allowedSort)) $sort = 'created_at';
$order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

// Собираем WHERE
$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(u.fio LIKE ? OR u.login LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($statusFilter !== '') {
    $where[] = "b.status = ?";
    $params[] = $statusFilter;
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// --- ПАГИНАЦИЯ: считаем всего записей ---
$countStmt = $pdo->prepare("
    SELECT COUNT(*) FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN rooms r ON b.room_id = r.id
    $whereSql
");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($total / $perPage));

// --- ВЫБОРКА С СОРТИРОВКОЙ ---
$sql = "
    SELECT b.*, u.fio, u.login, r.name AS room_name
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN rooms r ON b.room_id = r.id
    $whereSql
    ORDER BY $sort $order
    LIMIT $perPage OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

// Функция для ссылки сортировки
function sortLink($field, $label, $currentSort, $currentOrder, $search, $statusFilter) {
    $newOrder = ($currentSort === $field && $currentOrder === 'ASC') ? 'DESC' : 'ASC';
    $arrow = '';
    if ($currentSort === $field) {
        $arrow = $currentOrder === 'ASC' ? ' ▲' : ' ▼';
    }
    $query = http_build_query([
        'search' => $search,
        'status' => $statusFilter,
        'sort' => $field,
        'order' => $newOrder
    ]);
    return "<a href=\"?$query\" class=\"text-white text-decoration-none\">$label$arrow</a>";
}

function statusBadge($status) {
    if ($status === 'Новая') return 'bg-warning text-dark';
    if ($status === 'Банкет назначен') return 'bg-info text-dark';
    if ($status === 'Банкет завершен') return 'bg-success';
    return 'bg-secondary';
}
?>

<?php require_once 'includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Все заявки (<?= $total ?>)</h3>
</div>

<!-- Уведомление о смене статуса -->
<?php if (isset($_GET['updated'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        Статус заявки обновлён.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- ФИЛЬТРЫ -->
<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <h5 class="card-title">Фильтры и поиск</h5>
        <form class="row g-3" method="GET">
            <div class="col-md-5">
                <label class="form-label">Поиск по пользователю:</label>
                <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>" placeholder="ФИО или логин">
            </div>
            <div class="col-md-4">
                <label class="form-label">Статус:</label>
                <select name="status" class="form-select">
                    <option value="">Все статусы</option>
                    <option value="Новая" <?= $statusFilter === 'Новая' ? 'selected' : '' ?>>Новая</option>
                    <option value="Банкет назначен" <?= $statusFilter === 'Банкет назначен' ? 'selected' : '' ?>>Банкет назначен</option>
                    <option value="Банкет завершен" <?= $statusFilter === 'Банкет завершен' ? 'selected' : '' ?>>Банкет завершен</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100">Найти</button>
                <a href="admin.php" class="btn btn-outline-secondary w-100">Сброс</a>
            </div>
        </form>
    </div>
</div>

<!-- ТАБЛИЦА -->
<div class="card shadow-sm">
    <div class="card-body">
        <?php if (empty($bookings)): ?>
            <p class="text-muted text-center my-4">Заявок не найдено.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th><?= sortLink('id', '№', $sort, $order, $search, $statusFilter) ?></th>
                            <th><?= sortLink('fio', 'Пользователь', $sort, $order, $search, $statusFilter) ?></th>
                            <th><?= sortLink('room_name', 'Помещение', $sort, $order, $search, $statusFilter) ?></th>
                            <th><?= sortLink('start_date', 'Дата начала', $sort, $order, $search, $statusFilter) ?></th>
                            <th>Оплата</th>
                            <th><?= sortLink('status', 'Статус', $sort, $order, $search, $statusFilter) ?></th>
                            <th>Действие</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td><?= $b['id'] ?></td>
                                <td><?= htmlspecialchars($b['fio']) ?><br><small class="text-muted"><?= htmlspecialchars($b['login']) ?></small></td>
                                <td><?= htmlspecialchars($b['room_name']) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($b['start_date'])) ?></td>
                                <td><?= htmlspecialchars($b['payment_method']) ?></td>
                                <td>
                                    <span class="badge <?= statusBadge($b['status']) ?>">
                                        <?= htmlspecialchars($b['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" class="d-flex gap-2">
                                        <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                        <select name="new_status" class="form-select form-select-sm" style="min-width: 170px;">
                                            <option value="Новая" <?= $b['status'] === 'Новая' ? 'selected' : '' ?>>Новая</option>
                                            <option value="Банкет назначен" <?= $b['status'] === 'Банкет назначен' ? 'selected' : '' ?>>Банкет назначен</option>
                                            <option value="Банкет завершен" <?= $b['status'] === 'Банкет завершен' ? 'selected' : '' ?>>Банкет завершен</option>
                                        </select>
                                        <button type="submit" name="change_status" value="1" class="btn btn-sm btn-primary">OK</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- ПАГИНАЦИЯ -->
            <?php if ($totalPages > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php
                        $baseQuery = ['search' => $search, 'status' => $statusFilter, 'sort' => $sort, 'order' => $order];
                        ?>
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($baseQuery, ['page' => $page - 1])) ?>">Назад</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($baseQuery, ['page' => $i])) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($baseQuery, ['page' => $page + 1])) ?>">Вперёд</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>