<?php
require_once 'auth_helpers.php';
require_auth();
$current_user = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGA UPC - Système de Gestion Académique</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-surface { background-color: #f8fafc; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-surface text-slate-900">
    <div class="min-h-screen flex flex-col md:flex-row">
        
        <aside class="w-full md:w-64 bg-white border-r border-slate-200 flex flex-col shadow-sm">
            <div class="p-8">
                <div class="flex items-center gap-3 text-indigo-600">
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white font-bold">U</div>
                    <h1 class="font-bold text-xl tracking-tight text-slate-950">SGA UPC</h1>
                </div>
                <div class="mt-4 p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center gap-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider">Session: <?= htmlspecialchars($current_user) ?></span>
                </div>
            </div>
            
            <nav class="flex-1 px-4 space-y-1">
                <a href="index.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-slate-600 hover:bg-slate-50 transition-all">Accueil</a>
                <a href="manage.php?type=salles" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-slate-600 hover:bg-slate-50 transition-all">Salles</a>
                <a href="manage.php?type=promotions" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-slate-600 hover:bg-slate-50 transition-all">Promotions</a>
                <a href="manage.php?type=options" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-slate-600 hover:bg-slate-50 transition-all">Options</a>
                <a href="manage.php?type=cours" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-slate-600 hover:bg-slate-50 transition-all">Cours</a>
                <a href="rapports.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-slate-600 hover:bg-slate-50 transition-all">Rapports</a>
            </nav>

            <div class="p-6 mt-auto space-y-3">
                <form action="generate.php" method="POST">
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2.5 rounded-xl transition-all shadow-sm">
                        Générer Planning
                    </button>
                </form>
                <a href="logout.php" class="block w-full text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest hover:text-red-500 transition-all">Déconnexion</a>
            </div>
        </aside>

       
        <main class="flex-1 p-4 md:p-8">
