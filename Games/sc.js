document.addEventListener('DOMContentLoaded', function() {
    // Game state variables
    let currentLevel = 1;
    const totalLevels = 5;
    let score = 0;
    let timerInterval;
    let seconds = 0;
    let isSoundOn = true;
    let isGameRunning = false;
    let characterPosition = { x: 0, y: 0 };
    let targetPosition = { x: 0, y: 0 };
    let characterDirection = 0; // 0: right, 90: down, 180: left, 270: up
    let obstacles = [];
    let draggedBlock = null;
    
    // DOM elements
    const gameBoard = document.getElementById('game-board');
    const characterContainer = document.getElementById('character-container');
    const character = document.getElementById('character');
    const target = document.getElementById('target');
    const blocksContainer = document.getElementById('blocks-container');
    const workspace = document.getElementById('workspace');
    const runBtn = document.getElementById('run-btn');
    const resetBtn = document.getElementById('reset-btn');
    const clearBtn = document.getElementById('clear-btn');
    const hintBtn = document.getElementById('hint-btn');
    const helpBtn = document.getElementById('help-btn');
    const soundToggle = document.getElementById('sound-toggle');
    const nextLevelBtn = document.getElementById('next-level-btn');
    const restartGameBtn = document.getElementById('restart-game-btn');
    const shareBtn = document.getElementById('share-btn');
    const closeHintBtn = document.getElementById('close-hint-btn');
    const levelCompleteModal = document.getElementById('level-complete-modal');
    const hintModal = document.getElementById('hint-modal');
    const gameCompleteModal = document.getElementById('game-complete-modal');
    const missionText = document.getElementById('mission-text');
    const currentLevelDisplay = document.getElementById('current-level');
    const totalLevelsDisplay = document.getElementById('total-levels');
    const scoreDisplay = document.getElementById('score');
    const timerDisplay = document.getElementById('timer');
    const levelProgress = document.getElementById('level-progress');
    const completeTime = document.getElementById('complete-time');
    const completeScore = document.getElementById('complete-score');
    const completeStars = document.getElementById('complete-stars');
    const feedbackMessage = document.getElementById('feedback-message');
    const finalScore = document.getElementById('final-score');
    const fastestLevel = document.getElementById('fastest-level');
    const bestRating = document.getElementById('best-rating');
    
    // Game sounds
    const sounds = {
        move: new Howl({ src: ['https://assets.codepen.io/21542/howler-demo-move.mp3'] }),
        success: new Howl({ src: ['https://assets.codepen.io/21542/howler-demo-success.mp3'] }),
        error: new Howl({ src: ['https://assets.codepen.io/21542/howler-demo-error.mp3'] }),
        click: new Howl({ src: ['https://assets.codepen.io/21542/howler-demo-click.mp3'] }),
        pick: new Howl({ src: ['https://assets.codepen.io/21542/howler-demo-pick.mp3'] }),
        drop: new Howl({ src: ['https://assets.codepen.io/21542/howler-demo-drop.mp3'] })
    };
    
    // Game levels data
    const levels = [
        {
            mission: "استخدم كتلة 'تحرك للأمام' لجعل الشخصية تصل إلى الهدف",
            characterPos: { x: 1, y: 3 },
            targetPos: { x: 3, y: 3 },
            obstacles: [],
            hint: "اسحب كتلة 'تحرك للأمام' إلى منطقة العمل ثم اضغط على زر التشغيل",
            solution: ['moveForward']
        },
        {
            mission: "استخدم كتلتين 'تحرك للأمام' للوصول إلى الهدف",
            characterPos: { x: 1, y: 3 },
            targetPos: { x: 3, y: 3 },
            obstacles: [],
            hint: "ستحتاج إلى استخدام كتلتين 'تحرك للأمام' واحدة تلو الأخرى",
            solution: ['moveForward', 'moveForward']
        },
        {
            mission: "استخدم كتل 'تحرك للأمام' و'استدر لليمين' للوصول إلى الهدف",
            characterPos: { x: 1, y: 3 },
            targetPos: { x: 1, y: 1 },
            obstacles: [],
            hint: "تحرك للأمام ثم استدر لليمين ثم تحرك للأمام مرة أخرى",
            solution: ['moveForward', 'turnRight', 'moveForward']
        },
        {
            mission: "تجنب العقبات باستخدام كتل الحركة المناسبة",
            characterPos: { x: 1, y: 3 },
            targetPos: { x: 5, y: 3 },
            obstacles: [
                { x: 3, y: 2, width: 1, height: 3 }
            ],
            hint: "ستحتاج إلى التحرك حول العقبة باستخدام عدة كتل حركة",
            solution: ['moveForward', 'moveForward', 'turnRight', 'moveForward', 'turnLeft', 'moveForward', 'moveForward']
        },
        {
            mission: "استخدم كتلة 'التكرار' لإكمال المهمة بأقل عدد من الكتل",
            characterPos: { x: 1, y: 3 },
            targetPos: { x: 5, y: 3 },
            obstacles: [],
            hint: "استخدم كتلة التكرار لتكرار كتلة 'تحرك للأمام' مرتين",
            solution: ['repeatStart', 'moveForward', 'moveForward', 'repeatEnd']
        }
    ];
    
    // Programming blocks data
    const programmingBlocks = [
        {
            id: 'moveForward',
            name: 'تحرك للأمام',
            icon: 'fas fa-arrow-up',
            color: '#4361ee',
            description: 'تحريك الشخصية للأمام بمقدار خلية واحدة'
        },
        {
            id: 'moveBackward',
            name: 'تحرك للخلف',
            icon: 'fas fa-arrow-down',
            color: '#3f37c9',
            description: 'تحريك الشخصية للخلف بمقدار خلية واحدة'
        },
        {
            id: 'turnLeft',
            name: 'استدر لليسار',
            icon: 'fas fa-undo',
            color: '#4895ef',
            description: 'تدوير الشخصية 90 درجة لليسار'
        },
        {
            id: 'turnRight',
            name: 'استدر لليمين',
            icon: 'fas fa-redo',
            color: '#4cc9f0',
            description: 'تدوير الشخصية 90 درجة لليمين'
        },
        {
            id: 'jump',
            name: 'اقفز',
            icon: 'fas fa-arrow-alt-circle-up',
            color: '#f8961e',
            description: 'قفز فوق العقبات بارتفاع خلية واحدة'
        },
        {
            id: 'repeatStart',
            name: 'كرر',
            icon: 'fas fa-sync-alt',
            color: '#f8961e',
            hasCount: true,
            description: 'بداية تكرار مجموعة من الأوامر'
        },
        {
            id: 'repeatEnd',
            name: 'نهاية التكرار',
            icon: 'fas fa-stop-circle',
            color: '#f72585',
            description: 'نهاية مجموعة الأوامر المكررة'
        }
    ];
    
    // Initialize the game
    function initGame() {
        // Set up initial displays
        currentLevelDisplay.textContent = currentLevel;
        totalLevelsDisplay.textContent = totalLevels;
        scoreDisplay.textContent = score;
        
        // Load the current level
        loadLevel(currentLevel);
        
        // Create programming blocks
        createProgrammingBlocks();
        
        // Set up drag and drop with interact.js
        setupInteractJS();
        
        // Set up event listeners
        setupEventListeners();
    }
    
    // Load a specific level
    function loadLevel(levelNum) {
        // Reset game state
        clearInterval(timerInterval);
        seconds = 0;
        updateTimerDisplay();
        levelProgress.style.width = '0%';
        characterDirection = 0;
        
        // Clear the workspace
        workspace.innerHTML = '';
        
        // Get level data
        const level = levels[levelNum - 1];
        
        // Update mission text
        missionText.textContent = level.mission;
        
        // Set character and target positions
        characterPosition = { ...level.characterPos };
        targetPosition = { ...level.targetPos };
        obstacles = [...level.obstacles];
        
        // Update game board
        updateGameBoard();
        
        // Start timer
        startTimer();
    }
    
    // Update the game board visuals
    function updateGameBoard() {
        const cellSize = 50; // Each grid cell is 50px
        
        // Position character
        characterContainer.style.left = `${characterPosition.x * cellSize}px`;
        characterContainer.style.top = `${characterPosition.y * cellSize}px`;
        character.style.transform = `rotate(${characterDirection}deg)`;
        
        // Position target
        target.style.left = `${targetPosition.x * cellSize}px`;
        target.style.top = `${targetPosition.y * cellSize}px`;
        
        // Clear existing obstacles
        document.querySelectorAll('.obstacle').forEach(el => el.remove());
        
        // Create obstacles
        obstacles.forEach(obstacle => {
            const obstacleEl = document.createElement('div');
            obstacleEl.className = 'obstacle';
            obstacleEl.style.left = `${obstacle.x * cellSize}px`;
            obstacleEl.style.top = `${obstacle.y * cellSize}px`;
            obstacleEl.style.width = `${obstacle.width * cellSize}px`;
            obstacleEl.style.height = `${obstacle.height * cellSize}px`;
            
            // Add obstacle label
            const label = document.createElement('div');
            label.className = 'obstacle-label';
            label.textContent = 'عقبة';
            obstacleEl.appendChild(label);
            
            gameBoard.appendChild(obstacleEl);
        });
    }
    
    // Create programming blocks
    function createProgrammingBlocks() {
        blocksContainer.innerHTML = '';
        
        programmingBlocks.forEach(block => {
            const blockEl = document.createElement('div');
            blockEl.className = 'block';
            blockEl.dataset.id = block.id;
            blockEl.draggable = true;
            
            blockEl.innerHTML = `
                <div class="block-header" style="background-color: ${block.color}">
                    <i class="${block.icon} block-icon"></i>
                    <span>${block.name}</span>
                </div>
                ${block.hasCount ? 
                    `<div class="block-controls">
                        <label>عدد المرات:</label>
                        <input type="number" class="repeat-count" value="2" min="1" max="10">
                    </div>` 
                    : ''}
                <div class="block-handle">
                    <i class="fas fa-grip-lines"></i>
                </div>
            `;
            
            blocksContainer.appendChild(blockEl);
        });
        
        setupDragAndDrop();
    }

    // Set up drag and drop with interact.js
    function setupInteractJS() {
        // Make blocks draggable
        interact('.block').draggable({
            inertia: true,
            modifiers: [
                interact.modifiers.restrictRect({
                    restriction: 'parent',
                    endOnly: true
                })
            ],
            autoScroll: true,
            
            // On drag start
            onstart: function(event) {
                draggedBlock = event.target;
                event.target.classList.add('dragging');
                playSound('pick');
                
                // Create a clone for dragging
                const clone = event.target.cloneNode(true);
                clone.id = 'dragged-block';
                clone.style.width = `${event.target.offsetWidth}px`;
                document.body.appendChild(clone);
                
                // Update clone position
                const rect = event.target.getBoundingClientRect();
                clone.style.left = `${rect.left}px`;
                clone.style.top = `${rect.top}px`;
                
                event.data.clone = clone;
            },
            
            // While moving
            onmove: function(event) {
                const clone = event.data.clone;
                const x = event.clientX - clone.offsetWidth / 2;
                const y = event.clientY - clone.offsetHeight / 2;
                
                clone.style.left = `${x}px`;
                clone.style.top = `${y}px`;
                
                // Highlight workspace if pointer is over it
                const workspaceRect = workspace.getBoundingClientRect();
                const isOverWorkspace = (
                    event.clientX >= workspaceRect.left &&
                    event.clientX <= workspaceRect.right &&
                    event.clientY >= workspaceRect.top &&
                    event.clientY <= workspaceRect.bottom
                );
                
                workspace.classList.toggle('drop-highlight', isOverWorkspace);
            },
            
            // On drag end
            onend: function(event) {
                const clone = event.data.clone;
                
                // Check if dropped over workspace
                const workspaceRect = workspace.getBoundingClientRect();
                const isOverWorkspace = (
                    event.clientX >= workspaceRect.left &&
                    event.clientX <= workspaceRect.right &&
                    event.clientY >= workspaceRect.top &&
                    event.clientY <= workspaceRect.bottom
                );
                
                if (isOverWorkspace && draggedBlock) {
                    addBlockToWorkspace(draggedBlock);
                    playSound('drop');
                }
                
                // Clean up
                if (clone && clone.parentNode) {
                    clone.parentNode.removeChild(clone);
                }
                if (event.target) {
                    event.target.classList.remove('dragging');
                }
                workspace.classList.remove('drop-highlight');
                draggedBlock = null;
            }
        });
        
        // Make workspace a dropzone
        interact(workspace).dropzone({
            accept: '.block',
            overlap: 0.5,
            
            ondropactivate: function(event) {
                event.target.classList.add('drop-active');
            },
            
            ondragenter: function(event) {
                event.relatedTarget.classList.add('can-drop');
            },
            
            ondragleave: function(event) {
                event.relatedTarget.classList.remove('can-drop');
            },
            
            ondrop: function(event) {
                event.relatedTarget.classList.remove('can-drop');
            },
            
            ondropdeactivate: function(event) {
                event.target.classList.remove('drop-active');
            }
        });
    }

    // Add block to workspace
    function addBlockToWorkspace(block) {
        const clone = block.cloneNode(true);
        clone.classList.remove('dragging');
        clone.style.position = 'relative';
        clone.style.left = 'auto';
        clone.style.top = 'auto';
        clone.style.transform = '';
        clone.style.opacity = '1';
        
        // Add remove button
        const removeBtn = document.createElement('div');
        removeBtn.className = 'workspace-block-remove';
        removeBtn.innerHTML = '<i class="fas fa-times"></i>';
        removeBtn.addEventListener('click', function() {
            clone.remove();
            playSound('click');
        });
        
        clone.appendChild(removeBtn);
        
        // Add insertion effect
        clone.style.opacity = '0';
        clone.style.transform = 'translateX(20px)';
        workspace.appendChild(clone);
        
        setTimeout(() => {
            clone.style.transition = 'all 0.3s ease';
            clone.style.opacity = '1';
            clone.style.transform = 'translateX(0)';
        }, 10);
    }

    // Set up drag and drop
    function setupDragAndDrop() {
        // Make blocks draggable
        interact('.block').draggable({
            inertia: true,
            modifiers: [
                interact.modifiers.restrictRect({
                    restriction: 'parent',
                    endOnly: true
                })
            ],
            autoScroll: true,
            
            // On drag start
            onstart: function(event) {
                draggedBlock = event.target;
                event.target.classList.add('dragging');
                playSound('pick');
                
                // Create a clone for dragging
                const clone = event.target.cloneNode(true);
                clone.id = 'dragged-block';
                clone.style.width = `${event.target.offsetWidth}px`;
                document.body.appendChild(clone);
                
                // Update clone position
                const rect = event.target.getBoundingClientRect();
                clone.style.left = `${rect.left}px`;
                clone.style.top = `${rect.top}px`;
                
                event.data.clone = clone;
            },
            
            // While moving
            onmove: function(event) {
                const clone = event.data.clone;
                const x = event.clientX - clone.offsetWidth / 2;
                const y = event.clientY - clone.offsetHeight / 2;
                
                clone.style.left = `${x}px`;
                clone.style.top = `${y}px`;
                
                // Highlight workspace if pointer is over it
                const workspaceRect = workspace.getBoundingClientRect();
                const isOverWorkspace = (
                    event.clientX >= workspaceRect.left &&
                    event.clientX <= workspaceRect.right &&
                    event.clientY >= workspaceRect.top &&
                    event.clientY <= workspaceRect.bottom
                );
                
                workspace.classList.toggle('drop-highlight', isOverWorkspace);
            },
            
            // On drag end
            onend: function(event) {
                const clone = event.data.clone;
                
                // Check if dropped over workspace
                const workspaceRect = workspace.getBoundingClientRect();
                const isOverWorkspace = (
                    event.clientX >= workspaceRect.left &&
                    event.clientX <= workspaceRect.right &&
                    event.clientY >= workspaceRect.top &&
                    event.clientY <= workspaceRect.bottom
                );
                
                if (isOverWorkspace && draggedBlock) {
                    addBlockToWorkspace(draggedBlock);
                    playSound('drop');
                }
                
                // Clean up
                if (clone && clone.parentNode) {
                    clone.parentNode.removeChild(clone);
                }
                if (event.target) {
                    event.target.classList.remove('dragging');
                }
                workspace.classList.remove('drop-highlight');
                draggedBlock = null;
            }
        });
        
        // Make workspace a dropzone
        interact(workspace).dropzone({
            accept: '.block',
            overlap: 0.5,
            
            ondropactivate: function(event) {
                event.target.classList.add('drop-active');
            },
            
            ondragenter: function(event) {
                event.relatedTarget.classList.add('can-drop');
            },
            
            ondragleave: function(event) {
                event.relatedTarget.classList.remove('can-drop');
            },
            
            ondrop: function(event) {
                event.relatedTarget.classList.remove('can-drop');
            },
            
            ondropdeactivate: function(event) {
                event.target.classList.remove('drop-active');
            }
        });
    }

    // Set up event listeners
    function setupEventListeners() {
        // Run button
        runBtn.addEventListener('click', executeProgram);
        
        // Reset button
        resetBtn.addEventListener('click', resetLevel);
        
        // Clear button
        clearBtn.addEventListener('click', () => {
            workspace.innerHTML = '';
            playSound('click');
        });
        
        // Hint button
        hintBtn.addEventListener('click', showHint);
        
        // Help button
        helpBtn.addEventListener('click', () => {
            showHint();
            playSound('click');
        });
        
        // Sound toggle
        soundToggle.addEventListener('click', toggleSound);
        
        // Next level button
        nextLevelBtn.addEventListener('click', goToNextLevel);
        
        // Restart game button
        restartGameBtn.addEventListener('click', restartGame);
        
        // Share button
        shareBtn.addEventListener('click', shareResults);
        
        // Close hint button
        closeHintBtn.addEventListener('click', () => {
            hintModal.style.display = 'none';
            playSound('click');
        });
        
        // Close modals when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                e.target.style.display = 'none';
                playSound('click');
            }
        });
    }
    
    // Execute the program in the workspace
    async function executeProgram() {
        if (isGameRunning) return;
        isGameRunning = true;
        playSound('click');
        
        // Disable buttons during execution
        runBtn.disabled = true;
        resetBtn.disabled = true;
        clearBtn.disabled = true;
        
        // Get blocks from workspace
        const blocks = Array.from(workspace.children).map(el => {
            return {
                element: el,
                id: el.dataset.id,
                repeatCount: el.querySelector('.repeat-count') ? parseInt(el.querySelector('.repeat-count').value) : null
            };
        });
        
        // Validate program
        if (blocks.length === 0) {
            showError('المنطقة العمل فارغة! أضف بعض الكتل البرمجية أولاً.');
            resetControls();
            return;
        }
        
        // Check for unclosed repeat blocks
        const repeatStack = [];
        for (const block of blocks) {
            if (block.id === 'repeatStart') {
                repeatStack.push(block);
            } else if (block.id === 'repeatEnd') {
                if (repeatStack.length === 0) {
                    showError('هناك كتلة نهاية تكرار بدون بداية!');
                    resetControls();
                    return;
                }
                repeatStack.pop();
            }
        }
        
        if (repeatStack.length > 0) {
            showError('هناك كتلة تكرار بدون نهاية!');
            resetControls();
            return;
        }
        
        // Execute blocks
        let i = 0;
        while (i < blocks.length) {
            const block = blocks[i];
            
            if (block.id === 'repeatStart') {
                // Find matching repeat end
                let endIndex = i + 1;
                let nested = 0;
                
                while (endIndex < blocks.length) {
                    if (blocks[endIndex].id === 'repeatStart') {
                        nested++;
                    } else if (blocks[endIndex].id === 'repeatEnd') {
                        if (nested === 0) break;
                        nested--;
                    }
                    endIndex++;
                }
                
                // Repeat the blocks between start and end
                const repeatBlocks = blocks.slice(i + 1, endIndex);
                for (let j = 0; j < block.repeatCount; j++) {
                    for (const repeatBlock of repeatBlocks) {
                        await executeBlock(repeatBlock);
                        
                        // Check if character reached target after each move
                        if (checkWinCondition()) {
                            handleLevelComplete();
                            resetControls();
                            return;
                        }
                    }
                }
                
                i = endIndex + 1; // Skip past the repeat block
            } else {
                await executeBlock(block);
                i++;
                
                // Check if character reached target after each move
                if (checkWinCondition()) {
                    handleLevelComplete();
                    resetControls();
                    return;
                }
            }
        }
        
        // If we get here and haven't won, check if we're on target
        if (checkWinCondition()) {
            handleLevelComplete();
        } else {
            // Show message if didn't reach target
            setTimeout(() => {
                showError('لم تصل إلى الهدف بعد! حاول مرة أخرى.');
                resetControls();
            }, 500);
        }
        
        resetControls();
    }
    
    // Reset UI controls after execution
    function resetControls() {
        isGameRunning = false;
        runBtn.disabled = false;
        resetBtn.disabled = false;
        clearBtn.disabled = false;
        
        // Remove all active highlights
        document.querySelectorAll('.block-active').forEach(el => {
            el.classList.remove('block-active');
        });
    }
    
    // Execute a single block
    async function executeBlock(block) {
        // Highlight the block being executed
        block.element.classList.add('block-active');
        
        // Wait for animation to complete
        await new Promise(resolve => setTimeout(resolve, 300));
        
        // Execute the command
        switch (block.id) {
            case 'moveForward':
                await moveCharacter(1);
                break;
            case 'moveBackward':
                await moveCharacter(-1);
                break;
            case 'turnLeft':
                await turnCharacter(-90);
                break;
            case 'turnRight':
                await turnCharacter(90);
                break;
            case 'jump':
                await jumpCharacter();
                break;
        }
        
        // Remove highlight after execution
        block.element.classList.remove('block-active');
    }
    
    // Move the character based on current direction
    async function moveCharacter(steps) {
        playSound('move');
        
        // Calculate movement based on current direction
        let dx = 0, dy = 0;
        
        switch (characterDirection) {
            case 0: // Right
                dx = steps;
                break;
            case 90: // Down
                dy = steps;
                break;
            case 180: // Left
                dx = -steps;
                break;
            case 270: // Up
                dy = -steps;
                break;
        }
        
        // Calculate new position
        const newX = characterPosition.x + dx;
        const newY = characterPosition.y + dy;
        
        // Check for obstacles
        if (isObstacleAt(newX, newY)) {
            character.classList.add('animate-character');
            setTimeout(() => {
                character.classList.remove('animate-character');
            }, 500);
            playSound('error');
            return;
        }
        
        // Check boundaries
        if (newX < 0 || newX > 7 || newY < 0 || newY > 7) {
            character.classList.add('animate-character');
            setTimeout(() => {
                character.classList.remove('animate-character');
            }, 500);
            playSound('error');
            return;
        }
        
        // Update position
        characterPosition.x = newX;
        characterPosition.y = newY;
        
        // Animate movement
        const cellSize = 50;
        characterContainer.style.transition = 'all 0.4s ease-out';
        characterContainer.style.left = `${newX * cellSize}px`;
        characterContainer.style.top = `${newY * cellSize}px`;
        
        // Wait for animation to complete
        await new Promise(resolve => setTimeout(resolve, 400));
    }
    
    // Turn the character
    async function turnCharacter(degrees) {
        playSound('move');
        
        // Update direction (keep within 0-360)
        characterDirection = (characterDirection + degrees + 360) % 360;
        
        // Rotate character with smooth animation
        character.style.transition = 'transform 0.3s ease-out';
        character.style.transform = `rotate(${characterDirection}deg)`;
        
        // Wait for animation to complete
        await new Promise(resolve => setTimeout(resolve, 300));
    }
    
    // Make the character jump
    async function jumpCharacter() {
        playSound('move');
        
        // Animate jump with more realistic curve
        character.style.transition = 'transform 0.2s cubic-bezier(0.5, 1, 0.5, 1)';
        character.style.transform = `rotate(${characterDirection}deg) translateY(-30px)`;
        
        await new Promise(resolve => setTimeout(resolve, 200));
        
        character.style.transition = 'transform 0.3s cubic-bezier(0.2, 0.7, 0.3, 1)';
        character.style.transform = `rotate(${characterDirection}deg) translateY(0)`;
        
        await new Promise(resolve => setTimeout(resolve, 300));
    }
    
    // Check if there's an obstacle at a position
    function isObstacleAt(x, y) {
        return obstacles.some(obstacle => {
            return x >= obstacle.x && 
                   x < obstacle.x + obstacle.width && 
                   y >= obstacle.y && 
                   y < obstacle.y + obstacle.height;
        });
    }
    
    // Check if character has reached the target
    function checkWinCondition() {
        return characterPosition.x === targetPosition.x && 
               characterPosition.y === targetPosition.y;
    }
    
    // Handle level completion
    function handleLevelComplete() {
        clearInterval(timerInterval);
        playSound('success');
        
        // Calculate score
        const levelScore = calculateLevelScore();
        score += levelScore;
        scoreDisplay.textContent = score;
        
        // Update level progress
        levelProgress.style.width = '100%';
        
        // Show level complete modal
        completeTime.textContent = formatTime(seconds);
        completeScore.textContent = `+${levelScore}`;
        
        // Set stars based on performance (simplified)
        const stars = Math.min(3, Math.floor(levelScore / 50));
        completeStars.innerHTML = '<i class="fas fa-star"></i>'.repeat(stars);
        
        // Set feedback message
        const feedbackMessages = [
            "حاول مرة أخرى! يمكنك أن تفعل أفضل من ذلك.",
            "جيد! لكن هناك مجال للتحسين.",
            "أحسنت! لقد أكملت المستوى بنجاح!",
            "ممتاز! أداء رائع!"
        ];
        feedbackMessage.textContent = feedbackMessages[stars];
        
        // Show modal with animation
        levelCompleteModal.style.display = 'flex';
        
        // If this was the last level, show game complete modal
        if (currentLevel === totalLevels) {
            setTimeout(() => {
                levelCompleteModal.style.display = 'none';
                showGameComplete();
            }, 3000);
        }
    }
    
    // Calculate score for the completed level
    function calculateLevelScore() {
        // Base score for completing the level
        let levelScore = 100;
        
        // Time bonus (faster is better)
        const timeBonus = Math.max(0, 50 - Math.floor(seconds / 5));
        levelScore += timeBonus;
        
        // Block count penalty (fewer blocks is better)
        const blockCount = workspace.children.length;
        const blockPenalty = Math.min(30, blockCount * 5);
        levelScore -= blockPenalty;
        
        // Minimum score
        return Math.max(50, levelScore);
    }
    
    // Show game complete modal
    function showGameComplete() {
        finalScore.textContent = score;
        fastestLevel.textContent = "00:15"; // This would be tracked in a real game
        bestRating.textContent = "3 نجوم"; // This would be tracked in a real game
        gameCompleteModal.style.display = 'flex';
    }
    
    // Reset the current level
    function resetLevel() {
        playSound('click');
        loadLevel(currentLevel);
    }
    
    // Go to the next level
    function goToNextLevel() {
        playSound('click');
        levelCompleteModal.style.display = 'none';
        
        if (currentLevel < totalLevels) {
            currentLevel++;
            currentLevelDisplay.textContent = currentLevel;
            loadLevel(currentLevel);
        }
    }
    
    // Restart the game from level 1
    function restartGame() {
        playSound('click');
        gameCompleteModal.style.display = 'none';
        
        currentLevel = 1;
        score = 0;
        currentLevelDisplay.textContent = currentLevel;
        scoreDisplay.textContent = score;
        
        loadLevel(currentLevel);
    }
    
    // Share results (placeholder functionality)
    function shareResults() {
        playSound('click');
        alert('مشاركة النتائج: لقد حصلت على ' + score + ' نقطة في لعبة مغامرة الكود!');
    }
    
    // Show hint for current level
    function showHint() {
        playSound('click');
        const hint = levels[currentLevel - 1].hint;
        document.getElementById('hint-text').textContent = hint;
        hintModal.style.display = 'flex';
    }
    
    // Show error message
    function showError(message) {
        playSound('error');
        
        const errorEl = document.createElement('div');
        errorEl.className = 'alert-error animate__animated animate__fadeInDown';
        errorEl.innerHTML = `
            <i class="fas fa-exclamation-circle"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(errorEl);
        
        setTimeout(() => {
            errorEl.classList.add('animate__fadeOutUp');
            setTimeout(() => errorEl.remove(), 1000);
        }, 3000);
    }
    
    // Toggle sound on/off
    function toggleSound() {
        isSoundOn = !isSoundOn;
        
        if (isSoundOn) {
            soundToggle.innerHTML = '<i class="fas fa-volume-up"></i>';
            Howler.volume(1.0);
        } else {
            soundToggle.innerHTML = '<i class="fas fa-volume-mute"></i>';
            Howler.volume(0);
        }
        
        playSound('click');
    }
    
    // Play a sound
    function playSound(sound) {
        if (!isSoundOn) return;
        sounds[sound].play();
    }
    
    // Start the level timer
    function startTimer() {
        clearInterval(timerInterval);
        seconds = 0;
        updateTimerDisplay();
        
        timerInterval = setInterval(() => {
            seconds++;
            updateTimerDisplay();
            
            // Update progress bar (simplified)
            const progress = Math.min(100, (seconds / 60) * 100);
            levelProgress.style.width = `${progress}%`;
        }, 1000);
    }
    
    // Update timer display
    function updateTimerDisplay() {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        timerDisplay.textContent = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }
    
    // Format time as MM:SS
    function formatTime(totalSeconds) {
        const mins = Math.floor(totalSeconds / 60);
        const secs = totalSeconds % 60;
        return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }
    
    // Initialize the game
    initGame();
});