<?php
session_start();
include '../connection/dbconn.php';
include '../includes/header.php';
include '../includes/sidebar.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../users/login.php");
  exit;
}

$user_id = $_SESSION['user_id'];


// Total income
$stmt = $connection->prepare("SELECT SUM(amount) AS total FROM transactions WHERE user_id = ? AND type = 'income'");
$stmt->execute([$user_id]);
$total_income = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Total expense
$stmt = $connection->prepare("SELECT SUM(amount) AS total FROM transactions WHERE user_id = ? AND type = 'expense'");
$stmt->execute([$user_id]);
$total_expense = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Total budget
$stmt = $connection->prepare("SELECT SUM(amount_limit) AS total FROM budgets WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_budget = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Compute balance and savings
$total_balance = $total_income - $total_expense;
$savings = $total_balance - $total_budget;

// ===== Fetch recent transactions =====
$stmt = $connection->prepare("
  SELECT t.description, t.amount, t.type, c.category_name, t.date 
  FROM transactions t
  LEFT JOIN categories c ON t.category_id = c.category_id
  WHERE t.user_id = ?
  ORDER BY t.date DESC
  LIMIT 5
");
$stmt->execute([$user_id]);
$recent_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="../assets/css/pages.css">

<div class="dashboard-wrapper">
  <div class="dashboard-header">
    <h1>Dashboard</h1>
    <p>Welcome back, <?php echo htmlspecialchars($_SESSION['first_name'] ?? 'User'); ?>! Here’s your financial overview.</p>
  </div>

  <div class="dashboard-cards">
    <!-- Total Balance -->
    <div class="dashboard-card">
      <div class="icon-bg"><i class="lni lni-wallet"></i></div>
      <div class="card-info">
        <h4>Total Balance</h4>
        <p class="amount">₱<?php echo number_format($total_balance, 2); ?></p>
      </div>
    </div>

    <!-- Income -->
    <div class="dashboard-card">
      <div class="icon-bg"><i class="lni lni-arrow-up-circle"></i></div>
      <div class="card-info">
        <h4>Total Income</h4>
        <p class="amount">₱<?php echo number_format($total_income, 2); ?></p>
      </div>
    </div>

    <!-- Expenses -->
    <div class="dashboard-card">
      <div class="icon-bg"><i class="lni lni-arrow-down-circle"></i></div>
      <div class="card-info">
        <h4>Total Expenses</h4>
        <p class="amount">₱<?php echo number_format($total_expense, 2); ?></p>
      </div>
    </div>

    <!-- Savings -->
    <div class="dashboard-card">
      <div class="icon-bg"><i class="lni lni-piggy-bank"></i></div>
      <div class="card-info">
        <h4>Total Savings</h4>
        <p class="amount">₱<?php echo number_format($savings, 2); ?></p>
      </div>
    </div>
  </div>

  <div class="dashboard-transactions">
    <h2>Recent Transactions</h2>
    <ul>
      <?php if (!empty($recent_transactions)): ?>
        <?php foreach ($recent_transactions as $transaction): ?>
          <li>
            <div class="details">
              <h4><?php echo htmlspecialchars($transaction['description']); ?></h4>
              <span class="category">
                <?php echo htmlspecialchars($transaction['category_name'] ?? 'Uncategorized'); ?> • 
                <?php echo date('M d', strtotime($transaction['date'])); ?>
              </span>
            </div>
            <span class="amount <?php echo strtolower($transaction['type']); ?>">
              <?php echo ($transaction['type'] === 'Income' ? '+' : '-'); ?>₱<?php echo number_format($transaction['amount'], 2); ?>
            </span>
          </li>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="no-transactions">No recent transactions available.</p>
      <?php endif; ?>
    </ul>
  </div>
</div>

<link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
