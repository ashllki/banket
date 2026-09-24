<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: cabinet.php');
} else {
    header('Location: login.php');
}
exit;
?>