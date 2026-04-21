<?php 
include 'db.php';

$error = "";

if(isset($_POST['username']) && isset($_POST['password'])){
    if(!verify_csrf_token($_POST['csrf_token'] ?? '')){
        $error = "❌ Invalid request token. Please try again.";
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        // Validate input
        if(empty($username) || empty($password)){
            $error = "❌ Username and password are required";
        } else {
            $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username=?");
            if(!$stmt){
                $error = "❌ Database error. Please try again later.";
            } else {
                $stmt->bind_param("s", $username);
                if(!$stmt->execute()){
                    $error = "❌ Database error. Please try again later.";
                } else {
                    $result = $stmt->get_result();
                    
                    if($row = $result->fetch_assoc()){
                        // Support both hashed and legacy plain-text passwords
                        $password_match = false;
                        if($row['password'][0] === '$') {
                            // Hashed password (bcrypt)
                            $password_match = verify_password($password, $row['password']);
                        } else {
                            // Legacy plain-text (backwards compatibility only)
                            $password_match = ($password === $row['password']);
                        }
                        
                        if($password_match){
                            $_SESSION['user'] = $row['username'];
                            $_SESSION['user_id'] = $row['id'];
                            $_SESSION['role'] = $row['role'];

                            if($row['role'] == 'admin'){
                                header("Location: dashboard.php");
                            } else {
                                header("Location: pos.php");
                            }
                            exit;
                        } else {
                            $error = "❌ Invalid username or password";
                        }
                    } else {
                        $error = "❌ Invalid username or password";
                    }
                }
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kawaii POS - Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f3e5ff 0%, #e0f7fa 50%, #f1f8e9 100%);
            position: relative;
            overflow: hidden;
        }

        .auth-container::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(201, 160, 220, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .auth-container::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(152, 216, 200, 0.12) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .login-container {
            position: relative;
            z-index: 10;
        }

        .login-box {
            width: 100%;
            max-width: 420px;
            padding: 50px;
            background: white;
            border-radius: 28px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            border: 2px solid rgba(201, 160, 220, 0.2);
            animation: slideUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
            backdrop-filter: blur(10px);
        }

        .login-header {
            margin-bottom: 35px;
        }

        .login-box h2 {
            font-size: 2.2rem;
            margin: 0 0 8px 0;
            background: linear-gradient(135deg, #c9a0dc 0%, #98d8c8 50%, #f7b7a6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-align: center;
            letter-spacing: -0.5px;
            font-weight: 800;
        }

        .auth-subtitle {
            text-align: center;
            color: #999;
            font-size: 0.95rem;
            margin: 0;
            letter-spacing: 0.3px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .login-box input,
        .login-box select {
            width: 100%;
            padding: 14px 16px;
            margin: 0;
            border-radius: 12px;
            border: 2px solid #e8e8e8;
            font-size: 0.95rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            box-sizing: border-box;
            background: linear-gradient(135deg, #fafbfc 0%, #f5f9f8 100%);
        }

        .login-box input::placeholder {
            color: #ccc;
        }

        .login-box input:focus,
        .login-box select:focus {
            outline: none;
            border-color: #c9a0dc;
            background: white;
            box-shadow: 0 0 0 3px rgba(201, 160, 220, 0.15);
            transform: translateY(-2px);
        }

        .login-box form {
            display: flex;
            flex-direction: column;
        }

        .login-box button {
            width: 100%;
            padding: 14px 24px;
            margin-top: 10px;
            background: linear-gradient(135deg, #c9a0dc 0%, #d4a5f5 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 700;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(201, 160, 220, 0.3);
            text-transform: uppercase;
        }

        .login-box button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(201, 160, 220, 0.4);
            background: linear-gradient(135deg, #d4a5f5 0%, #c9a0dc 100%);
        }

        .login-box button:active {
            transform: translateY(0);
        }

        .auth-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid rgba(201, 160, 220, 0.2);
        }

        .auth-footer p {
            color: #999;
            font-size: 0.9rem;
            margin: 0;
        }

        .auth-footer a {
            color: #98d8c8;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .auth-footer a:hover {
            color: #f7b7a6;
            transform: translateX(2px);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h2>Kawaii POS</h2>
                <p class="auth-subtitle">Welcome Back!</p>
            </div>

            <?php if($error): ?>
            <div style="background: #ffcdd2; color: #c62828; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #f44336;">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit">Login</button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </div>
</div>

</body>
</html>