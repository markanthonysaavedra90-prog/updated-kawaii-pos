<?php
include 'db.php';

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