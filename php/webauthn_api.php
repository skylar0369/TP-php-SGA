<?php

require_once __DIR__ . '/auth_helpers.php';

$action = $_GET['action'] ?? '';


function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

if ($action === 'register-challenge') {
    $username = $_SESSION['pending_biometric_user'] ?? '';
    if (!$username) {
        echo json_encode(['error' => 'No user context']);
        exit;
    }
    
    $challenge = random_bytes(32);
    $_SESSION['webauthn_challenge'] = base64url_encode($challenge);
    
    echo json_encode([
        'challenge' => base64url_encode($challenge),
        'user' => [
            'id' => base64url_encode($username),
            'name' => $username,
            'displayName' => $username
        ],
        'rp' => [
            'name' => 'SGA UPC Biometrics',
            'id' => $_SERVER['HTTP_HOST']
        ],
        'pubKeyCredParams' => [['type' => 'public-key', 'alg' => -7]] // ES256
    ]);
    exit;
}

if ($action === 'register-verify') {
    $input = json_decode(file_get_contents('php://input'), true);
    $username = $_SESSION['pending_biometric_user'] ?? '';
    

    
    $user = find_user($username);
    if ($user) {
        if (!isset($user['credentials'])) $user['credentials'] = [];
        $user['credentials'][] = [
            'id' => $input['id'],
            'publicKey' => $input['response']['attestationObject'], 
            'signCount' => 0
        ];
        update_user($username, $user);
        $_SESSION['user_id'] = $username;
        unset($_SESSION['pending_biometric_user']);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'User not found']);
    }
    exit;
}

if ($action === 'login-challenge') {
    $username = $_GET['username'] ?? '';
    $user = find_user($username);
    if (!$user || !isset($user['credentials']) || empty($user['credentials'])) {
        echo json_encode(['error' => 'No biometric data for this user']);
        exit;
    }

    $challenge = random_bytes(32);
    $_SESSION['webauthn_challenge'] = base64url_encode($challenge);
    
    $allowed = [];
    foreach ($user['credentials'] as $cred) {
        $allowed[] = [
            'type' => 'public-key',
            'id' => $cred['id']
        ];
    }

    echo json_encode([
        'challenge' => base64url_encode($challenge),
        'allowCredentials' => $allowed,
        'rpId' => $_SERVER['HTTP_HOST'],
        'userVerification' => 'preferred'
    ]);
    exit;
}

if ($action === 'login-verify') {
    $input = json_decode(file_get_contents('php://input'), true);
    $username = $_GET['username'] ?? '';
    
    
    $user = find_user($username);
    $found = false;
    foreach ($user['credentials'] as $cred) {
        if ($cred['id'] === $input['id']) {
            $found = true;
            break;
        }
    }

    if ($found) {
        $_SESSION['user_id'] = $username;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Invalid biometric identity']);
    }
    exit;
}
?>
