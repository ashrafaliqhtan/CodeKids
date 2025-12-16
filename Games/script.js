// app.js
// حالات اللعبة
const GameState = {
    currentLevel: 1,
    totalLevels: 5,
    score: 0,
    startTime: null,
    timerInterval: null,
    levels: [],
    draggedBlock: null,
    character: null,
    target: null,
    obstacles: [],
    sounds: {
        move: null,
        success: null,
        error: null,
        click: null,
        background: null
    },
    soundEnabled: true,
    fastestLevels: {},
    starRatings: {}
};

// تهيئة الأصوات
function initSounds() {
    GameState.sounds.move = new Howl({
        src: ['https://assets.codepen.io/21542/howler-push.mp3'],
        volume: 0.5
    });
    
    GameState.sounds.success = new Howl({
        src: ['https://assets.codepen.io/21542/howler-level-complete.mp3'],
        volume: 0.7
    });
    
    GameState.sounds.error = new Howl({
        src: ['https://assets.codepen.io/21542/howler-error.mp3'],
        volume: 0.5
    });
    
    GameState.sounds.click = new Howl({
        src: ['https://assets.codepen.io/21542/howler-click.mp3'],
        volume: 0.3
    });
    
    GameState.sounds.background = new Howl({
        src: ['https://assets.codepen.io/21542/howler-bg-music.mp3'],
        volume: 0.2,
        loop: true
    });
}

// تعريف المستويات
function initLevels() {
    GameState.levels = [
        // المستوى 1: أساسيات الحركة
        {
            title: "الحركة البسيطة",
            description: "مرحبًا بك في مغامرة البرمجة! مهمتك الأولى هي توجيه الكلب إلى العلم باستخدام كتلة 'تحرك للأمام'.",
            hint: "اسحب كتلة 'تحرك للأمام' إلى منطقة العمل ثم اضغط على زر التشغيل.",
            startPos: { x: 100, y: 150 },
            targetPos: { x: 300, y: 150 },
            blocks: [
                { type: 'move_forward', icon: '⬆️', label: 'تحرك للأمام' }
            ],
            solution: ['move_forward', 'move_forward'],
            gridSize: 50
        },
        // المستوى 2: التكرار
// في دالة initLevels، تعديل المستوى الثاني ليصبح:
{
    title: "التكرار الذكي",
    description: "استخدم كتلة 'كرر' لتجنب تكرار الكتل. وفر الوقت وجعل الكود أنظف!",
    hint: "استخدم كتلة 'كرر' وضع بداخلها كتلة 'تحرك للأمام' ثم اضبط العدد على 2.",
    startPos: { x: 100, y: 150 },
    targetPos: { x: 400, y: 150 },
    blocks: [
        { type: 'move_forward', icon: '⬆️', label: 'تحرك للأمام' },
        { type: 'repeat', icon: '🔁', label: 'كرر' },
        { type: 'repeat_end', icon: '🔚', label: 'نهاية التكرار' }
    ],
    solution: ['repeat', 'move_forward', 'repeat_end'],
    gridSize: 50
},
        // المستوى 3: اتجاهات الحركة
        {
            title: "طرق متعددة",
            description: "استخدم كتل الحركة المختلفة للوصول إلى الهدف. حاول تجنب العقبات!",
            hint: "ستحتاج إلى استخدام كتل 'تحرك للأمام' و'تحرك لليسار' و'تحرك لليمين' معًا.",
            startPos: { x: 100, y: 150 },
            targetPos: { x: 400, y: 250 },
            obstacles: [
                { x: 200, y: 140, width: 200, height: 20 }
            ],
            blocks: [
                { type: 'move_forward', icon: '⬆️', label: 'تحرك للأمام' },
                { type: 'move_left', icon: '⬅️', label: 'تحرك لليسار' },
                { type: 'move_right', icon: '➡️', label: 'تحرك لليمين' }
            ],
            solution: ['move_forward', 'move_right', 'move_forward', 'move_right'],
            gridSize: 50
        },
        // المستوى 4: الشروط
        {
            title: "اتخاذ القرارات",
            description: "استخدم كتلة 'إذا' لتجنب العقبات. تحقق من وجود عقبة قبل التحرك!",
            hint: "تحقق إذا كان هناك عقبة أمامك باستخدام كتلة 'إذا' ثم اختر الاتجاه المناسب.",
            startPos: { x: 100, y: 150 },
            targetPos: { x: 450, y: 150 },
            obstacles: [
                { x: 200, y: 140, width: 20, height: 20 }
            ],
            blocks: [
                { type: 'move_forward', icon: '⬆️', label: 'تحرك للأمام' },
                { type: 'move_right', icon: '➡️', label: 'تحرك لليمين' },
                { type: 'if', icon: '❓', label: 'إذا' },
                { type: 'condition_obstacle', icon: '👀', label: 'عقبة أمامي؟' }
            ],
            solution: [
                'move_forward',
                'if_start',
                'condition_obstacle',
                'move_right',
                'if_end',
                'move_forward'
            ],
            gridSize: 50
        },
        // المستوى 5: الدوال
        {
            title: "الدوال المخصصة",
            description: "أنشئ دالة مخصصة لتكرار نمط الحركة. هذا سيجعل الكود أكثر تنظيماً!",
            hint: "استخدم كتلة 'أنشئ دالة' لتعريف نمط الحركة ثم استدع الدالة مرتين.",
            startPos: { x: 100, y: 150 },
            targetPos: { x: 450, y: 250 },
            obstacles: [
                { x: 200, y: 140, width: 20, height: 20 },
                { x: 300, y: 240, width: 20, height: 20 }
            ],
            blocks: [
                { type: 'move_forward', icon: '⬆️', label: 'تحرك للأمام' },
                { type: 'move_right', icon: '➡️', label: 'تحرك لليمين' },
                { type: 'function', icon: '🧩', label: 'أنشئ دالة' },
                { type: 'call_function', icon: '📞', label: 'استدع دالة' }
            ],
            solution: [
                'function_start:pattern',
                'move_forward',
                'move_right',
                'function_end',
                'call_function:pattern',
                'call_function:pattern'
            ],
            gridSize: 50
        }
    ];
    
    document.getElementById('total-levels').textContent = GameState.totalLevels;
}

// تهيئة اللعبة
function initGame() {
    initSounds();
    initLevels();
    loadLevel(GameState.currentLevel);
    setupEventListeners();
    
    // بدء تشغيل الموسيقى التصويرية
    if (GameState.soundEnabled) {
        GameState.sounds.background.play();
    }
}

// تحميل المستوى
function loadLevel(levelNum) {
    // إيقاف المؤقت إذا كان يعمل
    stopTimer();
    
    const level = GameState.levels[levelNum - 1];
    if (!level) {
        showGameComplete();
        return;
    }
    
    // تحديث واجهة المستخدم
    document.getElementById('current-level').textContent = levelNum;
    document.getElementById('mission-text').textContent = level.description;
    document.getElementById('level-complete-modal').style.display = 'none';
    document.getElementById('level-progress').style.width = '0%';
    
    // مسح لوحة اللعبة
    const gameBoard = document.getElementById('game-board');
    gameBoard.innerHTML = '<div class="grid-overlay"></div>';
    
    // إنشاء الشخصية
    GameState.character = document.createElement('div');
    GameState.character.className = 'character-container';
    GameState.character.id = 'character-container';
    GameState.character.style.left = `${level.startPos.x}px`;
    GameState.character.style.top = `${level.startPos.y}px`;
    gameBoard.appendChild(GameState.character);
    
    // إنشاء عنصر الشخصية الداخلي
    const characterInner = document.createElement('div');
    characterInner.className = 'character';
    characterInner.innerHTML = `
        <div class="character-face">
            <div class="eyes">
                <div class="eye left"></div>
                <div class="eye right"></div>
            </div>
            <div class="mouth"></div>
        </div>
    `;
    GameState.character.appendChild(characterInner);
    
    // إنشاء الهدف
    GameState.target = document.createElement('div');
    GameState.target.className = 'target';
    GameState.target.innerHTML = '<i class="fas fa-flag"></i>';
    GameState.target.style.left = `${level.targetPos.x}px`;
    GameState.target.style.top = `${level.targetPos.y}px`;
    gameBoard.appendChild(GameState.target);
    
    // إنشاء العقبات
    GameState.obstacles = [];
    level.obstacles?.forEach(obs => {
        const obstacle = document.createElement('div');
        obstacle.className = 'obstacle';
        obstacle.style.left = `${obs.x}px`;
        obstacle.style.top = `${obs.y}px`;
        obstacle.style.width = `${obs.width}px`;
        obstacle.style.height = `${obs.height}px`;
        gameBoard.appendChild(obstacle);
        GameState.obstacles.push(obstacle);
    });
    
    // تحميل كتل البرمجة المتاحة
    const blocksContainer = document.getElementById('blocks-container');
    blocksContainer.innerHTML = '';
    
    level.blocks.forEach(block => {
        const blockElement = createBlock(block);
        blocksContainer.appendChild(blockElement);
    });
    
    // مسح منطقة العمل
    document.getElementById('workspace').innerHTML = '';
    
    // بدء المؤقت
    startTimer();
    
    // تحديث البحث عن الكتل
    setupSearch();
}

// إنشاء كتلة برمجة
function createBlock(blockData) {
    const block = document.createElement('div');
    block.className = 'block';
    block.draggable = true;
    block.dataset.type = blockData.type;
    
    const icon = document.createElement('span');
    icon.className = 'block-icon';
    icon.textContent = blockData.icon;
    
    const label = document.createElement('span');
    label.textContent = blockData.label;
    
    block.appendChild(icon);
    block.appendChild(label);
    
    // إضافة تأثير السحب
    block.addEventListener('dragstart', (e) => {
        e.target.classList.add('dragging');
        GameState.draggedBlock = {
            type: blockData.type,
            icon: blockData.icon,
            label: blockData.label
        };
        if (GameState.soundEnabled) GameState.sounds.click.play();
    });
    
    block.addEventListener('dragend', (e) => {
        e.target.classList.remove('dragging');
    });
    
    return block;
}

// إعداد مستمعي الأحداث
function setupEventListeners() {
    // سحب وإسقاط الكتل
    const workspace = document.getElementById('workspace');
    
    workspace.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
    });
    
    workspace.addEventListener('drop', (e) => {
        e.preventDefault();
        if (GameState.draggedBlock) {
            const newBlock = createWorkspaceBlock(GameState.draggedBlock);
            
            // تحديد موقع الإسقاط
            const dropY = e.clientY;
            const workspaceBlocks = Array.from(workspace.children);
            let insertBefore = null;
            
            for (const block of workspaceBlocks) {
                const rect = block.getBoundingClientRect();
                if (dropY < rect.top + rect.height / 2) {
                    insertBefore = block;
                    break;
                }
            }
            
            if (insertBefore) {
                workspace.insertBefore(newBlock, insertBefore);
            } else {
                workspace.appendChild(newBlock);
            }
            
            if (GameState.soundEnabled) GameState.sounds.click.play();
        }
    });
    
    // تشغيل البرنامج
    document.getElementById('run-btn').addEventListener('click', () => {
        if (GameState.soundEnabled) GameState.sounds.click.play();
        runProgram();
    });
    
    // إعادة تعيين
    document.getElementById('reset-btn').addEventListener('click', () => {
        if (GameState.soundEnabled) GameState.sounds.click.play();
        document.getElementById('workspace').innerHTML = '';
    });
    
    // مسح الكل
    document.getElementById('clear-btn').addEventListener('click', () => {
        if (GameState.soundEnabled) GameState.sounds.click.play();
        document.getElementById('workspace').innerHTML = '';
    });
    
    // عرض المساعدة
    document.getElementById('hint-btn').addEventListener('click', showHint);
    document.getElementById('help-btn').addEventListener('click', showHelp);
    document.getElementById('close-hint-btn').addEventListener('click', () => {
        document.getElementById('hint-modal').style.display = 'none';
        if (GameState.soundEnabled) GameState.sounds.click.play();
    });
    
    // الانتقال للمستوى التالي
    document.getElementById('next-level-btn').addEventListener('click', () => {
        GameState.currentLevel++;
        loadLevel(GameState.currentLevel);
        if (GameState.soundEnabled) GameState.sounds.click.play();
    });
    
    // إعادة تشغيل اللعبة
    document.getElementById('restart-game-btn').addEventListener('click', () => {
        GameState.currentLevel = 1;
        GameState.score = 0;
        document.getElementById('score').textContent = '0';
        loadLevel(GameState.currentLevel);
        document.getElementById('game-complete-modal').style.display = 'none';
        if (GameState.soundEnabled) GameState.sounds.click.play();
    });
    
    // تبديل الصوت
    document.getElementById('sound-toggle').addEventListener('click', toggleSound);
}

// إنشاء كتلة في منطقة العمل
function createWorkspaceBlock(blockData) {
    const block = document.createElement('div');
    block.className = 'workspace-block';
    block.dataset.type = blockData.type;
    
    const icon = document.createElement('span');
    icon.className = 'workspace-block-icon';
    icon.textContent = blockData.icon;
    
    const label = document.createElement('span');
    label.textContent = blockData.label;
    
    const removeBtn = document.createElement('span');
    removeBtn.className = 'workspace-block-remove';
    removeBtn.innerHTML = '&times;';
    removeBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        block.remove();
        if (GameState.soundEnabled) GameState.sounds.click.play();
    });
    
    block.appendChild(icon);
    block.appendChild(label);
    block.appendChild(removeBtn);
    
    return block;
}

// إعداد البحث عن الكتل
function setupSearch() {
    const searchInput = document.querySelector('.search-box input');
    searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        const blocks = document.querySelectorAll('.block');
        
        blocks.forEach(block => {
            const label = block.textContent.toLowerCase();
            if (label.includes(searchTerm)) {
                block.style.display = 'flex';
            } else {
                block.style.display = 'none';
            }
        });
    });
}

// تشغيل البرنامج
async function runProgram() {
    const workspace = document.getElementById('workspace');
    const blocks = Array.from(workspace.children);
    const character = document.getElementById('character-container');
    const level = GameState.levels[GameState.currentLevel - 1];
    
    // إعادة تعيين موقع الشخصية
    resetCharacterPosition();
    
    // تعطيل الأزرار أثناء التنفيذ
    document.getElementById('run-btn').disabled = true;
    document.getElementById('reset-btn').disabled = true;
    document.getElementById('clear-btn').disabled = true;
    
    // تنفيذ الكتل
    try {
        await executeBlocks(blocks);
        
        // التحقق من الوصول للهدف
        if (checkCollision(character, GameState.target)) {
            completeLevel();
        } else {
            showError("لم تصل إلى الهدف بعد. حاول مرة أخرى!");
        }
    } catch (error) {
        showError(error.message);
    } finally {
        // تمكين الأزرار بعد الانتهاء
        document.getElementById('run-btn').disabled = false;
        document.getElementById('reset-btn').disabled = false;
        document.getElementById('clear-btn').disabled = false;
    }
}

// تنفيذ الكتل
async function executeBlocks(blocks) {
    const level = GameState.levels[GameState.currentLevel - 1];
    let index = 0;
    let progress = 0;
    const totalBlocks = blocks.length;
    
    while (index < blocks.length) {
        const block = blocks[index];
        block.classList.add('active');
        
        const blockType = block.dataset.type;
        
        switch(blockType) {
            case 'move_forward':
                await moveCharacter('forward', level.gridSize);
                index++;
                break;
                
            case 'move_left':
                await moveCharacter('left', level.gridSize);
                index++;
                break;
                
            case 'move_right':
                await moveCharacter('right', level.gridSize);
                index++;
                break;
                
            case 'repeat':
                // معالجة التكرار
                const repeatCount = 2; // يمكن جعل هذا قابل للتعديل من الواجهة
                const repeatStartIndex = index;
                let repeatEndIndex = -1;
                
                // البحث عن نهاية الكتلة المتكررة
                for (let i = index + 1; i < blocks.length; i++) {
                    if (blocks[i].dataset.type === 'repeat_end') {
                        repeatEndIndex = i;
                        break;
                    }
                }
                
                if (repeatEndIndex === -1) {
                    throw new Error("لا يوجد نهاية للكتلة المتكررة");
                }
                
                // تنفيذ الكتل داخل التكرار
                for (let r = 0; r < repeatCount; r++) {
                    for (let j = index + 1; j < repeatEndIndex; j++) {
                        const innerBlock = blocks[j];
                        innerBlock.classList.add('active');
                        
                        const innerBlockType = innerBlock.dataset.type;
                        
                        switch(innerBlockType) {
                            case 'move_forward':
                                await moveCharacter('forward', level.gridSize);
                                break;
                            case 'move_left':
                                await moveCharacter('left', level.gridSize);
                                break;
                            case 'move_right':
                                await moveCharacter('right', level.gridSize);
                                break;
                            // يمكن إضافة حالات أخرى للكتل الداخلية
                        }
                        
                        innerBlock.classList.remove('active');
                        
                        // التحقق من التصادم مع العقبات
                        if (checkObstacleCollision()) {
                            throw new Error("اصطدمت بعقبة! حاول مرة أخرى.");
                        }
                    }
                }
                
                index = repeatEndIndex + 1; // الانتقال إلى ما بعد نهاية التكرار
                break;
                
            case 'repeat_end':
                // تجاهل نهاية التكرار (يتم التعامل معها في كتلة repeat)
                index++;
                break;
                
            default:
                index++;
        }
        
        block.classList.remove('active');
        
        // تحديث شريط التقدم
        progress = Math.floor((index / totalBlocks) * 100);
        document.getElementById('level-progress').style.width = `${progress}%`;
        
        // التحقق من التصادم مع العقبات
        if (checkObstacleCollision()) {
            throw new Error("اصطدمت بعقبة! حاول مرة أخرى.");
        }
    }
}

// حركة الشخصية
async function moveCharacter(direction, distance) {
    const character = document.getElementById('character-container');
    let newX = parseInt(character.style.left);
    let newY = parseInt(character.style.top);
    
    switch(direction) {
        case 'forward':
            newX += distance;
            break;
        case 'left':
            newY -= distance;
            break;
        case 'right':
            newY += distance;
            break;
    }
    
    // تطبيق تأثير الحركة
    character.classList.add('animate-character');
    if (GameState.soundEnabled) GameState.sounds.move.play();
    
    // الانتقال السلس
    await new Promise(resolve => {
        character.style.transition = 'left 0.5s ease-out, top 0.5s ease-out';
        character.style.left = `${newX}px`;
        character.style.top = `${newY}px`;
        
        character.addEventListener('transitionend', function handler() {
            character.removeEventListener('transitionend', handler);
            character.classList.remove('animate-character');
            character.style.transition = '';
            resolve();
        }, { once: true });
    });
}

// التحقق من التصادم
function checkCollision(element1, element2) {
    const rect1 = element1.getBoundingClientRect();
    const rect2 = element2.getBoundingClientRect();
    
    return !(
        rect1.right < rect2.left || 
        rect1.left > rect2.right || 
        rect1.bottom < rect2.top || 
        rect1.top > rect2.bottom
    );
}

// التحقق من التصادم مع العقبات
function checkObstacleCollision() {
    const character = document.getElementById('character-container');
    return GameState.obstacles.some(obstacle => 
        checkCollision(character, obstacle)
    );
}

// إعادة تعيين موقع الشخصية
function resetCharacterPosition() {
    const level = GameState.levels[GameState.currentLevel - 1];
    const character = document.getElementById('character-container');
    character.style.left = `${level.startPos.x}px`;
    character.style.top = `${level.startPos.y}px`;
}

// عرض الخطأ
function showError(message) {
    if (GameState.soundEnabled) GameState.sounds.error.play();
    
    // إنشاء عنصر تنبيه
    const alert = document.createElement('div');
    alert.className = 'alert-error animate__animated animate__shakeX';
    alert.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(alert);
    
    // إزالة التنبيه بعد 3 ثواني
    setTimeout(() => {
        alert.classList.add('animate__fadeOut');
        setTimeout(() => alert.remove(), 500);
    }, 3000);
}

// عرض المساعدة
function showHint() {
    const level = GameState.levels[GameState.currentLevel - 1];
    document.getElementById('hint-text').textContent = level.hint;
    document.getElementById('hint-modal').style.display = 'flex';
    if (GameState.soundEnabled) GameState.sounds.click.play();
}

// عرض مساعدة عامة
function showHelp() {
    const helpText = `
        <h4>كيفية اللعب:</h4>
        <ol>
            <li>اسحب كتل البرمجة من اليمين إلى منطقة العمل في الوسط</li>
            <li>رتب الكتل بالترتيب الصحيح لحل المهمة</li>
            <li>اضغط على زر "تشغيل البرنامج" لتنفيذ الكود</li>
            <li>إذا وصلت إلى العلم، سوف تكمل المستوى!</li>
        </ol>
        <p>استخدم زر "مساعدة" للحصول على تلميحات إذا واجهتك صعوبة.</p>
    `;
    
    document.getElementById('hint-text').innerHTML = helpText;
    document.getElementById('hint-modal').style.display = 'flex';
    if (GameState.soundEnabled) GameState.sounds.click.play();
}

// إكمال المستوى
function completeLevel() {
    stopTimer();
    
    // حساب النقاط والوقت
    const timeElapsed = calculateTimeElapsed();
    const pointsEarned = 100 + Math.max(0, 50 - Math.floor(timeElapsed / 1000)) * 2;
    GameState.score += pointsEarned;
    
    // حفظ أسرع وقت لهذا المستوى
    if (!GameState.fastestLevels[GameState.currentLevel] || 
        timeElapsed < GameState.fastestLevels[GameState.currentLevel]) {
        GameState.fastestLevels[GameState.currentLevel] = timeElapsed;
    }
    
    // تحديث واجهة النجاح
    document.getElementById('complete-time').textContent = formatTime(timeElapsed);
    document.getElementById('complete-score').textContent = `+${pointsEarned}`;
    document.getElementById('score').textContent = GameState.score;
    
    // عرض رسالة التغذية الراجعة
    const feedbackMessages = [
        "ممتاز! أنت مبرمج رائع!",
        "عمل رائع! تستطيع حل أي مشكلة!",
        "أحسنت! مستواك يتحسن باستمرار!",
        "براعة! لقد وجدت الحل المثالي!"
    ];
    const randomFeedback = feedbackMessages[Math.floor(Math.random() * feedbackMessages.length)];
    document.getElementById('feedback-message').textContent = randomFeedback;
    
    // عرض النجوم (التقييم)
    const stars = calculateStars(timeElapsed);
    document.getElementById('complete-stars').innerHTML = stars;
    
    // حفظ تقييم النجوم لهذا المستوى
    GameState.starRatings[GameState.currentLevel] = stars.match(/fa-star/g)?.length || 0;
    
    // عرض نافذة النجاح
    document.getElementById('level-complete-modal').style.display = 'flex';
    if (GameState.soundEnabled) GameState.sounds.success.play();
    
    // تأثيرات للشخصية
    const character = document.querySelector('.character');
    character.classList.add('success-animation');
}

// حساب النجوم حسب الأداء
function calculateStars(timeElapsed) {
    const seconds = Math.floor(timeElapsed / 1000);
    
    if (seconds < 15) return '<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>';
    if (seconds < 30) return '<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i>';
    return '<i class="fas fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>';
}

// بدء المؤقت
function startTimer() {
    GameState.startTime = new Date();
    GameState.timerInterval = setInterval(updateTimer, 1000);
    updateTimer();
}

// تحديث المؤقت
function updateTimer() {
    if (!GameState.startTime) return;
    
    const currentTime = new Date();
    const elapsed = currentTime - GameState.startTime;
    document.getElementById('timer').textContent = formatTime(elapsed);
}

// إيقاف المؤقت
function stopTimer() {
    if (GameState.timerInterval) {
        clearInterval(GameState.timerInterval);
        GameState.timerInterval = null;
    }
}

// حساب الوقت المنقضي
function calculateTimeElapsed() {
    if (!GameState.startTime) return 0;
    return new Date() - GameState.startTime;
}

// تنسيق الوقت
function formatTime(milliseconds) {
    const totalSeconds = Math.floor(milliseconds / 1000);
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    
    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
}

// عرض نهاية اللعبة
function showGameComplete() {
    // حساب إجمالي النجوم
    const totalStars = Object.values(GameState.starRatings).reduce((sum, stars) => sum + stars, 0);
    
    // العثور على أسرع مستوى
    let fastestLevel = null;
    let fastestTime = Infinity;
    for (const [level, time] of Object.entries(GameState.fastestLevels)) {
        if (time < fastestTime) {
            fastestTime = time;
            fastestLevel = level;
        }
    }
    
    // تحديث واجهة نهاية اللعبة
    document.getElementById('final-score').textContent = GameState.score;
    document.getElementById('fastest-level').textContent = formatTime(fastestTime);
    document.getElementById('best-rating').textContent = `${totalStars} نجوم`;
    
    // عرض نافذة نهاية اللعبة
    document.getElementById('game-complete-modal').style.display = 'flex';
    if (GameState.soundEnabled) GameState.sounds.success.play();
}

// تبديل الصوت
function toggleSound() {
    GameState.soundEnabled = !GameState.soundEnabled;
    const soundBtn = document.getElementById('sound-toggle');
    
    if (GameState.soundEnabled) {
        soundBtn.innerHTML = '<i class="fas fa-volume-up"></i>';
        GameState.sounds.background.play();
    } else {
        soundBtn.innerHTML = '<i class="fas fa-volume-mute"></i>';
        GameState.sounds.background.pause();
    }
}

// بدء اللعبة عند تحميل الصفحة
window.onload = initGame;