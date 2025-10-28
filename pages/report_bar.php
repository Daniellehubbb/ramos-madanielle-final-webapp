<?php
session_start();
include '../connection/dbconn.php';
include '../includes/header.php';
include '../includes/sidebar.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../../users/login.php");
  exit();
}

$user_id = $_SESSION['user_id'] ?? 0;

// Fetch monthly income & expense data
$stmt = $connection->prepare("
  SELECT 
      DATE_FORMAT(date, '%Y-%m') AS month,
      SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS total_income,
      SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS total_expense
      FROM transactions
      WHERE user_id = :user_id
      GROUP BY month
      ORDER BY month");

$stmt->execute(['user_id' => $user_id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$months = [];
$income = [];
$expenses = [];

foreach ($results as $row) {
  $months[] = date("F Y", strtotime($row['month'] . "-01"));
  $income[] = (float)$row['total_income'];
  $expenses[] = (float)$row['total_expense'];
}

$highest_income_month = $highest_expense_month = "N/A";
if (!empty($income)) $highest_income_month = $months[array_search(max($income), $income)];
if (!empty($expenses)) $highest_expense_month = $months[array_search(max($expenses), $expenses)];

$insight = "";
if (count($income) >= 2 && count($expenses) >= 2) {
  $trend_income = end($income) - prev($income);
  $trend_expense = end($expenses) - prev($expenses);

  if ($trend_income > 0 && $trend_expense > 0) {
    $insight = "Both income and expenses increased this month — consider setting a higher savings goal.";
  } elseif ($trend_income > 0 && $trend_expense < 0) {
    $insight = "Your income increased while expenses decreased — great job managing your budget!";
  } elseif ($trend_income < 0 && $trend_expense > 0) {
    $insight = "Expenses rose while income dropped — time to reassess your spending priorities.";
  } else {
    $insight = "Both income and expenses decreased — aim to maintain steady earnings next month.";
  }
}
?>

<div class="bar-report-container">
  <h1 class="animate-header">📈 Monthly Income & Expense Report</h1>
  <p>Compare your financial performance across months and gain better insights into your spending habits.</p>

  <?php if (empty($results)): ?>
  <div class="empty-state">
    <h2>No Reports Yet</h2>
    <p>Looks like you’re new here 🎉<br>Start adding transactions to see your income and expense progress over time.</p>
  </div>
      <?php else: ?>
        <!-- Summary Cards -->
        <div class="summary-section fade-in">
          <div class="summary-card">
            <h3>💰 Highest Income Month</h3>
            <p><?php echo $highest_income_month; ?></p>
          </div>
          <div class="summary-card">
            <h3>💸 Highest Expense Month</h3>
            <p><?php echo $highest_expense_month; ?></p>
          </div>
        </div>

  <!-- Chart -->
  <div class="chart-card fade-in-delayed">
    <canvas id="barChart"></canvas>
  </div>

  <!-- Insight -->
  <div class="insight-box fade-in-late">
    <h2>💡 Financial Insight</h2>
    <p><?php echo $insight ?: "Once you record several months of transactions, insights will automatically appear here."; ?></p>
  </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('barChart').getContext('2d');

const gradientIncome = ctx.createLinearGradient(0, 0, 0, 400);
gradientIncome.addColorStop(0, 'rgba(0, 191, 166, 0.9)');
gradientIncome.addColorStop(1, 'rgba(0, 191, 166, 0.4)');

const gradientExpense = ctx.createLinearGradient(0, 0, 0, 400);
gradientExpense.addColorStop(0, 'rgba(255, 91, 91, 0.9)');
gradientExpense.addColorStop(1, 'rgba(255, 91, 91, 0.4)');

new Chart(ctx, {
  type: 'bar',
  data: {
    labels: <?php echo json_encode($months); ?>,
    datasets: [
      {
        label: 'Income',
        data: <?php echo json_encode($income); ?>,
        backgroundColor: gradientIncome,
        borderColor: '#00bfa6',
        borderWidth: 1.5,
        borderRadius: 8,
        hoverBackgroundColor: '#00d4b3'
      },
      {
        label: 'Expense',
        data: <?php echo json_encode($expenses); ?>,
        backgroundColor: gradientExpense,
        borderColor: '#ff5b5b',
        borderWidth: 1.5,
        borderRadius: 8,
        hoverBackgroundColor: '#ff7373'
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'bottom',
        labels: {
          color: '#333',
          font: { family: 'Poppins', size: 13, weight: '500' }
        }
      },
      title: {
        display: true,
        text: 'Income vs Expense by Month',
        color: '#111',
        font: { family: 'Poppins', size: 18, weight: '600' },
        padding: { top: 10, bottom: 30 }
      },
      tooltip: {
        backgroundColor: '#1e3a8a',
        titleColor: '#fff',
        bodyColor: '#fff',
        cornerRadius: 10,
        padding: 10
      }
    },
    scales: {
      x: {
        ticks: { color: '#555', font: { family: 'Poppins' } },
        grid: { display: false }
      },
      y: {
        beginAtZero: true,
        ticks: { color: '#555', font: { family: 'Poppins' } },
        grid: { color: 'rgba(0,0,0,0.05)' }
      }
    },
    animation: {
      duration: 1800,
      easing: 'easeOutQuart'
    }
  }
});
</script>

<style>
.bar-report-container {
  margin-left: 230px;
  padding: 100px 40px;
  background: linear-gradient(to bottom right, #e8f5f4, #f9fbfd);
  font-family: 'Poppins', sans-serif;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  animation: fadeSlideIn 1s ease forwards;
}

.bar-report-container h1 {
  font-size: 2.3em;
  font-weight: 700;
  color: #1e3a8a;
  margin-bottom: 10px;
  text-align: center;
}

.bar-report-container p {
  color: #555;
  margin-bottom: 40px;
  text-align: center;
  max-width: 700px;
}

/* Summary Cards */
.summary-section {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 25px;
  margin-bottom: 40px;
}

.summary-card {
  background: rgba(255, 255, 255, 0.9);
  border-radius: 20px;
  padding: 25px 40px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
  text-align: center;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.summary-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 35px rgba(0, 0, 0, 0.12);
}

.summary-card h3 {
  font-weight: 600;
  color: #444;
  margin-bottom: 8px;
}

.summary-card p {
  font-size: 1.2rem;
  font-weight: 700;
  color: #00bfa6;
}

.chart-card {
  background: #fff;
  border-radius: 25px;
  padding: 40px;
  width: 90%;
  max-width: 950px;
  height: 520px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
  transition: transform 0.3s ease;
}

.chart-card:hover {
  transform: scale(1.01);
}

.insight-box {
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
  padding: 30px 40px;
  max-width: 950px;
  margin-top: 40px;
  text-align: center;
}

.insight-box h2 {
  color: #1e3a8a;
  font-weight: 600;
  margin-bottom: 10px;
}

.insight-box p {
  color: #555;
  line-height: 1.6;
}

/* Animations */
@keyframes fadeSlideIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.fade-in {
  opacity: 0;
  animation: fadeIn 1.2s ease forwards;
}

.fade-in-delayed {
  opacity: 0;
  animation: fadeIn 1.5s ease forwards;
  animation-delay: 0.4s;
}

.fade-in-late {
  opacity: 0;
  animation: fadeIn 1.7s ease forwards;
  animation-delay: 0.7s;
}

.empty-state {
  background: rgba(255, 255, 255, 0.9);
  border-radius: 25px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
  padding: 60px 40px;
  text-align: center;
  max-width: 700px;
  animation: fadeSlideIn 1s ease forwards;
}

.empty-state h2 {
  color: #000000ff;
  font-weight: 600;
  margin-top: 20px;
  font-size: 1.8em;
}

.empty-state p {
  color: #555;
  margin: 15px 0 30px;
  font-size: 1rem;
  line-height: 1.6;
}

@keyframes fadeIn {
  to { opacity: 1; }
}

.animate-header {
  opacity: 0;
  animation: headerPop 1s ease forwards;
}

@keyframes headerPop {
  0% { opacity: 0; transform: translateY(-15px) scale(0.95); }
  100% { opacity: 1; transform: translateY(0) scale(1); }
}

@media (max-width: 768px) {
  .bar-report-container {
    padding: 80px 20px;
  }

  .chart-card {
    padding: 25px;
    height: 420px;
  }

  .bar-report-container h1 {
    font-size: 1.8em;
  }
}
</style>
