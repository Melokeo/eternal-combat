<?php
/**
 * Handles user interaction - cheers
 * and write cheers.json
 */
session_start();

// Reset cheer tracking on new session/refresh - use a simple timestamp check
if (!isset($_SESSION['last_activity']) || time() - $_SESSION['last_activity'] > 10) { // secs
    $_SESSION['cheer_count'] = 0;
    $_SESSION['cheer_side'] = null;
}
$_SESSION['last_activity'] = time();

// Initialize session data
if (!isset($_SESSION['cheer_count'])) {
    $_SESSION['cheer_count'] = 0;
}
if (!isset($_SESSION['cheer_side'])) {
    $_SESSION['cheer_side'] = null;
}

// Check if user has reached limit
if ($_SESSION['cheer_count'] >= 60) {
    echo 'limit';
    exit;
}

// Validate input
if ($_POST['fighter'] !== 'wolf' && $_POST['fighter'] !== 'loong') {
    echo 'error';
    exit;
}

$fighter = $_POST['fighter'];

// Check if user is trying to cheer for opposite side (only within current session)
if ($_SESSION['cheer_side'] !== null && $_SESSION['cheer_side'] !== $fighter) {
    echo 'locked';
    exit;
}

// Set user's side on first cheer
if ($_SESSION['cheer_side'] === null) {
    $_SESSION['cheer_side'] = $fighter;
}

$stats = ['hp', 'attack', 'defense', 'speed', 'heal'];
$randomStat = $stats[array_rand($stats)];
$randomBoost = rand(1, 3);

// Create cheer data
$cheerData = [
    'stat' => $randomStat,
    'boost' => $randomBoost,
    'timestamp' => time()
];

$cheersFile = '/var/www/typecho/webtool/eternalcombat/json/cheers.json';
$lockFile = $cheersFile . '.lock';

// Acquire lock
$lock = fopen($lockFile, 'w');
if (flock($lock, LOCK_EX)) {
    // Read existing cheers
    $cheers = ['wolf_cheers' => [], 'loong_cheers' => []];
    if (file_exists($cheersFile)) {
        $existing = json_decode(file_get_contents($cheersFile), true);
        if ($existing) {
            $cheers = $existing;
        }
    }
    
    // Add new cheer
    if ($fighter === 'wolf') {
        $cheers['wolf_cheers'][] = $cheerData;
    } else {
        $cheers['loong_cheers'][] = $cheerData;
    }
    
    // Save cheers
    file_put_contents($cheersFile, json_encode($cheers));
    
    // Release lock
    flock($lock, LOCK_UN);
    
    $_SESSION['cheer_count']++;
    
    echo 'success';
} else {
    echo 'error';
}

fclose($lock);
?>