<?php
require_once 'config.php';

function generateAccountNumber() {
    return 'BNK' . mt_rand(10000000, 99999999);
}

function isCustomerLoggedIn() {
    return isset($_SESSION['customer_id']);
}

function isManagerLoggedIn() {
    return isset($_SESSION['manager_id']) && isset($_SESSION['manager_role']);
}

function isAdminLoggedIn() {
    return isManagerLoggedIn() && $_SESSION['manager_role'] === 'admin';
}
function redirectIfNotLoggedIn($type) {
    if ($type === 'customer' && !isCustomerLoggedIn()) {
        header("Location: ../login.php");
        exit();
    }
    if ($type === 'manager' && !isManagerLoggedIn()) {
        header("Location: ../login.php");
        exit();
    }
}
?>