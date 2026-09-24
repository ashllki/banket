    </div> <!-- закрываем container -->

    <!-- ПОДВАЛ -->
    <footer class="site-footer">
        <div class="container">
            <div class="row">
                <div class="col-md-5 mb-3">
                    <h5>Наше местонахождение</h5>
                    <p>Адрес головного офиса: <br>г. Москва, ул. Большая Ордынка, д. 15</p>
                    <p>Телефон горячей линии: <br><a href="tel:+74951234567">+7 (495) 123-45-67</a></p>
                    <p>Если возникли вопросы или пожелания, позвоните нам. Ответим оперативно и подробно.</p>
                </div>
                <div class="col-md-4 mb-3">
                    <h5>Варианты оплаты</h5>
                    <ul>
                        <li>— предоплата по QR-коду</li>
                        <li>— оплата картой МИР</li>
                        <li>— постоплата в офисе организации</li>
                    </ul>
                </div>
                <div class="col-md-3 mb-3">
                    <h5>Меню</h5>
                    <ul>
                        <li><a href="cabinet.php">Личный кабинет</a></li>
                        <li><a href="booking.php">Создать заявку</a></li>
                        <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                            <li><a href="admin.php">Админ-панель</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                © <?= date('Y') ?> Банкетам.Нет. Все права защищены.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>