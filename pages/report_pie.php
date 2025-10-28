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

// 🔹 Fetch total expenses per category
$query = "SELECT c.category_name, SUM(t.amount) AS total_amount
          FROM transactions t
          JOIN categories c ON t.category_id = c.category_id
          WHERE t.user_id = :user_id AND t.type = 'expense'
          GROUP BY c.category_name";
$stmt = $connection->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categoryNames = [];
$totalAmounts = [];

foreach ($results as $row) {
  $categoryNames[] = $row['category_name'];
  $totalAmounts[] = $row['total_amount'];
}

$hasData = !empty($results);
?>

<div class="pie-report-container">
  <h1 class="animate-header">📊 Expense Distribution</h1>
  <p>Visual breakdown of your spending by category.</p>

  <div class="pie-chart-card fade-in">
    <?php if (!$hasData): ?>
      <div class="no-data">
        <h2>No Expense Data Yet</h2>
        <p>Once you start adding transactions, your spending by category will appear here!</p>
      </div>
    <?php else: ?>
      <div class="pie-chart-wrapper">
        <canvas id="expensePieChart"></canvas>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php if ($hasData): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('expensePieChart');

new Chart(ctx, {
  type: 'pie',
  data: {
    labels: <?php echo json_encode($categoryNames); ?>,
    datasets: [{
      label: 'Total Expense',
      data: <?php echo json_encode($totalAmounts); ?>,
      backgroundColor: [
        '#00bfa6', '#ff6b6b', '#ffd93d', '#6c63ff', '#4ecdc4', '#ff9f43', '#a78bfa', '#48cae4'
      ],
      borderColor: '#fff',
      borderWidth: 3,
      hoverOffset: 15
    }]
  },
  options: {
    plugins: {
      legend: {
        position: 'bottom',
        labels: {
          color: '#333',
          font: { size: 14, family: 'Poppins' },
          padding: 15
        }
      },
      tooltip: {
        backgroundColor: 'rgba(0,0,0,0.85)',
        titleColor: '#fff',
        bodyColor: '#fff',
        padding: 10,
        cornerRadius: 10,
        titleFont: { family: 'Poppins', size: 15, weight: '600' },
        bodyFont: { family: 'Poppins', size: 13 }
      }
    },
    animation: {
      animateScale: true,
      animateRotate: true,
      duration: 2000,
      easing: 'easeOutElastic'
    },
    maintainAspectRatio: false
  }
});
</script>
<?php endif; ?>

<style>
.pie-report-container {
  margin-left: 230px;
  padding: 100px 40px;
  font-family: 'Poppins', sans-serif;
  background: radial-gradient(circle at top left, #e8f5f4, #f9fbfd);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  animation: fadeSlideIn 1s ease forwards;
}

.pie-report-container h1 {
  font-size: 2.3em;
  font-weight: 700;
  color: #004c4c;
  margin-bottom: 10px;
  text-align: center;
  letter-spacing: 0.5px;
}

.pie-report-container p {
  color: #555;
  margin-bottom: 40px;
  text-align: center;
  font-size: 1rem;
}

.pie-chart-card {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 25px;
  padding: 40px;
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
  width: 90%;
  max-width: 700px;
  display: flex;
  justify-content: center;
  align-items: center;
  transition: transform 0.4s ease, box-shadow 0.4s ease;
  animation: floatCard 3s ease-in-out infinite;
}

.pie-chart-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 18px 45px rgba(0, 0, 0, 0.1);
}

.pie-chart-wrapper {
  position: relative;
  width: 100%;
  max-width: 500px;
  aspect-ratio: 1 / 1;
}

.pie-chart-wrapper canvas {
  width: 100% !important;
  height: 100% !important;
  display: block;
  filter: drop-shadow(0 2px 6px rgba(0,0,0,0.08));
}

.no-data {
  text-align: center;
  animation: fadeIn 1s ease;
}

.no-data h2 {
  font-weight: 600;
  color: #222;
  margin-bottom: 10px;
}

.no-data p {
  color: #666;
  font-size: 0.95rem;
}

@keyframes fadeSlideIn {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
@keyframes floatCard {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-5px); }
}
.animate-header {
  opacity: 0;
  animation: headerPop 1s ease forwards;
  animation-delay: 0.2s;
}
@keyframes headerPop {
  0% { opacity: 0; transform: translateY(-15px) scale(0.95); }
  100% { opacity: 1; transform: translateY(0) scale(1); }
}

@media (max-width: 768px) {
  .pie-report-container {
    padding: 80px 20px;
  }
  .pie-chart-card {
    padding: 25px;
  }
  .pie-chart-wrapper {
    max-width: 350px;
  }
  .pie-report-container h1 {
    font-size: 1.8em;
  }
}
</style>
