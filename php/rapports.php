<?php
require_once 'functions.php';
$stats = get_stats();

include 'header.php';
?>

<div class="space-y-6">
    <header class="mb-10">
        <h2 class="text-2xl font-bold tracking-tight text-slate-950">Rapports d'Occupation</h2>
        <p class="text-slate-500 text-sm font-medium italic">Analyse de l'utilisation des auditoires</p>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
            <h3 class="font-bold text-lg mb-6 flex items-center gap-2">
                📊 Taux d'Occupation par Salle
            </h3>
            <div class="space-y-4">
                <?php foreach ($stats as $s): ?>
                <div class="flex flex-col gap-1.5">
                    <div class="flex justify-between text-xs font-bold text-slate-600">
                        <span><?= htmlspecialchars($s['id']) ?> (<?= $s['occupied'] ?> créneaux)</span>
                        <span><?= $s['rate'] ?>%</span>
                    </div>
                    <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                        <div class="h-full bg-indigo-600" style="width: <?= $s['rate'] ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
            <h3 class="font-bold text-lg mb-6 flex items-center gap-2">
                💾 Exporter les Données
            </h3>
            <div class="space-y-3">
                <button onclick="window.print()" class="w-full py-4 px-6 bg-slate-50 hover:bg-slate-100 text-slate-900 font-bold rounded-2xl flex items-center justify-between transition-all">
                    <span>Imprimer le Planning</span>
                    <span>➜</span>
                </button>
                <div class="p-4 bg-indigo-50 border border-indigo-100 rounded-2xl">
                    <p class="text-[11px] text-indigo-700 font-bold uppercase tracking-widest mb-1">Rapport de gestion (B2)</p>
                    <p class="text-xs text-indigo-600 italic">Un fichier <strong>rapport_occupation.txt</strong> est généré automatiquement dans le dossier data lors de chaque consultation.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 

$textReport = "RAPPORT D'OCCUPATION DES SALLES\n" . str_repeat("=", 30) . "\n";
foreach ($stats as $r) {
    $textReport .= "Salle: {$r['id']} ({$r['designation']})\n";
    $textReport .= "- Créneaux occupés: {$r['occupied']}\n";
    $textReport .= "- Créneaux libres: {$r['free']}\n";
    $textReport .= "- Taux d'occupation: {$r['rate']}%\n\n";
}
file_put_contents(DATA_DIR . '/rapport_occupation.txt', $textReport);

include 'footer.php'; 
?>
