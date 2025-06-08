<?php
// attack databases
class Attack {
    public string $name;
    public float $base_damage_multiplier;
    public int $energy_cost;
    public float $accuracy;
    public float $effect_chance;
    public string $status_effect;
    public string $description;
    
    public function __construct(
        string $name, 
        float $base_damage_multiplier, 
        int $energy_cost, 
        float $accuracy, 
        float $effect_chance = 0.0, 
        string $status_effect = "", 
        string $description = ""
    ) {
        $this->name = $name;
        $this->base_damage_multiplier = $base_damage_multiplier;
        $this->energy_cost = $energy_cost;
        $this->accuracy = $accuracy;
        $this->effect_chance = $effect_chance;
        $this->status_effect = $status_effect;
        $this->description = $description;
    }
}

return [
    'earth' => [
        new Attack("岩爪裂空", 0.9, 15, 0.95, 0.15, "armor_break", "利爪撕裂大地，碎石如狼牙般贯穿苍穹"),
        new Attack("地脉震颤", 1.2, 27, 0.85, 0.1, "stun", "远古地核的脉动化作冲击波"),
        new Attack("陨星坠击", 1.1, 16, 0.9, 0.0, "", "天外岩体裹挟星辉轰然坠落"),
        new Attack("尘沙迷障", 0.7, 4, 0.98, 0.2, "blind", "戈壁狂沙席卷成视觉牢笼"),
        new Attack("棘刺地牢", 1.0, 7, 0.88, 0.12, "slow", "结晶岩刺破土成荆棘囚笼"),
        new Attack("断崖崩落", 1.4, 12, 0.75, 0.08, "armor_break", "山峦倾塌的末日图景"),
        new Attack("群岩奔袭", 0.8, 5, 0.92, 0.18, "confusion", "万千岩狼幻影的协同狩猎"),
        new Attack("地渊冲撞", 1.3, 10, 0.8, 0.1, "knockdown", "熔岩地脉在爪下迸发"),
        new Attack("熔心喷涌", 1.5, 15, 0.7, 0.25, "burn", "地心怒火凝成赤红狼首"),
        new Attack("玄铁爪痕", 1.1, 8, 0.9, 0.0, "armor_break", "黑曜石淬炼的致命爪光"),
        new Attack("血牙烙印", 1.2, 9, 0.87, 0.12, "bleed", "狼形岩牙嵌入血肉的撕裂"),
        new Attack("荒原战嚎", 1.3, 11, 0.8, 0.15, "fear", "唤醒先祖魂灵的震撼咆哮"),
        new Attack("裂地四重奏", 1.0, 7, 0.9, 0.1, "cripple", "四道爪痕同步切割大地"),
        new Attack("噬岩碎咬", 1.5, 14, 0.76, 0.2, "armor_break", "岩层在利齿间如饼干般粉碎"),
        new Attack("野性共鸣", 0.6, 4, 0.99, 0.25, "stun", "大地脉动与狼嚎的共振波"),

        new Attack("流沙葬", 0.8, 6, 0.93, 0.18, "entrap", "岩层液化吞噬猎物的死亡陷阱"),
        new Attack("月下岩影", 1.1, 7, 0.91, 0.0, "nightfall", "借月华施展的岩石分身突袭"),
        new Attack("晶簇爆破", 1.3, 10, 0.82, 0.22, "splinter", "地脉水晶的毁灭性绽放"),
        new Attack("花岗岩颚", 1.6, 45, 0.78, 0.15, "crush", "岩化狼首的毁灭咬合"),
        new Attack("地脉汲取", 0.9, 5, 0.96, 0.28, "fatigue", "抽取对手能量的古老仪式"),
        new Attack("岩甲反刺", 0.7, 4, 0.97, 0.3, "bleed", "体表岩鳞倒竖的反击"),
        new Attack("孤峰落", 1.4, 12, 0.77, 0.1, "knockdown", "从岩峰跃下的千钧坠击"),
        new Attack("琥珀禁锢", 0.5, 3, 0.99, 0.35, "petrify", "树脂状岩液包裹目标"),
        new Attack("地听突袭", 1.2, 9, 0.85, 0.0, "", "通过震动感知的精准扑杀"),
        new Attack("腐殖毒牙", 0.8, 6, 0.94, 0.25, "poison", "沼泽毒气凝成的狼牙"),
        new Attack("岩心脉动", 1.0, 8, 0.89, 0.18, "dissonance", "地核频率干扰神经"),
        new Attack("磁暴爪", 1.1, 7, 0.88, 0.2, "disrupt", "带电岩粒形成的磁场风暴"),
        new Attack("地衣蔓延", 0.6, 5, 0.95, 0.32, "entangle", "活化苔藓的束缚牢网"),
        new Attack("燧石火花", 0.9, 6, 0.92, 0.26, "ignite", "爪击摩擦迸射的星火"),
        new Attack("归尘", 1.7, 16, 0.7, 0.4, "decay", "将物质回归原始尘埃"),
        new Attack("岩窟幻影", 0.7, 5, 0.98, 0.3, "illusion", "钟乳石折射的迷惑幻象"),
        new Attack("地核重压", 1.5, 32, 0.75, 0.12, "gravity", "局部重力倍增的压迫"),
        new Attack("玄武岩甲", 0.0, 8, 0.9, 0.5, "brittle", "用防御性岩层使目标硬化变脆"),
        new Attack("狼群图腾", 1.2, 10, 0.83, 0.28, "pack_fear", "岩刻狼图腾的精神威压"),
        new Attack("结晶增殖", 1.0, 7, 0.86, 0.33, "crystallize", "在目标体内生长矿物结晶")
    ],
    'wind' => [
        new Attack("岚刃回旋", 3.1, 45, 0.92, 0.1, "cut", "真空刃在龙翼扇动间流转"),
        new Attack("蟠龙卷", 1.6, 10, 0.8, 0.15, "dizzy", "东方龙形飓风吞噬万物"),
        new Attack("流云掌", 0.8, 5, 0.95, 0.0, "", "云气凝成巨爪的连击"),
        new Attack("气爆环", 1.1, 8, 0.88, 0.12, "knockback", "环形气压场的多重爆发"),
        new Attack("雷狱天罚", 1.9, 12, 0.75, 0.2, "shock", "龙角引导的九天雷劫"),
        new Attack("真空断层", 1.8, 9, 0.85, 0.08, "vacuum", "撕裂空间的气压深渊"),
        new Attack("云霰弹", 1.0, 7, 0.9, 0.18, "wet", "高压水汽凝成的弹幕"),
        new Attack("舍身风", 3, 45, 0.7, 0.1, "sacrifice", "燃烧龙魂的终焉冲击"),
        new Attack("天罡怒", 3.2, 50, 0.73, 0.15, "rage", "引动九天罡风的龙怒"),
        new Attack("天罡怒", 45, 150, 0.73, 0.15, "rage", "引动九天罡风的龙怒"),
        new Attack("千刃岚", 2.2, 48, 0.89, 0.0, "", "千道风刃的华美之舞"),
        new Attack("碧霄龙吟", 2.4, 43, 0.8, 0.2, "fear", "引动云海翻腾的太古龙啸"),
        new Attack("游天尾", 1.1, 9, 0.9, 0.1, "evasion_up", "龙尾搅动气流的飘忽轨迹"),
        new Attack("风翎拂", 1.0, 6, 0.93, 0.0, "", "风羽轻抚般的优雅打击"),
        new Attack("坠天尾", 1.3, 25, 0.85, 0.1, "stun", "龙尾裹挟云层下击"),
        new Attack("天烬", 8, 80, 0.7, 0.5, "burn", "苍穹本身燃烧着坠落"),
        new Attack("天烬", 32, 180, 0.7, 0.5, "burn", "苍穹本身燃烧着坠落"),

        new Attack("云梭穿刺", 1.4, 11, 0.84, 0.15, "pierce", "凝云成梭的贯穿打击"),
        new Attack("蜃楼吐息", 0.9, 6, 0.94, 0.28, "mirage", "海市幻雾中的多重幻影"),
        new Attack("逆鳞风暴", 1.8, 20, 0.76, 0.18, "fury", "暴怒龙鳞掀起的毁灭飓风"),
        new Attack("风雷引", 2.6, 28, 0.81, 0.25, "electrocute", "龙须引导的风雷锁链"),
        new Attack("游云化形", 1.0, 8, 0.9, 0.0, "disorient", "云雾凝成游龙的迷惑"),
        new Attack("罡风护体", 0.0, 7, 0.95, 0.4, "pressure", "高密度气壁的挤压束缚"),
        new Attack("星屑吐纳", 1.2, 9, 0.87, 0.22, "cosmic", "蕴含星尘的龙息"),
        new Attack("风眼寂灭", 2.1, 20, 0.72, 0.3, "void", "在风暴中心创造真空湮灭"),
        new Attack("龙游三折", 1.3, 10, 0.86, 0.12, "dazzle", "三段变向的空中突袭"),
        new Attack("云篆符", 0.7, 5, 0.97, 0.35, "silence", "古篆风纹的禁言咒法"),
        new Attack("气脉截断", 0.8, 6, 0.93, 0.4, "choke", "封锁呼吸的气流操控"),
        new Attack("风伯之泪", 2.5, 25, 0.88, 0.26, "condense", "高湿度气流凝成的窒息水牢"),
        new Attack("九天垂帘", 1.7, 43, 0.79, 0.15, "deluge", "天幕倾泻的瀑布冲击"),
        new Attack("风骨刃", 1.4, 11, 0.83, 0.0, "", "压缩空气形成的透明骨刺"),
        new Attack("太虚游", 0.9, 7, 0.96, 0.3, "levitate", "将敌人抛入失控浮空"),
        new Attack("龙珠爆", 2.3, 30, 0.68, 0.45, "implode", "高密度风压球的坍缩"),
        new Attack("节气更迭", 1.6, 15, 0.74, 0.33, "seasons", "模拟四季变迁的气象攻击"),
        new Attack("风蚀刻", 0.6, 4, 0.98, 0.38, "erosion", "千年风化般的侵蚀"),
        new Attack("云纱缚", 0.5, 3, 0.99, 0.42, "weave", "柔云化刚的缠绕"),
        new Attack("虹吸", 1.0, 9, 0.85, 0.28, "siphon", "龙角形成的能量虹吸"),
        new Attack("天鼓震", 2.5, 24, 0.77, 0.2, "resonate", "云层碰撞的次声冲击")
    ],
];
?>