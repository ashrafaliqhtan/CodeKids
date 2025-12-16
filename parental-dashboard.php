<?php
// parental-dashboard.php
session_start();
include('./dbConnection.php');

if(!isset($_SESSION['parent_logged_in'])) {
  header('Location: parent-login.php');
  exit();
}

$parentId = $_SESSION['parent_id'];
$children = [];
$stmt = $conn->prepare("SELECT * FROM child_profiles WHERE parent_id = ?");
$stmt->bind_param("i", $parentId);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    $children[] = $row;
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Parent Dashboard</title>
  <style>
    .dashboard-container {
      display: grid;
      grid-template-columns: 1fr 3fr;
      gap: 2rem;
      padding: 2rem;
    }
    
    .child-card {
      background: #fff;
      border-radius: 15px;
      padding: 1.5rem;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .progress-chart {
      background: #f8f9fa;
      padding: 2rem;
      border-radius: 15px;
    }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <div class="sidebar">
      <h2>Your Children</h2>
      <?php foreach($children as $child): ?>
        <div class="child-card">
          <h3><?= htmlspecialchars($child['username']) ?></h3>
          <p>Level: <?= $child['level'] ?></p>
          <p>XP: <?= $child['xp'] ?></p>
          <a href="child-progress.php?id=<?= $child['id'] ?>">View Details</a>
        </div>
      <?php endforeach; ?>
    </div>
    
    <div class="main-content">
      <h2>Activity Overview</h2>
      <div class="progress-chart">
        <canvas id="progressChart"></canvas>
      </div>
      
      <div class="controls">
        <h3>Parental Controls</h3>
        <form method="post" action="update-controls.php">
          <label>
            Daily Time Limit (minutes):
            <input type="number" name="time_limit" min="30" max="180" value="120">
          </label>
          
          <label>
            Content Filter Level:
            <select name="content_filter">
              <option value="basic">Basic</option>
              <option value="moderate">Moderate</option>
              <option value="strict">Strict</option>
            </select>
          </label>
          
          <button type="submit">Save Settings</button>
        </form>
      </div>
    </div>
  </div>
</body>

<script>
  class ProgressSystem {
  constructor() {
    this.xp = 0;
    this.level = 1;
    this.progressChart = null;
    this.initChart();
  }

  initChart() {
    const ctx = document.getElementById('progressChart').getContext('2d');
    this.progressChart = new Chart(ctx, {
      type: 'radar',
      data: {
        labels: ['Variables', 'Loops', 'Functions', 'Logic', 'Creativity'],
        datasets: [{
          label: 'Skill Progress',
          data: [3, 5, 2, 4, 7],
          backgroundColor: 'rgba(78, 205, 196, 0.2)',
          borderColor: '#4ECDC4'
        }]
      },
      options: {
        scales: {
          r: {
            beginAtZero: true,
            max: 10
          }
        }
      }
    });
  }

  addXP(amount) {
    this.xp += amount;
    this.checkLevelUp();
    this.updateProgressDisplay();
  }

  checkLevelUp() {
    const xpNeeded = this.level * 100;
    if(this.xp >= xpNeeded) {
      this.level++;
      this.xp -= xpNeeded;
      this.showLevelUpAnimation();
    }
  }

  showLevelUpAnimation() {
    const levelUpDiv = document.createElement('div');
    levelUpDiv.className = 'level-up-animation';
    levelUpDiv.innerHTML = `
      <div class="confetti"></div>
      <h2>Level Up! 🎉</h2>
      <p>You're now Level ${this.level}!</p>
    `;
    
    document.body.appendChild(levelUpDiv);
    setTimeout(() => levelUpDiv.remove(), 3000);
  }

  updateProgressDisplay() {
    document.getElementById('xpCounter').textContent = `XP: ${this.xp}`;
    document.getElementById('levelDisplay').textContent = `Level: ${this.level}`;
  }
}

const progressSystem = new ProgressSystem();
  
  
</script>
</html>