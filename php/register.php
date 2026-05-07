<?php
require_once 'auth_helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (find_user($username)) {
        $error = "Cet utilisateur existe déjà";
    } else {
        $users = get_users();
        $users[] = [
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'credentials' => []
        ];
        save_users($users);
        $_SESSION['pending_biometric_user'] = $username;
        header('Location: register_biometric.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SGA UPC - Inscription</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
        <div class="p-8 pb-4 text-center">
            <div class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center mx-auto text-white font-bold text-xl mb-4">U</div>
            <h1 class="text-2xl font-black text-slate-950 tracking-tight">Inscription</h1>
            <p class="text-slate-500 text-sm mt-1">Créez votre compte administrateur</p>
        </div>

        <div class="p-8 pt-4 space-y-6">
            <?php if (isset($error)): ?>
                <div class="p-3 bg-red-50 border border-red-100 text-red-600 text-xs font-bold rounded-xl text-center"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-2">Nom d'utilisateur</label>
                    <input type="text" name="username" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition-all">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-2">Mot de passe</label>
                    <input type="password" name="password" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition-all">
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-3.5 rounded-2xl shadow-lg hover:bg-indigo-700 transition-all active:scale-95">
                    Créer mon compte
                </button>
            </form>

            <p class="text-center text-xs text-slate-400">
                Déjà un compte ? <a href="login.php" class="text-indigo-600 font-bold hover:underline">Se connecter</a>
            </p>
        </div>
    </div>
</body>
</html>
