<?php
class GameConfig {
    const WOLF_BASE_HP = 720;
    const WOLF_BASE_ATTACK = 250;
    const WOLF_BASE_DEFENSE = 120;
    const WOLF_BASE_SPEED = 300;
    const WOLF_BASE_ENERGY = 60;
    
    const LOONG_BASE_HP = 1500;
    const LOONG_BASE_ATTACK = 480;
    const LOONG_BASE_DEFENSE = 480;
    const LOONG_BASE_SPEED = 220;
    const LOONG_BASE_ENERGY = 360;
    
    const DEFENSE_REDUCTION_FACTOR = 0.7;
    const ELEMENTAL_WIND_VS_EARTH = 1.15;
    const ELEMENTAL_EARTH_VS_WIND = 0.92;
    const DAMAGE_VARIANCE_MIN = 0.8;
    const DAMAGE_VARIANCE_MAX = 1.4;
    const ESCAPE_POOL = 1500;
    const ESCAPE_START = 100;
    const ESCAPE_MAX = 600;
    
    const POWER_INHERITANCE_FACTOR = 0.075;
    const COMEBACK_BOOST_PER_LOSS = 0.005;
    const WINNER_NERF_PER_WIN = 0.005;
    const CHAOS_VARIANCE_FACTOR = 0.15;
    const BOOST_CHANCE_POOL = 2;
    
    const MAX_STAT_MULTIPLIER = 50.0;
    const MIN_STAT_MULTIPLIER = 0.8;
    
    const ROUND_DELAY = 1;
    const ACTION_DELAY = 1;
    const BREAK_DURATION = 5;
    const INHERIT_DISPLAY_DURATION = 3;
    const MAX_ROUNDS_PER_BATTLE = 150;
    const ENERGY_RESTORE_AMOUNT = 25;
    const ENERGY_GAIN_MIN = 4;
    const ENERGY_GAIN_MAX = 15;
    const REGEN_ENERGY = 35;
    const REGEN_RATIO = 0.3;
    const REGEN_SHUFFLE = 0.25;

    const MAX_LINES = 12;
    const MAX_STAT_HISTORY = 50;
}