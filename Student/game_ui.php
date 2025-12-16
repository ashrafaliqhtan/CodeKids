<?php
session_start();
include('../dbConnection.php');
include('game_functions.php');

// Check authentication
if(!isset($_SESSION['is_login'])) {
    header("Location: ../index.php");
    exit();
}

// Get game data
$lesson_id = isset($_GET['lesson_id']) ? $_GET['lesson_id'] : null;
$game_id = getLessonGameId($conn, $lesson_id);
$game_data = $game_id ? getGameQuestions($conn, $game_id) : null;
$game_completed = hasCompletedGame($conn, $_SESSION['user_id'], $lesson_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programming Adventure | CodeKids</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS from previous section goes here */
        /* Full CSS available in the download package */
    </style>
</head>
<body>
    <div class="game-container">
        <div class="game-header">
            <div class="game-title">Programming Adventure!</div>
            <div class="game-progress">
                <span id="progressText">Question 1/20</span>
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
            </div>
        </div>
        
        <div class="game-body">
            <div class="character">
                <img src="images/character-normal.png" alt="Learning Character" id="characterImg">
                <div class="speech-bubble" id="hintText">Are you ready for the challenge?</div>
            </div>
            
            <div class="question-container">
                <div class="question-text" id="questionText"></div>
                <div class="options-container" id="optionsContainer"></div>
                <button class="help-btn" onclick="showHint()">
                    <i class="fas fa-lightbulb"></i> Get Hint
                </button>
            </div>
        </div>
        
        <div class="reward-effects">
            <div class="confetti"></div>
            <div class="stars"></div>
        </div>
        
        <div class="result-screen" id="resultScreen">
            <h2 class="result-title">Congratulations!</h2>
            <div class="result-score" id="finalScore">0%</div>
            <p class="result-message" id="resultMessage"></p>
            <button class="next-btn" onclick="exitGame()">Continue</button>
        </div>
    </div>

    <script>
    // Game data from PHP
    const gameData = <?php echo json_encode($game_data); ?>;
    const gameCompleted = <?php echo $game_completed ? 'true' : 'false'; ?>;
    const userId = <?php echo $_SESSION['user_id']; ?>;
    const gameId = <?php echo $game_id ?: 'null'; ?>;
    const lessonId = <?php echo $lesson_id ?: 'null'; ?>;

    // Game state
    let currentGame = {
        section: 'true_false',
        questionIndex: 0,
        score: 0,
        hintsUsed: 0,
        totalQuestions: 0
    };

    // Initialize game
    function initGame() {
        if (!gameData) {
            alert('Game data not found!');
            return;
        }

        currentGame.totalQuestions = gameData.true_false.length + 
                                  gameData.multiple_choice.length + 
                                  gameData.multi_select.length;
        
        showQuestion();
        updateProgress();
        speak("Hello! I'm Robo, your programming guide. Are you ready to learn?");
    }

    // Show current question
    function showQuestion() {
        const question = gameData[currentGame.section][currentGame.questionIndex];
        const questionText = document.getElementById('questionText');
        const optionsContainer = document.getElementById('optionsContainer');
        
        questionText.textContent = question.question;
        optionsContainer.innerHTML = '';
        
        if (currentGame.section === 'true_false') {
            optionsContainer.innerHTML = `
                <button class="option true-btn" onclick="checkAnswer(true)">True</button>
                <button class="option false-btn" onclick="checkAnswer(false)">False</button>
            `;
        } 
        else if (currentGame.section === 'multiple_choice') {
            question.options.forEach((option, index) => {
                const btn = document.createElement('button');
                btn.className = 'option';
                btn.textContent = option;
                btn.onclick = () => checkAnswer(index);
                optionsContainer.appendChild(btn);
            });
        }
        else if (currentGame.section === 'multi_select') {
            question.options.forEach((option, index) => {
                const label = document.createElement('label');
                label.className = 'option checkbox-option';
                label.innerHTML = `
                    <input type="checkbox" value="${index}">
                    ${option}
                `;
                optionsContainer.appendChild(label);
            });
            
            const submitBtn = document.createElement('button');
            submitBtn.className = 'submit-btn';
            submitBtn.textContent = 'Submit Answer';
            submitBtn.onclick = checkMultiSelectAnswer;
            optionsContainer.appendChild(submitBtn);
        }
        
        setCharacterMood('normal');
        speak("Try to answer this question!");
    }

    // Check answer for true/false and multiple choice
    function checkAnswer(userAnswer) {
        const question = gameData[currentGame.section][currentGame.questionIndex];
        const options = document.querySelectorAll('.option');
        let isCorrect = false;
        
        options.forEach(opt => opt.style.pointerEvents = 'none');
        
        if (currentGame.section === 'true_false') {
            isCorrect = userAnswer === question.correctAnswer;
        } 
        else if (currentGame.section === 'multiple_choice') {
            isCorrect = userAnswer === question.correctIndex;
        }
        
        if (isCorrect) {
            markCorrectAnswer(userAnswer);
            currentGame.score++;
            setCharacterMood('happy');
            speak("Great job! Correct answer!");
            playSound('correct');
            showConfetti();
        } else {
            markWrongAnswer(userAnswer, question.correctIndex);
            setCharacterMood('confused');
            speak("Not quite right! The correct answer is highlighted.");
            playSound('wrong');
        }
        
        setTimeout(nextQuestion, 2000);
    }

    // Check multi-select answers
    function checkMultiSelectAnswer() {
        const question = gameData[currentGame.section][currentGame.questionIndex];
        const selected = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
                            .map(el => parseInt(el.value));
        
        const isCorrect = selected.length === question.correctIndices.length &&
                          question.correctIndices.every(i => selected.includes(i));
        
        if (isCorrect) {
            currentGame.score++;
            setCharacterMood('happy');
            speak("Excellent! All correct answers selected!");
            playSound('correct');
            showConfetti();
        } else {
            setCharacterMood('confused');
            speak("Some answers are missing. The correct ones are highlighted.");
            playSound('wrong');
            highlightCorrectAnswers(question.correctIndices);
        }
        
        setTimeout(nextQuestion, 2000);
    }

    // Helper functions
    function markCorrectAnswer(index) {
        const options = document.querySelectorAll('.option');
        options[index].classList.add('correct');
    }

    function markWrongAnswer(userIndex, correctIndex) {
        const options = document.querySelectorAll('.option');
        options[userIndex].classList.add('wrong');
        
        if (correctIndex !== undefined) {
            options[correctIndex].classList.add('correct');
        }
    }

    function highlightCorrectAnswers(correctIndices) {
        const options = document.querySelectorAll('.checkbox-option');
        correctIndices.forEach(i => {
            options[i].classList.add('correct');
        });
    }

    function nextQuestion() {
        currentGame.questionIndex++;
        currentGame.hintsUsed = 0;
        
        // Move to next section if needed
        if (currentGame.questionIndex >= gameData[currentGame.section].length) {
            if (currentGame.section === 'true_false') {
                currentGame.section = 'multiple_choice';
            } else if (currentGame.section === 'multiple_choice') {
                currentGame.section = 'multi_select';
            } else {
                endGame();
                return;
            }
            currentGame.questionIndex = 0;
        }
        
        showQuestion();
        updateProgress();
    }

    function showHint() {
        if (currentGame.hintsUsed >= 2) {
            speak("You've used all hints for this question!");
            return;
        }
        
        const question = gameData[currentGame.section][currentGame.questionIndex];
        let hint = "";
        
        if (currentGame.hintsUsed === 0) {
            hint = question.hint || question.hint1 || "Think about the keywords in the question";
        } else {
            hint = question.hint2 || "Try eliminating obviously wrong options first";
        }
        
        speak(hint);
        currentGame.hintsUsed++;
        playSound('hint');
    }

    function updateProgress() {
        const progressText = document.getElementById('progressText');
        const progressFill = document.querySelector('.progress-fill');
        
        const questionsDone = currentGame.section === 'true_false' ? currentGame.questionIndex :
                            currentGame.section === 'multiple_choice' ? gameData.true_false.length + currentGame.questionIndex :
                            gameData.true_false.length + gameData.multiple_choice.length + currentGame.questionIndex;
        
        const progress = ((questionsDone + 1) / currentGame.totalQuestions) * 100;
        progressText.textContent = `Question ${questionsDone + 1}/${currentGame.totalQuestions}`;
        progressFill.style.width = `${progress}%`;
    }

    function endGame() {
        const scorePercentage = Math.round((currentGame.score / currentGame.totalQuestions) * 100);
        const resultScreen = document.getElementById('resultScreen');
        const finalScore = document.getElementById('finalScore');
        const resultMessage = document.getElementById('resultMessage');
        
        finalScore.textContent = `${scorePercentage}%`;
        resultMessage.textContent = `You answered ${currentGame.score} out of ${currentGame.totalQuestions} questions correctly!`;
        
        resultScreen.classList.add('show');
        
        if (scorePercentage >= 70) {
            setCharacterMood('excited');
            speak("Awesome! You passed the test. I'm so proud of you!");
            playSound('success');
            showStars();
            saveResultToServer(scorePercentage);
        } else {
            setCharacterMood('happy');
            speak("Good try! You can try again to improve your score.");
            playSound('applause');
        }
    }

    function saveResultToServer(score) {
        if (!gameId) return;
        
        fetch('save_game_result.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId,
                game_id: gameId,
                score: score
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Error saving result:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function exitGame() {
        if (lessonId) {
            window.location.href = `watchCourse.php?course_id=${getCourseId()}&lesson_id=${lessonId}`;
        } else {
            window.location.href = 'myCourses.php';
        }
    }

    // UI Effects
    function setCharacterMood(mood) {
        document.getElementById('characterImg').src = `images/character-${mood}.png`;
    }

    function speak(text) {
        const speechBubble = document.getElementById('hintText');
        speechBubble.textContent = text;
        resetAnimation(speechBubble);
    }

    function playSound(type) {
        // In a real implementation, you would play audio files here
        console.log(`Playing sound: ${type}`);
    }

    function showConfetti() {
        const confetti = document.querySelector('.confetti');
        confetti.style.opacity = '1';
        setTimeout(() => confetti.style.opacity = '0', 2000);
    }

    function showStars() {
        const stars = document.querySelector('.stars');
        stars.style.opacity = '1';
        setTimeout(() => stars.style.opacity = '0', 3000);
    }

    function resetAnimation(element) {
        element.style.animation = 'none';
        void element.offsetWidth;
        element.style.animation = 'bounce 2s infinite';
    }

    function getCourseId() {
        // Extract course_id from URL or use a global variable
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('course_id') || 0;
    }

    // Initialize game when page loads
    window.onload = function() {
        if (gameCompleted) {
            alert("You've already completed this game!");
            exitGame();
        } else {
            initGame();
        }
    };
    </script>
</body>
</html>