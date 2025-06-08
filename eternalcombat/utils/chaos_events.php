<?php
/**
 * Here are the chaos logic & functions used in combat_daemon
 */

function apply_effect(Fighter $target, string $name, int $duration, string $type, int $value): void {
    $target->addStatusEffect(new StatusEffect($name, $duration, $type, $value));
}

function apply_multiple_effects(Fighter $target, array $effects): void {
    foreach ($effects as [$name, $duration, $type, $value]) {
        apply_effect($target, $name, $duration, $type, $value);
    }
}

function random_target(Fighter $wolf, Fighter $loong): Fighter {
    return random_int(0, 1) ? $wolf : $loong;
}

/* ============================================================================ */

function chaos_RESTART($wolf, $loong, &$battle_count, &$round_num): array {
    $battle_count = 1;
    $round_num = 1;
    return [
        "🚨检测到未知登录请求",
        "DELAY:3",
        "battle@melokeo.icu:~/eternalbattle $ sudo rm -rf /",
        "DELAY:1",
        "[sudo] password for battle",
        "DELAY:3",
        "...",
        "程序崩溃了！！",
        "战斗将会从头开始",
        "DELAY:2",
        "RESTART:true"
    ];
}

function chaos_chick() {
    return ["🐔 一只鸡跑过了战场...", "DELAY:1", "🐥🐥 还带着两只小鸡!", "DELAY:1", "...无事发生"];
}

function chaos_sheep() {
    return ["🥦 一只羊路过战场...", "DELAY:1", "🐑🐑 咩咩咩~"];
}

function chaos_bolopizza() {
    return ["🍍🍕 菠萝披萨出现了!", "DELAY:1", "...", "DELAY:2", "🏃‍♀️ Cazzo! 一个意大利人在后面追!"];
}

function chaos_HealAll($wolf, $loong): array {
    $healAmount = random_int(20, 30);
    $wolfHeal = intval($wolf->max_hp * $healAmount / 100);
    $loongHeal = intval($loong->max_hp * $healAmount / 100);
    $wolf->heal($wolfHeal);
    $loong->heal($loongHeal);
    return [
        "🌸【桃园结义】",
        "{$wolf->name} +{$wolfHeal} HP, {$loong->name} +{$loongHeal} HP",
        "DELAY:1",
        "未知观战用户 +1s"
    ];
}

function chaos_lair($wolf, $loong): array {
    $hpBoost = random_int(200, 500);
    $energyLoss = random_int(60, 120);
    $loong->max_hp += $hpBoost;
    $loong->hp += $hpBoost;
    $wolf->energy = max(0, $wolf->energy - $energyLoss);
    return ["...??", "DELAY:1", "?...?!!", "DELAY:1", "....!!!!", "DELAY:1", "... {$loong->name}+{$hpBoost}HP, {$wolf->name}-{$energyLoss}能量"];
}

function chaos_7star() {
    return ["🚗💨💨", "DELAY:1", "🎶全速怕什么怕", "DELAY:2", "🚓🚓🚓🚓⭐⭐⭐", "DELAY:1", "🚨 警车追逐战! 本回合跳过!", "SKIP_ROUND:true"];
}

function chaos_ddl($wolf, $loong): array {
    $target = random_int(0, 1) ? $wolf : $loong;
    $effect = random_int(0, 1) ? ['fear', 3, 'speed_debuff', random_int(20, 150)] : ['armor_break', 3, 'defense_debuff', random_int(20, 200)];
    apply_effect($target, ...$effect);
    return ["⏰📅 DDL来袭! {$target->name}受到{$effect[0]}效果"];
}

function chaos_dizzy() {
    return ["💫 攻击者眩晕了! 攻击自己!", "DIZZY:true"];
}

function chaos_exist($wolf, $loong): array {
    $statFlutter = random_int(-50, 50);
    $wolf->attack += $statFlutter;
    $loong->defense += $statFlutter;
    return ["🪬 存在主义危机... 属性波动中"];
}

function chaos_alien($wolf, $loong): array {
    $target = random_int(0, 1) ? $wolf : $loong;
    $isBonus = random_int(0, 1);
    $amount = random_int(50, 800);
    if ($isBonus) {
        $target->attack += $amount;
        return ["👽 外星人赠送了力量! {$target->name}+{$amount}攻击"];
    } else {
        $target->hp = max(1, $target->hp - $amount);
        return ["👽 外星人发动攻击! {$target->name}-{$amount}HP"];
    }
}

function chaos_RandomHit($wolf, $loong): array {
    $target = random_int(0, 1) ? $wolf : $loong;
    $damage = random_int(500, 10000);
    $target->hp = max(1, $target->hp - $damage);
    return ["🎯 神秘攻击! {$target->name}-{$damage}HP"];
}

function chaos_RandomHeal($wolf, $loong): array {
    $target = random_int(0, 1) ? $wolf : $loong;
    $heal = random_int(300, 8000);
    $target->heal($heal);
    return ["🩹 神秘治疗! {$target->name}+{$heal}HP"];
}

function chaos_EnergyTrap($wolf, $loong): array {
    $wolf->energy = 0;
    $loong->energy = 0;
    return ["🪫 能量陷阱! 双方能量归零"];
}

function chaos_SlowBoth($wolf, $loong): array {
    apply_effect($wolf, 'slow', 3, 'speed_debuff', 30);
    apply_effect($loong, 'slow', 3, 'speed_debuff', 30);
    return ["🐢🐢 双方都变慢了"];
}

function chaos_BurnBoth($wolf, $loong): array {
    apply_effect($wolf, 'burn', 3, 'dot', 100);
    apply_effect($loong, 'burn', 3, 'dot', 100);
    return ["☲ 双方都着火了!"];
}

function chaos_ArmorBreak($wolf, $loong): array {
    $target = random_int(0, 1) ? $wolf : $loong;
    apply_effect($target, 'armor_break', 4, 'defense_debuff', 50);
    return ["🛡️💥 {$target->name}的护甲破碎了!"];
}

function chaos_WindBlow($wolf, $loong): array {
    $wolf->speed += random_int(20, 280);
    $loong->speed += random_int(150, 1650);
    return ["🀀 风元素暴涨! 速度提升"];
}

function chaos_outstanding($wolf, $loong): array {
    $stats = ['hp', 'attack', 'defense', 'speed'];
    $boostStat = $stats[array_rand($stats)];
    $boostAmount = random_int(50, 150);
    $msg = ["卓越福瑞科学家"];
    switch ($boostStat) {
        case 'hp':
            $wolf->max_hp += $boostAmount * 10;
            $loong->max_hp += $boostAmount * 10;
            $msg[] = "..生命力提升!";
            break;
        case 'attack':
            $wolf->attack += $boostAmount;
            $loong->attack += $boostAmount;
            $msg[] = "..攻击力暴涨!";
            break;
        case 'defense':
            $wolf->defense += $boostAmount;
            $loong->defense += $boostAmount;
            $msg[] = "..防御力增强!";
            break;
        case 'speed':
            $wolf->speed += $boostAmount;
            $loong->speed += $boostAmount;
            $msg[] = "..速度飞升!";
            break;
    }
    if (random_int(1, 10) <= 3) {
        apply_effect($wolf, 'stun', 3, 'speed_debuff', 50);
        apply_effect($loong, 'stun', 3, 'speed_debuff', 50);
        $msg[] = "😰 被卓越吓晕...";
    }
    return $msg;
}

function chaos_Chong($wolf, $loong): array {
    $target = random_int(0, 1) ? $wolf : $loong;
    $target->can_act = false;
    $target->energy = intval($target->energy * 0.3);
    return ["{$target->name} 🐛晕过去了"];
}

function chaos_UpsideDown($wolf, $loong): array {
    return ["🙃 世界颠倒了! 找不着北...", "双方将攻击自己！", "DIZZY:true"];
}

function chaos_FourthWall($wolf, $loong): array {
    return [
        "🎭 第四面墙被打破了!",
        "DELAY:1",
        "{$wolf->name}: '我们只是在代码里打架吗?'",
        "DELAY:2",
        "{$loong->name}: ...",
        "DELAY:7",
        "{$loong->name}: '别想太多，继续战斗!'",
    ];
}

function chaos_TacoRain($wolf, $loong): array {
    $tacoCount = random_int(5, 15);
    $messages = ["🌮🌮🌮 天降塔可饼! 🌮🌮🌮", "DELAY:1"];
    $stats = ['heal' => 0, 'damage' => 0];
    
    for ($i = 0; $i < $tacoCount; $i++) {
        array_push($messages, "DELAY:1");
        $target = random_target($wolf, $loong);
        $isSpicy = random_int(0, 1);
        
        if ($isSpicy) {
            $dmg = random_int(30, 80);
            $target->hp = max(1, $target->hp - $dmg);
            $stats['damage'] += $dmg;
            array_push($messages, "🔥 {$target->name}吃到辣塔可! -{$dmg}HP");
        } else {
            $heal = random_int(20, 50);
            $target->heal($heal);
            $stats['heal'] += $heal;
            array_push($messages, "😋 {$target->name}吃到美味塔可! +{$heal}HP");
        }
    }
    
    array_push($messages, 
        "DELAY:1",
        "总计: +{$stats['heal']}HP, -{$stats['damage']}HP"
    );
    return $messages;
}

function chaos_DinoAttack($wolf, $loong): array {
    $dinos = ['🦖霸王龙', '🦕雷龙', '🦕三角龙', '🦕翼龙'];
    $dinoType = $dinos[random_int(0, 3)];
    $target = random_target($wolf, $loong);
    $damage = random_int(200, 5500);
    $target->hp = max(1, $target->hp - $damage);
    
    return [
        "🦖 恐龙入侵! {$dinoType}加入战斗!",
        "DELAY:1",
        "{$dinoType}咬了{$target->name}! -{$damage}HP",
        "DELAY:1",
        "🦴 恐龙心满意足地离开了..."
    ];
}

function chaos_KaLe($wolf, $loong): array {
    $lagDuration = random_int(2, 5);
    $wolf->energy = min($wolf->max_energy, $wolf->energy + 80);
    $loong->energy = min($loong->max_energy, $loong->energy + 80);
    
    return [
        "🌐 网络延迟中...",
        "DELAY:{$lagDuration}",
        "⏱️ 延迟了{$lagDuration}秒",
        "双方在等待中恢复了能量",
        "{$wolf->name}和{$loong->name}能量+40"
    ];
}

function chaos_UFO($wolf, $loong): array {
    $target = random_target($wolf, $loong);
    $messages = [
        "🛸 UFO出现!",
        "DELAY:1",
        "💫 光束罩住了{$target->name}...",
        "DELAY:2"
    ];
    
    if (random_int(1, 10) <= 7) {
        $statChange = random_int(-350, 550);
        $stat = ['attack', 'defense', 'speed'][random_int(0, 2)];
        $statNames = ['attack' => '攻击力', 'defense' => '防御力', 'speed' => '速度'];
        $target->{$stat} += $statChange;
        
        array_push($messages,
            "👽 外星人做了些奇怪的实验",
            "{$target->name}的{$statNames[$stat]}" . ($statChange > 0 ? "+" : "") . "{$statChange}"
        );
    } else {
        array_push($messages,
            "👽 外星人把{$target->name}放了回来",
            "...但好像忘了什么",
            "DELAY:1",
            "{$target->name}的记忆被抹除了!",
            "但战斗还是继续"
        );
    }
    return $messages;
}

function chaos_PlushieRain($wolf, $loong): array {
    $healPercent = random_int(10, 30);
    $wolfHeal = intval($wolf->max_hp * $healPercent / 100);
    $loongHeal = intval($loong->max_hp * $healPercent / 100);
    $wolf->heal($wolfHeal);
    $loong->heal($loongHeal);
    
    return [
        "🧸 毛绒玩具从天而降!",
        "DELAY:1",
        "双方被治愈了{$healPercent}%HP",
        "{$wolf->name} +{$wolfHeal}HP, {$loong->name} +{$loongHeal}HP",
        "🎵 毛绒绒~软乎乎~"
    ];
}

function chaos_Glitch($wolf, $loong): array {
    $glitchType = random_int(1, 4);
    $messages = ["📺 信号干扰...", "DELAY:1"];
    
    switch ($glitchType) {
        case 1:
            array_push($messages, 
                "҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉",
                "DELAY:3",
                "҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉҉"
            );
            break;
        case 2:
            array_push($messages,
                "01010111 01101000 01100001 01110100 00100000 01101000 01100001 01110000 01110000 01100101 01101110 01100101 01100100 00111111",
                "DELAY:2"
            );
            break;
        case 3:
            $wolf->energy = max(0, $wolf->energy - 250);
            $loong->energy = max(0, $loong->energy - 250);
            array_push($messages, "💾 内存溢出错误", "双方能量-250");
            break;
        case 4:
            array_push($messages, "🔄 渲染错误...");
            break;
    }
    return $messages;
}

function chaos_CatIntervention($wolf, $loong): array {
    $catCount = random_int(3, 9);
    $messages = ["🐱 {$catCount}只猫闯入战场!", "DELAY:1"];
    
    for ($i = 0; $i < $catCount; $i++) {
        $action = random_int(1, 4);
        $target = random_target($wolf, $loong);
        
        switch ($action) {
            case 1:
                $dmg = random_int(5, 15);
                $target->hp = max(1, $target->hp - $dmg);
                array_push($messages, "🐾 猫抓了{$target->name}一下! -{$dmg}HP");
                break;
            case 2:
                $heal = random_int(10, 20);
                $target->heal($heal);
                array_push($messages, "😻 猫蹭了蹭{$target->name} +{$heal}HP");
                break;
            case 3:
                apply_effect($target, "distracted", 2, "accuracy_debuff", 30);
                array_push($messages, "🧶 {$target->name}被逗猫棒吸引了注意力!");
                break;
            case 4:
                array_push($messages, "🐈 猫在{$target->name}脚边蜷成一团睡着了...");
                break;
        }
        array_push($messages, "DELAY:1");
    }
    return $messages;
}
?>