<?php


define('DATA_DIR', __DIR__ . '/data');

if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0777, true);
}

function charger_donnees($fichier) {
    $path = DATA_DIR . '/' . $fichier . '.json';
    if (!file_exists($path)) return [];
    $content = file_get_contents($path);
    return json_decode($content, true) ?: [];
}

function sauvegarder_donnees($fichier, $data) {
    $path = DATA_DIR . '/' . $fichier . '.json';
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
}


function salle_disponible($planning, $id_salle, $jour, $creneau) {
    foreach ($planning as $p) {
        if ($p['id_salle'] === $id_salle && $p['jour'] === $jour && $p['creneau'] === $creneau) {
            return false;
        }
    }
    return true;
}

function creneau_libre_groupe($planning, $id_groupe, $jour, $creneau, $options) {
    foreach ($planning as $p) {
        if ($p['jour'] !== $jour || $p['creneau'] !== $creneau) continue;
        
        if ($p['id_groupe'] === $id_groupe) return false;
        
       
        foreach ($options as $o) {
            if ($o['id'] === $id_groupe && $o['promotionParent'] === $p['id_groupe']) return false;
            if ($o['id'] === $p['id_groupe'] && $o['promotionParent'] === $id_groupe) return false;
        }
    }
    return true;
}

function generate_planning() {
    $salles = charger_donnees('salles');
    $promotions = charger_donnees('promotions');
    $cours = charger_donnees('cours');
    $options = charger_donnees('options');

    $jours = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi"];
    $creneaux = ["08h00-12h00", "13h00-17h00"];
    
    $planning = [];
    $logs = [];

    foreach ($cours as $c) {
        $slotsNeeded = ceil($c['volumeHoraire'] / 4);
        $slotsFound = 0;

        $id_groupe = $c['rattachement'];
        $effectif = 0;
        
        foreach ($promotions as $p) {
            if ($p['id'] === $id_groupe) {
                $effectif = $p['effectif'];
                break;
            }
        }
        if ($effectif === 0) {
            foreach ($options as $o) {
                if ($o['id'] === $id_groupe) {
                    $effectif = $o['effectif'];
                    break;
                }
            }
        }

        
        usort($salles, function($a, $b) {
            return $a['capacite'] - $b['capacite'];
        });

        $found_all_slots = false;
        foreach ($jours as $jour) {
            foreach ($creneaux as $creneau) {
                if ($slotsFound >= $slotsNeeded) {
                    $found_all_slots = true;
                    break 2;
                }

                foreach ($salles as $s) {
                    if ($s['capacite'] >= $effectif && 
                        salle_disponible($planning, $s['id'], $jour, $creneau) &&
                        creneau_libre_groupe($planning, $id_groupe, $jour, $creneau, $options)) {
                        
                        $planning[] = [
                            'jour' => $jour,
                            'creneau' => $creneau,
                            'id_salle' => $s['id'],
                            'id_cours' => $c['id'],
                            'intitule_cours' => $c['intitule'],
                            'id_groupe' => $id_groupe,
                            'designation_salle' => $s['designation']
                        ];
                        $slotsFound++;
                        break;
                    }
                }
            }
        }

        if ($slotsFound < $slotsNeeded) {
            $logs[] = "Impossible de placer tout le volume horaire pour: " . $c['intitule'];
        }
    }

    sauvegarder_donnees('planning', $planning);
    return ['planning' => $planning, 'logs' => $logs];
}

function get_stats() {
    $salles = charger_donnees('salles');
    $planning = charger_donnees('planning');
    $totalSlots = 10; 

    $report = [];
    foreach ($salles as $s) {
        $occupiedCount = 0;
        foreach ($planning as $p) {
            if ($p['id_salle'] === $s['id']) $occupiedCount++;
        }
        $report[] = [
            'id' => $s['id'],
            'designation' => $s['designation'],
            'occupied' => $occupiedCount,
            'free' => $totalSlots - $occupiedCount,
            'rate' => round(($occupiedCount / $totalSlots) * 100, 1)
        ];
    }
    return $report;
}
?>
