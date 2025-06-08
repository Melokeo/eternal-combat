<?php
/**
 * Handles user interaction - chaos
 * and write chaos.json
 */

session_start();
$CHAOS_CHANCE_POOL = 100;
$CHAOS_CHANCE = 60;

// Reset chaos tracking on new session/refresh - use a simple timestamp check
if (!isset($_SESSION['last_activity']) || time() - $_SESSION['last_activity'] > 45) { // secs
    $_SESSION['chaos_count'] = 0;
}
$_SESSION['last_activity'] = time();

// Initialize session data
if (!isset($_SESSION['chaos_count'])) {
    $_SESSION['chaos_count'] = 0;
}

// Check if user has reached limit
/*if ($_SESSION['chaos_count'] >= 30) {
    echo 'limit';
    exit;
}*/

if ($_SESSION['chaos_count'] !== 0 && random_int(0, $CHAOS_CHANCE_POOL) > $CHAOS_CHANCE) {
    echo 'nothing';
    exit;
}

$events = [
    'chick', 'sheep', 'bolopizza', 'HealAll', 'WindBlow', 'RESTART', 
    'outstanding', 'lair', '7star', 'ddl', 'dizzy', 'exist', 'alien', 
    'RandomHit', 'RandomHeal', 'EnergyTrap', 'SlowBoth', 'BurnBoth', 
    'ArmorBreak', 'Chong', 'QuanJia', 'UpsideDown',
    'PlushieRain', 'Glitch', 'CatIntervention', 'FourthWall',
    'TacoRain', 'DinoAttack', 'KaLe', 'UFO'
];
$randomEvent = $events[array_rand($events)];
$randomBoost = rand(1, 3);

$chaosMsg = match($randomEvent) {
    'chick'           => '🐔🐥🐥',
    'sheep'           => '🥦🐑🐑',
    'bolopizza'       => '🍍🍕⁉️🏃‍♀️',
    'HealAll'         => '🤝🌸',
    'WindBlow'        => '🀀',
    'RESTART'         => '🐞‼️',
    'outstanding'     => '..卓越?',
    'lair'            => '...??',
    '7star'           => '🚨',
    'ddl'             => '⏰📅',
    'dizzy'           => '💫',
    'exist'           => '👁️',
    'alien'           => '👽',
    'RandomHit'       => '🎯',
    'RandomHeal'      => '🩹',
    'EnergyTrap'      => '🪫',
    'SlowBoth'        => '🐢🐢',
    'BurnBoth'        => '☲',
    'ArmorBreak'      => '🛡️💥',
    'Chong'           => '🐛',
    'UpsideDown'      => '🙃',
    'FourthWall'      => '🎭',
    'TacoRain'        => '🌮🌮🌮',
    'DinoAttack'      => '🦖',
    'KaLe'            => '🌐',
    'UFO'             => '🛸',
    'PlushieRain'     => '🧸',
    'Glitch'          => '📺',
    'CatIntervention' => '🐱',
    default           => '',
};

// Create chaos data
$chaosData = [
    'event' => $randomEvent,
    'timestamp' => time()
];

$chaosFile = '/var/www/typecho/webtool/eternalcombat/json/chaos.json';
$lockFile = $chaosFile . '.lock';

// Acquire lock
$lock = fopen($lockFile, 'w');
if (flock($lock, LOCK_EX)) {
    // Read existing chaos'
    $chaos = [];
    if (file_exists($chaosFile)) {
        $existing = json_decode(file_get_contents($chaosFile), true);
        if ($existing) {
            $chaos = $existing;
        }
    }
    
    // Add new chaos
    $chaos[] = $chaosData;
    
    // Save cheers
    file_put_contents($chaosFile, json_encode($chaos));
    
    // Release lock
    flock($lock, LOCK_UN);

    $_SESSION['chaos_count']++;
    
    echo 'success:' . $chaosMsg;
} else {
    echo 'error';
}

fclose($lock);
?>