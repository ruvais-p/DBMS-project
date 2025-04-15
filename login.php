<?php
// login.php - Glassmorphic UI Version
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "expense";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$login_type = isset($_GET['type']) ? $_GET['type'] : 'individual';
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];
    
    // Regular individual login
    if ($login_type == 'individual') {
        $sql = "SELECT id, password FROM user_accounts WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row["password"])) {
                $_SESSION["user_id"] = $row["id"];
                $_SESSION["login_type"] = "individual";
                header("Location: dashboard.php");
                exit();
            } else {
                $error_message = "Invalid password.";
            }
        } else {
            $error_message = "No user found with this email.";
        }
    } 
    // Family group login
    else if ($login_type == 'family') {
        $family_group = $_POST["family_group"];
        
        $sql = "SELECT id, password, family_group FROM user_accounts WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row["password"])) {
                // Verify family group matches
                if (!empty($row["family_group"]) && $row["family_group"] == $family_group) {
                    $_SESSION["user_id"] = $row["id"];
                    $_SESSION["family_group"] = $family_group;
                    $_SESSION["login_type"] = "family";
                    header("Location: family_dashboard.php");
                    exit();
                } else {
                    $error_message = "Invalid family group or you don't belong to this family group.";
                }
            } else {
                $error_message = "Invalid password.";
            }
        } else {
            $error_message = "No user found with this email.";
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - <?php echo ucfirst($login_type); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #74ebd5, #9face6);
            padding: 30px;
            background-attachment: fixed;
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            padding: 40px;
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }
        
        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(31, 38, 135, 0.5);
        }
        
        h2 {
            text-align: center;
            color: white;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 25px;
            text-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        
        .error-message {
            color: #ff4757;
            margin-bottom: 20px;
            padding: 12px;
            background: rgba(255, 71, 87, 0.1);
            border-radius: 10px;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 71, 87, 0.3);
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: white;
            font-weight: 500;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        input[type="email"], 
        input[type="password"], 
        input[type="text"] {
            width: 100%;
            padding: 14px 16px;
            border: none;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            color: #333;
            font-size: 15px;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.7);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        ::placeholder {
            color: #666;
            opacity: 0.7;
        }
        
        button {
            width: 100%;
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(106, 17, 203, 0.4);
            margin-top: 10px;
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(106, 17, 203, 0.6);
        }
        
        .links {
            margin-top: 25px;
            text-align: center;
        }
        
        .links a {
            color: white;
            text-decoration: none;
            font-size: 15px;
            margin: 5px 0;
            display: inline-block;
            transition: all 0.3s ease;
            font-weight: 500;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }
        
        .links a:hover {
            text-decoration: underline;
            transform: translateY(-2px);
        }
        
        /* Background shapes */
        .bg-shape {
            position: fixed;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(1px);
            -webkit-backdrop-filter: blur(1px);
            z-index: -1;
            animation: float 8s infinite ease-in-out;
        }
        
        .shape1 {
            width: 300px;
            height: 300px;
            top: -100px;
            left: -100px;
        }
        
        .shape2 {
            width: 200px;
            height: 200px;
            bottom: 50px;
            right: -50px;
            animation-delay: 3s;
        }
        
        .shape3 {
            width: 150px;
            height: 150px;
            bottom: -50px;
            left: 30%;
            animation-delay: 5s;
        }
        
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        
        /* Responsive styles */
        @media (max-width: 576px) {
            .login-container {
                padding: 30px 20px;
            }
            
            h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <!-- Background shapes -->
    <div class="bg-shape shape1"></div>
    <div class="bg-shape shape2"></div>
    <div class="bg-shape shape3"></div>
    
    <div class="login-container">
        <h2><?php echo ucfirst($login_type); ?> Login</h2>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required placeholder="Enter your email">
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
            </div>
            
            <?php if ($login_type == 'family'): ?>
            <div class="form-group">
                <label for="family_group">Family Group:</label>
                <input type="text" id="family_group" name="family_group" required placeholder="Enter your family group name">
            </div>
            <?php endif; ?>
            
            <button type="submit">Login</button>
        </form>
        
        <div class="links">
            <a href="register.php">Don't have an account? Register</a><br>
            <a href="index.php">Back to Home</a>
        </div>
    </div>
</body>
</html>