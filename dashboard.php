<?php
// dashboard.php - Glassmorphic UI Version
session_start();
if (!isset($_SESSION["user_id"])) {
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

// Get user's family group
$user_sql = "SELECT family_group FROM user_accounts WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
$family_group = $user_data['family_group'];

// Handle transaction submission
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

// Handle budget submission
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

// Fetch transactions
$transactions = $conn->query("SELECT * FROM transactions WHERE user_id = $user_id ORDER BY date DESC");

// Fetch budgets
$budgets = $conn->query("SELECT * FROM budgets WHERE user_id = $user_id ORDER BY start_date DESC");

// Calculate totals
$income_total = 0;
$expense_total = 0;

$totals_sql = "SELECT type, SUM(amount) as total FROM transactions WHERE user_id = ? GROUP BY type";
$totals_stmt = $conn->prepare($totals_sql);
$totals_stmt->bind_param("i", $user_id);
$totals_stmt->execute();
$totals_result = $totals_stmt->get_result();

while ($row = $totals_result->fetch_assoc()) {
    if ($row['type'] == 'income') {
        $income_total = $row['total'];
    } else if ($row['type'] == 'expense') {
        $expense_total = $row['total'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
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
        
        h2, h3 {
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
        
        .summary {
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
        
        .summary p {
            margin-bottom: 8px;
            font-size: 1.1rem;
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
        @media (max-width: 768px) {
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
            <h2>Welcome to Your Dashboard</h2>
            <div class="nav-buttons">
                <?php if (!empty($family_group)): ?>
                    <a href="family_dashboard.php" class="btn">Family Dashboard</a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
        
        <div class="summary">
            <h3>Financial Summary</h3>
            <p>Total Income: $<?php echo number_format($income_total, 2); ?></p>
            <p>Total Expenses: $<?php echo number_format($expense_total, 2); ?></p>
            <p>Net Balance: $<?php echo number_format($income_total - $expense_total, 2); ?></p>
            <?php if (empty($family_group)): ?>
                <p><em>Note: You are not part of a family group. Set a family group in your profile to enable family features.</em></p>
            <?php endif; ?>
        </div>
        
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