<?php
session_start();
include '../connection/dbconn.php';
include '../includes/header.php';
include '../includes/sidebar.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../../users/login.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Categories | CashMate</title>
  <link rel="stylesheet" href="../../assets/css/sidebar.css">
  <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/css/pages.css">
</head>
<body>
  <div class="categories-page">
    <div class="categories-header">
      <div>
        <h1>Categories</h1>
        <p>Manage your expense and income categories.</p>
      </div>
      <button class="add-category-btn" onclick="openModal('addCategoryModal')">+ Add Category</button>
    </div>

    <p id="message"></p>

    <div class="categories-grid" id="categoriesContainer"></div>
  </div>

  <!-- ADD MODAL -->
  <div id="addCategoryModal" class="category-modal">
    <div class="category-modal-content">
      <h2>Add Category</h2>
      <input type="text" id="add_name" placeholder="Enter category name" required>
      <br><br>
      <button onclick="addCategory()" class="update-btn">Save</button>
      <button type="button" class="cancel-btn" onclick="closeModal('addCategoryModal')">Cancel</button>
    </div>
  </div>

  <!-- EDIT MODAL -->
  <div id="editCategoryModal" class="category-modal">
    <div class="category-modal-content">
      <h2>Edit Category</h2>
      <input type="hidden" id="edit_id">
      <input type="text" id="edit_name" required>
      <br><br>
      <button onclick="updateCategory()" class="update-btn">Update</button>
      <button type="button" class="cancel-btn" onclick="closeModal('editCategoryModal')">Cancel</button>
    </div>
  </div>

  <!-- DELETE MODAL -->
  <div id="deleteCategoryModal" class="category-modal">
    <div class="category-modal-content">
      <h2>Confirm Deletion</h2>
      <p>Are you sure you want to delete <strong id="delete_category_name"></strong>?</p>
      <input type="hidden" id="delete_category_id">
      <div class="modal-buttons">
        <button onclick="deleteCategory()" class="delete-confirm-btn">Yes, Delete</button>
        <button type="button" class="cancel-btn" onclick="closeModal('deleteCategoryModal')">Cancel</button>
      </div>
    </div>
  </div>

  <!-- ✅ Toast container -->
<div id="toastBox" class="toast-box"><span id="toastMessage"></span></div>

<script src="../assets/js/pages.js"></script>


</body>
</html>
