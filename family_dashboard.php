<?php
// family_dashboard.php - Glassmorphic UI Version
session_start();
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["family_group"])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "expense";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION["user_id"];
$family_group = $_SESSION["family_group"];

// Get current user's info
$user_sql = "SELECT name FROM user_accounts WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
$current_user_name = $user_data['name'];

// Get all family members
$members_sql = "SELECT id, name FROM user_accounts WHERE family_group = ? AND id != ?";
$members_stmt = $conn->prepare($members_sql);
$members_stmt->bind_param("si", $family_group, $user_id);
$members_stmt->execute();
$members_result = $members_stmt->get_result();

// Handle transaction submission for the current user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["transaction_type"])) {
    $type = $_POST["transaction_type"];
    $category = $_POST["category"];
    $amount = $_POST["amount"];
    $date = date("Y-m-d");
    
    $sql = "INSERT INTO transactions (user_id, type, category, amount, date) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issds", $user_id, $type, $category, $amount, $date);
    $stmt->execute();
}

// Handle budget submission for the current user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["budget_category"])) {
    $category = $_POST["budget_category"];
    $amount = $_POST["budget_amount"];
    $start_date = $_POST["start_date"];
    $end_date = $_POST["end_date"];
    
    $sql = "INSERT INTO budgets (user_id, category, amount, start_date, end_date) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isdss", $user_id, $category, $amount, $start_date, $end_date);
    $stmt->execute();
}

// Get selected family member data if viewing a specific member
$selected_member_id = isset($_GET['member_id']) ? $_GET['member_id'] : null;
$selected_member_name = "";

if ($selected_member_id) {
    // Verify the selected member belongs to the same family group
    $verify_sql = "SELECT name, family_group FROM user_accounts WHERE id = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("i", $selected_member_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows == 1) {
        $member_data = $verify_result->fetch_assoc();
        if ($member_data['family_group'] == $family_group) {
            $selected_member_name = $member_data['name'];
        } else {
            // Redirect if trying to access member from different family
            header("Location: family_dashboard.php");
            exit();
        }
    }
}

// Fetch current user transactions if no member is selected, otherwise fetch selected member's transactions
if ($selected_member_id) {
    $transactions = $conn->query("SELECT * FROM transactions WHERE user_id = $selected_member_id ORDER BY date DESC");
    $budgets = $conn->query("SELECT * FROM budgets WHERE user_id = $selected_member_id ORDER BY start_date DESC");
} else {
    $transactions = $conn->query("SELECT * FROM transactions WHERE user_id = $user_id ORDER BY date DESC");
    $budgets = $conn->query("SELECT * FROM budgets WHERE user_id = $user_id ORDER BY start_date DESC");
}

// Calculate family totals
$family_members = array();
$family_expense_total = 0;
$family_income_total = 0;

// Get all users in the family group
$family_sql = "SELECT id, name FROM user_accounts WHERE family_group = ?";
$family_stmt = $conn->prepare($family_sql);
$family_stmt->bind_param("s", $family_group);
$family_stmt->execute();
$family_result = $family_stmt->get_result();

while ($member = $family_result->fetch_assoc()) {
    $member_id = $member['id'];
    $member_name = $member['name'];
    
    // Get expense total
    $expense_sql = "SELECT SUM(amount) as total FROM transactions WHERE user_id = ? AND type = 'expense'";
    $expense_stmt = $conn->prepare($expense_sql);
    $expense_stmt->bind_param("i", $member_id);
    $expense_stmt->execute();
    $expense_result = $expense_stmt->get_result();
    $expense_row = $expense_result->fetch_assoc();
    $expense_total = $expense_row['total'] ?: 0;
    
    // Get income total
    $income_sql = "SELECT SUM(amount) as total FROM transactions WHERE user_id = ? AND type = 'income'";
    $income_stmt = $conn->prepare($income_sql);
    $income_stmt->bind_param("i", $member_id);
    $income_stmt->execute();
    $income_result = $income_stmt->get_result();
    $income_row = $income_result->fetch_assoc();
    $income_total = $income_row['total'] ?: 0;
    
    $family_members[] = array(
        'id' => $member_id,
        'name' => $member_name,
        'expense_total' => $expense_total,
        'income_total' => $income_total
    );
    
    $family_expense_total += $expense_total;
    $family_income_total += $income_total;
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Family Dashboard</title>
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
            background: linear-gradient(135deg, #74ebd5, #9face6);
            padding: 30px;
            background-attachment: fixed;
            color: #333;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            z-index: 1;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            padding: 20px;
        }
        
        h2, h3, h4 {
            color: white;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .nav-buttons {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(106, 17, 203, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.18);
            text-align: center;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(106, 17, 203, 0.6);
        }
        
        .btn-secondary {
            background: linear-gradient(45deg, #607d8b, #78909c);
            box-shadow: 0 4px 15px rgba(96, 125, 139, 0.4);
        }
        
        .btn-secondary:hover {
            box-shadow: 0 6px 20px rgba(96, 125, 139, 0.6);
        }
        
        .family-summary {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            padding: 25px;
            margin-bottom: 30px;
            color: white;
        }
        
        .family-summary p {
            margin-bottom: 8px;
            font-size: 1.1rem;
        }
        
        .member-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .member-card {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            padding: 20px;
            width: 280px;
            transition: all 0.3s ease;
        }
        
        .member-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(31, 38, 135, 0.5);
        }
        
        .member-card.active {
            background: rgba(106, 17, 203, 0.25);
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        .member-card h4 {
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        
        .member-card p {
            margin-bottom: 8px;
            color: rgba(255,255,255,0.9);
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .section {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            padding: 25px;
            transition: all 0.3s ease;
        }
        
        .section:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(31, 38, 135, 0.5);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: white;
            font-weight: 500;
        }
        
        input, select {
            width: 100%;
            padding: 12px 15px;
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
        
        input:focus, select:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.7);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        button[type="submit"] {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
        }
        
        .transaction-list, .budget-list {
            max-height: 300px;
            overflow-y: auto;
            padding-right: 10px;
        }
        
        ul {
            list-style-type: none;
        }
        
        li {
            padding: 12px 15px;
            margin-bottom: 10px;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 10px;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            color: #333;
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
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.5);
        }
        
        /* Responsive styles */
        @media (max-width: 900px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .nav-buttons {
                width: 100%;
                flex-direction: column;
            }
            
            .member-list {
                justify-content: center;
            }
            
            .member-card {
                width: 100%;
                max-width: 350px;
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
        <div class="header">
            <h2>Family Group: <?php echo htmlspecialchars($family_group); ?></h2>
            <div class="nav-buttons">
                <a href="family_dashboard.php" class="btn">My Dashboard</a>
                <a href="dashboard.php" class="btn btn-secondary">Individual Mode</a>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
        
        <div class="family-summary">
            <h3>Family Summary</h3>
            <p>Total Family Income: $<?php echo number_format($family_income_total, 2); ?></p>
            <p>Total Family Expenses: $<?php echo number_format($family_expense_total, 2); ?></p>
            <p>Net Balance: $<?php echo number_format($family_income_total - $family_expense_total, 2); ?></p>
        </div>
        
        <h3>Family Members</h3>
        <div class="member-list">
            <?php foreach ($family_members as $member): ?>
                <div class="member-card <?php echo ($selected_member_id == $member['id'] || (!$selected_member_id && $member['id'] == $user_id)) ? 'active' : ''; ?>">
                    <h4><?php echo htmlspecialchars($member['name']); ?> <?php echo ($member['id'] == $user_id) ? '(You)' : ''; ?></h4>
                    <p>Income: $<?php echo number_format($member['income_total'], 2); ?></p>
                    <p>Expenses: $<?php echo number_format($member['expense_total'], 2); ?></p>
                    <?php if ($member['id'] != $user_id): ?>
                        <a href="family_dashboard.php?member_id=<?php echo $member['id']; ?>" class="btn">View Details</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ($selected_member_id): ?>
            <h3>Viewing <?php echo htmlspecialchars($selected_member_name); ?>'s Dashboard (Read-Only)</h3>
        <?php else: ?>
            <h3>Your Dashboard</h3>
            
            <div class="dashboard-grid">
                <div class="section">
                    <h3>Add Transaction</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>Type:</label>
                            <select name="transaction_type">
                                <option value="income">Income</option>
                                <option value="expense">Expense</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Category:</label>
                            <input type="text" name="category" required>
                        </div>
                        <div class="form-group">
                            <label>Amount:</label>
                            <input type="number" step="0.01" name="amount" required>
                        </div>
                        <button type="submit" class="btn">Add Transaction</button>
                    </form>
                </div>
                
                <div class="section">
                    <h3>Set Budget</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>Category:</label>
                            <input type="text" name="budget_category" required>
                        </div>
                        <div class="form-group">
                            <label>Amount:</label>
                            <input type="number" step="0.01" name="budget_amount" required>
                        </div>
                        <div class="form-group">
                            <label>Start Date:</label>
                            <input type="date" name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label>End Date:</label>
                            <input type="date" name="end_date" required>
                        </div>
                        <button type="submit" class="btn">Set Budget</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="section">
            <h3>Transaction History</h3>
            <div class="transaction-list">
                <?php if ($transactions->num_rows > 0): ?>
                    <ul>
                        <?php while ($row = $transactions->fetch_assoc()): ?>
                            <li><?php echo $row["date"] . " - " . ucfirst($row["type"]) . " - " . $row["category"] . " - $" . number_format($row["amount"], 2); ?></li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p>No transactions found.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="section">
            <h3>Budget Summary</h3>
            <div class="budget-list">
                <?php if ($budgets->num_rows > 0): ?>
                    <ul>
                        <?php while ($row = $budgets->fetch_assoc()): ?>
                            <li><?php echo $row["category"] . " - Budget: $" . number_format($row["amount"], 2) . " (" . date("M d, Y", strtotime($row["start_date"])) . " to " . date("M d, Y", strtotime($row["end_date"])) . ")"; ?></li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p>No budgets found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>