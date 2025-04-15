<?php
// index.php - New Home Page with Login Options
session_start();

// Redirect to dashboard if already logged in
if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Expense Tracker - Home</title>
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
            overflow-x: hidden;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            text-align: center;
            z-index: 1;
        }
        
        h1 {
            color: white;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        p {
            color: rgba(255,255,255,0.9);
            font-size: 1.1rem;
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .login-options {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
            margin-top: 40px;
        }
        
        .option-card {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            padding: 30px;
            width: 320px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .option-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 40px rgba(31, 38, 135, 0.5);
        }
        
        .option-card h2 {
            color: #333;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        
        .option-card p {
            color: rgba(51,51,51,0.8);
            font-size: 0.95rem;
            margin-bottom: 20px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(106, 17, 203, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(106, 17, 203, 0.6);
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
        @media (max-width: 900px) {
            .login-options {
                flex-direction: column;
                align-items: center;
            }
            
            .option-card {
                width: 100%;
                max-width: 400px;
            }
        }
    </style>
</head>
<body>
    <!-- Background shapes -->
    <div class="bg-shape shape1"></div>
    <div class="bg-shape shape2"></div>
    <div class="bg-shape shape3"></div>
    
    <div class="container">
        <h1>Welcome to Expense Tracker</h1>
        <p>Manage your finances efficiently and collaborate with your family members.</p>
        
        <div class="login-options">
            <div class="option-card">
                <h2>Individual Login</h2>
                <p>Access your personal expense tracking account.</p>
                <a href="login.php?type=individual" class="btn">Login</a>
            </div>
            
            <div class="option-card">
                <h2>Family Login</h2>
                <p>Access expense tracking for your entire family group.</p>
                <a href="login.php?type=family" class="btn">Family Login</a>
            </div>
            
            <div class="option-card">
                <h2>New User?</h2>
                <p>Create a new account to start tracking your expenses.</p>
                <a href="register.php" class="btn">Sign Up</a>
            </div>
        </div>
    </div>
</body>
</html>