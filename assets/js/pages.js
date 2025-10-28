// Transactions JS
function showToast(message, type = "success") {
  const toastBox = document.getElementById("toastBox");
  const toastMessage = document.getElementById("toastMessage");

  toastMessage.textContent = message;

  if (type === "success") {
    toastBox.style.background = "linear-gradient(135deg, #00bfa6, #00d4b2)";
  } else if (type === "error") {
    toastBox.style.background = "linear-gradient(135deg, #ef4444, #f87171)";
  } else if (type === "info") {
    toastBox.style.background = "linear-gradient(135deg, #3b82f6, #60a5fa)";
  }

  toastBox.classList.add("show");
  setTimeout(() => toastBox.classList.remove("show"), 2500);
}

document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("searchTransaction");
  const modal = document.getElementById("transactionModal");
  const addBtn = document.getElementById("addTransactionBtn");
  const closeBtn = document.getElementById("closeModal");
  const form = document.getElementById("transactionForm");
  const modalTitle = document.getElementById("modalTitle");

  searchInput.addEventListener("keyup", function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll("#transactionBody tr").forEach(row => {
      const desc = row.children[1].textContent.toLowerCase();
      const cat = row.children[2].textContent.toLowerCase();
      row.style.display = desc.includes(val) || cat.includes(val) ? "" : "none";
    });
  });

  addBtn.onclick = () => {
    modal.style.display = "flex";
    modalTitle.textContent = "Add Transaction";
    document.getElementById("formAction").value = "add";
    form.reset();
  };

  closeBtn.onclick = () => modal.style.display = "none";

  document.querySelectorAll(".editBtn").forEach(btn => {
    btn.onclick = () => {
      modal.style.display = "flex";
      modalTitle.textContent = "Edit Transaction";
      document.getElementById("formAction").value = "edit";
      document.getElementById("transaction_id").value = btn.dataset.id;
      document.getElementById("type").value = btn.dataset.type;
      document.getElementById("amount").value = btn.dataset.amount;
      document.getElementById("date").value = btn.dataset.date;
      document.getElementById("description").value = btn.dataset.description;
    };
  });
});



// ✅ Toast function (same as transactions page)
function showToast(message, type = "success") {
  const toastBox = document.getElementById("toastBox");
  const toastMessage = document.getElementById("toastMessage");

  if (!toastBox || !toastMessage) return;

  toastMessage.textContent = message;

  // Change background color based on type
  if (type === "success") {
    toastBox.style.background = "linear-gradient(135deg, #00bfa6, #00d4b2)";
  } else if (type === "error") {
    toastBox.style.background = "linear-gradient(135deg, #ef4444, #f87171)";
  } else if (type === "info") {
    toastBox.style.background = "linear-gradient(135deg, #3b82f6, #60a5fa)";
  }

  toastBox.classList.add("show");
  setTimeout(() => toastBox.classList.remove("show"), 2500);
}

// ========== FETCH & DISPLAY CATEGORIES ==========
async function loadCategories() {
  const res = await fetch('../api/categories/read.php');
  const data = await res.json();
  const container = document.getElementById('categoriesContainer');
  container.innerHTML = '';

  if (data.data && data.data.length > 0) {
    data.data.forEach(c => {
      container.innerHTML += `
        <div class="category-card">
          <h3>${c.category_name}</h3>
          <p>Category ID: ${c.category_id}</p>
          <div class="category-actions">
            <button class="category-edit" onclick="openEditModal('${c.category_id}', '${c.category_name}')">Edit</button>
            <button class="category-delete" onclick="openDeleteModal('${c.category_id}', '${c.category_name}')">Delete</button>
          </div>
        </div>
      `;
    });
  } else {
    container.innerHTML = `
      <p style="text-align:center; color:#666; font-size:1.1em; width:100%; margin-top:30px;">
        No categories found.
      </p>
    `;
  }
}

// ========== ADD CATEGORY ==========
async function addCategory() {
  const name = document.getElementById('add_name').value.trim();
  if (!name) return showToast('Please enter a category name.', 'error');

  const res = await fetch('../api/categories/create.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ category_name: name })
  });

  const data = await res.json();
  const type = data.status === 'success' ? 'success' : 'error'; // 🔹 check status key
  showToast(data.message, type);

  closeModal('addCategoryModal');
  loadCategories();
}

// ========== UPDATE CATEGORY ==========
async function updateCategory() {
  const id = document.getElementById('edit_id').value;
  const name = document.getElementById('edit_name').value.trim();
  if (!name) return showToast('Please enter a category name.', 'error');

  const res = await fetch('../api/categories/update.php', {
    method: 'PUT',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({category_id: id, category_name: name})
  });
  const data = await res.json();
  const type = data.status === 'success' ? 'info' : 'error';
showToast(data.message, type);

  closeModal('editCategoryModal');
  loadCategories();
}

// ========== DELETE CATEGORY ==========
async function deleteCategory() {
  const id = document.getElementById('delete_category_id').value;

  const res = await fetch('../api/categories/delete.php', {
    method: 'DELETE',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({category_id: id})
  });
  const data = await res.json();
  const type = data.status === 'success' ? 'success' : 'error';
showToast(data.message, type);

  closeModal('deleteCategoryModal');
  loadCategories();
}

// ========== MODAL HANDLING ==========
function openModal(id) {
  document.getElementById(id).style.display = 'block';
}

function closeModal(id) {
  document.getElementById(id).style.display = 'none';
}

function openEditModal(id, name) {
  document.getElementById('editCategoryModal').style.display = 'block';
  document.getElementById('edit_id').value = id;
  document.getElementById('edit_name').value = name;
}

function openDeleteModal(id, name) {
  document.getElementById('deleteCategoryModal').style.display = 'block';
  document.getElementById('delete_category_id').value = id;
  document.getElementById('delete_category_name').textContent = name;
}

window.onclick = function(event) {
  const modals = document.querySelectorAll('.category-modal');
  modals.forEach(modal => {
    if (event.target === modal) modal.style.display = 'none';
  });
};

// Load categories on page load
loadCategories();


// Budgets JS
function openAddModal() {
  document.getElementById('addModal').style.display = 'flex';
}
function closeAddModal() {
  document.getElementById('addModal').style.display = 'none';
}
function openEditModal(id, category, amount, start, end) {
  document.getElementById('edit_budget_id').value = id;
  document.getElementById('edit_category_id').value = category;
  document.getElementById('edit_amount_limit').value = amount;
  document.getElementById('edit_start_date').value = start;
  document.getElementById('edit_end_date').value = end;
  document.getElementById('editModal').style.display = 'flex';
}
function closeEditModal() {
  document.getElementById('editModal').style.display = 'none';
}

