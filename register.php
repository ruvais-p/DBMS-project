<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "expense";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $family_action = isset($_POST["family_action"]) ? $_POST["family_action"] : "create";
    
    // Set family_group based on the selected action
    $family_group = "";
    if ($family_action == "create") {
        $family_group = isset($_POST["family_group"]) ? $_POST["family_group"] : "";
    } else if ($family_action == "join") {
        // First check the dropdown value
        if (!empty($_POST["family_group"])) {
            $family_group = $_POST["family_group"];
        }
        // Then check the manual input (which takes precedence if filled)
        if (isset($_POST["manual_family_group"]) && !empty($_POST["manual_family_group"])) {
            $family_group = $_POST["manual_family_group"];
        }
    }
    // If action is "none", family_group remains empty
    
    // Log the values for debugging
    error_log("Name: " . $name . ", Email: " . $email . ", Family Group: " . $family_group . ", Action: " . $family_action);
    
    // Check if email already exists
    $check_sql = "SELECT id FROM user_accounts WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $error_message = "Email already exists. Please use a different email or login.";
    } else {
        // Handle family group logic
        if ($family_action == "join" && !empty($family_group)) {
            // Verify if family group exists
            $verify_sql = "SELECT COUNT(*) as count FROM user_accounts WHERE family_group = ?";
            $verify_stmt = $conn->prepare($verify_sql);
            $verify_stmt->bind_param("s", $family_group);
            $verify_stmt->execute();
            $verify_result = $verify_stmt->get_result();
            $verify_row = $verify_result->fetch_assoc();
            
            if ($verify_row['count'] == 0) {
                $error_message = "This family group doesn't exist. Please check the name or create a new group.";
            }
        }
        
        // If no errors, proceed with registration
        if (empty($error_message)) {
            $sql = "INSERT INTO user_accounts (name, email, password, family_group) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $name, $email, $password, $family_group);
            
            if ($stmt->execute()) {
                $_SESSION["user_id"] = $stmt->insert_id;
                
                if (!empty($family_group)) {
                    $_SESSION["family_group"] = $family_group;
                    $_SESSION["login_type"] = "family";
                    header("Location: family_dashboard.php");
                    exit();
                } else {
                    $_SESSION["login_type"] = "individual";
                    header("Location: dashboard.php");
                    exit();
                }
            } else {
                $error_message = "Error: " . $stmt->error;
            }
        }
    }
}

// Get list of existing family groups for dropdown
$family_groups = array();
$groups_sql = "SELECT DISTINCT family_group FROM user_accounts WHERE family_group IS NOT NULL AND family_group != '' ORDER BY family_group";
$groups_result = $conn->query($groups_sql);

if ($groups_result->num_rows > 0) {
    while ($row = $groups_result->fetch_assoc()) {
        $family_groups[] = $row['family_group'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
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
        
        .register-container {
            width: 500px;
            max-width: 95%;
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            padding: 30px;
            position: relative;
            z-index: 1;
        }
        
        h2 {
            text-align: center;
            color: #333;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 25px;
            text-shadow: 0 1px 3px rgba(255, 255, 255, 0.2);
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.3);
        }
        
        input[type="text"], 
        input[type="email"], 
        input[type="password"], 
        select {
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
        
        input[type="text"]:focus, 
        input[type="email"]:focus, 
        input[type="password"]:focus, 
        select:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.7);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        ::placeholder {
            color: #666;
            opacity: 0.7;
        }
        
        .error-message {
            color: #ff4757;
            margin-bottom: 15px;
            padding: 10px;
            background: rgba(255, 71, 87, 0.1);
            border-radius: 8px;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 71, 87, 0.3);
        }
        
        .success-message {
            color: #2ed573;
            margin-bottom: 15px;
            padding: 10px;
            background: rgba(46, 213, 115, 0.1);
            border-radius: 8px;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border: 1px solid rgba(46, 213, 115, 0.3);
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
            color: #333;
            text-decoration: none;
            font-size: 15px;
            margin: 5px 0;
            display: inline-block;
            transition: all 0.3s ease;
            font-weight: 500;
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.3);
        }
        
        .links a:hover {
            text-decoration: underline;
            transform: translateY(-2px);
        }
        
        .family-options {
            margin-top: 10px;
            padding: 15px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .radio-group {
            margin-bottom: 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .radio-option {
            display: flex;
            align-items: center;
            margin-right: 15px;
            position: relative;
        }
        
        input[type="radio"] {
            opacity: 0;
            position: absolute;
        }
        
        .radio-label {
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
            font-size: 14px;
            color: #333;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        input[type="radio"]:checked + .radio-label {
            background: rgba(106, 17, 203, 0.6);
            box-shadow: 0 2px 8px rgba(106, 17, 203, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.4);
            transform: translateY(-2px);
            color: white;
        }
        
        #join-group, #create-group {
            margin-top: 15px;
            padding: 12px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        small {
            display: block;
            margin: 8px 0;
            color: #333;
            font-size: 13px;
        }
        
        /* Add animated background shapes */
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        
        .bg-shape {
            position: fixed;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(1px);
            -webkit-backdrop-filter: blur(1px);
            z-index: -1;
        }
        
        .shape1 {
            width: 300px;
            height: 300px;
            top: -100px;
            left: -100px;
            animation: float 8s infinite ease-in-out;
        }
        
        .shape2 {
            width: 200px;
            height: 200px;
            bottom: 50px;
            right: -50px;
            animation: float 12s infinite ease-in-out;
            animation-delay: 3s;
        }
        
        .shape3 {
            width: 150px;
            height: 150px;
            bottom: -50px;
            left: 30%;
            animation: float 10s infinite ease-in-out;
            animation-delay: 5s;
        }
        
        /* Add responsive styles */
        @media (max-width: 576px) {
            .register-container {
                padding: 20px;
            }
            
            h2 {
                font-size: 24px;
            }
            
            .radio-group {
                flex-direction: column;
                gap: 8px;
            }
            
            .radio-option {
                margin-right: 0;
            }
        }
    </style>
    <script>
        function toggleFamilyFields() {
            var familyAction = document.querySelector('input[name="family_action"]:checked').value;
            var joinGroup = document.getElementById('join-group');
            var createGroup = document.getElementById('create-group');
            
            // Clear values when hiding to prevent conflicts
            if (familyAction !== 'create') {
                document.getElementById('new-family').value = '';
            }
            
            if (familyAction !== 'join') {
                document.getElementById('existing-family').value = '';
                document.getElementById('manual-family').value = '';
            }
            
            if (familyAction === 'join') {
                joinGroup.style.display = 'block';
                createGroup.style.display = 'none';
            } else if (familyAction === 'create') {
                joinGroup.style.display = 'none';
                createGroup.style.display = 'block';
            } else {
                joinGroup.style.display = 'none';
                createGroup.style.display = 'none';
            }
        }
        
        // Initialize on page load
        window.onload = function() {
            toggleFamilyFields();
        };
    </script>
</head>
<body>
    <!-- Background shapes -->
    <div class="bg-shape shape1"></div>
    <div class="bg-shape shape2"></div>
    <div class="bg-shape shape3"></div>
    
    <div class="register-container">
        <h2>Register</h2>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required placeholder="Enter your name">
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="Enter your email">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Create a password">
            </div>
            
            <div class="form-group">
                <label>Family Group Options</label>
                <div class="family-options">
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" id="none" name="family_action" value="none" onclick="toggleFamilyFields()">
                            <label for="none" class="radio-label">No Family Group</label>
                        </div>
                        
                        <div class="radio-option">
                            <input type="radio" id="create" name="family_action" value="create" checked onclick="toggleFamilyFields()">
                            <label for="create" class="radio-label">Create Family Group</label>
                        </div>
                        
                        <div class="radio-option">
                            <input type="radio" id="join" name="family_action" value="join" onclick="toggleFamilyFields()">
                            <label for="join" class="radio-label">Join Existing Family</label>
                        </div>
                    </div>
                    
                    <div id="create-group">
                        <label for="new-family">Create New Family Group</label>
                        <input type="text" id="new-family" name="family_group" placeholder="Enter a unique family group name">
                    </div>
                    
                    <div id="join-group" style="display: none;">
                        <label for="existing-family">Select Existing Family Group</label>
                        <select id="existing-family" name="family_group">
                            <option value="">Select a family group</option>
                            <?php foreach ($family_groups as $group): ?>
                                <option value="<?php echo htmlspecialchars($group); ?>"><?php echo htmlspecialchars($group); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small>If your family group is not listed, make sure you type the exact name</small>
                        <input type="text" id="manual-family" placeholder="Or type exact family group name" name="manual_family_group">
                    </div>
                </div>
            </div>
            
            <button type="submit">Create Account</button>
        </form>
        
        <div class="links">
            <a href="login.php">Already have an account? Login</a><br>
            <a href="index.php">Back to Home</a>
        </div>
    </div>
</body>
</html>