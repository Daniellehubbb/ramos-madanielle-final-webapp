<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- ====== Sidebar Section ====== -->
<aside class="cm-sidebar">
  <div class="cm-sidebar-inner">

    <div class="cm-user-panel">
      <div class="cm-avatar">👤</div>
      <div class="cm-username">
        <?php echo htmlspecialchars($_SESSION['first_name'] ?? 'User', ENT_QUOTES, 'UTF-8'); ?>
      </div>
    </div>

    <nav class="cm-nav">
      <ul>
        <li class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
          <a href="dashboard.php"><i class="lni lni-grid-alt"></i> Dashboard</a>
        </li>

        <li class="<?php echo ($current_page == 'categories.php') ? 'active' : ''; ?>">
          <a href="categories.php"><i class="lni lni-tag"></i> Categories</a>
        </li>

        <li class="<?php echo ($current_page == 'transactions.php') ? 'active' : ''; ?>">
          <a href="transactions.php"><i class="lni lni-wallet"></i> Transactions</a>
        </li>

        <li class="<?php echo ($current_page == 'budgets.php') ? 'active' : ''; ?>">
          <a href="budgets.php"><i class="lni lni-target"></i> Budget</a>
        </li>

        <!-- 🔽 Reports Dropdown -->
        <?php
        $report_pages = ['report_pie.php', 'report_bar.php', 'report_line.php'];
        $is_report_active = in_array($current_page, $report_pages);
        ?>
        <li class="has-dropdown <?php echo $is_report_active ? 'active' : ''; ?>">
          <a href="#" onclick="toggleDropdown(event)">
            <i class="lni lni-bar-chart"></i> Reports 
            <i class="lni lni-chevron-down dropdown-arrow"></i>
          </a>
          <ul class="sidebar-dropdown" style="<?php echo $is_report_active ? 'display: block;' : ''; ?>">
            <li class="<?php echo ($current_page == 'report_pie.php') ? 'active' : ''; ?>">
              <a href="report_pie.php"><i class="lni lni-pie-chart"></i> Pie Chart Report</a>
            </li>
            <li class="<?php echo ($current_page == 'report_bar.php') ? 'active' : ''; ?>">
              <a href="report_bar.php"><i class="lni lni-bar-chart"></i> Bar Chart Report</a>
            </li>
            <li class="<?php echo ($current_page == 'report_line.php') ? 'active' : ''; ?>">
              <a href="report_line.php"><i class="lni lni-stats-up"></i> Line Graph Report</a>
            </li>
          </ul>
        </li>
        <!-- 🔼 End Reports Dropdown -->

        <li class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
          <a href="profile.php"><i class="lni lni-user"></i> Profile</a>
        </li>

        <li>
          <a href="../pages/users/logout.php" class="cm-logout"><i class="lni lni-exit"></i> Logout</a>
        </li>
      </ul>
    </nav>
  </div>
</aside>

<!-- ====== Sidebar Styles ====== -->
<link rel="stylesheet" href="../assets/css/includes.css">
<link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />

<!-- ====== Dropdown Script ====== -->
<script>
function toggleDropdown(event) {
  event.preventDefault();
  const item = event.target.closest('.has-dropdown');
  const dropdown = item.querySelector('.sidebar-dropdown');
  const isOpen = dropdown.style.display === 'block';
  
  // Close all other dropdowns
  document.querySelectorAll('.sidebar-dropdown').forEach(d => d.style.display = 'none');
  document.querySelectorAll('.has-dropdown').forEach(i => i.classList.remove('open'));

  // Toggle current
  dropdown.style.display = isOpen ? 'none' : 'block';
  item.classList.toggle('open', !isOpen);
}
</script>
