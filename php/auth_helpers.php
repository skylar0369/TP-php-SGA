<?php

session_start();
require_once __DIR__ . '/functions.php';

function get_users() {
    return charger_donnees('users');
}

function save_users($users) {
    sauvegarder_donnees('users', $users);
}

function find_user($username) {
    $users = get_users();
    foreach ($users as $user) {
        if ($user['username'] === $username) return $user;
    }
    return null;
}

function update_user($username, $updated_user) {
    $users = get_users();
    foreach ($users as $idx => $user) {
        if ($user['username'] === $username) {
            $users[$idx] = $updated_user;
            save_users($users);
            return true;
        }
    }
    return false;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_auth() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
