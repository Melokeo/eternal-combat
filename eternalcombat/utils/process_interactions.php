<?php
require_once __DIR__ . '/fighter.php';

function processQueuedCheers(Fighter $wolf, Fighter $loong): array {
    $cheersFile = '/var/www/typecho/webtool/eternalcombat/json/cheers.json';
    $lockFile = $cheersFile . '.lock';
    
    // Acquire lock
    $lock = fopen($lockFile, 'w');
    if (!flock($lock, LOCK_EX)) {
        fclose($lock);
        return [];
    }
    
    if (!file_exists($cheersFile)) {
        flock($lock, LOCK_UN);
        fclose($lock);
        return [];
    }
    
    $cheers = json_decode(file_get_contents($cheersFile), true);
    if (!$cheers || (empty($cheers['wolf_cheers']) && empty($cheers['loong_cheers']))) {
        flock($lock, LOCK_UN);
        fclose($lock);
        return [];
    }
    
    $messages = [];
    
    // Process wolf cheers
    if (!empty($cheers['wolf_cheers'])) {
        $wolfBoosts = ['hp' => 0, 'attack' => 0, 'defense' => 0, 'speed' => 0, 'heal' => 0];
        foreach ($cheers['wolf_cheers'] as $cheer) {
            $wolfBoosts[$cheer['stat']] += $cheer['boost'];
        }
        
        $statChanges = [];
        foreach ($wolfBoosts as $stat => $boost) {
            if ($boost > 0) {
                $oldValue = match($stat) {
                    'hp' => $wolf->max_hp,
                    'attack' => $wolf->attack,
                    'defense' => $wolf->defense,
                    'speed' => $wolf->speed,
                    'heal' => $wolf->hp
                };
                
                switch ($stat) {
                    case 'heal':
                        $wolf->hp += $boost * 300;
                        if ($wolf->hp >$wolf->max_hp) {
                            $wolf->hp = $wolf->max_hp;
                        }
                        $statChanges[] = "恢复HP " . intval($boost * 300);
                        break;
                    case 'hp': 
                        $wolf->max_hp += $boost * 50; 
                        $wolf->hp += $boost * 50; 
                        $statChanges[] = "HP {$oldValue}->{$wolf->max_hp}";
                        break;
                    case 'attack': 
                        $wolf->attack += $boost * 2; 
                        $statChanges[] = "A {$oldValue}->{$wolf->attack}";
                        break;
                    case 'defense': 
                        $wolf->defense += $boost * 2; 
                        $statChanges[] = "D {$oldValue}->{$wolf->defense}";
                        break;
                    case 'speed': 
                        $wolf->speed += $boost * 2; 
                        $statChanges[] = "S {$oldValue}->{$wolf->speed}";
                        break;
                }
            }
        }
        
        $wolf->energy = min($wolf->energy, $wolf->max_energy);
        
        if (!empty($statChanges)) {
            $messages[] = "🎉 {$wolf->name}获得加油: " . implode(', ', $statChanges);
        }
    }
    
    // Process loong cheers
    if (!empty($cheers['loong_cheers'])) {
        $loongBoosts = ['hp' => 0, 'attack' => 0, 'defense' => 0, 'speed' => 0, 'heal' => 0];
        foreach ($cheers['loong_cheers'] as $cheer) {
            $loongBoosts[$cheer['stat']] += $cheer['boost'];
        }
        
        $statChanges = [];
        foreach ($loongBoosts as $stat => $boost) {
            if ($boost > 0) {
                $oldValue = match($stat) {
                    'hp' => $loong->max_hp,
                    'attack' => $loong->attack,
                    'defense' => $loong->defense,
                    'speed' => $loong->speed,
                    'heal' => $loong->hp
                };
                
                switch ($stat) {
                    case 'heal':
                        $loong->hp += $boost * 350;
                        if ($loong->hp >$loong->max_hp) {
                            $loong->hp = $loong->max_hp;
                        }
                        $statChanges[] = "恢复HP " . intval($boost * 300);
                        break;
                    case 'hp': 
                        $loong->max_hp += $boost * 50; 
                        $loong->hp += $boost * 50; 
                        $statChanges[] = "HP {$oldValue}->{$loong->max_hp}";
                        break;
                    case 'attack': 
                        $loong->attack += $boost * (5 + mt_rand() / mt_getrandmax() * 3); 
                        $statChanges[] = "A {$oldValue}->{$loong->attack}";
                        break;
                    case 'defense': 
                        $loong->defense += $boost * (3 + mt_rand() / mt_getrandmax() * 2); 
                        $statChanges[] = "D {$oldValue}->{$loong->defense}";
                        break;
                    case 'speed': 
                        $loong->speed += $boost * (5 + mt_rand() / mt_getrandmax() * 3); 
                        $statChanges[] = "S {$oldValue}->{$loong->speed}";
                        break;
                }
            }
        }

        $loong->energy = min($loong->energy, $loong->max_energy);

        if (!empty($statChanges)) {
            $messages[] = "🎉 {$loong->name}获得加油: " . implode(', ', $statChanges);
        }
    }
    
    // Clear processed cheers and release lock
    file_put_contents($cheersFile, json_encode(['wolf_cheers' => [], 'loong_cheers' => []]));
    flock($lock, LOCK_UN);
    fclose($lock);
    
    return $messages;
}

function processQueuedChaos(Fighter $wolf, Fighter $loong, int &$battle_count, int &$round_num): array {
    $chaosFile = '/var/www/typecho/webtool/eternalcombat/json/chaos.json';
    $lockFile = $chaosFile . '.lock';

    // Locking
    $lock = fopen($lockFile, 'w');
    if (!flock($lock, LOCK_EX)) {
        fclose($lock);
        return [];
    }

    if (!file_exists($chaosFile)) {
        flock($lock, LOCK_UN);
        fclose($lock);
        return [];
    }

    $chaos = json_decode(file_get_contents($chaosFile), true);
    if (!$chaos || empty($chaos)) {
        flock($lock, LOCK_UN);
        fclose($lock);
        return [];
    }

    // Chaos dispatch
    require_once __DIR__ . '/chaos_events.php';

    $messages = ["🌀🌀🌀"];

    foreach ($chaos as $chaosEvent) {
        $event = $chaosEvent['event'];
        $handler = "chaos_" . $event;

        if (function_exists($handler)) {
            $result = $handler($wolf, $loong, $battle_count, $round_num);
            $messages = array_merge($messages, is_array($result) ? $result : []);
        } else {
            $messages[] = "🌀 未知事件：$event";
        }
    }

    // Cleanup
    file_put_contents($chaosFile, json_encode([]));
    flock($lock, LOCK_UN);
    fclose($lock);

    return $messages;
}