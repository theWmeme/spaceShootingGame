<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = intval($_SESSION['user_id']);
$username = '';

$userQuery = "SELECT name FROM users WHERE id = $user_id LIMIT 1";
$userResult = $conn->query($userQuery);
if ($userResult && $userResult->num_rows > 0) {
    $userRow = $userResult->fetch_assoc();
    $username = $userRow['name'];
} else {
    header('Location: login.php');
    exit;
}

$createLeaderboardTable = "CREATE TABLE IF NOT EXISTS highscores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    username VARCHAR(100) NOT NULL,
    score INT NOT NULL DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY user_score_unique (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$conn->query($createLeaderboardTable);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    $action = $_POST['action'];

    if ($action === 'fetch_scores') {
        $search = isset($_POST['search']) ? $conn->real_escape_string(trim($_POST['search'])) : '';
        $where = '';
        if ($search !== '') {
            $where = "WHERE username LIKE '%" . $search . "%'";
        }

        $sql = "SELECT username, score FROM highscores $where ORDER BY score DESC, updated_at DESC LIMIT 10";
        $result = $conn->query($sql);
        $scores = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $scores[] = [
                    'name' => $row['username'],
                    'score' => intval($row['score'])
                ];
            }
        }

        echo json_encode(['success' => true, 'scores' => $scores]);
        exit;
    }

    if ($action === 'submit_score') {
        $score = isset($_POST['score']) ? intval($_POST['score']) : 0;
        $safeName = $conn->real_escape_string($username);

        $sql = "INSERT INTO highscores (user_id, username, score) VALUES ($user_id, '$safeName', $score) 
            ON DUPLICATE KEY UPDATE score = GREATEST(score, VALUES(score)), username = VALUES(username), updated_at = CURRENT_TIMESTAMP";
        $conn->query($sql);

        $result = $conn->query("SELECT username, score FROM highscores ORDER BY score DESC, updated_at DESC LIMIT 10");
        $scores = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $scores[] = [
                    'name' => $row['username'],
                    'score' => intval($row['score'])
                ];
            }
        }

        echo json_encode(['success' => true, 'scores' => $scores]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>SP-OOTER KITTY</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body.game-page {
      min-height: 100vh;
      margin: 0;
      background-image: url('images/login-background.png');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      color: #fff;
      font-family: Inter, system-ui, -apple-system, 'Segoe UI', Roboto, Arial, sans-serif;
    }

    .page-shell {
      min-height: 100vh;
      width: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 24px;
      gap: 18px;
    }

    .top-bar {
      width: min(1080px, 100%);
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .title-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 12px;
    }

    .title-row h1 {
      margin: 0;
      font-size: clamp(2rem, 3vw, 3rem);
      letter-spacing: 0.2em;
      text-transform: uppercase;
      text-shadow: 
        -1px -1px 0 #cb7600,
        1px -1px 0 #cb7600,
        -1px 1px 0 #cb7600,
        1px 1px 0 #cb7600,
        -2px 0 0 #cb7600,
        2px 0 0 #cb7600,
        0 -2px 0 #cb7600,
        0 2px 0 #cb7600,
        -2px -2px 0 #cb7600,
        2px -2px 0 #cb7600,
        -2px 2px 0 #cb7600,
        2px 2px 0 #cb7600,
        -3px 0 0 #cb7600,
        3px 0 0 #cb7600,
        0 -3px 0 #cb7600,
        0 3px 0 #cb7600;
    }

    .user-panel {
      display: flex;
      align-items: center;
      gap: 14px;
      flex-wrap: wrap;
    }

    .user-label {
      color: #7ecbff;
      font-weight: 700;
      text-shadow: 
        -1px -1px 0 #000,
        1px -1px 0 #000,
        -1px 1px 0 #000,
        1px 1px 0 #000,
        -2px 0 0 #000,
        2px 0 0 #000,
        0 -2px 0 #000,
        0 2px 0 #000,
        -2px -2px 0 #000,
        2px -2px 0 #000,
        -2px 2px 0 #000,
        2px 2px 0 #000,
        -3px 0 0 #000,
        3px 0 0 #000,
        0 -3px 0 #000,
        0 3px 0 #000;
    }

    .logout-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: #1e90ff;
      border: none;
      color: white;
      font-weight: 700;
      padding: 10px 16px;
      border-radius: 14px;
      cursor: pointer;
      transition: transform 0.2s ease, background 0.2s ease;
      text-decoration: none;
    }

    .logout-btn:hover {
      background: #1477d9;
      transform: translateY(-1px);
    }

    .subhead {
      margin: 0;
      color: white;
      max-width: 1080px;
      font-size: 0.95rem;
      opacity: 0.9;
      text-shadow: 
        -1px -1px 0 #000,
        1px -1px 0 #000,
        -1px 1px 0 #000,
        1px 1px 0 #000,
        -2px 0 0 #000,
        2px 0 0 #000,
        0 -2px 0 #000,
        0 2px 0 #000,
        -2px -2px 0 #000,
        2px -2px 0 #000,
        -2px 2px 0 #000,
        2px 2px 0 #000,
        -3px 0 0 #000,
        3px 0 0 #000,
        0 -3px 0 #000,
        0 3px 0 #000;
    }

    #game-container {
      position: relative;
      width: min(100%, 900px);
      max-width: 1080px;
      height: 650px;
      border-radius: 26px;
      overflow: hidden;
      box-shadow: 0 30px 90px rgba(0, 0, 0, 0.45);
      background: rgba(1, 7, 25, 0.9);
      border: 1px solid rgba(255, 255, 255, 0.08);
    }

    #gameCanvas {
      width: 100%;
      height: 100%;
      display: block;
      background: #02030c;
    }

    .overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 24px;
      gap: 16px;
      background: linear-gradient(180deg, rgba(0, 4, 15, 0.72), rgba(0, 0, 0, 0.78));
    }

    .menu-nav-container {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      width: 100%;
      max-width: 860px;
      justify-content: center;
    }

    .nav-btn {
      flex: 1 1 160px;
      min-width: 150px;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.12);
      color: #c9d2e6;
      padding: 12px 18px;
      border-radius: 18px;
      font-size: 0.95rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .nav-btn.active,
    .nav-btn:hover {
      background: #1e90ff;
      color: white;
      border-color: #1e90ff;
      box-shadow: 0 12px 30px rgba(30, 144, 255, 0.25);
    }

    .menu-tab-content {
      width: 100%;
      max-width: 860px;
      min-height: 220px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 18px;
      background: rgba(3, 18, 38, 0.85);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 24px;
      padding: 24px;
      box-sizing: border-box;
    }

    .action-btn {
      min-width: 220px;
      padding: 16px 32px;
      background: #1e90ff;
      color: white;
      border: none;
      border-radius: 999px;
      font-size: 1rem;
      font-weight: 800;
      cursor: pointer;
      transition: transform 0.2s ease, background 0.2s ease;
      box-shadow: 0 16px 30px rgba(0, 105, 255, 0.18);
    }

    .action-btn:hover {
      transform: translateY(-1px);
      background: #1677d8;
    }

    .search-bar {
      width: 100%;
      max-width: 560px;
      padding: 14px 18px;
      border-radius: 16px;
      border: 1px solid rgba(255, 255, 255, 0.12);
      background: rgba(255, 255, 255, 0.06);
      color: white;
      font-size: 0.95rem;
    }

    .leaderboard-box {
      width: 100%;
    }

    .leaderboard-title {
      font-weight: 700;
      color: #1e90ff;
      border-bottom: 1px solid rgba(255, 255, 255, 0.12);
      padding-bottom: 10px;
      margin-bottom: 10px;
      display: flex;
      justify-content: space-between;
      gap: 10px;
      font-size: 0.98rem;
    }

    .leaderboard-rows {
      width: 100%;
      max-height: 260px;
      overflow-y: auto;
      display: grid;
      gap: 8px;
    }

    .score-row {
      display: flex;
      justify-content: space-between;
      gap: 16px;
      align-items: center;
      padding: 12px 14px;
      border-radius: 16px;
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.08);
      font-size: 0.95rem;
    }

    .score-row.highlight {
      background: rgba(30, 144, 255, 0.18);
      border-color: rgba(30, 144, 255, 0.28);
      color: #d0f0ff;
      font-weight: 700;
    }

    .user-tag {
      font-size: 0.95rem;
      color: #7ecbff;
      font-weight: 700;
    }

    .color-picker-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 14px;
      justify-content: center;
      width: 100%;
    }

    .color-swatch {
      width: 48px;
      height: 48px;
      border-radius: 999px;
      border: 3px solid rgba(255, 255, 255, 0.12);
      cursor: pointer;
      transition: transform 0.18s ease, border-color 0.18s ease;
    }

    .color-swatch.selected,
    .color-swatch:hover {
      transform: scale(1.08);
      border-color: #1e90ff;
    }

    .flex-row {
      width: 100%;
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      gap: 12px;
      align-items: center;
    }

    .hidden { display: none !important; }

    @media (max-width: 900px) {
      #game-container { height: 640px; }
      .menu-tab-content { padding: 18px; }
    }
  </style>
</head>
<body class="game-page">
  <div class="page-shell">
    <div class="top-bar">
      <div class="title-row">
        <h1>SP-OOTER KITTY</h1>
        <div class="user-panel">
          <span class="user-label">Active Pilot:</span>
          <strong><?= htmlspecialchars($username) ?></strong>
          <a href="logout.php" class="logout-btn">Logout</a>
        </div>
      </div>
      <p class="subhead">Game login is handled by the separate login flow. This page now focuses on the shoot‑em‑up experience and MySQL leaderboard.</p>
    </div>

    <div id="game-container">
      <canvas id="gameCanvas" width="800" height="600"></canvas>

      <div id="main-menu" class="overlay">
        <h2>Commander Bridge</h2>
        <div class="flex-row">
          <div class="user-tag">Pilot: <?= htmlspecialchars($username) ?></div>
          <div class="user-tag">Best session score appears after your run.</div>
        </div>

        <div class="menu-nav-container">
          <button id="nav-play" class="nav-btn active">1. Play Game</button>
          <button id="nav-leaderboard" class="nav-btn">2. Leaderboard</button>
          <button id="nav-customize" class="nav-btn">3. Customize</button>
        </div>

        <div id="tab-play" class="menu-tab-content">
          <p>Warning: Deep space sectors are highly hostile. Enemy cats will counterattack with plasma yarn balls.</p>
          <p style="color:#c9d2e6; font-size:0.92rem; margin:0;">Controls: Left/Right Arrow or A/D to move. Spacebar to shoot.</p>
          <button id="start-btn" class="action-btn">Launch Rocket</button>
        </div>

        <div id="tab-leaderboard" class="menu-tab-content hidden">
          <div class="leaderboard-box">
            <input type="text" id="menu-search" class="search-bar" placeholder="🔍 Search pilot username...">
            <div class="leaderboard-title"><span>Global Top 10</span><span>Highscore</span></div>
            <div id="menu-leaderboard-content" class="leaderboard-rows">Loading leaderboard...</div>
          </div>
        </div>

        <div id="tab-customize" class="menu-tab-content hidden">
          <p>Choose a beam color for your rocket ammunition.</p>
          <div class="color-picker-grid">
            <div class="color-swatch selected" data-color="#ff3366" style="background:#ff3366;"></div>
            <div class="color-swatch" data-color="#00f2fe" style="background:#00f2fe;"></div>
            <div class="color-swatch" data-color="#e1701a" style="background:#e1701a;"></div>
            <div class="color-swatch" data-color="#bf5cf2" style="background:#bf5cf2;"></div>
            <div class="color-swatch" data-color="#5cf27e" style="background:#5cf27e;"></div>
          </div>
          <p id="custom-tint-label" style="margin-top: 18px; font-weight:700; color:#7ecbff;">Weapon Output: Toe-Beam Pink (Standard)</p>
        </div>
      </div>

      <div id="game-over-screen" class="overlay hidden">
        <h2>Game Over</h2>
        <p id="final-score-text" style="font-size:1.05rem; margin:0;">Your Final Score: 0</p>
        <button id="restart-btn" class="action-btn" style="margin-bottom:18px;">Play Again</button>
        <div class="menu-tab-content" style="width: 100%; min-height: auto;">
          <div class="leaderboard-box">
            <div class="leaderboard-title"><span>Global Top 10</span><span>Score</span></div>
            <div id="gameover-leaderboard-content" class="leaderboard-rows">Loading leaderboard...</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    const currentUser = <?= json_encode($username, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP) ?>;
    const canvas = document.getElementById('gameCanvas');
    const ctx = canvas.getContext('2d');

    const mainMenu = document.getElementById('main-menu');
    const welcomeTag = document.querySelector('.user-tag');
    const gameOverScreen = document.getElementById('game-over-screen');
    const startBtn = document.getElementById('start-btn');
    const restartBtn = document.getElementById('restart-btn');
    const finalScoreText = document.getElementById('final-score-text');

    const menuLeaderboard = document.getElementById('menu-leaderboard-content');
    const gameoverLeaderboard = document.getElementById('gameover-leaderboard-content');
    const menuSearchInput = document.getElementById('menu-search');

    const navPlay = document.getElementById('nav-play');
    const navLeaderboard = document.getElementById('nav-leaderboard');
    const navCustomize = document.getElementById('nav-customize');

    const tabPlay = document.getElementById('tab-play');
    const tabLeaderboard = document.getElementById('tab-leaderboard');
    const tabCustomize = document.getElementById('tab-customize');

    let cachedGlobalScores = [];
    let selectedLaserColor = '#ff3366';
    let gameState = 'MENU';
    let score = 0;
    let lives = 3;
    let player;
    let lasers = [];
    let enemyBombs = [];
    let enemies = [];
    let keys = {};
    let enemyDirection = 1;
    let enemySpeed = 1.5;
    let enemyWave = 1;

    const bgImg = new Image(); bgImg.src = 'images/background-alt.png';
    const homeBgImg = new Image(); homeBgImg.src = 'images/background-alt.png';
    const rocketImg = new Image(); rocketImg.src = 'images/kitty-rocket-frame1.png';
    const catSkin1 = new Image(); catSkin1.src = 'images/kitty-alt-skin1.png';
    const catSkin2 = new Image(); catSkin2.src = 'images/kitty-alt-skin2.png';
    const catSkin3 = new Image(); catSkin3.src = 'images/kitty-alt-skin3.png';

    homeBgImg.onload = () => {
      document.getElementById('main-menu').style.backgroundImage = `url('${homeBgImg.src}')`;
      document.getElementById('main-menu').style.backgroundSize = 'cover';
      document.getElementById('main-menu').style.backgroundPosition = 'center';
    };

    window.addEventListener('keydown', e => keys[e.code] = true);
    window.addEventListener('keyup', e => keys[e.code] = false);

    function switchMenuTab(activeNavButton, targetTabElement) {
      [navPlay, navLeaderboard, navCustomize].forEach(btn => btn.classList.remove('active'));
      [tabPlay, tabLeaderboard, tabCustomize].forEach(tab => tab.classList.add('hidden'));
      activeNavButton.classList.add('active');
      targetTabElement.classList.remove('hidden');
    }

    navPlay.addEventListener('click', () => switchMenuTab(navPlay, tabPlay));
    navLeaderboard.addEventListener('click', () => {
      switchMenuTab(navLeaderboard, tabLeaderboard);
      fetchAndRenderLeaderboard(menuLeaderboard);
    });
    navCustomize.addEventListener('click', () => switchMenuTab(navCustomize, tabCustomize));

    document.querySelectorAll('.color-swatch').forEach(swatch => {
      swatch.addEventListener('click', (e) => {
        document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('selected'));
        e.target.classList.add('selected');
        selectedLaserColor = e.target.getAttribute('data-color');
        const labels = {
          '#ff3366': 'Toe-Beam Pink (Standard)',
          '#00f2fe': 'Zoomie Hyper-Cyan',
          '#e1701a': 'Hairball Hazard Orange',
          '#bf5cf2': 'Catnip Overdose Purple',
          '#5cf27e': 'Sour Kibble Radioactive Green'
        };
        document.getElementById('custom-tint-label').innerText = `Weapon Output: ${labels[selectedLaserColor]}`;
      });
    });

    function initPlayer() {
      player = {
        x: canvas.width / 2 - 30,
        y: canvas.height - 90,
        width: 60,
        height: 75,
        hitboxOffsetX: 12,
        hitboxOffsetY: 10,
        hitboxWidth: 36,
        hitboxHeight: 55,
        speed: 6.5,
        cooldown: 0
      };
    }

    function spawnEnemies() {
      enemies = [];
      const rows = 4;
      const cols = 8;
      const xOffset = 75;
      const yOffset = 55;
      for (let r = 0; r < rows; r++) {
        for (let c = 0; c < cols; c++) {
          let skinType = catSkin1;
          if (r === 1) skinType = catSkin2;
          if (r >= 2) skinType = catSkin3;
          enemies.push({
            x: c * xOffset + 100,
            y: r * yOffset + 60,
            width: 45,
            height: 45,
            image: skinType,
            points: (4 - r) * 10
          });
        }
      }
    }

    function update() {
      if (gameState !== 'PLAYING') return;
      if (keys['ArrowLeft'] || keys['KeyA']) player.x -= player.speed;
      if (keys['ArrowRight'] || keys['KeyD']) player.x += player.speed;
      if (player.x < 0) player.x = 0;
      if (player.x > canvas.width - player.width) player.x = canvas.width - player.width;
      if (player.cooldown > 0) player.cooldown--;
      if (keys['Space'] && player.cooldown === 0) {
        lasers.push({ x: player.x + player.width / 2 - 2.5, y: player.y, width: 5, height: 15, speed: 9 });
        player.cooldown = 15;
      }

      lasers.forEach((laser, index) => {
        laser.y -= laser.speed;
        if (laser.y < 0) lasers.splice(index, 1);
      });

      enemyBombs.forEach((bomb, index) => {
        bomb.y += bomb.speed;
        if (bomb.y > canvas.height) enemyBombs.splice(index, 1);
        const playerHitX = player.x + player.hitboxOffsetX;
        const playerHitY = player.y + player.hitboxOffsetY;
        if (bomb.x < playerHitX + player.hitboxWidth && bomb.x + bomb.width > playerHitX && bomb.y < playerHitY + player.hitboxHeight && bomb.y + bomb.height > playerHitY) {
          enemyBombs.splice(index, 1);
          loseLife();
        }
      });

      let hitWall = false;
      const baseBombDropChance = 0.0008;
      const waveModifierChance = baseBombDropChance * enemyWave;
      enemies.forEach(enemy => {
        enemy.x += enemySpeed * enemyDirection;
        if (enemy.x <= 10 || enemy.x >= canvas.width - enemy.width - 10) hitWall = true;
        if (Math.random() < Math.min(waveModifierChance, 0.02)) {
          enemyBombs.push({
            x: enemy.x + enemy.width / 2 - 2,
            y: enemy.y + enemy.height,
            width: 4,
            height: 12,
            speed: 3.0 + Math.min(enemyWave * 0.15, 0.9)
          });
        }
      });

      if (hitWall) {
        enemyDirection *= -1;
        enemies.forEach(enemy => enemy.y += 24);
      }

      lasers.forEach((laser, lIdx) => {
        enemies.forEach((enemy, eIdx) => {
          if (laser.x < enemy.x + enemy.width && laser.x + laser.width > enemy.x && laser.y < enemy.y + enemy.height && laser.y + laser.height > enemy.y) {
            score += enemy.points;
            enemies.splice(eIdx, 1);
            lasers.splice(lIdx, 1);
          }
        });
      });

      if (enemies.length === 0) {
        enemyWave++;
        enemySpeed = Math.min(enemySpeed + 0.15, 3.5);
        spawnEnemies();
      }

      enemies.forEach(enemy => {
        if (enemy.y + enemy.height >= player.y || enemy.y + enemy.height >= canvas.height) {
          endGame();
        }
      });
    }

    function loseLife() {
      lives--;
      if (lives <= 0) endGame();
    }

    function draw() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.drawImage(bgImg, 0, 0, canvas.width, canvas.height);
      if (gameState !== 'PLAYING') return;
      ctx.drawImage(rocketImg, player.x, player.y, player.width, player.height);
      ctx.fillStyle = selectedLaserColor;
      lasers.forEach(laser => ctx.fillRect(laser.x, laser.y, laser.width, laser.height));
      ctx.fillStyle = '#00f2fe';
      enemyBombs.forEach(bomb => ctx.fillRect(bomb.x, bomb.y, bomb.width, bomb.height));
      enemies.forEach(enemy => ctx.drawImage(enemy.image, enemy.x, enemy.y, enemy.width, enemy.height));
      ctx.fillStyle = '#ffffff';
      ctx.font = 'bold 16px sans-serif';
      ctx.fillText(`SCORE: ${score}`, 20, 35);
      ctx.fillText(`PILOT: ${currentUser.toUpperCase()}`, 20, 60);
      ctx.fillText(`LIVES:`, canvas.width / 2 - 40, 35);
      ctx.fillStyle = '#ff3366';
      for (let i = 0; i < lives; i++) {
        ctx.fillRect(canvas.width / 2 + 15 + (i * 18), 22, 12, 14);
      }
      ctx.fillStyle = '#ffffff';
      ctx.fillText(`WAVE: ${enemyWave}`, canvas.width - 110, 35);
    }

    function loop() {
      update();
      draw();
      requestAnimationFrame(loop);
    }

    function startGame() {
      score = 0;
      lives = 3;
      enemyWave = 1;
      enemySpeed = 1.6;
      lasers = [];
      enemyBombs = [];
      initPlayer();
      spawnEnemies();
      mainMenu.classList.add('hidden');
      gameOverScreen.classList.add('hidden');
      gameState = 'PLAYING';
    }

    function endGame() {
      gameState = 'GAMEOVER';
      finalScoreText.innerText = `Your Final Score: ${score}`;
      gameOverScreen.classList.remove('hidden');
      submitAndRenderLeaderboard(score);
    }

    function renderLeaderboardView(targetContainer, records) {
      targetContainer.innerHTML = '';
      if (!records.length) {
        targetContainer.innerHTML = `<div class="score-row" style="color: #888; justify-content:center;">No scores available yet.</div>`;
        return;
      }
      records.forEach(entry => {
        const row = document.createElement('div');
        row.className = 'score-row';
        if (entry.name.toLowerCase() === currentUser.toLowerCase()) {
          row.classList.add('highlight');
        }
        row.innerHTML = `<span>${entry.name}</span><span>${entry.score}</span>`;
        targetContainer.appendChild(row);
      });
    }

    async function fetchLeaderboardData(search = '') {
      const body = new URLSearchParams();
      body.append('action', 'fetch_scores');
      body.append('search', search);
      try {
        const response = await fetch('game.php', { method: 'POST', body });
        const result = await response.json();
        if (result.success) return result.scores || [];
      } catch (err) {
        console.error('Leaderboard fetch error', err);
      }
      return [];
    }

    async function submitAndRenderLeaderboard(finalScore) {
      gameoverLeaderboard.innerHTML = 'Updating leaderboard...';
      const body = new URLSearchParams();
      body.append('action', 'submit_score');
      body.append('score', finalScore);
      try {
        const response = await fetch('game.php', { method: 'POST', body });
        const result = await response.json();
        if (result.success) {
          cachedGlobalScores = result.scores || [];
          renderLeaderboardView(gameoverLeaderboard, cachedGlobalScores);
          return;
        }
      } catch (err) {
        console.error('Score submit error', err);
      }
      gameoverLeaderboard.innerHTML = '<div class="score-row" style="color:#f28530; justify-content:center;">Unable to update scoreboard.</div>';
    }

    async function fetchAndRenderLeaderboard(targetContainer) {
      targetContainer.innerHTML = 'Loading leaderboard...';
      cachedGlobalScores = await fetchLeaderboardData(menuSearchInput.value.trim());
      renderLeaderboardView(targetContainer, cachedGlobalScores);
    }

    menuSearchInput.addEventListener('input', async (e) => {
      cachedGlobalScores = await fetchLeaderboardData(e.target.value.trim());
      renderLeaderboardView(menuLeaderboard, cachedGlobalScores);
    });

    startBtn.addEventListener('click', startGame);
    restartBtn.addEventListener('click', () => {
      gameOverScreen.classList.add('hidden');
      mainMenu.classList.remove('hidden');
      switchMenuTab(navPlay, tabPlay);
    });

    fetchAndRenderLeaderboard(menuLeaderboard);
    loop();
  </script>
</body>
</html>
