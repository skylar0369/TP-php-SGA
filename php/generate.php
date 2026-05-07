<?php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = generate_planning();
    
    session_start();
    $_SESSION['logs'] = $result['logs'];
    header("Location: index.php?generated=1");
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>
