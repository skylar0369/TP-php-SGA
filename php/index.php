<?php
require_once 'functions.php';
$planning = charger_donnees('planning');
$stats = get_stats();

$DAYS = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi"];
$SLOTS = ["08h00-12h00", "13h00-17h00"];

include 'header.php';
?>

<header class="flex flex-col md:flex-row md:justify-between md:items-center mb-10 gap-4">
    <div class="space-y-1">
        <h2 class="text-2xl font-bold tracking-tight text-slate-950 flex items-center gap-2">
            Tableau de Bord de Performance
            <div class="w-2 h-2 bg-green-500 rounded-full ml-1"></div>
        </h2>
        <p class="text-slate-500 text-sm font-medium italic">UPC Faculté des Sciences Informatiques</p>
    </div>
    
    <?php if (isset($_GET['generated'])): ?>
    <div class="bg-indigo-50 border border-indigo-100 p-4 rounded-2xl flex items-center gap-3">
        <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white">✓</div>
        <div>
            <p class="text-xs font-bold text-indigo-900">Planning généré avec succès!</p>
            <?php if (isset($_SESSION['logs']) && !empty($_SESSION['logs'])): ?>
                <p class="text-[10px] text-indigo-600 leading-tight mt-0.5"><?= count($_SESSION['logs']) ?> alertes détectées.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</header>

<?php if (isset($_SESSION['logs']) && !empty($_SESSION['logs'])): ?>
<div class="mb-8 bg-amber-50 border border-amber-100 p-6 rounded-3xl">
    <h3 class="text-xs font-black text-amber-600 uppercase tracking-widest mb-3">Alertes de Génération</h3>
    <ul class="space-y-2">
        <?php foreach ($_SESSION['logs'] as $log): ?>
            <li class="text-xs text-amber-700 flex items-start gap-2">
                <span class="mt-0.5">•</span>
                <span><?= htmlspecialchars($log) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php unset($_SESSION['logs']); ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    <?php foreach (array_slice($stats, 0, 4) as $s): ?>
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col justify-center gap-1">
        <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1"><?= htmlspecialchars($s['id']) ?></p>
        <div class="flex items-baseline gap-2">
            <p class="text-4xl font-bold text-slate-950"><?= $s['occupied'] ?></p>
            <p class="text-slate-400 text-sm font-medium">/ 10</p>
        </div>
        <div class="flex items-center gap-2 mt-2">
            <div class="flex-1 bg-slate-100 h-1.5 rounded-full overflow-hidden">
                <div class="h-full bg-indigo-600" style="width: <?= $s['rate'] ?>%"></div>
            </div>
            <span class="text-[10px] font-bold text-slate-500"><?= $s['rate'] ?>%</span>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto scrollbar-hide">
        <div class="min-w-[800px]">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-white border-b border-slate-200">
                        <th class="p-6 border-r border-slate-100 w-32 text-slate-400 font-bold text-[10px] uppercase tracking-widest bg-slate-50/50">Créneau</th>
                        <?php foreach ($DAYS as $day): ?>
                        <th class="p-6 border-r border-slate-100 text-slate-950 font-bold text-sm tracking-tight text-center"><?= $day ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($SLOTS as $slot): ?>
                    <tr class="border-b border-slate-200 min-h-[16rem]">
                        <td class="p-6 bg-slate-50/30 border-r border-slate-100 text-slate-400 font-bold text-[10px] text-center align-middle uppercase tracking-widest">
                            <?= $slot ?>
                        </td>
                        <?php foreach ($DAYS as $day): ?>
                        <td class="p-3 border-r border-slate-100 align-top bg-white min-w-40">
                            <div class="flex flex-col gap-3">
                                <?php 
                                $entries = array_filter($planning, function($p) use ($day, $slot) {
                                    return $p['jour'] === $day && $p['creneau'] === $slot;
                                });
                                
                                if (empty($entries)): ?>
                                    <div class="min-h-32 flex items-center justify-center p-4 border-2 border-dashed border-slate-100 rounded-3xl">
                                        <span class="text-slate-300 text-[10px] font-bold uppercase tracking-widest">Libre</span>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($entries as $p): ?>
                                    <div class="bg-white p-4 rounded-2xl border-l-[6px] border-l-indigo-600 shadow-sm border border-slate-100 space-y-2 relative">
                                        <div class="flex justify-between items-center">
                                            <span class="bg-indigo-50 text-indigo-700 text-[10px] px-2 py-0.5 rounded-md font-bold uppercase tracking-wider"><?= htmlspecialchars($p['id_groupe']) ?></span>
                                            <span class="text-[10px] text-slate-300 font-mono font-bold"><?= htmlspecialchars($p['id_cours']) ?></span>
                                        </div>
                                        <p class="font-bold text-slate-900 leading-tight text-sm"><?= htmlspecialchars($p['intitule_cours']) ?></p>
                                        <div class="flex items-center gap-1.5 pt-2 border-t border-slate-50">
                                            <span class="text-[10px] text-slate-500 font-bold uppercase tracking-tighter">📍 <?= htmlspecialchars($p['id_salle']) ?></span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
