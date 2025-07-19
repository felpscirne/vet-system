<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password) {
    return strlen($password) >= 6;
}

function validateFutureDate($date) {
    $appointmentDate = new DateTime($date);
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    return $appointmentDate > $today;
}

function validateBusinessHours($time) {
    $appointmentTime = new DateTime($time);
    $startTime = new DateTime('08:00');
    $endTime = new DateTime('18:00');
    
    return $appointmentTime >= $startTime && $appointmentTime <= $endTime;
}

function validateWeekday($date) {
    $dayOfWeek = date('N', strtotime($date));
    return $dayOfWeek < 6;
}

function validateReason($reason) {
    return strlen(trim($reason)) >= 10;
}

function sanitizeString($string) {
    return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function showMessage($type, $message) {
    $class = $type === 'error' ? 'alert-danger' : 'alert-success';
    echo "<div class='alert $class alert-dismissible fade show' role='alert'>
            $message
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
          </div>";
}

function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>