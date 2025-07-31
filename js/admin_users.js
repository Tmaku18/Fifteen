// Admin Users Management JavaScript

// Store user data for editing
let currentUsers = {};

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Store user data from the table
    const userRows = document.querySelectorAll('.user-row');
    userRows.forEach(row => {
        const cells = row.children;
        const userId = cells[0].textContent;
        const username = cells[1].textContent;
        const isAdmin = cells[2].querySelector('.role-badge').classList.contains('admin');
        const isActive = cells[3].querySelector('.status-badge').classList.contains('active');
        
        currentUsers[userId] = {
            username: username,
            isAdmin: isAdmin,
            isActive: isActive
        };
    });
});

// Edit user function
function editUser(userId) {
    const user = currentUsers[userId];
    if (!user) return;
    
    // Populate form
    document.getElementById('editUserId').value = userId;
    document.getElementById('editUsername').value = user.username;
    document.getElementById('editIsAdmin').checked = user.isAdmin;
    document.getElementById('editIsActive').checked = user.isActive;
    
    // Show modal
    showModal('editUserModal');
}

// Reset password function
function resetPassword(userId) {
    document.getElementById('resetUserId').value = userId;
    document.getElementById('newPassword').value = '';
    showModal('resetPasswordModal');
}

// Delete user function
function deleteUser(userId) {
    const user = currentUsers[userId];
    if (!user) return;
    
    if (confirm(`Are you sure you want to delete user "${user.username}"? This action cannot be undone.`)) {
        // Create and submit form
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_user">
            <input type="hidden" name="user_id" value="${userId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Modal functions
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.display = 'block';
    
    // Close modal when clicking outside
    modal.onclick = function(event) {
        if (event.target === modal) {
            closeModal(modalId);
        }
    };
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Close modals with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (modal.style.display === 'block') {
                modal.style.display = 'none';
            }
        });
    }
});

// Form validation
document.getElementById('editUserForm').addEventListener('submit', function(event) {
    const username = document.getElementById('editUsername').value.trim();
    
    if (username.length < 3) {
        alert('Username must be at least 3 characters long.');
        event.preventDefault();
        return false;
    }
    
    if (!/^[a-zA-Z0-9_]+$/.test(username)) {
        alert('Username can only contain letters, numbers, and underscores.');
        event.preventDefault();
        return false;
    }
});

document.getElementById('resetPasswordForm').addEventListener('submit', function(event) {
    const password = document.getElementById('newPassword').value;
    
    if (password.length < 6) {
        alert('Password must be at least 6 characters long.');
        event.preventDefault();
        return false;
    }
});

// Search functionality
function createSearchBox() {
    const usersPanel = document.querySelector('.users-panel h2');
    const searchContainer = document.createElement('div');
    searchContainer.className = 'search-container';
    searchContainer.innerHTML = `
        <input type="text" id="userSearch" placeholder="Search users..." class="search-input">
        <i class="fas fa-search search-icon"></i>
    `;
    
    usersPanel.parentNode.insertBefore(searchContainer, usersPanel.nextSibling);
    
    // Add search functionality
    document.getElementById('userSearch').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('.user-row');
        
        rows.forEach(row => {
            const username = row.querySelector('.username').textContent.toLowerCase();
            const visible = username.includes(searchTerm);
            row.style.display = visible ? '' : 'none';
        });
    });
}

// Initialize search box
document.addEventListener('DOMContentLoaded', createSearchBox);
