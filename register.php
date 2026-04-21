<?php include 'db.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Kawaii POS</title>
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

        .register-container {
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

        .success-message {
            text-align: center;
            padding: 30px;
            background: linear-gradient(135deg, #c8e6c9 0%, #b2dfdb 100%);
            border-radius: 20px;
            border: 2px solid #66bb6a;
            animation: slideUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 10px 30px rgba(102, 187, 106, 0.2);
        }

        .success-message h3 {
            color: #2e7d32;
            font-size: 1.8rem;
            margin: 0 0 8px 0;
            font-weight: 800;
        }

        .success-message p {
            color: #388e3c;
            font-size: 0.95rem;
            margin: 0;
        }

        .success-message a {
            display: inline-block;
            margin-top: 15px;
            color: white;
            background: linear-gradient(135deg, #66bb6a 0%, #81c784 100%);
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(102, 187, 106, 0.3);
        }

        .success-message a:hover {
            background: linear-gradient(135deg, #81c784 0%, #66bb6a 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(102, 187, 106, 0.4);
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
    <div class="register-container">
        <?php
        if(isset($_POST['register'])){
            $u = $_POST['username'];
            $p = $_POST['password'];
            $r = $_POST['role'];

            $conn->query("
            INSERT INTO users (username,password,role)
            VALUES ('$u','$p','$r')
            ");
        ?>
            <div class="login-box success-message">
                <h3>Account Created!</h3>
                <p>Your account has been successfully registered.</p>
                <a href="index.php">Login Now</a>
            </div>
        <?php } else { ?>
            <div class="login-box">
                <div class="login-header">
                    <h2>Create Account</h2>
                    <p class="auth-subtitle">Join Kawaii POS!</p>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Create a username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Create a password" required>
                    </div>
                    <div class="form-group">
                        <label for="role">Select Role</label>
                        <select id="role" name="role" required>
                            <option value="" disabled selected>Choose a role...</option>
                            <option value="cashier">Cashier</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <button type="submit" name="register">Register</button>
                </form>

                <div class="auth-footer">
                    <p>Already have an account? <a href="index.php">Login here</a></p>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

</body>
</html>
