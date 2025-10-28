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

try {
  $query = "SELECT first_name, middle_name, last_name, email, created_at 
            FROM users WHERE user_id = ?";
  $stmt = $connection->prepare($query);
  $stmt->execute([$user_id]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$user) {
    echo "<p style='color:red; padding:100px;'>No user found for ID: $user_id</p>";
    exit();
  }

  $full_name = trim($user['first_name'] . ' ' . $user['middle_name'] . ' ' . $user['last_name']);
} catch (PDOException $e) {
  echo "<p style='color:red; padding:100px;'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
  exit();
}
?>

<link rel="stylesheet" href="../assets/css/pages.css">

<!-- PROFILE PAGE -->
<div class="profile-page-wrapper">
  <div class="profile-page-header">
    <h1>Profile</h1>
    <p>Manage your account settings and preferences.</p>
  </div>

  <div class="profile-page-content">
    <!-- LEFT CARD -->
    <div class="profile-page-card profile-page-main">
      <div class="profile-page-avatar">
        <i class="lni lni-user"></i>
      </div>
      <h2><?php echo htmlspecialchars($full_name); ?></h2>
      <p class="profile-page-role">Member since <?php echo date("F Y", strtotime($user['created_at'])); ?></p>
      <button class="profile-page-edit-btn" id="openEditModal">Edit Profile</button>
    </div>

    <!-- RIGHT CARD -->
    <div class="profile-page-card profile-page-info">
      <h3>Personal Information</h3>

      <div class="profile-page-info-item">
        <i class="lni lni-user"></i>
        <div>
          <span class="label">Full Name</span>
          <p><?php echo htmlspecialchars($full_name); ?></p>
        </div>
      </div>

      <div class="profile-page-info-item">
        <i class="lni lni-envelope"></i>
        <div>
          <span class="label">Email</span>
          <p><?php echo htmlspecialchars($user['email']); ?></p>
        </div>
      </div>

      <div class="profile-page-info-item">
        <i class="lni lni-calendar"></i>
        <div>
          <span class="label">Account Created</span>
          <p><?php echo htmlspecialchars(date("M d, Y", strtotime($user['created_at']))); ?></p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 🔹 MODAL -->
<div class="profile-page-modal" id="editProfileModal">
  <div class="profile-page-modal-content">
    <span class="profile-page-close-btn" id="closeEditModal">&times;</span>
    <h2>Edit Profile</h2>

    <form class="profile-page-form" action="update_profile.php" method="POST">
      <div class="profile-page-form-group">
        <label>First Name</label>
        <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
      </div>

      <div class="profile-page-form-group">
        <label>Middle Name</label>
        <input type="text" name="middle_name" value="<?php echo htmlspecialchars($user['middle_name']); ?>">
      </div>

      <div class="profile-page-form-group">
        <label>Last Name</label>
        <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
      </div>

      <div class="profile-page-form-group">
        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
      </div>

      <div class="profile-page-form-actions">
        <button type="submit" class="profile-page-save-btn">Save Changes</button>
      </div>
    </form>
  </div>
</div>


<!-- 🔹 MODAL SCRIPT -->
<script>
  const openModal = document.getElementById('openEditModal');
  const closeModal = document.getElementById('closeEditModal');
  const modal = document.getElementById('editProfileModal');

  openModal.onclick = () => modal.style.display = 'flex';
  closeModal.onclick = () => modal.style.display = 'none';
  window.onclick = (e) => { if (e.target === modal) modal.style.display = 'none'; }
</script>


