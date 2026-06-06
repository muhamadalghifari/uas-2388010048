<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function currentUsername() {
    return $_SESSION['username'] ?? null;
}
