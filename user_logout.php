<?php
/*
 * CUSTOMER LOGOUT
 * ---------------
 * Destroys only the customer session keys, leaving admin session intact.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);
session_write_close();

header('Location: index.php');
exit;
