<?php
/**
 * Battle Page Template
 * @package custom
 */
if (!defined("__TYPECHO_ROOT_DIR__")) {
    exit();
}
?>

<?php $this->need("component/header.php"); ?>
<?php $this->need("component/aside.php"); ?>

<style>
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

@keyframes attackShake {
    0%, 100% { transform: translateX(0) scale(1.02); }
    20% { transform: translateX(-3px) scale(1.02); }
    40% { transform: translateX(3px) scale(1.02); }
    60% { transform: translateX(-2px) scale(1.02); }
    80% { transform: translateX(2px) scale(1.02); }
}

@keyframes floatUp {
    0% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
    30% {
        transform: translateY(-10px) scale(1.1);
    }
    100% {
        opacity: 0;
        transform: translateY(-40px) scale(0.8);
    }
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}

.critical-hit {
    animation: pulse 0.3s ease-in-out;
    box-shadow: 0 0 15px #ffaa00 !important;
}

#action-log {
    display: flex;
    flex-direction: column;
}

/* Add to the existing style section */
#action-log {
    scroll-behavior: smooth;
}

#action-log div {
    border-left: 2px solid transparent;
    transition: border-color 0.3s ease;
}

#action-log div:hover {
    border-left-color: #666;
    background: rgba(255,255,255,0.05);
}

/* Pulse animation for critical messages */
.critical-message {
    animation: messagePulse 0.6s ease-in-out;
}

@keyframes messagePulse {
    0% { background: rgba(255,100,100,0.3); }
    100% { background: transparent; }
}
</style>

<main class="app-content-body">
    <div class="hbox hbox-auto-xs hbox-auto-sm">
        <div class="col center-part gpu-speed" id="battle-panel">
            <div class="wrapper-md">
                <div class="blog-post">
                    <article class="single-post panel">
                        <div class="wrapper-lg">
                            <h2>⚔️ 小麦 vs 洛璞</h2>
                            
                            <!-- Battle info and break timer combined -->
                            <div id="battle-status" style="margin: 10px 0; padding: 15px; background: #1a1a1a; border-radius: 8px; color: white;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div id="battle-info" style="color: #00aaff; font-weight: bold;">第1场 回合1</div>
                                    <div id="score-info" style="color: #ffff00; font-weight: bold;">🐺0 - 0🐉</div>
                                </div>
                                
                                <div id="break-timer" style="color: #ffaa00; text-align: center; margin-top: 8px; display: none;"></div>
                            </div>
                            
                            <!-- Fighters Display -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 10px 0;">
                                <!-- Wolf Status -->
                                <div id="wolf-status" style="background: #2a1a1a; border: 1px solid #444; border-radius: 8px; padding: 15px;">
                                    <div style="color:rgb(243, 182, 125); font-weight: bold; margin-bottom: 8px;">🐺小麦</div>
                                    <div id="wolf-hp" style="color: #ff6666; font-size: 12px;">HP: 7200/7200</div>
                                    <div style="background: #333; border-radius: 4px; height: 8px; margin: 4px 0;">
                                        <div id="wolf-hp-bar" style="background: #ff4444; height: 100%; border-radius: 4px; width: 100%;"></div>
                                    </div>
                                    <div id="wolf-energy" style="color: #6666ff; font-size: 12px;">EN: 60/60</div>
                                    <div style="background: #333; border-radius: 4px; height: 6px; margin: 4px 0;">
                                        <div id="wolf-energy-bar" style="background: #4444ff; height: 100%; border-radius: 4px; width: 100%;"></div>
                                    </div>
                                    <div id="wolf-stats" style="color: #aaaaaa; font-size: 11px;">正常 | A250 D120 S350</div>
                                </div>
                                
                                <!-- Loong Status -->
                                <div id="loong-status" style="background: #1a1a2a; border: 1px solid #444; border-radius: 8px; padding: 15px;">
                                    <div style="color:rgb(114, 224, 197); font-weight: bold; margin-bottom: 8px;">洛璞🐉</div>
                                    <div id="loong-hp" style="color: #ff6666; font-size: 12px;">HP: 15000/15000</div>
                                    <div style="background: #333; border-radius: 4px; height: 8px; margin: 4px 0;">
                                        <div id="loong-hp-bar" style="background: #ff4444; height: 100%; border-radius: 4px; width: 100%;"></div>
                                    </div>
                                    <div id="loong-energy" style="color: #6666ff; font-size: 12px;">EN: 160/160</div>
                                    <div style="background: #333; border-radius: 4px; height: 6px; margin: 4px 0;">
                                        <div id="loong-energy-bar" style="background: #4444ff; height: 100%; border-radius: 4px; width: 100%;"></div>
                                    </div>
                                    <div id="loong-stats" style="color: #aaaaaa; font-size: 11px;">正常 | A480 D480 S220</div>
                                </div>
                            </div>
                            
                            <!-- Action Log -->
                            <div style="background: #0d0d0d; border: 1px solid #444; border-radius: 8px; padding: 15px; height: 250px; overflow-y: auto;">
                                <div style="color: #cccccc; font-size: 12px; font-family: monospace;" id="action-log">
                                    战斗即将开始...
                                </div>
                            </div>

                            <!-- Battle History Panel -->
                            <div style="margin: 10px 0; padding: 15px; background: #1a1a1a; border: 1px solid #444; border-radius: 8px;">
                                <!--<div style="color: #ffaa00; font-weight: bold; margin-bottom: 10px; text-align: center;">战斗历史 (最近几场)</div> -->
                                <div id="battle-history" style="display: flex; align-items: center; justify-content: center; min-height: 40px;">
                                    <div style="color: #666;">等待战斗结果...</div>
                                </div>
                            </div>

                            <!-- Cheers!! -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin: 10px 0;">
                                <!-- Wolf Cheer Button -->
                                <div style="text-align: center;">
                                    <button id="cheer-wolf" onclick="cheer('wolf')" style="
                                        background: linear-gradient(45deg, #2a1a1a, #4a3a2a);
                                        color: #e0b76a;
                                        border: 2px solid #e0b76a;
                                        border-radius: 8px;
                                        padding: 8px 16px;
                                        font-size: 14px;
                                        cursor: pointer;
                                        transition: all 0.3s ease;
                                    " onmouseover="this.style.background='linear-gradient(45deg, #4a3a2a, #6a5a4a)'" 
                                    onmouseout="this.style.background='linear-gradient(45deg, #2a1a1a, #4a3a2a)'">
                                        为小麦加油 🐺
                                    </button>
                                </div>
                                
                                <!-- Chaos Button -->
                                <div style="text-align: center;">
                                    <button id="chaos-btn" onclick="createChaos()" style="
                                        background: linear-gradient(45deg, #2a1a2a, #3a2a3a);
                                        color: #aa88ff;
                                        border: 2px solid #aa88ff;
                                        border-radius: 8px;
                                        padding: 8px 16px;
                                        font-size: 14px;
                                        cursor: pointer;
                                        transition: all 0.3s ease;
                                    " onmouseover="this.style.background='linear-gradient(45deg, #3a2a3a, #4a3a4a)'" 
                                    onmouseout="this.style.background='linear-gradient(45deg, #2a1a2a, #3a2a3a)'">
                                        制造混乱 🌀
                                    </button>
                                </div>
                                
                                <!-- Loong Cheer Button -->
                                <div style="text-align: center;">
                                    <button id="cheer-loong" onclick="cheer('loong')" style="
                                        background: linear-gradient(45deg, #1a1a2a, #2a3a4a);
                                        color: #82d8bb;
                                        border: 2px solid #82d8bb;
                                        border-radius: 8px;
                                        padding: 8px 16px;
                                        font-size: 14px;
                                        cursor: pointer;
                                        transition: all 0.3s ease;
                                    " onmouseover="this.style.background='linear-gradient(45deg, #2a3a4a, #4a5a6a)'" 
                                    onmouseout="this.style.background='linear-gradient(45deg, #1a1a2a, #2a3a4a)'">
                                        为洛璞加油 🐉
                                    </button>
                                </div>
                            </div>

                            <!-- Stats History Panel -->
                            <div style="margin: 10px 0; padding: 15px; background: #1a1a1a; border: 1px solid #444; border-radius: 8px;">
                                <div style="color: #ffaa00; font-weight: bold; margin-bottom: 15px; text-align: center;">属性变化历史</div>
                                
                                <!-- Wolf Stats -->
                                <div style="margin-bottom: 15px;">
                                    <div style="color: #e0b76a; font-weight: bold; margin-bottom: 8px;">🐺</div>
                                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;">
                                        <div>
                                            <div style="color: #ff6666; font-size: 11px; margin-bottom: 3px;">HP</div>
                                            <canvas id="wolf-hp-chart" width="80" height="35" style="width: 100%; height: 35px; background: #0a0a0a; border-radius: 6px;"></canvas>
                                        </div>
                                        <div>
                                            <div style="color: #ffaa66; font-size: 11px; margin-bottom: 3px;">ATK</div>
                                            <canvas id="wolf-attack-chart" width="80" height="35" style="width: 100%; height: 35px; background: #0a0a0a; border-radius: 6px;"></canvas>
                                        </div>
                                        <div>
                                            <div style="color: #66aaff; font-size: 11px; margin-bottom: 3px;">DEF</div>
                                            <canvas id="wolf-defense-chart" width="80" height="35" style="width: 100%; height: 35px; background: #0a0a0a; border-radius: 6px;"></canvas>
                                        </div>
                                        <div>
                                            <div style="color: #66ff66; font-size: 11px; margin-bottom: 3px;">SPD</div>
                                            <canvas id="wolf-speed-chart" width="80" height="35" style="width: 100%; height: 35px; background: #0a0a0a; border-radius: 6px;"></canvas>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Loong Stats -->
                                <div>
                                    <div style="color: #82d8bb; font-weight: bold; margin-bottom: 8px;">🐉</div>
                                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;">
                                        <div>
                                            <div style="color: #ff6666; font-size: 11px; margin-bottom: 3px;">HP</div>
                                            <canvas id="loong-hp-chart" width="80" height="35" style="width: 100%; height: 35px; background: #0a0a0a; border-radius: 6px;"></canvas>
                                        </div>
                                        <div>
                                            <div style="color: #ffaa66; font-size: 11px; margin-bottom: 3px;">ATK</div>
                                            <canvas id="loong-attack-chart" width="80" height="35" style="width: 100%; height: 35px; background: #0a0a0a; border-radius: 6px;"></canvas>
                                        </div>
                                        <div>
                                            <div style="color: #66aaff; font-size: 11px; margin-bottom: 3px;">DEF</div>
                                            <canvas id="loong-defense-chart" width="80" height="35" style="width: 100%; height: 35px; background: #0a0a0a; border-radius: 6px;"></canvas>
                                        </div>
                                        <div>
                                            <div style="color: #66ff66; font-size: 11px; margin-bottom: 3px;">SPD</div>
                                            <canvas id="loong-speed-chart" width="80" height="35" style="width: 100%; height: 35px; background: #0a0a0a; border-radius: 6px;"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <!-- Status Indicator -->
                            <div style="display: flex; justify-content: center; align-items: center; margin-top: 5px; padding: 8px;">
                                <div id="status-light" style="
                                    width: 8px; 
                                    height: 8px; 
                                    background: #7fca7f; 
                                    border-radius: 50%; 
                                    box-shadow: 0 0 8px #7fca7f;
                                    margin-right: 8px;
                                    transition: all 0.15s ease;
                                "></div>
                                <span style="color:rgb(248, 243, 238); font-size: 11px;">更新中</span>
                            </div>
                            
                        </div>
                    </article>
                </div>
            </div>
        </div>
        <?php $this->need("component/sidebar.php"); ?>
    </div>
</main>

<script>
// const eventSource = new EventSource('<?= Helper::options()->siteUrl ?>webtool/eternalcombat/battle_stream.php');
let lastWolfAction = '';
let lastLoongAction = '';
let lastActionCount = 0;
let wolfStatsHistory = { hp: [], attack: [], defense: [], speed: [] };
let loongStatsHistory = { hp: [], attack: [], defense: [], speed: [] };
let panelsFlipped = false;
let flipTimeout = null;

function updateStatsHistory(data) {
    if (!data.stats_history) return;
    
    // Use server data directly
    const wolfStats = data.stats_history.wolf;
    const loongStats = data.stats_history.loong;
    
    // Draw charts
    drawMiniChart('wolf-hp-chart', wolfStats.hp, '#ff6666');
    drawMiniChart('wolf-attack-chart', wolfStats.attack, '#ffaa66');
    drawMiniChart('wolf-defense-chart', wolfStats.defense, '#66aaff');
    drawMiniChart('wolf-speed-chart', wolfStats.speed, '#66ff66');
    
    drawMiniChart('loong-hp-chart', loongStats.hp, '#ff6666');
    drawMiniChart('loong-attack-chart', loongStats.attack, '#ffaa66');
    drawMiniChart('loong-defense-chart', loongStats.defense, '#66aaff');
    drawMiniChart('loong-speed-chart', loongStats.speed, '#66ff66');
}

function drawMiniChart(canvasId, data, color, maxPoints = 50) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    const width = canvas.width;
    const height = canvas.height;
    
    // Clear canvas
    ctx.clearRect(0, 0, width, height);
    
    if (data.length < 2) return;
    
    // Keep only last maxPoints
    const displayData = data.slice(-maxPoints);
    const min = Math.min(...displayData);
    const max = Math.max(...displayData);
    const range = max - min || 1;
    
    // Draw line
    ctx.strokeStyle = color;
    ctx.lineWidth = 1.5;
    ctx.beginPath();
    
    displayData.forEach((value, index) => {
        const x = (index / (displayData.length - 1)) * width;
        const y = height - ((value - min) / range) * height;
        
        if (index === 0) {
            ctx.moveTo(x, y);
        } else {
            ctx.lineTo(x, y);
        }
    });
    
    ctx.stroke();
    
    // Draw current value dot
    if (displayData.length > 0) {
        const lastValue = displayData[displayData.length - 1];
        const x = width;
        const y = height - ((lastValue - min) / range) * height;
        
        ctx.fillStyle = color;
        ctx.beginPath();
        ctx.arc(x, y, 2.8, 0, 2 * Math.PI);
        ctx.fill();
    }
}

function updateDisplay(data) {
    // battle info
    document.getElementById('battle-info').textContent = `第${data.battle_count}场 回合${data.round_num}`;
    document.getElementById('score-info').textContent = `🐺${data.wolf_wins} - ${data.loong_wins}🐉`;

    // preserve flip state after updates
    if (panelsFlipped) {
        const wolfPanel = document.getElementById('wolf-status');
        const loongPanel = document.getElementById('loong-status');
        wolfPanel.style.transform = 'rotate(180deg)';
        loongPanel.style.transform = 'rotate(-180deg)';
    }

    // break timer
    const breakTimer = document.getElementById('break-timer');
    if (data.break_remaining > 0) {
        const mins = Math.floor(data.break_remaining / 60);
        const secs = data.break_remaining % 60;
        breakTimer.textContent = `⏰ 休战 ${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        breakTimer.style.display = 'block';
    } else {
        breakTimer.style.display = 'none';
    }
    
    // wolf status with death check
    const wolfStatusDiv = document.getElementById('wolf-status');
    if (!data.wolf.alive) {
        wolfStatusDiv.style.background = '#4a1a1a';
        wolfStatusDiv.style.opacity = '0.6';
    } else {
        wolfStatusDiv.style.background = '#2a1a1a';
        wolfStatusDiv.style.opacity = '1';
    }

    // wolf status
    document.getElementById('wolf-hp').textContent = `HP: ${data.wolf.hp}/${data.wolf.max_hp}`;
    document.getElementById('wolf-energy').textContent = `EN: ${data.wolf.energy}/${data.wolf.max_energy}`;
    
    const wolfHpPercent = (data.wolf.hp / data.wolf.max_hp) * 100;
    const wolfEnergyPercent = (data.wolf.energy / data.wolf.max_energy) * 100;
    document.getElementById('wolf-hp-bar').style.width = wolfHpPercent + '%';
    document.getElementById('wolf-energy-bar').style.width = wolfEnergyPercent + '%';
    
    const wolfStatusText = data.wolf.status_effects.length > 0 ?
        data.wolf.status_effects.map(s => `${s.type}(${s.duration})`).slice(0, 2).join(', ') :
        '正常';
    document.getElementById('wolf-stats').textContent = 
        `${wolfStatusText} | A${data.wolf.attack} D${data.wolf.defense} S${data.wolf.speed}`;
   
    // loong status with death check
    const loongStatusDiv = document.getElementById('loong-status');
    if (!data.loong.alive) {
        loongStatusDiv.style.background = '#1a1a4a';
        loongStatusDiv.style.opacity = '0.6';
    } else {
        loongStatusDiv.style.background = '#1a1a2a';
        loongStatusDiv.style.opacity = '1';
    }    
    // loong status
    document.getElementById('loong-hp').textContent = `HP: ${data.loong.hp}/${data.loong.max_hp}`;
    document.getElementById('loong-energy').textContent = `EN: ${data.loong.energy}/${data.loong.max_energy}`;
    
    const loongHpPercent = (data.loong.hp / data.loong.max_hp) * 100;
    const loongEnergyPercent = (data.loong.energy / data.loong.max_energy) * 100;
    document.getElementById('loong-hp-bar').style.width = loongHpPercent + '%';
    document.getElementById('loong-energy-bar').style.width = loongEnergyPercent + '%';
    
    const loongStatusText = data.loong.status_effects.length > 0 ?
        data.loong.status_effects.map(s => `${s.type}(${s.duration})`).slice(0, 2).join(', ') :
        '正常';
    document.getElementById('loong-stats').textContent = 
        `${loongStatusText} | A${data.loong.attack} D${data.loong.defense} S${data.loong.speed}`;
    
    // action log
    const actionLog = document.getElementById('action-log');
    if (data.action_log && data.action_log.length > 0) {
        // actionLog.innerHTML = data.action_log.map(msg => 
        //     msg.length > 45 ? msg.substring(0, 42) + '...' : msg
        // ).join('<br>');
        actionLog.innerHTML = data.action_log.map(msg => msg).join('<br>');
        actionLog.scrollTop = actionLog.scrollHeight;
    }

    // parse damage from action
    const currentAction = data.last_action || '';
    const damageMatch = currentAction.match(/-(\d+)/);
    const damage = damageMatch ? parseInt(damageMatch[1]) : 0;
    
    // wolf attack animation
    if (currentAction.includes('小麦') && damage > 0 && currentAction !== lastWolfAction) {
        animateAttack('wolf-status');
        animateDamage('loong-status', damage);
        lastWolfAction = currentAction;
    }
    
    // loong attack animation  
    if (currentAction.includes('洛璞') && damage > 0 && currentAction !== lastLoongAction) {
        animateAttack('loong-status');
        animateDamage('wolf-status', damage);
        lastLoongAction = currentAction;
    }

    if (data.battle_history) {
        updateBattleHistory(data.battle_history)
    }

    updateStatsHistory(data)
}

function updateBattleHistory(history) {
    const historyDiv = document.getElementById('battle-history');
    
    if (!history || history.length === 0) {
        historyDiv.innerHTML = '<div style="color: #666;">等待战斗结果...</div>';
        return;
    }
    
    // Create CSGO-style history display
    const historyHtml = `
        <div style="display: flex; flex-direction: column; align-items: center; width: 100%;">
            <!-- Wolf side -->
            <div style="display: flex; align-items: center; margin-bottom: 5px;">
                <span style="color:#e0b76a; margin-right: 10px;">🐺</span>
                <div style="display: flex; gap: 2px;">
                    ${history.map((battle, index) => `
                        <div style="
                            width: 20px; 
                            height: 8px; 
                            background: ${battle.winner === 'wolf' ? '#e0b76a' : 
                                         battle.winner === 'tie' ? '#ffaa00' : '#333'};
                            border-radius: 2px;
                            opacity: ${battle.winner === 'wolf' ? '1' : '0.3'};
                            position: relative;
                            ${index === history.length - 1 ? 'box-shadow: 0 0 4px #fff;' : ''}
                        " title="第${battle.battle}场 ${battle.round_count}回合"></div>
                    `).join('')}
                </div>
            </div>
            
            <!-- Center line -->
            <div style="width: ${Math.min(history.length * 22, 440)}px; height: 1px; background: #444; margin: 2px 0;"></div>
            
            <!-- Loong side -->
            <div style="display: flex; align-items: center; margin-top: 5px;">
                <span style="color:#82d8bb; margin-right: 10px;">🐉</span>
                <div style="display: flex; gap: 2px;">
                    ${history.map((battle, index) => `
                        <div style="
                            width: 20px; 
                            height: 8px; 
                            background: ${battle.winner === 'loong' ? '#82d8bb' : 
                                         battle.winner === 'tie' ? '#ffaa00' : '#333'};
                            border-radius: 2px;
                            opacity: ${battle.winner === 'loong' ? '1' : '0.3'};
                            position: relative;
                            ${index === history.length - 1 ? 'box-shadow: 0 0 4px #fff;' : ''}
                        " title="第${battle.battle}场 ${battle.round_count}回合"></div>
                    `).join('')}
                </div>
            </div>
        </div>
    `;
    
    historyDiv.innerHTML = historyHtml;
}

function updateBattleHistory(history) {
    const historyDiv = document.getElementById('battle-history');
    
    if (!historyDiv) {
        console.log('Battle history element not found');
        return;
    }
    
    if (!history || history.length === 0) {
        historyDiv.innerHTML = '<div style="color: #666;">等待战斗结果...</div>';
        return;
    }
    
    // Calculate responsive size
    const containerWidth = historyDiv.offsetWidth || 300;
    const maxItems = Math.floor((containerWidth - 100) / 22); // Reserve space for names
    const displayHistory = history.slice(-maxItems);
    const itemWidth = Math.min(20, Math.floor((containerWidth - 100) / displayHistory.length - 2));
    
    const historyHtml = `
        <div style="display: flex; flex-direction: column; align-items: center; width: 100%; overflow: hidden;">
            <!-- Wolf side -->
            <div style="display: flex; align-items: center; margin-bottom: 5px; width: 100%;">
                <span style="color:#d1a265; margin-right: 10px; font-size: 12px; min-width: 60px;">🐺</span>
                <div style="display: flex; gap: 2px; flex: 1; justify-content: flex-start; overflow: hidden;">
                    ${displayHistory.map((battle, index) => `
                        <div style="
                            width: ${itemWidth}px; 
                            min-width: 12px;
                            height: 8px; 
                            background: ${battle.winner === 'wolf' ? '#d1a265' : 
                                         battle.winner === 'tie' ? '#ffaa00' : '#333'};
                            border-radius: 2px;
                            opacity: ${battle.winner === 'wolf' ? '1' : '0.3'};
                            ${index === displayHistory.length - 1 ? 'box-shadow: 0 0 4px #fff;' : ''}
                        " title="第${battle.battle}场"></div>
                    `).join('')}
                </div>
            </div>
            
            <!-- Center line -->
            <div style="width: 100%; max-width: ${displayHistory.length * (itemWidth + 2)}px; height: 1px; background: #444; margin: 2px 0;"></div>
            
            <!-- Loong side -->
            <div style="display: flex; align-items: center; margin-top: 5px; width: 100%;">
                <span style="color:#86c7ae; margin-right: 10px; font-size: 12px; min-width: 60px;">🐉</span>
                <div style="display: flex; gap: 2px; flex: 1; justify-content: flex-start; overflow: hidden;">
                    ${displayHistory.map((battle, index) => `
                        <div style="
                            width: ${itemWidth}px; 
                            min-width: 12px;
                            height: 8px; 
                            background: ${battle.winner === 'loong' ? '#86c7ae' : 
                                         battle.winner === 'tie' ? '#ffaa00' : '#333'};
                            border-radius: 2px;
                            opacity: ${battle.winner === 'loong' ? '1' : '0.3'};
                            ${index === displayHistory.length - 1 ? 'box-shadow: 0 0 4px #fff;' : ''}
                        " title="第${battle.battle}场"></div>
                    `).join('')}
                </div>
            </div>
        </div>
    `;
    
    historyDiv.innerHTML = historyHtml;
}

function animateAttack(elementId) {
   const element = document.getElementById(elementId);
   
   // Attacker shake - more pronounced
   element.style.animation = 'attackShake 0.4s ease-in-out';
   element.style.transform = 'scale(1.02)';
   
   setTimeout(() => {
       element.style.animation = '';
       element.style.transform = 'scale(1)';
   }, 400);
}

function animateDamage(elementId, damage) {
   const element = document.getElementById(elementId);
   
   // Defender flash and scale - impact effect
   element.style.transition = 'all 0.15s ease';
   element.style.transform = 'scale(0.98)';
   element.style.boxShadow = '0 0 15px #ff4444';
   element.style.filter = 'brightness(1.3)';
   
   // Floating damage number
   const damageFloat = document.createElement('div');
   damageFloat.textContent = `-${damage}`;
   damageFloat.style.cssText = `
       position: absolute;
       top: 10px;
       right: 10px;
       color: #ff6666;
       font-weight: bold;
       font-size: 16px;
       z-index: 1000;
       pointer-events: none;
       text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
       animation: floatUp 1.2s ease-out forwards;
   `;
   
   element.style.position = 'relative';
   element.appendChild(damageFloat);
   
   setTimeout(() => {
       element.style.transform = 'scale(1)';
       element.style.boxShadow = 'none';
       element.style.filter = 'brightness(1)';
   }, 200);
   
   setTimeout(() => {
       if (damageFloat.parentNode) {
           damageFloat.parentNode.removeChild(damageFloat);
       }
   }, 1200);
}

// Initial display
updateDisplay({
   battle_count: 1, round_num: 1, wolf_wins: 0, loong_wins: 0,
   wolf: {hp: 7200, max_hp: 7200, energy: 60, max_energy: 60, attack: 250, defense: 120, speed: 350, status_effects: []},
   loong: {hp: 15000, max_hp: 15000, energy: 160, max_energy: 160, attack: 480, defense: 480, speed: 220, status_effects: []},
   action_log: ['战斗即将开始...']
});
function updateBattleData() {
    fetch('<?= Helper::options()->siteUrl ?>webtool/eternalcombat/json/battle_state.json')
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            updateDisplay(data);
            flickerStatusLight();
        })
        .catch(error => {
            console.log('Fetch error:', error);
            // Keep trying even on errors
        });
}

function cheer(fighter) {
    const button = document.getElementById(`cheer-${fighter}`);
    const originalText = button.textContent;
    
    // Disable button temporarily
    button.disabled = true;
    button.style.opacity = '0.7';
    
    fetch('<?= Helper::options()->siteUrl ?>webtool/eternalcombat/interaction/cheer.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `fighter=${fighter}`
    })
    .then(response => response.text())
    .then(result => {
        if (result.includes('success')) {
            button.textContent = '加油成功! ✨';
            button.style.background = fighter === 'wolf' ? 
                'linear-gradient(45deg, #4a4a2a, #6a6a4a)' : 
                'linear-gradient(45deg, #2a4a4a, #4a6a6a)';
        } else if (result.includes('limit')) {
            button.textContent = '❤️';
            button.style.background = '#444';
        } else if (result.includes('locked')) {
            button.textContent = '墙头草打咩';
            button.style.background = '#444';
            button.style.cursor = 'not-allowed';
            // Don't reset this button
            return;
        } else {
            button.textContent = '稍后再试';
        }
        
        setTimeout(() => {
            button.textContent = originalText;
            button.style.background = fighter === 'wolf' ? 
                'linear-gradient(45deg, #2a1a1a, #4a3a2a)' : 
                'linear-gradient(45deg, #1a1a2a, #2a3a4a)';
            button.disabled = false;
            button.style.opacity = '1';
        }, 850);
    })
    .catch(error => {
        button.textContent = '网络错误';
        setTimeout(() => {
            button.textContent = originalText;
            button.disabled = false;
            button.style.opacity = '1';
        }, 850);
    });
}

function createChaos() {
    const button = document.getElementById('chaos-btn');
    const originalText = button.textContent;
    
    // Disable button
    button.disabled = true;
    button.style.opacity = '0.8';
    
    // Loading animation
    let dots = '';
    const loadingInterval = setInterval(() => {
        dots = dots.length >= 3 ? '' : dots + '.';
        button.textContent = `混乱生成中${dots}`;
    }, 300);
    
    // Loading effect with color pulse
    button.style.background = 'linear-gradient(45deg, #3a2a3a, #5a4a5a)';
    button.style.animation = 'pulse 0.8s ease-in-out infinite';
    
    // Make request to chaos.php
    fetch('<?= Helper::options()->siteUrl ?>webtool/eternalcombat/interaction/chaos.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=chaos'
    })
    .then(response => response.text())
    .then(result => {
        // After 2 seconds, show result
        setTimeout(() => {
            clearInterval(loadingInterval);
            button.style.animation = '';
            let reset_interval = 1500;
            
            if (result.includes('success')) {
                let message = '混乱已生成🌀'; 
                reset_interval = 5000;
                
                if (result.includes('success:')) {
                    const customMessage = result.replace('success:', '');
                    if (customMessage.trim()) { // Check if custom message exists and isn't empty
                        message = customMessage;
                    }
                }

                if (result.includes('🙃')) {
                    flipFighterPanels()
                }
                
                button.textContent = message;
                button.style.background = 'linear-gradient(45deg, #5a4a5a, #6a5a6a)';
            } else if (result.includes('limit')) {
                button.textContent = '混乱已满 💥';
                button.style.background = 'linear-gradient(45deg, #444, #555)';
            } else {
                button.textContent = '无事发生 🤷';
                button.style.background = 'linear-gradient(45deg, #444, #555)';
            }
            
            // Reset after another seconds
            setTimeout(() => {
                button.textContent = originalText;
                button.style.background = 'linear-gradient(45deg, #2a1a2a, #3a2a3a)';
                button.disabled = false;
                button.style.opacity = '1';
            }, reset_interval);
        }, 2000);
    })
    .catch(error => {
        // Handle network error after 2 seconds
        setTimeout(() => {
            clearInterval(loadingInterval);
            button.style.animation = '';
            button.textContent = '网络错误 ❌';
            button.style.background = 'linear-gradient(45deg, #444, #555)';
            
            setTimeout(() => {
                button.textContent = originalText;
                button.style.background = 'linear-gradient(45deg, #2a1a2a, #3a2a3a)';
                button.disabled = false;
                button.style.opacity = '1';
            }, 2000);
        }, 2000);
    });
}

function flipFighterPanels() {
    const wolfPanel = document.getElementById('wolf-status');
    const loongPanel = document.getElementById('loong-status');

    panelsFlipped = true;
    wolfPanel.style.transform = 'rotate(180deg)';
    loongPanel.style.transform = 'rotate(-180deg)';
    wolfPanel.style.transition = 'transform 0.5s ease-in-out';
    loongPanel.style.transition = 'transform 0.5s ease-in-out';

    if (flipTimeout) clearTimeout(flipTimeout);
    flipTimeout = setTimeout(() => {
        wolfPanel.style.transform = 'rotate(0deg)';
        loongPanel.style.transform = 'rotate(0deg)';
        panelsFlipped = false;
    }, 5000);
}

function flickerStatusLight() {
    const light = document.getElementById('status-light');
    if (!light) return;
    
    // bright flash
    light.style.background = '#47ffa9';
    light.style.boxShadow = '0 0 15px #47ffa9';
    light.style.transform = 'scale(1.2)';
    
    setTimeout(() => {
        light.style.background = '#7fca7f';
        light.style.boxShadow = '0 0 8px #7fca7f';
        light.style.transform = 'scale(1)';
    }, 150);
}

// Update every 1 second
setInterval(updateBattleData, 1000);

// Initial load
updateBattleData();
</script>

<?php $this->need("component/footer.php"); ?>