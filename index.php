<?php
session_start();
require 'db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: chat.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            header("Location: chat.php");
            exit;
        } else {
            $error = "Invalid credentials.";
        }
    } elseif (isset($_POST['register'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $password]);

            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['name'] = $name;
            header("Location: chat.php");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Email already registered.";
            } else {
                $error = "Registration failed.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chat - Login</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="auth-container">
        <div class="glass-panel" id="login-box">
            <h1 class="auth-title">AI Chat</h1>
            <?php if ($error): ?>
                <div style="color: #ff4444; text-align: center; margin-bottom: 15px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <input type="email" name="email" class="form-input" placeholder="Email Address" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" class="form-input" placeholder="Password" required>
                </div>
                <button type="submit" name="login" class="btn-neon">Log In</button>
            </form>

            <div class="switch-auth">
                Don't have an account? <a onclick="toggleAuth()">Sign Up</a>
            </div>
        </div>

        <div class="glass-panel" id="register-box" style="display: none;">
            <h1 class="auth-title">Sign Up</h1>

            <form method="POST">
                <div class="form-group">
                    <input type="text" name="name" class="form-input" placeholder="Full Name" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" class="form-input" placeholder="Email Address" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" class="form-input" placeholder="Password" required>
                </div>
                <button type="submit" name="register" class="btn-neon">Create Account</button>
            </form>

            <div class="switch-auth">
                Already have an account? <a onclick="toggleAuth()">Log In</a>
            </div>
        </div>
    </div>

    <script>
        function toggleAuth() {
            const loginBox = document.getElementById('login-box');
            const registerBox = document.getElementById('register-box');
            if (loginBox.style.display === 'none') {
                loginBox.style.display = 'block';
                registerBox.style.display = 'none';
            } else {
                loginBox.style.display = 'none';
                registerBox.style.display = 'block';
            }
        }
    </script>
</body>

</html>
