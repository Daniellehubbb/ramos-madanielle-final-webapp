<?php
session_start();
include '../connection/dbconn.php';
include '../includes/header.php';
include '../includes/sidebar.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../../users/login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// Monthly Expenses
$stmt_expense = $connection->prepare("
    SELECT DATE_FORMAT(date, '%Y-%m') AS month, SUM(amount) AS total_expense
    FROM transactions
    WHERE user_id = :user_id AND type = 'Expense'
    GROUP BY month ORDER BY month
");
$stmt_expense->execute(['user_id' => $user_id]);
$expense_data = $stmt_expense->fetchAll(PDO::FETCH_ASSOC);

// Monthly Budgets
$stmt_budget = $connection->prepare("
    SELECT DATE_FORMAT(start_date, '%Y-%m') AS month, SUM(amount_limit) AS total_budget
    FROM budgets
    WHERE user_id = :user_id
    GROUP BY month ORDER BY month
");
$stmt_budget->execute(['user_id' => $user_id]);
$budget_data = $stmt_budget->fetchAll(PDO::FETCH_ASSOC);

$expenses = [];
$budgets = [];

foreach ($budget_data as $row) $budgets[$row['month']] = $row['total_budget'];
foreach ($expense_data as $row) $expenses[$row['month']] = $row['total_expense'];

$chartMonths = array_unique(array_merge(array_keys($budgets), array_keys($expenses)));
sort($chartMonths);

$chartExpenses = [];
$chartBudgets = [];

foreach ($chartMonths as $m) {
  $chartExpenses[] = $expenses[$m] ?? 0;
  $chartBudgets[] = $budgets[$m] ?? 0;
}

$hasData = !empty($chartMonths);
?>

<div class="line-wrapper">
  <div class="line-header">
    <h1>💸 Monthly Budget vs Spending</h1>
    <p>Stay on track — visualize your spending pattern against your monthly budget goals.</p>
  </div>

  <div class="line-chart-card">
    <?php if (!$hasData): ?>
      <div class="no-data">
        <h2>No Reports Yet</h2>
        <p>Start adding transactions and budgets to see your spending insights here!</p>
      </div>
    <?php else: ?>
      <canvas id="lineChart"></canvas>
    <?php endif; ?>
  </div>
</div>

<?php if ($hasData): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('lineChart').getContext('2d');

const gradientSpending = ctx.createLinearGradient(0, 0, 0, 400);
gradientSpending.addColorStop(0, 'rgba(255, 99, 132, 0.5)');
gradientSpending.addColorStop(1, 'rgba(255, 99, 132, 0.05)');

const gradientBudget = ctx.createLinearGradient(0, 0, 0, 400);
gradientBudget.addColorStop(0, 'rgba(54, 162, 235, 0.5)');
gradientBudget.addColorStop(1, 'rgba(54, 162, 235, 0.05)');

new Chart(ctx, {
  type: 'line',
  data: {
    labels: <?= json_encode($chartMonths) ?>,
    datasets: [
      {
        label: 'Actual Spending',
        data: <?= json_encode($chartExpenses) ?>,
        borderColor: '#ff4d6d',
        backgroundColor: gradientSpending,
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#ff4d6d',
        pointBorderColor: '#fff',
        pointRadius: 6,
        pointHoverRadius: 10,
        pointHoverBackgroundColor: '#ff1e56',
        borderWidth: 3
      },
      {
        label: 'Budget Limit',
        data: <?= json_encode($chartBudgets) ?>,
        borderColor: '#3fa9f5',
        backgroundColor: gradientBudget,
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#3fa9f5',
        pointBorderColor: '#fff',
        pointRadius: 6,
        pointHoverRadius: 10,
        pointHoverBackgroundColor: '#007bff',
        borderWidth: 3
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'bottom',
        labels: { color: '#333', font: { size: 14, family: 'Poppins' } }
      },
      tooltip: {
        backgroundColor: 'rgba(0,0,0,0.8)',
        titleFont: { size: 15, family: 'Poppins' },
        bodyFont: { size: 13, family: 'Poppins' },
        padding: 12,
        cornerRadius: 10
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: { color: '#555', font: { family: 'Poppins' } },
        grid: { color: 'rgba(0,0,0,0.05)' }
      },
      x: {
        ticks: { color: '#555', font: { family: 'Poppins' } },
        grid: { color: 'transparent' }
      }
    },
    animation: {
      duration: 2500,
      easing: 'easeOutElastic'
    }
  }
});
</script>
<?php endif; ?>

<style>
.line-wrapper {
  margin-left: 230px;
  padding: 100px 40px;
  background: radial-gradient(circle at top left, #d4f1f4, #f9fbfd);
  font-family: 'Poppins', sans-serif;
  min-height: 100vh;
  overflow: hidden;
  animation: fadeIn 1.2s ease-in-out;
}

.line-header {
  text-align: center;
  margin-bottom: 40px;
  animation: slideDown 1s ease;
}
.line-header h1 {
  font-size: 2.3em;
  font-weight: 700;
  color: #222;
  letter-spacing: 1px;
}
.line-header p {
  color: #555;
  font-size: 1rem;
  margin-top: 8px;
}

.line-chart-card {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 25px;
  padding: 35px;
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
  transition: transform 0.4s ease, box-shadow 0.4s ease;
  max-width: 950px;
  margin: 0 auto;
  height: 520px;
  display: flex;
  justify-content: center;
  align-items: center;
  animation: floatCard 3s ease-in-out infinite;
}
.line-chart-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 20px 45px rgba(0, 0, 0, 0.12);
}

.no-data {
  text-align: center;
  color: #555;
  animation: fadeIn 1.2s ease;
}
.no-data-img {
  width: 150px;
  opacity: 0.8;
  margin-bottom: 20px;
  animation: floatCard 3s ease-in-out infinite;
}
.no-data h2 {
  font-weight: 600;
  color: #222;
  margin-bottom: 8px;
}
.no-data p {
  font-size: 0.95rem;
  color: #666;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes slideDown {
  from { opacity: 0; transform: translateY(-20px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes floatCard {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-5px); }
}
</style>
