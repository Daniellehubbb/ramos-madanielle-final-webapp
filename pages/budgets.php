<?php
session_start();
include '../connection/dbconn.php';
include '../includes/header.php';
include '../includes/sidebar.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../../users/login.php");
  exit();
}

$user_id = $_SESSION['user_id'];

// ADD Budget
if (isset($_POST['add_budget'])) {
  $category_id = $_POST['category_id'];
  $amount_limit = $_POST['amount_limit'];
  $start_date = $_POST['start_date'];
  $end_date = $_POST['end_date'];

  $stmt = $connection->prepare("INSERT INTO budgets (amount_limit, start_date, end_date, user_id, category_id)
                          VALUES (?, ?, ?, ?, ?)");
  $stmt->execute([$amount_limit, $start_date, $end_date, $user_id, $category_id]);
  header("Location: budgets.php");
  exit;
}

// UPDATE Budget
if (isset($_POST['update_budget'])) {
  $budget_id = $_POST['budget_id'];
  $category_id = $_POST['category_id'];
  $amount_limit = $_POST['amount_limit'];
  $start_date = $_POST['start_date'];
  $end_date = $_POST['end_date'];

  $stmt = $connection->prepare("UPDATE budgets SET amount_limit=?, start_date=?, end_date=?, category_id=? WHERE budget_id=?");
  $stmt->execute([$amount_limit, $start_date, $end_date, $category_id, $budget_id]);
  header("Location: budgets.php");
  exit;
}

// DELETE Budget
if (isset($_GET['delete'])) {
  $budget_id = $_GET['delete'];
  $stmt = $connection->prepare("DELETE FROM budgets WHERE budget_id=?");
  $stmt->execute([$budget_id]);
  header("Location: budgets.php");
  exit;
}

// FETCH all budgets
$stmt = $connection->prepare("
  SELECT b.*, c.category_name
  FROM budgets b
  JOIN categories c ON b.category_id = c.category_id
  WHERE b.user_id = ?
");
$stmt->execute([$user_id]);
$budgets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="../assets/css/pages.css">

<body class="budget-page">
<div class="budget-page-container">
  <div class="budget-page-header">
    <div>
      <h1>Budgets</h1>
      <p>Track and manage your monthly budgets.</p>
    </div>
    <button class="budget-page-add-btn" onclick="openAddModal()">+ Add Budget</button>
  </div>

  <div class="budget-page-grid">
  <?php if (empty($budgets)): ?>
    <p class="no-budgets" style="text-align:center; color:#666; font-size:1.1em; width:100%; margin-top:30px;">
      No budgets found. Start by adding one!
    </p>
  <?php else: ?>
    <?php foreach ($budgets as $budget): 
      $stmt = $connection->prepare("
        SELECT SUM(amount) AS spent
        FROM transactions
        WHERE category_id = ? 
        AND user_id = ?
        AND date BETWEEN ? AND ?
      ");
      $stmt->execute([$budget['category_id'], $user_id, $budget['start_date'], $budget['end_date']]);
      $spent = $stmt->fetch(PDO::FETCH_ASSOC)['spent'] ?? 0;

      $remaining = $budget['amount_limit'] - $spent;
      $percentage = ($spent / $budget['amount_limit']) * 100;
      $percentage = $percentage > 100 ? 100 : $percentage;
      $progressColor = $percentage >= 90 ? '#e53935' : '#00bfa6';
    ?>
    <div class="budget-page-card">
      <h3><?= htmlspecialchars($budget['category_name']) ?></h3>
      <p><strong>$<?= number_format($budget['amount_limit'], 2) ?></strong> 
        <span style="color:#666;">Monthly Budget</span></p>
      <p>Spent: $<?= number_format($spent, 2) ?> 
         <span class="budget-page-remaining">Remaining: $<?= number_format($remaining, 2) ?></span>
      </p>

      <div class="budget-page-progress-bar">
        <div class="budget-page-progress" style="width: <?= $percentage ?>%; background: <?= $progressColor ?>;"></div>
      </div>
      <p style="font-size:0.85em; color:#666;"><?= number_format($percentage, 1) ?>% of budget used</p>

      <?php if ($percentage >= 90): ?>
        <div class="budget-page-warning">⚠ You are approaching your budget limit!</div>
      <?php endif; ?>

      <div class="budget-page-actions" style="margin-top:10px;">
        <button class="budget-page-edit-btn" 
          onclick="openEditModal(<?= $budget['budget_id'] ?>, <?= $budget['category_id'] ?>, <?= $budget['amount_limit'] ?>, '<?= $budget['start_date'] ?>', '<?= $budget['end_date'] ?>')">Edit</button>
        <a href="?delete=<?= $budget['budget_id'] ?>" onclick="return confirm('Delete this budget?')">
          <button class="budget-page-delete-btn">Delete</button>
        </a>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>


<!--  ADD BUDGET MODAL -->
<div id="addModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.4); justify-content:center; align-items:center;">
  <div style="background:white; padding:30px; border-radius:10px; width:400px;">
    <h2>Add Budget</h2>
    <form method="POST">
      <label>Category:</label><br>
      <select name="category_id" required style="width:100%; padding:8px; margin-bottom:10px;">
        <?php
          $cat = $connection->prepare("SELECT * FROM categories WHERE user_id = ?");
          $cat->execute([$user_id]);
          while ($c = $cat->fetch(PDO::FETCH_ASSOC)) {
            echo "<option value='{$c['category_id']}'>{$c['category_name']}</option>";
          }
        ?>
      </select>
      <label>Amount Limit:</label><br>
      <input type="number" step="0.01" name="amount_limit" required style="width:100%; padding:8px; margin-bottom:10px;">
      <label>Start Date:</label><br>
      <input type="date" name="start_date" required style="width:100%; padding:8px; margin-bottom:10px;">
      <label>End Date:</label><br>
      <input type="date" name="end_date" required style="width:100%; padding:8px; margin-bottom:15px;">
      <button type="submit" name="add_budget" class="budget-page-add-btn">Save</button>
      <button type="button" onclick="closeAddModal()" style="margin-left:10px;">Cancel</button>
    </form>
  </div>
</div>


<!--  EDIT BUDGET MODAL -->
<div id="editModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.4); justify-content:center; align-items:center;">
  <div style="background:white; padding:30px; border-radius:10px; width:400px;">
    <h2>Edit Budget</h2>
    <form method="POST">
      <input type="hidden" name="budget_id" id="edit_budget_id">
      <label>Category:</label><br>
      <select name="category_id" id="edit_category_id" required style="width:100%; padding:8px; margin-bottom:10px;">
        <?php
          $cat = $connection->prepare("SELECT * FROM categories WHERE user_id = ?");
          $cat->execute([$user_id]);
          while ($c = $cat->fetch(PDO::FETCH_ASSOC)) {
            echo "<option value='{$c['category_id']}'>{$c['category_name']}</option>";
          }
        ?>
      </select>
      <label>Amount Limit:</label><br>
      <input type="number" step="0.01" name="amount_limit" id="edit_amount_limit" required style="width:100%; padding:8px; margin-bottom:10px;">
      <label>Start Date:</label><br>
      <input type="date" name="start_date" id="edit_start_date" required style="width:100%; padding:8px; margin-bottom:10px;">
      <label>End Date:</label><br>
      <input type="date" name="end_date" id="edit_end_date" required style="width:100%; padding:8px; margin-bottom:15px;">
      <button type="submit" name="update_budget" class="budget-page-add-btn">Update</button>
      <button type="button" onclick="closeEditModal()" style="margin-left:10px;">Cancel</button>
    </form>
  </div>
</div>

<script src="../assets/js/pages.js"></script>

