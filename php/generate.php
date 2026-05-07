<?php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = generate_planning();
    // In a real app we might store logs in session to show them once after redirect
    session_start();
    $_SESSION['logs'] = $result['logs'];
    header("Location: index.php?generated=1");
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>
