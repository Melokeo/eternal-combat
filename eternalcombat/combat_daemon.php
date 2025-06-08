<?php
/**
 * Combat Daemon
 */

require_once __DIR__ . '/utils/game_config.php';
require_once __DIR__ . '/utils/fighter.php';
require_once __DIR__ . '/utils/fighter_func.php';
require_once __DIR__ . '/utils/process_interactions.php';

$attks = require_once __DIR__ . '/utils/element_attacks.php';
$EARTH_ATTACKS = $attks['earth'];
$WIND_ATTACKS = $attks['wind'];

function writeState($wolf, $loong, $battle_count, $round_num, $wolf_wins, $loong_wins, $action_log, $extra = []) {
    global $battle_history, $stats_history;
    $state = array_merge([
        'battle_count' => $battle_count,
        'round_num' => $round_num,
        'wolf_wins' => $wolf_wins,
        'loong_wins' => $loong_wins,
        'battle_history' => $battle_history,
        'stats_history' => $stats_history,
        'wolf' => [
            'name' => $wolf->name,
            'hp' => $wolf->hp,
            'max_hp' => $wolf->max_hp,
            'energy' => $wolf->energy,
            'max_energy' => $wolf->max_energy,
            'attack' => $wolf->attack,
            'defense' => $wolf->defense,
            'speed' => $wolf->speed,
            'status_effects' => array_map(fn($e) => ['type' => $e->name, 'duration' => $e->duration], $wolf->status_effects),
            'alive' => $wolf->isAlive()
        ],
        'loong' => [
            'name' => $loong->name,
            'hp' => $loong->hp,
            'max_hp' => $loong->max_hp,
            'energy' => $loong->energy,
            'max_energy' => $loong->max_energy,
            'attack' => $loong->attack,
            'defense' => $loong->defense,
            'speed' => $loong->speed,
            'status_effects' => array_map(fn($e) => ['type' => $e->name, 'duration' => $e->duration], $loong->status_effects),
            'alive' => $loong->isAlive()
        ],
        'action_log' => array_slice($action_log, -GameConfig::MAX_LINES),
        'last_action' => end($action_log) ?: "战斗开始"
    ], $extra);
    
    file_put_contents('/var/www/typecho/webtool/eternalcombat/json/battle_state.json', json_encode($state, JSON_UNESCAPED_UNICODE));
}

// main combat daemon
$wolf = new Fighter("小麦", "earth", 
                   GameConfig::WOLF_BASE_HP, GameConfig::WOLF_BASE_HP, GameConfig::WOLF_BASE_HP,
                   GameConfig::WOLF_BASE_ATTACK, GameConfig::WOLF_BASE_DEFENSE, GameConfig::WOLF_BASE_SPEED,
                   GameConfig::WOLF_BASE_ENERGY, GameConfig::WOLF_BASE_ENERGY);
$loong = new Fighter("洛璞", "wind",
                    GameConfig::LOONG_BASE_HP, GameConfig::LOONG_BASE_HP, GameConfig::LOONG_BASE_HP,
                    GameConfig::LOONG_BASE_ATTACK, GameConfig::LOONG_BASE_DEFENSE, GameConfig::LOONG_BASE_SPEED,
                    GameConfig::LOONG_BASE_ENERGY, GameConfig::LOONG_BASE_ENERGY);

$battle_count = 1;
$wolf_wins = 0;
$loong_wins = 0;
$round_num = 1;
$battle_results = [];
$action_log = [];
$battle_history = [];
$stats_history = [
    'wolf' => ['hp' => [], 'attack' => [], 'defense' => [], 'speed' => []],
    'loong' => ['hp' => [], 'attack' => [], 'defense' => [], 'speed' => []]
];

// main loop
while (true) {
    $state = [
        'battle_count' => $battle_count,
        'round_num' => $round_num,
        'wolf_wins' => $wolf_wins,
        'loong_wins' => $loong_wins,
        'wolf' => [
            'name' => $wolf->name,
            'hp' => $wolf->hp,
            'max_hp' => $wolf->max_hp,
            'energy' => $wolf->energy,
            'max_energy' => $wolf->max_energy,
            'attack' => $wolf->attack,
            'defense' => $wolf->defense,
            'speed' => $wolf->speed,
            'status_effects' => array_map(fn($e) => $e->name, $wolf->status_effects)
        ],
        'loong' => [
            'name' => $loong->name,
            'hp' => $loong->hp,
            'max_hp' => $loong->max_hp,
            'energy' => $loong->energy,
            'max_energy' => $loong->max_energy,
            'attack' => $loong->attack,
            'defense' => $loong->defense,
            'speed' => $loong->speed,
            'status_effects' => array_map(fn($e) => $e->name, $loong->status_effects)
        ],
        'action_log' => array_slice($action_log, -GameConfig::MAX_LINES),
        'last_action' => end($action_log) ?: "战斗开始"
    ];
    
    // write state to file
    file_put_contents('/var/www/typecho/webtool/eternalcombat/json/battle_state.json', json_encode($state, JSON_UNESCAPED_UNICODE));

    // run combat round
    while ($wolf->isAlive() && $loong->isAlive() && $round_num <= GameConfig::MAX_ROUNDS_PER_BATTLE) {
        $action_log[] = "⚔️ 第{$round_num}回合开始";
        // apply chaos: process chaos events with delayed display
        $chaosMessages = processQueuedChaos($wolf, $loong, $battle_count, $round_num);
        $should_restart = false;
        $self_attack = false;
        $skip_round = false;
        foreach ($chaosMessages as $msg) {
            if (strpos($msg, 'DELAY:') === 0) {
                $delay = intval(substr($msg, 6));
                writeState($wolf, $loong, $battle_count, $round_num, $wolf_wins, $loong_wins, $action_log);
                sleep($delay);
            } elseif (strpos($msg, 'RESTART:') === 0) {
                $should_restart = true;
            } elseif (strpos($msg, 'DIZZY:') === 0) {
                $self_attack = true;
            } elseif (strpos($msg, 'SKIP_ROUND:') === 0) {
                $skip_round = true;
            } else {
                $action_log[] = $msg;
                writeState($wolf, $loong, $battle_count, $round_num, $wolf_wins, $loong_wins, $action_log);
            }
            sleep(1);
        }
        
        if ($skip_round) {
            $round_num ++;
            continue;
        }

        // Handle chaos-induced restart
        if ($should_restart) {
            if (random_int(1,20) === 1) {
                // Reset fighters to original GameConfig values
                $wolf = new Fighter("小麦", "earth", 
                                GameConfig::WOLF_BASE_HP, GameConfig::WOLF_BASE_HP, GameConfig::WOLF_BASE_HP,
                                GameConfig::WOLF_BASE_ATTACK, GameConfig::WOLF_BASE_DEFENSE, GameConfig::WOLF_BASE_SPEED,
                                GameConfig::WOLF_BASE_ENERGY, GameConfig::WOLF_BASE_ENERGY);
                $loong = new Fighter("洛璞", "wind",
                                    GameConfig::LOONG_BASE_HP, GameConfig::LOONG_BASE_HP, GameConfig::LOONG_BASE_HP,
                                    GameConfig::LOONG_BASE_ATTACK, GameConfig::LOONG_BASE_DEFENSE, GameConfig::LOONG_BASE_SPEED,
                                    GameConfig::LOONG_BASE_ENERGY, GameConfig::LOONG_BASE_ENERGY);
                
                // Reset battle tracking
                $action_log = ["⚔️ 战斗重新开始!"];
                $battle_count = 1;
                $round_num = 1;
                $wolf_wins = 0;
                $loong_wins = 0;
                $battle_results = [];
                $battle_history = [];
                
                writeState($wolf, $loong, $battle_count, $round_num, $wolf_wins, $loong_wins, $action_log);
                break; // Break out of current battle loop
            } else {
                $action_log[] = "服务器备份恢复了！战斗继续";
                writeState($wolf, $loong, $battle_count, $round_num, $wolf_wins, $loong_wins, $action_log);
            }
        }

        // apply cheers
        $cheerMessages = processQueuedCheers($wolf, $loong);
        foreach ($cheerMessages as $msg) {
            $action_log[] = $msg;
        }
        writeState($wolf, $loong, $battle_count, $round_num, $wolf_wins, $loong_wins, $action_log);
        sleep(1);
        // Process status effects
        foreach ([$wolf, $loong] as $fighter) {
            if ($fighter->status_effects) {
                $messages = $fighter->processStatusEffects();
                foreach ($messages as $msg) {
                    $action_log[] = $msg;
                    writeState($wolf, $loong, $battle_count, $round_num, $wolf_wins, $loong_wins, $action_log);
                    sleep(0.5);
                }
            }
        }
        
        if (!$wolf->isAlive() || !$loong->isAlive()) break;
        
        // Determine turn order
        $wolf_speed = $wolf->getEffectiveStats()[2];
        $loong_speed = $loong->getEffectiveStats()[2];
        
        if ($wolf_speed >= $loong_speed) {
            $first = $wolf; $second = $loong;
        } else {
            $first = $loong; $second = $wolf;
        }
        
        // First fighter's turn
        if ($first->isAlive() && $second->isAlive()) {
            if ($self_attack) {
                $result = performAction($first, $first);
            } else {
                $result = performAction($first, $second);
            }
            $action_log[] = $result;
            writeState($wolf, $loong, $battle_count, $round_num, $wolf_wins, $loong_wins, $action_log);
            sleep(1);
        }
        
        // Check for death after first attack
        if (!$first->isAlive() || !$second->isAlive()) {
            writeState($wolf, $loong, $battle_count, $round_num, $wolf_wins, $loong_wins, $action_log);
            break;
        }
        
        // Second fighter's turn
        if ($first->isAlive() && $second->isAlive()) {
            if ($self_attack) {
                $result = performAction($second, $second);
            } else {
                $result = performAction($second, $first);
            }
            $action_log[] = $result;
            writeState($wolf, $loong, $battle_count, $round_num, $wolf_wins, $loong_wins, $action_log);
            sleep(1);
        }
        
        // Final death check
        writeState($wolf, $loong, $battle_count, $round_num, $wolf_wins, $loong_wins, $action_log);
        $round_num++;
    }
    
    // determine winner
    if ($wolf->isAlive() && !$loong->isAlive()) {
        $wolf_wins++;
        $battle_results[] = 1;
        $battle_history[] = ['winner' => 'wolf', 'battle' => $battle_count, 'round_count' => $round_num - 1];
        $action_log[] = "{$loong->name}倒下了！";
    } elseif ($loong->isAlive() && !$wolf->isAlive()) {
        $loong_wins++;
        $battle_results[] = 2;
        $battle_history[] = ['winner' => 'loong', 'battle' => $battle_count, 'round_count' => $round_num - 1];
        $action_log[] = "{$wolf->name}倒下了!";
    } else {
        $battle_results[] = 0;
        $battle_history[] = ['winner' => 'tie', 'battle' => $battle_count, 'round_count' => $round_num - 1];
        $action_log[] = "⏰ 平局!";
    }
    if (count($battle_history)>50) {
        $battle_history = array_slice($battle_history, -50);
    }
    writeState($wolf, $loong, $battle_count, $round_num, $wolf_wins, $loong_wins, $action_log);
    sleep(1);
    
    // break period
    $action_log[] = "💤 进入休息时间...";
    for ($remaining = GameConfig::BREAK_DURATION; $remaining > 0; $remaining--) {
        writeState($wolf, $loong, $battle_count, $round_num, $wolf_wins, $loong_wins, $action_log, ['break_remaining' => $remaining]);
        sleep(1);
    }

    // Clear break timer
    writeState($wolf, $loong, $battle_count, $round_num, $wolf_wins, $loong_wins, $action_log, ['break_remaining' => 0]);
    // reset for next battle
    balancedFighterReset($wolf, $loong, $wolf_wins, $loong_wins, $battle_results);

    // keep msg for plotting
    $stats_history['wolf']['hp'][] = $wolf->max_hp;
    $stats_history['wolf']['attack'][] = $wolf->attack;
    $stats_history['wolf']['defense'][] = $wolf->defense;
    $stats_history['wolf']['speed'][] = $wolf->speed;

    $stats_history['loong']['hp'][] = $loong->max_hp;
    $stats_history['loong']['attack'][] = $loong->attack;
    $stats_history['loong']['defense'][] = $loong->defense;
    $stats_history['loong']['speed'][] = $loong->speed;

    // Keep only last 50 points
    foreach ($stats_history as $fighter => $stats) {
        foreach ($stats as $stat => $values) {
            if (count($values) > GameConfig::MAX_STAT_HISTORY) {
                $stats_history[$fighter][$stat] = array_slice($values, -GameConfig::MAX_STAT_HISTORY);
            }
        }
    }
    
    // evolution messages
    $wolf_mult = $wolf->attack / GameConfig::WOLF_BASE_ATTACK;
    $loong_mult = $loong->attack / GameConfig::LOONG_BASE_ATTACK;
    
    $action_log[] = "🐺 {$wolf->name}进化: x" . sprintf("%.1f", $wolf_mult) . " | A{$wolf->attack} D{$wolf->defense} H{$wolf->max_hp}";
    $action_log[] = "🐉 {$loong->name}进化: x" . sprintf("%.1f", $loong_mult) . " | A{$loong->attack} D{$loong->defense} H{$loong->max_hp}";
    
    writeState($wolf, $loong, $battle_count, $round_num, $wolf_wins, $loong_wins, $action_log);

    $battle_count++;
    $round_num = 1;
    
    sleep(GameConfig::INHERIT_DISPLAY_DURATION);
}
?>