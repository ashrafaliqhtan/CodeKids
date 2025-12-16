
// Quiz System
function checkAnswer(option) {
  const correctAnswer = 2;
  const options = document.querySelectorAll('.quiz-option');
  
  options.forEach(btn => {
    btn.style.backgroundColor = '#fff';
    if(parseInt(btn.getAttribute('data-option')) === correctAnswer) {
      btn.style.backgroundColor = '#83D483';
    }
  });

  if(option === correctAnswer) {
    gameSystem.unlockAchievement('Quiz Master');
  }
}

// Daily Challenge Runner
function runDailyChallenge() {
  const code = document.getElementById('dailyCode').value;
  try {
    const gameScene = gameSystem.games.loopAdventure.scene.getScene('LoopAdventure');
    gameScene.runCustomCode(code);
  } catch(error) {
    console.error('Code execution error:', error);
  }
}

// Badge Display
function loadBadges() {
  const container = document.getElementById('badgeContainer');
  gameSystem.achievements.forEach(achievement => {
    const badge = document.createElement('div');
    badge.className = 'badge-card';
    badge.innerHTML = `
      <img src="${achievement.icon}" alt="${achievement.name}">
      <p>${achievement.name}</p>
    `;
    container.appendChild(badge);
  });
}
</script>
     
      // Virtual Pet Interaction
      const pet = document.getElementById('virtualPet');
      const petStatus = document.getElementById('petStatus');
      
      pet.addEventListener('click', () => {
        pet.classList.add('animate__animated', 'animate__tada');
        petStatus.textContent = "Codey is happy! 💖";
        setTimeout(() => {
          pet.classList.remove('animate__tada');
          petStatus.textContent = "";
        }, 2000);
      });

      // Interactive Coding Game
      class CodeRunner {
        constructor() {
          this.game = new Phaser.Game({
            type: Phaser.AUTO,
            parent: 'loopAdventure',
            scene: {
              preload: this.preload,
              create: this.create,
              update: this.update
            },
            // Additional game configuration...
          });
        }

        preload() {
          this.load.image('codey', 'images/game/codey.png');
          this.load.image('star', 'images/game/star.png');
        }

        create() {
          this.codey = this.physics.add.sprite(100, 450, 'codey');
          this.stars = this.physics.add.group();
          // Game initialization...
        }

        runCode(code) {
          // Code execution logic...
        }
      }

      // Initialize game
      const game = new CodeRunner();

      // Achievement System
      function unlockAchievement(name) {
        const toast = document.getElementById('achievementToast');
        toast.querySelector('p').textContent = name;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
      }

      // Story Guide Animations
      const guide = document.getElementById('storyGuide');
      let currentLesson = 0;
      
      function nextLesson() {
        const messages = [
          "Great job! 🎉 Let's learn variables!",
          "Now we'll discover functions! 🚀",
          "Time for loops! 🔄"
        ];
        
        guide.classList.add('animate__bounce');
        setTimeout(() => {
          document.getElementById('guideSpeech').textContent = messages[currentLesson];
          currentLesson = (currentLesson + 1) % messages.length;
          guide.classList.remove('animate__bounce');
        }, 500);
      }

      // Parental Controls
      class ParentalControls {
        constructor() {
          this.dailyLimit = 120; // minutes
          this.achievementNotifications = true;
          this.contentFilters = [];
        }

        updateSettings(settings) {
          // Update control settings...
        }
      }
    

    <!-- Enhanced Progress Tracking -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
     
      // Progress Visualization
      const progressChart = new Chart(document.getElementById('progressChart'), {
        type: 'radar',
        data: {
          labels: ['Variables', 'Loops', 'Functions', 'Logic', 'Projects'],
          datasets: [{
            label: 'Coding Skills',
            data: [8, 6, 5, 7, 4],
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
    
    <!-- Game Engine -->
<script src="https://cdn.jsdelivr.net/npm/phaser@3.60.0/dist/phaser.min.js"></script>

<!-- Animation Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

<!-- Voice Synthesis -->
<script src="https://code.responsivevoice.org/responsivevoice.js"></script>
    
   
    
    class GameSystem {
  constructor() {
    this.petStates = ['normal', 'happy', 'sad'];
    this.currentPetState = 'normal';
    this.achievements = [];
    this.initPet();
    this.initGames();
  }

  initPet() {
    this.pet = document.getElementById('virtualPet');
    this.pet.addEventListener('click', () => this.petInteraction());
    setInterval(() => this.updatePetState(), 60000);
  }

  petInteraction() {
    this.showParticleEffect();
    this.unlockAchievement('Pet Lover');
    this.currentPetState = 'happy';
    this.updatePetVisual();
  }

  showParticleEffect() {
    const particles = document.createElement('div');
    particles.className = 'particle-effect';
    // Particle animation implementation
  }

  updatePetState() {
    // Update pet state based on user activity
  }

  updatePetVisual() {
    this.pet.src = `images/pet-${this.currentPetState}.png`;
  }

  initGames() {
    this.games = {
      loopAdventure: new Phaser.Game({
        type: Phaser.AUTO,
        parent: 'loopAdventure',
        scene: LoopAdventureScene,
        scale: {
          mode: Phaser.Scale.FIT,
          autoCenter: Phaser.Scale.CENTER_BOTH
        }
      })
    };
  }

  unlockAchievement(name) {
    const achievement = {
      name,
      date: new Date(),
      icon: `images/${name.toLowerCase().replace(' ', '-')}-badge.png`
    };
    this.achievements.push(achievement);
    this.showAchievementToast(achievement);
  }

  showAchievementToast(achievement) {
    const toast = document.getElementById('achievementToast');
    toast.querySelector('img').src = achievement.icon;
    toast.querySelector('p').textContent = achievement.name;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
  }

  initParentalControls() {
    this.parentalControls = {
      timeLimit: 120,
      contentFilters: [],
      activityMonitoring: true
    };
  }
}

class LoopAdventureScene extends Phaser.Scene {
  constructor() {
    super({ key: 'LoopAdventure' });
    this.player;
    this.stars;
    this.score = 0;
  }

  preload() {
    this.load.image('codey', 'images/game/codey.png');
    this.load.image('star', 'images/game/star.png');
    this.load.image('ground', 'images/game/platform.png');
  }

  create() {
    // Game world setup
    this.platforms = this.physics.add.staticGroup();
    this.platforms.create(400, 568, 'ground').setScale(2).refreshBody();
    
    this.player = this.physics.add.sprite(100, 450, 'codey');
    this.player.setBounce(0.2);
    this.player.setCollideWorldBounds(true);
    
    this.stars = this.physics.add.group();
    this.spawnStars();
    
    this.physics.add.collider(this.player, this.platforms);
    this.physics.add.overlap(this.player, this.stars, this.collectStar, null, this);
  }

  spawnStars() {
    for(let i = 0; i < 5; i++) {
      this.stars.create(Phaser.Math.Between(100, 700), 0, 'star');
    }
  }

  collectStar(player, star) {
    star.disableBody(true, true);
    this.score += 10;
    this.updateScoreDisplay();
  }

  updateScoreDisplay() {
    document.querySelector('.game-score').textContent = `Score: ${this.score}`;
  }

  runCustomCode(code) {
    try {
      const func = new Function(code);
      func.call(this);
    } catch(error) {
      this.showError(error);
    }
  }

  showError(message) {
    const errorDisplay = document.querySelector('.game-error');
    errorDisplay.textContent = message;
    setTimeout(() => errorDisplay.textContent = '', 3000);
  }
}

// Initialize Game System
const gameSystem = new GameSystem();
    
    
    
     
      
      class KidsGameEngine {
  constructor() {
    this.gameConfig = {
      type: Phaser.AUTO,
      parent: 'gameCanvas',
      width: 800,
      height: 400,
      physics: {
        default: 'arcade',
        arcade: {
          gravity: { y: 0 },
          debug: false
        }
      },
      scene: {
        preload: this.preload,
        create: this.create,
        update: this.update
      }
    };
    
    this.game = new Phaser.Game(this.gameConfig);
    this.codeBlocks = [];
  }

  preload() {
    this.load.image('character', 'images/game/character.png');
    this.load.image('star', 'images/game/star.png');
    this.load.image('obstacle', 'images/game/rock.png');
  }

  create() {
    this.character = this.physics.add.sprite(100, 300, 'character');
    this.stars = this.physics.add.group();
    this.obstacles = this.physics.add.group();
    this.score = 0;
    
    this.createLevel();
  }

  createLevel() {
    // Generate random level layout
    for(let i = 0; i < 5; i++) {
      this.stars.create(200 + (i * 150), 300, 'star');
    }
    
    this.physics.add.overlap(this.character, this.stars, this.collectStar, null, this);
  }

  collectStar(character, star) {
    star.disableBody(true, true);
    this.score++;
    this.updateScoreDisplay();
  }

  updateScoreDisplay() {
    document.getElementById('gameScore').textContent = `Stars Collected: ${this.score}`;
  }

  executeUserCode(code) {
    try {
      const commands = {
        moveForward: () => this.character.x += 50,
        turnLeft: () => this.character.angle -= 90,
        turnRight: () => this.character.angle += 90
      };
      
      // Safe code evaluation
      const safeCode = code.replace(/[^a-zA-Z0-9();{} ]/g, '');
      const func = new Function('commands', `with(commands) { ${safeCode} }`);
      func.call(this, commands);
    } catch(error) {
      this.showError(error.message);
    }
  }

  showError(message) {
    const errorDisplay = document.createElement('div');
    errorDisplay.className = 'game-error';
    errorDisplay.textContent = `🤖 Oops! ${message}`;
    document.querySelector('.workspace').appendChild(errorDisplay);
    setTimeout(() => errorDisplay.remove(), 3000);
  }
}

// Initialize the game
const gameEngine = new KidsGameEngine();

let draggedElement = null;

document.querySelectorAll('.code-block').forEach(block => {
  block.addEventListener('dragstart', e => {
    draggedElement = e.target.cloneNode(true);
    e.dataTransfer.setData('text/plain', e.target.dataset.code);
  });
});

document.getElementById('workspace').addEventListener('dragover', e => {
  e.preventDefault();
  const afterElement = getDragAfterElement(e.clientY);
  const workspace = e.currentTarget;
  
  if(afterElement == null) {
    workspace.appendChild(draggedElement);
  } else {
    workspace.insertBefore(draggedElement, afterElement);
  }
});

function getDragAfterElement(y) {
  const elements = [...document.querySelectorAll('#workspace .code-block:not(.dragging)')];
  
  return elements.reduce((closest, child) => {
    const box = child.getBoundingClientRect();
    const offset = y - box.top - box.height / 2;
    return offset < 0 && offset > closest.offset ? 
      { offset: offset, element: child } : closest;
  }, { offset: Number.NEGATIVE_INFINITY }).element;
}

function executeCode() {
  const codeBlocks = [...document.querySelectorAll('#workspace .code-block')];
  const code = codeBlocks.map(block => block.dataset.code).join('\n');
  gameEngine.executeUserCode(code);
}

function resetWorkspace() {
  document.getElementById('workspace').innerHTML = '<div class="game-view" id="gameCanvas"></div>';
  gameEngine.game.scene.restart();
}

    