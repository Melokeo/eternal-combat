<?php
/**
 * Fighter balancing & perform attacks
 */

require_once __DIR__ . '/fighter.php';

function calculateElementalBonus(string $attacker, string $defender): float {
    if ($attacker === "wind" && $defender === "earth") {
        return GameConfig::ELEMENTAL_WIND_VS_EARTH;
    } elseif ($attacker === "earth" && $defender === "wind") {
        return GameConfig::ELEMENTAL_EARTH_VS_WIND;
    }
    return 1.0;
}

function f($x) { // win-history based bonus factor
    return $x + 0.25 * pow(max(0, $x - 5), 2) - 0.25 * pow(max(0, -$x - 5), 2);
}

function balancedFighterReset(Fighter $wolf, Fighter $loong, int $wolf_wins, int $loong_wins, array $win_curve): void {
    $adjust_stats = function(Fighter $fighter, array $base_stats, float $adjustment) {
        $inherit_factor = GameConfig::POWER_INHERITANCE_FACTOR;

        $fighter->inherited_attack = intval($fighter->inherited_attack * $inherit_factor + $fighter->attack * (1 - $inherit_factor));
        $fighter->inherited_defense = intval($fighter->inherited_defense * $inherit_factor + $fighter->defense * (1 - $inherit_factor));
        $fighter->inherited_speed = intval($fighter->inherited_speed * $inherit_factor + $fighter->speed * (1 - $inherit_factor));
        $fighter->inherited_max_hp = intval($fighter->inherited_max_hp * $inherit_factor + $fighter->max_hp * (1 - $inherit_factor));

        $fighter->attack = (random_int(0, GameConfig::BOOST_CHANCE_POOL) === 1) ? 
            intval($fighter->inherited_attack * $adjustment) : $fighter->inherited_attack;
        $fighter->defense = (random_int(0, GameConfig::BOOST_CHANCE_POOL) === 1) ? 
            intval($fighter->inherited_defense * $adjustment) : $fighter->inherited_defense;
        $fighter->speed = (random_int(0, GameConfig::BOOST_CHANCE_POOL) === 1) ? 
            intval($fighter->inherited_speed * $adjustment) : $fighter->inherited_speed;
        $fighter->max_hp = (random_int(0, GameConfig::BOOST_CHANCE_POOL) === 1) ? 
            intval($fighter->inherited_max_hp * $adjustment) : $fighter->inherited_max_hp;

        $fighter->attack = max(intval($base_stats[0] * GameConfig::MIN_STAT_MULTIPLIER), min(intval($base_stats[0] * GameConfig::MAX_STAT_MULTIPLIER), $fighter->attack));
        $fighter->defense = max(intval($base_stats[1] * GameConfig::MIN_STAT_MULTIPLIER), min(intval($base_stats[1] * GameConfig::MAX_STAT_MULTIPLIER), $fighter->defense));
        $fighter->speed = max(intval($base_stats[2] * GameConfig::MIN_STAT_MULTIPLIER), min(intval($base_stats[2] * GameConfig::MAX_STAT_MULTIPLIER), $fighter->speed));
        $fighter->max_hp = max(intval($base_stats[3] * GameConfig::MIN_STAT_MULTIPLIER), min(intval($base_stats[3] * GameConfig::MAX_STAT_MULTIPLIER), $fighter->max_hp));
    };

    $wolf_accum = end($win_curve) === 1 ? 1 : -1;
    $loong_accum = end($win_curve) === 2 ? 1 : -1;

    $i = count($win_curve) - 2;
    while ($i >= 0) {  // ensure $i never goes below 0
        if ($wolf_accum > 0) {
            if ($win_curve[$i] !== 1) break;
            $wolf_accum++;
        } else {
            if ($win_curve[$i] !== 2) break;
            $wolf_accum--;
        }
        $i--;
    }

    // handle the case where we've gone through all history
    if ($i < 0 && $wolf_accum < 0) {
        $wolf_accum -= count($win_curve);
    }

    $i = count($win_curve) - 2;
    while ($i >= 0) {  
        if ($loong_accum > 0) {
            if ($win_curve[$i] !== 2) break;
            $loong_accum++;
        } else {
            if ($win_curve[$i] !== 1) break;
            $loong_accum--;
        }
        $i--;
    }

    if ($i < 0 && $loong_accum < 0) {
        $loong_accum -= count($win_curve);
    }

    if ($wolf_accum < 0) $wolf_accum = f($wolf_accum);
    if ($loong_accum < 0) $loong_accum = f($loong_accum);

    $wolf_adj = ($wolf_accum < 0)
        ? 1 - $wolf_accum * GameConfig::COMEBACK_BOOST_PER_LOSS
        : 1 - $wolf_accum * GameConfig::WINNER_NERF_PER_WIN;

    $loong_adj = ($loong_accum < 0)
        ? 1 - $loong_accum * GameConfig::COMEBACK_BOOST_PER_LOSS
        : 1 - $loong_accum * GameConfig::WINNER_NERF_PER_WIN;

    echo sprintf("wolf_adj=%.2f, loong_adj=%.2f\n", $wolf_adj, $loong_adj);

    $chaos = 1 + (mt_rand() / mt_getrandmax()) * 2 * GameConfig::CHAOS_VARIANCE_FACTOR - GameConfig::CHAOS_VARIANCE_FACTOR;
    $wolf_adj *= $chaos;
    $loong_adj *= $chaos;

    $adjust_stats($wolf, [GameConfig::WOLF_BASE_ATTACK, GameConfig::WOLF_BASE_DEFENSE, GameConfig::WOLF_BASE_SPEED, GameConfig::WOLF_BASE_HP], $wolf_adj);
    $adjust_stats($loong, [GameConfig::LOONG_BASE_ATTACK, GameConfig::LOONG_BASE_DEFENSE, GameConfig::LOONG_BASE_SPEED, GameConfig::LOONG_BASE_HP], $loong_adj);

    $wolf->max_energy = min(800, intval(GameConfig::WOLF_BASE_ENERGY + ($wolf->attack + $wolf->speed) * 0.05));
    $loong->max_energy = min(800, intval(GameConfig::LOONG_BASE_ENERGY + ($loong->attack + $loong->speed) * 0.05));

    foreach ([$wolf, $loong] as $fighter) {
        $fighter->hp = $fighter->max_hp;
        $fighter->energy = random_int(intval($fighter->max_energy / 2), $fighter->max_energy);
        $fighter->special_cooldown = 0;
        $fighter->status_effects = [];
    }
}

function performAction(Fighter $attacker, Fighter $defender): string {
    global $EARTH_ATTACKS, $WIND_ATTACKS;
    
    if ($attacker->energy < 5) {
        $attacker->restoreEnergy(GameConfig::ENERGY_RESTORE_AMOUNT);
        return "{$attacker->name} 恢复能量";
    }

    if (!$attacker->can_act) {
        $attacker->can_act = true;
        return "{$attacker->name} 被眩晕";
    }
    
    $attacks = $attacker->element === "earth" ? $EARTH_ATTACKS : $WIND_ATTACKS;
    $attack = $attacks[array_rand($attacks)];

    if ($attacker->hp < 0.01 * $attacker->max_hp) {
        if ($attacker->energy >= GameConfig::REGEN_ENERGY) {
            $attacker->energy -= GameConfig::REGEN_ENERGY;
            $rand = mt_rand() / mt_getrandmax();  // [0,1]
            $value = GameConfig::REGEN_RATIO - GameConfig::REGEN_SHUFFLE + $rand * 0.5;
            $attacker->hp += intval($attacker->max_hp * $value);
            return "{$attacker->name} 恢复了 {intval($attacker->max_hp * $value)} HP";
        }
    }
    
    if ($attacker->energy >= $attack->energy_cost) {
        $attacker->energy -= $attack->energy_cost;
        
        [$effective_att, , ] = $attacker->getEffectiveStats();
        $base_damage = intval($effective_att * $attack->base_damage_multiplier * 
                             (mt_rand() / mt_getrandmax() * (GameConfig::DAMAGE_VARIANCE_MAX - GameConfig::DAMAGE_VARIANCE_MIN) + GameConfig::DAMAGE_VARIANCE_MIN));
        $elemental_bonus = calculateElementalBonus($attacker->element, $defender->element);
        $final_damage = intval($base_damage * $elemental_bonus);

        $delta_speed = $attacker->speed - $defender->speed;
        if ($delta_speed < -GameConfig::ESCAPE_START) {
            $escape_chance = min(abs($delta_speed), GameConfig::ESCAPE_MAX);
            if (random_int(0, GameConfig::ESCAPE_POOL) < $escape_chance) {
                return random_int(0,1) === 0 ? "{$defender->name} 逃逸了攻击！" : "{$defender->name} 躲避了 {$attack->name}！"; 
            }
        }

        $hitChance = $attack->accuracy * $attacker->accuracy;
        // echo $hitChance;
        if ((mt_rand() / mt_getrandmax()) > $hitChance) {
            return random_int(0,1) === 0 ? 
            "{$attacker->name} {$attack->name} 打歪了！" : 
            "{$attacker->name} 使用了 {$attack->name} 但取得了近失！";
        }
        
        $actual_damage = $defender->takeDamage($final_damage);
        $attacker->restoreEnergy(random_int(GameConfig::ENERGY_GAIN_MIN, GameConfig::ENERGY_GAIN_MAX));
        
        //  resolve status
        if ($attack->status_effect && (mt_rand() / mt_getrandmax()) < $attack->effect_chance) {
            $defender->addStatusEffect(new StatusEffect(
            $attack->status_effect,
            3,
            in_array($attack->status_effect, ['burn', 'bleed']) ? 'dot' : 'debuff',
            40  // or dynamically based on context
        ));
        }
        return "{$attacker->name} {$attack->name} -{$actual_damage}";
    } else {
        $attacker->restoreEnergy(GameConfig::ENERGY_RESTORE_AMOUNT);
        return "{$attacker->name} 能量不足";
    }
}