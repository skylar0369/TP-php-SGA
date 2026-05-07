<?php
require_once 'auth_helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $user = find_user($username);
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $username;
        header('Location: index.php');
        exit;
    } else {
        $error = "Identifiants invalides";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SGA UPC - Connexion</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
        <div class="p-8 pb-4 text-center">
            <div class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center mx-auto text-white font-bold text-xl mb-4">U</div>
            <h1 class="text-2xl font-black text-slate-950 tracking-tight">SGA UPC</h1>
            <p class="text-slate-500 text-sm mt-1">Connectez-vous à votre espace</p>
        </div>

        <div class="p-8 pt-4 space-y-6">
            <?php if (isset($error)): ?>
                <div class="p-3 bg-red-50 border border-red-100 text-red-600 text-xs font-bold rounded-xl text-center"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-2">Nom d'utilisateur</label>
                    <input type="text" name="username" id="username" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition-all">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-2">Mot de passe</label>
                    <input type="password" name="password" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition-all">
                </div>
                <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3.5 rounded-2xl shadow-lg hover:bg-slate-800 transition-all active:scale-95">
                    Se connecter
                </button>
            </form>

            <div class="relative py-2">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-100"></div></div>
                <div class="relative flex justify-center text-[10px] uppercase font-bold text-slate-300 bg-white px-2">Ou utiliser</div>
            </div>

            <button onclick="loginBiometric()" class="w-full bg-indigo-50 text-indigo-700 font-bold py-3.5 rounded-2xl hover:bg-indigo-100 transition-all flex items-center justify-center gap-3 active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A10.003 10.003 0 0012 3m0 18a10.003 10.003 0 01-8.313-12.45l.06-.089m9.111 12.363l.049.08a10.003 10.003 0 001.213-9.12V9m0 0a4 4 0 00-8 0v3M8 8.4V11m4-1.6V11m1.2-5.4V7.2M15 11l.011-.01"></path></svg>
                Biométrie (Empreinte)
            </button>

            <p class="text-center text-xs text-slate-400">
                Pas encore de compte ? <a href="register.php" class="text-indigo-600 font-bold hover:underline">S'inscrire</a>
            </p>
        </div>
    </div>

    <script>
        const bufferToBase64 = (buffer) => btoa(String.fromCharCode(...new Uint8Array(buffer))).replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
        const base64ToBuffer = (base64) => Uint8Array.from(atob(base64.replace(/-/g, '+').replace(/_/g, '/')), c => c.charCodeAt(0));

        async function loginBiometric() {
            const username = document.getElementById('username').value;
            if (!username) {
                alert("Veuillez d'abord saisir votre nom d'utilisateur pour identifier vos clés.");
                return;
            }

            try {
                const response = await fetch(`webauthn_api.php?action=login-challenge&username=${username}`);
                const options = await response.json();
                
                if (options.error) {
                    alert(options.error);
                    return;
                }

                // Prepare options for navigator.credentials.get
                options.challenge = base64ToBuffer(options.challenge);
                options.allowCredentials = options.allowCredentials.map(c => ({
                    ...c,
                    id: base64ToBuffer(c.id)
                }));

                const assertion = await navigator.credentials.get({ publicKey: options });
                
                const verifyResponse = await fetch(`webauthn_api.php?action=login-verify&username=${username}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: assertion.id,
                        rawId: bufferToBase64(assertion.rawId),
                        type: assertion.type,
                        response: {
                            authenticatorData: bufferToBase64(assertion.response.authenticatorData),
                            clientDataJSON: bufferToBase64(assertion.response.clientDataJSON),
                            signature: bufferToBase64(assertion.response.signature),
                            userHandle: assertion.response.userHandle ? bufferToBase64(assertion.response.userHandle) : null
                        }
                    })
                });

                const result = await verifyResponse.json();
                if (result.success) {
                    window.location.href = 'index.php';
                } else {
                    alert(result.error);
                }
            } catch (err) {
                console.error(err);
                alert("Erreur lors de l'authentification biométrique. Vérifiez que votre appareil supporte WebAuthn.");
            }
        }
    </script>
</body>
</html>
