<?php
require_once 'auth_helpers.php';
$username = $_SESSION['pending_biometric_user'] ?? '';
if (!$username) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SGA UPC - Configuration Biométrique</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-indigo-600 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-2xl border border-white/20 overflow-hidden">
        <div class="p-10 text-center space-y-6">
            <div class="w-20 h-20 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A10.003 10.003 0 0012 3m0 18a10.003 10.003 0 01-8.313-12.45l.06-.089m9.111 12.363l.049.08a10.003 10.003 0 001.213-9.12V9m0 0a4 4 0 00-8 0v3M8 8.4V11m4-1.6V11m1.2-5.4V7.2M15 11l.011-.01"></path></svg>
            </div>
            
            <h1 class="text-2xl font-black text-slate-950">Activez la Biométrie</h1>
            <p class="text-slate-500 text-sm leading-relaxed">
                Utilisez votre <strong>empreinte digitale</strong> ou <strong>reconnaissance faciale</strong> pour vous connecter plus rapidement la prochaine fois.
            </p>

            <div class="p-4 bg-amber-50 rounded-2xl border border-amber-100 text-left">
                <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest mb-1">Astuce Mobile</p>
                <p class="text-[11px] text-amber-700 leading-tight">
                    Si votre ordinateur n'a pas de lecteur d'empreinte, cliquez sur le bouton et choisissez <strong>"Utiliser un téléphone ou une tablette"</strong> pour scanner un QR code.
                </p>
            </div>

            <div class="space-y-3 pt-4">
                <button onclick="registerBiometric()" class="w-full bg-indigo-600 text-white font-bold py-4 rounded-2xl shadow-lg hover:bg-indigo-700 transition-all active:scale-95">
                    Configurer mon empreinte
                </button>
                <a href="index.php?skip_bio=1" onclick="skipBio()" class="block w-full text-slate-400 font-bold py-2 text-xs uppercase tracking-widest hover:text-slate-600 transition-all">
                    Plus tard
                </a>
            </div>
        </div>
    </div>

    <script>
        const bufferToBase64 = (buffer) => btoa(String.fromCharCode(...new Uint8Array(buffer))).replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
        const base64ToBuffer = (base64) => Uint8Array.from(atob(base64.replace(/-/g, '+').replace(/_/g, '/')), c => c.charCodeAt(0));

        async function registerBiometric() {
            try {
                const response = await fetch('webauthn_api.php?action=register-challenge');
                const options = await response.json();
                
             
                options.challenge = base64ToBuffer(options.challenge);
                options.user.id = base64ToBuffer(options.user.id);

                const credential = await navigator.credentials.create({ publicKey: options });
                
                const verifyResponse = await fetch('webauthn_api.php?action=register-verify', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: credential.id,
                        rawId: bufferToBase64(credential.rawId),
                        type: credential.type,
                        response: {
                            attestationObject: bufferToBase64(credential.response.attestationObject),
                            clientDataJSON: bufferToBase64(credential.response.clientDataJSON),
                        }
                    })
                });

                const result = await verifyResponse.json();
                if (result.success) {
                    window.location.href = 'index.php?bio_ready=1';
                } else {
                    alert(result.error);
                }
            } catch (err) {
                console.error(err);
                alert("Erreur lors de la configuration biométrique. Assurez-vous d'utiliser un navigateur compatible (Chrome, Safari, Edge).");
            }
        }

        function skipBio() {
            // Set session directly for 'skip'
            fetch('webauthn_api.php?action=skip').then(() => {
                window.location.href = 'index.php';
            });
        }
    </script>
</body>
</html>
