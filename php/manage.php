<?php
require_once 'functions.php';

$type = $_GET['type'] ?? 'salles';
$items = charger_donnees($type);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $new_items = [];

        if (isset($_POST['id'])) {
            foreach ($_POST['id'] as $idx => $id) {
                if (empty($id)) continue;
                $item = [];
                foreach ($_POST as $key => $values) {
                    if (is_array($values) && isset($values[$idx])) {
                        $item[$key] = ($key === 'capacite' || $key === 'effectif' || $key === 'volumeHoraire') ? (int)$values[$idx] : $values[$idx];
                    }
                }
                $new_items[] = $item;
            }
        }
        sauvegarder_donnees($type, $new_items);
        header("Location: manage.php?type=$type&saved=1");
        exit;
    }
}

$fields = [];
$title = "";
switch ($type) {
    case 'salles':
        $title = "Gestion des Auditoires";
        $fields = [
            ['key' => 'id', 'label' => 'ID', 'placeholder' => 'AUD-L1'],
            ['key' => 'designation', 'label' => 'Désignation', 'placeholder' => 'Auditoire Principal'],
            ['key' => 'capacite', 'label' => 'Capacité', 'type' => 'number']
        ];
        break;
    case 'promotions':
        $title = "Gestion des Promotions";
        $fields = [
            ['key' => 'id', 'label' => 'Code', 'placeholder' => 'L1'],
            ['key' => 'libelle', 'label' => 'Libellé', 'placeholder' => 'Licence 1'],
            ['key' => 'effectif', 'label' => 'Effectif', 'type' => 'number']
        ];
        break;
    case 'options':
        $title = "Filières d'Options";
        $promotions = charger_donnees('promotions');
        $fields = [
            ['key' => 'id', 'label' => 'Code', 'placeholder' => 'SEC-INF'],
            ['key' => 'libelle', 'label' => 'Désignation', 'placeholder' => 'Sécurité'],
            ['key' => 'promotionParent', 'label' => 'Promotion Parente', 'options' => array_column($promotions, 'id')],
            ['key' => 'effectif', 'label' => 'Effectif', 'type' => 'number']
        ];
        break;
    case 'cours':
        $title = "Grille des Cours";
        $promotions = charger_donnees('promotions');
        $options = charger_donnees('options');
        $rattachement_options = array_merge(array_column($promotions, 'id'), array_column($options, 'id'));
        $fields = [
            ['key' => 'id', 'label' => 'Code', 'placeholder' => 'C01'],
            ['key' => 'intitule', 'label' => 'Intitulé', 'placeholder' => 'Algorithmique'],
            ['key' => 'volumeHoraire', 'label' => 'VH (h)', 'type' => 'number'],
            ['key' => 'rattachement', 'label' => 'Rattachement', 'options' => $rattachement_options]
        ];
        break;
}

include 'header.php';
?>

<div class="flex flex-col gap-6">
    <div class="flex justify-between items-center bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
        <h2 class="text-xl font-bold tracking-tight text-slate-950"><?= $title ?></h2>
        <?php if (isset($_GET['saved'])): ?>
            <span class="text-green-600 text-sm font-bold">Sauvegardé avec succès !</span>
        <?php endif; ?>
    </div>

    <form method="POST" class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <input type="hidden" name="action" value="save">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-slate-50/50">
                        <?php foreach ($fields as $f): ?>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100"><?= $f['label'] ?></th>
                        <?php endforeach; ?>
                        <th class="px-6 py-4 border-b border-slate-100"></th>
                    </tr>
                </thead>
                <tbody id="data-rows" class="divide-y divide-slate-50">
                    <?php if (empty($items)): ?>
                        <tr class="empty-state"><td colspan="<?= count($fields) + 1 ?>" class="p-12 text-center text-slate-400 italic">Aucune donnée configurée.</td></tr>
                    <?php else: ?>
                        <?php foreach ($items as $idx => $item): ?>
                        <tr>
                            <?php foreach ($fields as $f): ?>
                            <td class="px-6 py-3">
                                <?php if (isset($f['options'])): ?>
                                    <select name="<?= $f['key'] ?>[]" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm">
                                        <?php foreach ($f['options'] as $opt): ?>
                                            <option value="<?= $opt ?>" <?= (isset($item[$f['key']]) && $item[$f['key']] === $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    <input type="<?= $f['type'] ?? 'text' ?>" name="<?= $f['key'] ?>[]" value="<?= htmlspecialchars($item[$f['key']] ?? '') ?>" placeholder="<?= $f['placeholder'] ?? '' ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm">
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>
                            <td class="px-6 py-3">
                                <button type="button" onclick="this.closest('tr').remove()" class="text-slate-300 hover:text-red-500">Supprimer</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="p-6 bg-slate-50/50 flex justify-between gap-4">
            <button type="button" onclick="addRow()" class="bg-white border border-slate-200 text-slate-900 px-6 py-2.5 rounded-xl text-xs font-bold shadow-sm">
                Ajouter une ligne
            </button>
            <button type="submit" class="bg-indigo-600 text-white px-8 py-2.5 rounded-xl text-xs font-bold shadow-md shadow-indigo-100">
                Sauvegarder les modifications
            </button>
        </div>
    </form>
</div>

<script>
function addRow() {
    const table = document.getElementById('data-rows');
    const emptyState = table.querySelector('.empty-state');
    if (emptyState) emptyState.remove();

    const row = document.createElement('tr');
    row.innerHTML = `
        <?php foreach ($fields as $f): ?>
        <td class="px-6 py-3">
            <?php if (isset($f['options'])): ?>
                <select name="<?= $f['key'] ?>[]" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm">
                    <?php foreach ($f['options'] as $opt): ?>
                        <option value="<?= $opt ?>"><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <input type="<?= $f['type'] ?? 'text' ?>" name="<?= $f['key'] ?>[]" placeholder="<?= $f['placeholder'] ?? '' ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm">
            <?php endif; ?>
        </td>
        <?php endforeach; ?>
        <td class="px-6 py-3">
            <button type="button" onclick="this.closest('tr').remove()" class="text-slate-300 hover:text-red-500">Supprimer</button>
        </td>
    `;
    table.appendChild(row);
}
</script>

<?php include 'footer.php'; ?>
