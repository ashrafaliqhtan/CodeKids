<?php
// admin-signup.php
session_start();
include('../dbConnection.php');

$error = '';
$success = '';

// Only allow signup if secret code matches
$secretCode = "CODINGKIDS2023";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminName = htmlspecialchars(trim($_POST['admin_name']));
    $adminEmail = filter_var(trim($_POST['admin_email']), FILTER_SANITIZE_EMAIL);
    $adminPass = $_POST['admin_pass'];
    $confirmPass = $_POST['confirm_pass'];
    $inputSecretCode = $_POST['secret_code'];

    // Validation
    if (empty($adminName) || empty($adminEmail) || empty($adminPass)) {
        $error = "🦸 All hero fields are required!";
    } elseif (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $error = "📧 Oops! That's not a proper email castle!";
    } elseif ($adminPass !== $confirmPass) {
        $error = "🔒 Secret codes don't match!";
    } elseif ($inputSecretCode !== $secretCode) {
        $error = "❌ Incorrect magic phrase!";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT admin_email FROM admin WHERE admin_email = ?");
        $stmt->bind_param("s", $adminEmail);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = "✉️ This owl post already exists!";
        } else {
            // Create admin
            $hashedPass = password_hash($adminPass, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO admin (admin_name, admin_email, admin_pass) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $adminName, $adminEmail, $hashedPass);
            
            if ($stmt->execute()) {
                $success = "🎉 Welcome to the hero squad!";
                header("refresh:2;url=index.php");
            } else {
                $error = "🧙♂️ Magic failed! Try again later.";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Hero Squad - CodeKids</title>
    <style>
        :root {
            --primary: #FF6B6B;
            --secondary: #4ECDC4;
            --accent: #FFE66D;
        }

        body {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            font-family: 'Comic Neue', cursive;
            min-height: 100vh;
            display: grid;
            place-items: center;
        }

        .signup-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 90%;
            max-width: 500px;
            text-align: center;
        }

        .hero-mascot {
            width: 120px;
            margin: -80px auto 1rem;
            filter: drop-shadow(0 5px 10px rgba(0,0,0,0.2));
        }

        h1 {
            color: var(--primary);
            font-family: 'Chewy', cursive;
            font-size: 2.2rem;
            margin: 1rem 0;
        }

        .input-group {
            margin: 1.2rem 0;
            text-align: left;
        }

        label {
            display: block;
            color: var(--secondary);
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        input {
            width: 100%;
            padding: 0.8rem;
            border: 3px solid var(--accent);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input:focus {
            border-color: var(--primary);
            transform: scale(1.03);
        }

        .submit-btn {
            background: var(--primary);
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 20px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            background: var(--secondary);
        }

        .message {
            margin: 1rem 0;
            padding: 0.8rem;
            border-radius: 10px;
        }
        .error { background: #FFEBEE; color: #D32F2F; }
        .success { background: #E8F5E9; color: #388E3C; }
    </style>
</head>
<body>
    <div class="signup-container">
        <img src="images/superhero-admin.png" class="hero-mascot" alt="Admin Hero">
        <h1>Join the Teacher Heroes!</h1>
        
        <?php if($error): ?>
            <div class="message error"><?= $error ?></div>
        <?php elseif($success): ?>
            <div class="message success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label>🦸 Hero Name</label>
                <input type="text" name="admin_name" required 
                       pattern="[A-Za-z ]{3,}" 
                       title="3+ letters only">
            </div>

            <div class="input-group">
                <label>📧 Owl Post Address</label>
                <input type="email" name="admin_email" required>
            </div>

            <div class="input-group">
                <label>🔐 Secret Code</label>
                <input type="password" name="admin_pass" 
                       required minlength="8"
                       pattern="^(?=.*[A-Za-z])(?=.*\d).{8,}$"
                       title="8+ chars with letters & numbers">
            </div>

            <div class="input-group">
                <label>🔑 Confirm Secret Code</label>
                <input type="password" name="confirm_pass" required>
            </div>

            <div class="input-group">
                <label>✨ Magic Phrase</label>
                <input type="text" name="secret_code" required>
            </div>

            <button type="submit" class="submit-btn">
                🚀 Become a Hero!
            </button>
        </form>
    </div>

    <script>
        // Add interactive animations
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.style.transform = 'scale(1.02)';
            });
            input.addEventListener('blur', () => {
                input.parentElement.style.transform = 'none';
            });
        });
    </script>
</body>
</html>