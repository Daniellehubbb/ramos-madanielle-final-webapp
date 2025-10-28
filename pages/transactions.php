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
$message = "";
$type = "success"; // default

// ✅ Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  $action = $_POST['action'];

  if ($action === 'add') {
    $stmt = $connection->prepare("INSERT INTO transactions (type, amount, date, description, user_id, category_id)
                                  VALUES (:type, :amount, :date, :description, :user_id, :category_id)");
    $stmt->execute([
      ':type' => $_POST['type'],
      ':amount' => $_POST['amount'],
      ':date' => $_POST['date'],
      ':description' => $_POST['description'],
      ':user_id' => $user_id,
      ':category_id' => $_POST['category_id'] 
    ]);
    $message = "Transaction added successfully!";
    $type = "success";
  }

  if ($action === 'edit') {
    $stmt = $connection->prepare("UPDATE transactions 
                                 SET type=:type, amount=:amount, date=:date, description=:description, category_id=:category_id 
                                 WHERE transaction_id=:id AND user_id=:user_id");
    $stmt->execute([
      ':type' => $_POST['type'],
      ':amount' => $_POST['amount'],
      ':date' => $_POST['date'],
      ':description' => $_POST['description'],
      ':category_id' => $_POST['category_id'] ?: null,
      ':id' => $_POST['transaction_id'],
      ':user_id' => $user_id
    ]);
    $message = "Transaction updated successfully!";
    $type = "info";
  }

  if ($action === 'delete') {
    $stmt = $connection->prepare("DELETE FROM transactions WHERE transaction_id=:id AND user_id=:user_id");
    $stmt->execute([
      ':id' => $_POST['transaction_id'],
      ':user_id' => $user_id
    ]);
    $message = "Transaction deleted successfully!";
    $type = "error";
  }

  $_SESSION['toast_message'] = $message;
  $_SESSION['toast_type'] = $type;
  header("Location: transactions.php");
  exit;
}

// ✅ Fetch categories
$catStmt = $connection->prepare("SELECT * FROM categories WHERE user_id=:user_id");
$catStmt->execute([':user_id' => $user_id]);
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Fetch transactions
$sql = "SELECT t.transaction_id, t.type, t.amount, t.date, t.description, c.category_name
        FROM transactions t
        LEFT JOIN categories c ON t.category_id = c.category_id
        WHERE t.user_id = :user_id
        ORDER BY t.date DESC";
$stmt = $connection->prepare($sql);
$stmt->execute([':user_id' => $user_id]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="../assets/css/pages.css">

<!-- ✅ PAGE CONTAINER -->
<div class="page-container">
  <header class="content-header">
    <h1>Transactions</h1>
    <p>View and manage all your transactions.</p>
  </header>

  <!-- ✅ TOOLS BAR -->
  <div class="transaction-tools">
    <input type="text" id="searchTransaction" placeholder="🔍 Search transactions..." />
    <button id="addTransactionBtn" class="btn-primary">+ Add Transaction</button>
  </div>

  <!-- ✅ TRANSACTIONS TABLE -->
  <div class="transaction-table">
    <table>
      <thead>
        <tr>
          <th>Date</th>
          <th>Description</th>
          <th>Category</th>
          <th>Amount</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="transactionBody">
        <?php if ($transactions): ?>
          <?php foreach ($transactions as $t): ?>
            <tr>
              <td><?= htmlspecialchars($t['date']) ?></td>
              <td><strong><?= htmlspecialchars($t['description']) ?></strong></td>
             <td><?= htmlspecialchars($t['category_name']) ?></td>
              <td class="<?= $t['type'] === 'Income' ? 'income' : 'expense' ?>">
                <?= $t['type'] === 'Income' ? '+' : '-' ?>₱<?= number_format($t['amount'], 2) ?>
              </td>
              <td class="actions-cell">
                <button 
                  class="editBtn"
                  data-id="<?= $t['transaction_id'] ?>"
                  data-type="<?= $t['type'] ?>"
                  data-amount="<?= $t['amount'] ?>"
                  data-date="<?= $t['date'] ?>"
                  data-description="<?= htmlspecialchars($t['description']) ?>"
                  data-category="<?= $t['category_name'] ?>">
                  Edit
                </button>
                <form method="POST" class="inline-form">
                  <input type="hidden" name="transaction_id" value="<?= $t['transaction_id'] ?>">
                  <input type="hidden" name="action" value="delete">
                  <button type="submit" class="deleteBtn" onclick="return confirm('Delete this transaction?')">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="5" style="text-align:center;">No transactions found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ✅ MODAL -->
<div id="transactionModal" class="modal">
  <div class="modal-content">
    <h2 id="modalTitle">Add Transaction</h2>
    <form method="POST" id="transactionForm" class="transaction-form">
      <input type="hidden" name="transaction_id" id="transaction_id">
      <input type="hidden" name="action" id="formAction" value="add">

      <label for="type">Type:</label>
      <select name="type" id="type" required>
        <option value="Income">Income</option>
        <option value="Expense">Expense</option>
      </select>

      <label for="amount">Amount:</label>
      <input type="number" step="0.01" name="amount" id="amount" required>

      <label for="date">Date:</label>
      <input type="date" name="date" id="date" required>

      <label for="description">Description:</label>
      <input type="text" name="description" id="description" required>

     <label for="category_id">Category:</label>
<select name="category_id" id="category_id" required>
  <option value="" disabled selected>Select category</option>
  <?php foreach ($categories as $c): ?>
    <option value="<?= $c['category_id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
  <?php endforeach; ?>
</select>


      <div class="modal-actions">
        <button type="submit" class="btn-primary">Save</button>
        <button type="button" id="closeModal" class="btn-secondary">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- ✅ TOAST -->
<div id="toastBox" class="toast-box"><span id="toastMessage"></span></div>

<!-- JS -->
<script src="../assets/js/pages.js"></script>

<?php if (isset($_SESSION['toast_message'])): ?>
<script>
  showToast("<?= $_SESSION['toast_message'] ?>", "<?= $_SESSION['toast_type'] ?>");
</script>
<?php unset($_SESSION['toast_message']); unset($_SESSION['toast_type']); ?>
<?php endif; ?>