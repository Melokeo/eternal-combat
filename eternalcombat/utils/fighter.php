<?php
/**
 * Fighter class; status processing
 */

require_once __DIR__ . '/game_config.php';

class StatusEffect {
    public string $name;
    public int $duration;
    public string $effect_type; // "buff", "debuff", "dot", "regen"
    public int $value;
    
    public function __construct(string $name, int $duration, string $effect_type, int $value) {
        $this->name = $name;
        $this->duration = $duration;
        $this->effect_type = $effect_type;
        $this->value = $value;
    }
}

class Fighter {
    public string $name;
    public string $element; // "earth" | "wind"
    public int $hp;
    public int $max_hp;
    public int $base_max_hp;
    public int $attack;
    public int $defense;
    public int $speed;
    public int $energy;
    public int $max_energy;
    public int $special_cooldown = 0;
    public array $status_effects = [];
    public bool $can_act = true;
    public float $accuracy = 1.1;
    
    // inheritance tracking
    public int $inherited_attack = 0;
    public int $inherited_defense = 0;
    public int $inherited_speed = 0;
    public int $inherited_max_hp = 0;
    public float $inherited_accuracy = 1.1;
    
    public function __construct(
        string $name, string $element, int $hp, int $max_hp, int $base_max_hp,
        int $attack, int $defense, int $speed, int $energy, int $max_energy
    ) {
        $this->name = $name;
        $this->element = $element;
        $this->hp = $hp;
        $this->max_hp = $max_hp;
        $this->base_max_hp = $base_max_hp;
        $this->attack = $attack;
        $this->defense = $defense;
        $this->speed = $speed;
        $this->energy = $energy;
        $this->max_energy = $max_energy;
        
        // initialize inheritance
        if ($this->inherited_attack == 0) {
            $this->inherited_attack = $this->attack;
            $this->inherited_defense = $this->defense;
            $this->inherited_speed = $this->speed;
            $this->inherited_max_hp = $this->max_hp;
            $this->inherited_accuracy = $this->accuracy;
        }
    }
    
    public function isAlive(): bool {
        return $this->hp > 0;
    }
    
    public function getEffectiveStats(): array {
        $att_mod = $def_mod = $spd_mod = 0;
        foreach ($this->status_effects as $effect) {
            switch ($effect->effect_type) {
                case "attack_buff": $att_mod += $effect->value; break;
                case "attack_debuff": $att_mod -= $effect->value; break;
                case "defense_buff": $def_mod += $effect->value; break;
                case "defense_debuff": $def_mod -= $effect->value; break;
                case "speed_buff": $spd_mod += $effect->value; break;
                case "speed_debuff": $spd_mod -= $effect->value; break;
            }
        }
        
        return [
            max(1, $this->attack + $att_mod),
            max(0, $this->defense + $def_mod),
            max(1, $this->speed + $spd_mod)
        ];
    }
    
    public function takeDamage(int $damage): int {
        [$effective_att, $effective_def, $effective_spd] = $this->getEffectiveStats();
        $damage_reduction = $effective_def * GameConfig::DEFENSE_REDUCTION_FACTOR;
        $actual_damage = max(1, intval($damage - $damage_reduction));
        $this->hp = max(0, $this->hp - $actual_damage);
        return $actual_damage;
    }
    
    public function heal(int $amount): void {
        $this->hp = min($this->max_hp, $this->hp + $amount);
    }
    
    public function restoreEnergy(int $amount): void {
        $this->energy = min($this->max_energy, $this->energy + $amount);
    }
    


    // ========================== BELOW HANDLES NUMEROUS STATUSES ============================

    public function addStatusEffect(StatusEffect $effect): void {
        // remove existing effect of same type
        $this->status_effects = array_filter($this->status_effects, fn($e) => $e->name !== $effect->name);
        $this->status_effects[] = $effect;
    }

    public function clearAllStatusEffects(): void {
        $this->status_effects = [];
        $this->speed = $this->inherited_speed;
        $this->attack = $this->inherited_attack;  
        $this->defense = $this->inherited_defense;
        $this->accuracy = $this->inherited_accuracy;
        $this->can_act = true;
    }
    
    /* public function processStatusEffects(): array {
        $messages = [];
        $effects_to_remove = [];

        foreach ($this->status_effects as $effect) {
            // Tick duration
            $effect->duration--;
            if ($effect->duration <= 0) {
                $effects_to_remove[] = $effect;
            }
            // Core logic
            switch ($effect->name) {
                case 'burn':
                    $damage = intval($this->max_hp * 0.08);
                    $this->hp -= $damage;
                    $messages[] = "🔥 {$this->name} 被灼烧，失去 {$damage} 生命";
                    break;
                case 'bleed':
                    $damage = intval($this->max_hp * 0.08);
                    $this->hp -= $damage;
                    $messages[] = "🩸 {$this->name} 流血，失去 {$damage} 生命";
                    break;
                case 'slow':
                    $this->speed = intval($this->inherited_speed * 0.4);
                    $messages[] = "🐢 {$this->name} 被减速";
                    break;
                case 'stun':
                    $this->can_act = false;
                    $messages[] = "💫 {$this->name} 被眩晕，无法行动";
                    break;
                case 'heal':
                    $heal = intval($this->max_hp * 0.1);
                    $this->heal($heal);
                    $messages[] = "💚 {$this->name} 治愈自身，恢复 {$heal} 生命";
                    break;
                case 'armor_break':
                    $this->defense = intval($this->inherited_defense * 0.4);
                    $messages[] = "🛡️ {$this->name} 护甲被破坏";
                    break;
                case 'fear':
                    $this->defense = intval($this->inherited_defense * 0.5);
                    $this->attack = intval($this->inherited_attack * 0.6);
                    $messages[] = "😨 {$this->name} 陷入恐惧，攻防下降";
                    break;
            }
            if ($effect->duration <= 0) {
                $messages[] = "✨ {$this->name} 的 {$effect->name} 效果消失";
                switch ($effect->name) {
                    case 'slow':
                        $this->speed = intval($this->inherited_speed);
                        break;
                    case 'armor_break':
                        $this->defense = intval($this->inherited_defense);
                        break;
                    case 'fear':
                        $this->defense = intval($this->inherited_defense);
                        $this->attack = intval($this->inherited_attack);
                        break;
                }
            }
        }

        // Remove expired effects
        foreach ($effects_to_remove as $effect) {
            $this->status_effects = array_filter($this->status_effects, fn($e) => $e !== $effect);
        }

        return $messages;
    }
    */

    public function processStatusEffects(): array {
        $messages = [];
        $effects_to_remove = [];

        foreach ($this->status_effects as $effect) {
            $effect->duration--;
            
            if ($effect->duration <= 0) {
                $effects_to_remove[] = $effect;
                $messages[] = "✨ {$this->name} 的 {$effect->name} 效果消失";
            }
            
            // Core logic
            switch ($effect->name) {
                case 'burn':
                    $damage = intval($this->max_hp * 0.08);
                    $this->hp -= $damage;
                    $messages[] = "🔥 {$this->name} 被灼烧，失去 {$damage} 生命";
                    break;
                case 'bleed':
                    $damage = intval($this->max_hp * 0.08);
                    $this->hp -= $damage;
                    $messages[] = "🩸 {$this->name} 流血，失去 {$damage} 生命";
                    break;
                case 'slow':
                    $this->speed = intval($this->inherited_speed * 0.4);
                    $messages[] = "🐢 {$this->name} 被减速";
                    break;
                case 'stun':
                    $this->can_act = false;
                    $messages[] = "💫 {$this->name} 被眩晕，无法行动";
                    break;
                case 'heal':
                    $heal = intval($this->max_hp * 0.1);
                    $this->heal($heal);
                    $messages[] = "💚 {$this->name} 治愈自身，恢复 {$heal} 生命";
                    break;
                case 'armor_break':
                    $this->defense = intval($this->inherited_defense * 0.4);
                    $messages[] = "🛡️ {$this->name} 护甲被破坏";
                    break;
                case 'fear':
                    $this->defense = intval($this->inherited_defense * 0.5);
                    $this->attack = intval($this->inherited_attack * 0.6);
                    $messages[] = "😨 {$this->name} 陷入恐惧，攻防下降";
                    break;
                case 'cut':
                    $damage = intval($this->max_hp * 0.04);
                    $this->hp -= $damage;
                    $this->defense = intval($this->inherited_defense * 0.9);
                    $messages[] = "🗡️ {$this->name} 被切割，持续流血并降低防御";
                    break;

                case 'dizzy':
                    $this->accuracy = 0.7;
                    $this->speed = intval($this->inherited_speed * 0.8);
                    $messages[] = "💫 {$this->name} 眩晕，命中和速度下降";
                    break;

                case 'knockback':
                    $this->speed = intval($this->inherited_speed * 0.6);
                    $damage = intval($this->max_hp * 0.03);
                    $this->hp -= $damage;
                    $messages[] = "💨 {$this->name} 被击退，失去 {$damage} 生命";
                    break;

                case 'vacuum':
                    $damage = intval($this->max_hp * 0.12);
                    $this->hp -= $damage;
                    $this->can_act = rand(0, 3) > 0; // 75% chance to act
                    $messages[] = "🌪️ {$this->name} 被真空撕扯，严重受伤";
                    break;

                case 'wet':
                    $this->defense = intval($this->inherited_defense * 0.8);
                    // increases electric damage vulnerability
                    $messages[] = "💧 {$this->name} 被浸湿，防御下降";
                    break;

                /*case 'sacrifice':
                    // self-damage for massive attack boost
                    $selfDamage = intval($this->max_hp * 0.3);
                    $this->hp -= $selfDamage;
                    $this->attack = intval($this->inherited_attack * 2.0);
                    $messages[] = "⚔️ {$this->name} 舍身一击，攻击大幅提升但自损";
                    break;*/

                case 'rage':
                    $this->attack = intval($this->inherited_attack * 1.5);
                    $this->defense = intval($this->inherited_defense * 0.7);
                    $this->accuracy = 0.8;
                    $messages[] = "😤 {$this->name} 狂怒，攻击提升但防御和精准下降";
                    break;

                case 'evasion_up':
                    $this->speed = intval($this->inherited_speed * 1.3);
                    $messages[] = "🦋 {$this->name} 身法提升，闪避能力增强";
                    break;

                case 'pierce':
                    $damage = intval($this->max_hp * 0.06);
                    $this->hp -= $damage;
                    $this->defense = intval($this->inherited_defense * 0.85);
                    $messages[] = "🎯 {$this->name} 被穿刺，护甲效果减弱";
                    break;

                case 'mirage':
                    $this->accuracy = 0.6;
                    $messages[] = "🏜️ {$this->name} 陷入幻象，难以分辨真实目标";
                    break;

                case 'fury':
                    $this->attack = intval($this->inherited_attack * 1.4);
                    $selfDamage = intval($this->max_hp * 0.08);
                    $this->hp -= $selfDamage;
                    $messages[] = "🔥 {$this->name} 进入狂暴状态，攻击提升但持续自损";
                    break;

                case 'electrocute':
                    $damage = intval($this->max_hp * 0.10);
                    $this->hp -= $damage;
                    $this->can_act = rand(0, 1) == 0; // 50% paralysis chance
                    $messages[] = "⚡ {$this->name} 被电流贯穿，剧痛并可能麻痹";
                    break;

                case 'disorient':
                    $this->accuracy = 0.3;
                    $this->speed = intval($this->inherited_speed * 0.7);
                    $messages[] = "🌫️ {$this->name} 迷失方向";
                    break;

                case 'pressure':
                    $this->speed = intval($this->inherited_speed * 0.5);
                    $damage = intval($this->max_hp * 0.05);
                    $this->hp -= $damage;
                    $messages[] = "🏔️ {$this->name} 被高压束缚，行动困难";
                    break;

                case 'cosmic':
                    $damage = intval($this->max_hp * 0.07);
                    $this->hp -= $damage;
                    $this->energy = max(0, $this->energy - 8);
                    $messages[] = "⭐ {$this->name} 被星尘侵蚀，生命和能量流失";
                    break;

                case 'void':
                    $damage = intval($this->max_hp * 0.15);
                    $this->hp -= $damage;
                    $this->attack = intval($this->inherited_attack * 0.7);
                    $this->defense = intval($this->inherited_defense * 0.7);
                    $messages[] = "🕳️ {$this->name} 被虚无吞噬，全属性下降";
                    break;

                case 'dazzle':
                    $this->accuracy = 0.2;
                    $messages[] = "✨ {$this->name} 被炫目光芒致盲";
                    break;

                case 'choke':
                    $damage = intval($this->max_hp * 0.09);
                    $this->hp -= $damage;
                    $this->energy = max(0, $this->energy - 6);
                    $messages[] = "🫁 {$this->name} 窒息，生命和能量持续流失";
                    break;

                case 'condense':
                    $this->speed = intval($this->inherited_speed * 0.3);
                    $damage = intval($this->max_hp * 0.06);
                    $this->hp -= $damage;
                    $messages[] = "💧 {$this->name} 被水压束缚，移动困难";
                    break;

                case 'deluge':
                    $damage = intval($this->max_hp * 0.08);
                    $this->hp -= $damage;
                    $this->accuracy = 0.7;
                    $messages[] = "🌊 {$this->name} 被洪流冲击";
                    break;

                case 'levitate':
                    $this->can_act = rand(0, 2) > 0; // 66% chance to act
                    $this->accuracy = 0.6;
                    $messages[] = "🎈 {$this->name} 失控浮空，难以攻击";
                    break;

                case 'implode':
                    $damage = intval($this->max_hp * 0.18);
                    $this->hp -= $damage;
                    $this->defense = intval($this->inherited_defense * 0.6);
                    $messages[] = "💥 {$this->name} 被内爆重创，防御大幅下降";
                    break;

                case 'seasons':
                    // random seasonal effect
                    $season = rand(0, 3);
                    switch($season) {
                        case 0: // spring - heal
                            $heal = intval($this->max_hp * 0.05);
                            $this->heal($heal);
                            $messages[] = "🌸 春之生机治愈了 {$this->name}";
                            break;
                        case 1: // summer - burn
                            $damage = intval($this->max_hp * 0.08);
                            $this->hp -= $damage;
                            $messages[] = "☀️ 夏之烈焰灼烧了 {$this->name}";
                            break;
                        case 2: // autumn - weaken
                            $this->attack = intval($this->inherited_attack * 0.8);
                            $messages[] = "🍂 秋之凋零削弱了 {$this->name}";
                            break;
                        case 3: // winter - slow
                            $this->speed = intval($this->inherited_speed * 0.6);
                            $messages[] = "❄️ 冬之严寒冻结了 {$this->name}";
                            break;
                    }
                    break;

                case 'erosion':
                    $this->defense = intval($this->inherited_defense * 0.7);
                    $this->max_hp = intval($this->max_hp * 0.95);
                    $messages[] = "🌪️ {$this->name} 被侵蚀，最大生命永久下降";
                    break;

                case 'weave':
                    $this->can_act = false;
                    $this->energy = max(0, $this->energy - 10);
                    $messages[] = "🕸️ {$this->name} 被云纱缠绕，无法行动";
                    break;

                case 'siphon':
                    $energyDrain = min(10, $this->energy);
                    $this->energy -= $energyDrain;
                    // opponent gains energy (implement in combat logic)
                    $messages[] = "🌀 {$this->name} 被吸取 {$energyDrain} 点能量";
                    break;

                case 'resonate':
                    $damage = intval($this->max_hp * 0.12);
                    $this->hp -= $damage;
                    $this->can_act = rand(0, 4) > 0; // 80% chance to act
                    $messages[] = "📢 {$this->name} 被音波震荡，可能无法行动";
                    break;
                
                case 'blind':
                    $this->accuracy = 0.6; // reduce accuracy significantly
                    $messages[] = "👁️ {$this->name} 被致盲，命中率大幅下降";
                    break;
                    
                case 'confusion':
                    $this->can_act = rand(0, 1) == 0; // 50% chance to act
                    $messages[] = "🌀 {$this->name} 陷入混乱";
                    break;
                    
                case 'knockdown':
                    $this->can_act = false;
                    $this->defense = intval($this->inherited_defense * 0.4);
                    $messages[] = "⬇️ {$this->name} 被击倒，防御下降";
                    break;
                    
                case 'cripple':
                    $this->speed = intval($this->inherited_speed * 0.3);
                    $this->attack = intval($this->inherited_attack * 0.8);
                    $messages[] = "🦵 {$this->name} 被致残，移动和攻击受限";
                    break;
                    
                case 'entrap':
                    $this->can_act = false;
                    $this->speed = 0;
                    $messages[] = "🕸️ {$this->name} 被困住，无法移动";
                    break;
                    
                case 'petrify':
                    $this->can_act = false;
                    $this->defense = intval($this->inherited_defense * 2.2); // stone form increases defense
                    $messages[] = "🗿 {$this->name} 被石化";
                    break;
                    
                case 'poison':
                    $damage = intval($this->max_hp * 0.06);
                    $this->hp -= $damage;
                    $this->attack = intval($this->inherited_attack * 0.9);
                    $messages[] = "☠️ {$this->name} 中毒，失去 {$damage} 生命并削弱攻击";
                    break;
                    
                case 'shock':
                    $damage = intval($this->max_hp * 0.05);
                    $this->hp -= $damage;
                    $this->can_act = rand(0, 2) > 0; // 66% chance to act
                    $messages[] = "⚡ {$this->name} 被电击，失去 {$damage} 生命并可能麻痹";
                    break;
                    
                case 'fatigue':
                    $this->energy = max(0, $this->energy - 5);
                    $this->attack = intval($this->inherited_attack * 0.8);
                    $messages[] = "😴 {$this->name} 疲劳，能量和攻击下降";
                    break;
                    
                case 'silence':
                    // prevent special attacks (energy cost > 20)
                    $messages[] = "🤐 {$this->name} 被沉默，无法使用强力技能";
                    break;
            }
        }

        // Clean up expired effects
        $this->cleanupExpiredEffects($effects_to_remove);
        
        // Remove from array
        foreach ($effects_to_remove as $effect) {
            $this->status_effects = array_filter($this->status_effects, fn($e) => $e !== $effect);
        }

        return $messages;
    }

    private function cleanupExpiredEffects(array $expired_effects): void {
        $stat_modifying_effects = [
            'slow', 'armor_break', 'fear', 'blind', 'cripple', 'rage', 'evasion_up',
            'dizzy', 'knockback', 'wet', 'sacrifice', 'pierce', 'mirage', 'fury',
            'disorient', 'pressure', 'void', 'dazzle', 'levitate', 'implode', 'erosion'
        ];
        
        $action_blocking_effects = [
            'stun', 'knockdown', 'entrap', 'petrify', 'weave', 'confusion', 'vacuum',
            'electrocute', 'resonate'
        ];
        
        foreach ($expired_effects as $effect) {
            switch ($effect->name) {
                // stat restoration cases
                case 'slow':
                case 'cripple':
                case 'dizzy':
                case 'knockback':
                case 'pressure':
                case 'condense':
                    $this->speed = $this->inherited_speed;
                    break;
                    
                case 'armor_break':
                case 'pierce':
                case 'wet':
                case 'implode':
                    $this->defense = $this->inherited_defense;
                    break;
                    
                case 'fear':
                case 'void':
                case 'fury':
                case 'fatigue':
                    $this->attack = $this->inherited_attack;
                    $this->defense = $this->inherited_defense;
                    break;
                    
                case 'rage':
                    $this->attack = $this->inherited_attack;
                    $this->defense = $this->inherited_defense;
                    $this->accuracy = $this->inherited_accuracy;
                    break;
                    
                case 'sacrifice':
                    $this->attack = $this->inherited_attack;
                    break;
                    
                case 'evasion_up':
                    $this->speed = $this->inherited_speed;
                    break;
                    
                case 'blind':
                case 'mirage':
                case 'disorient':
                case 'dazzle':
                case 'deluge':
                case 'levitate':
                    $this->accuracy = $this->inherited_accuracy;
                    break;
                    
                case 'petrify':
                    $this->defense = $this->inherited_defense;
                    $this->can_act = true;
                    break;
                    
                // action restoration
                case 'stun':
                case 'knockdown':
                case 'entrap':
                case 'weave':
                    $this->can_act = true;
                    break;
            }
        }
    }

}