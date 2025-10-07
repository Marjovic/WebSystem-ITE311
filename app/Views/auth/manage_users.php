<?= $this->include('templates/header') ?>

<!-- Manage Users View - Admin only functionality for user management -->
<!-- Professional and clean design with improved color scheme and layout -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
          <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-2 fw-bold">👥 Manage Users</h2>
                                <p class="mb-0 opacity-75">Create, edit, and manage user accounts in the system</p>
                            </div>
                            <div>
                                <a href="<?= base_url('dashboard') ?>" class="btn btn-light btn-sm">
                                    ← Back to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- User Statistics Cards - Moved to top with new order -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">👥</div>
                    <div class="display-5 fw-bold"><?= count($users) ?></div>
                    <div class="fw-semibold">Total Users</div>
                    <small class="opacity-75">Active in system</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-danger text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">👑</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($users, fn($u) => $u['role'] === 'admin')) ?></div>
                    <div class="fw-semibold">Admins</div>
                    <small class="opacity-75">System administrators</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">👨‍🏫</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($users, fn($u) => $u['role'] === 'teacher')) ?></div>
                    <div class="fw-semibold">Teachers</div>
                    <small class="opacity-75">Creating content</small>
                </div>
            </div>            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">🎓</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($users, fn($u) => $u['role'] === 'student')) ?></div>
                    <div class="fw-semibold">Students</div>
                    <small class="opacity-75">Learning actively</small>
                </div>
            </div>
        </div>        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold text-dark">⚡ User Management</h5>
                            <a href="<?= base_url('dashboard?action=createUser') ?>" class="btn btn-success">
                                ➕ Create New User
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create User Form (shown when action=create) -->
        <?php if ($showCreateForm): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-success">
                    <div class="card-header bg-success text-white border-0">
                        <h5 class="mb-0">➕ Create New User</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('dashboard?action=createUser') ?>">
                            <div class="row">                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label fw-semibold">Full Name</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?= old('name') ?>" required 
                                               pattern="[A-Za-zñÑ\s]+" 
                                               title="Name can only contain letters (including ñ/Ñ) and spaces"
                                               minlength="3" maxlength="100">
                                        <div class="form-text">Enter full name (letters including ñ/Ñ and spaces only, 3-100 characters)</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label fw-semibold">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?= old('email') ?>" required>
                                    </div>
                                </div>                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label fw-semibold">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" 
                                               required minlength="6">
                                        <div class="form-text">Password must be at least 6 characters long</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="role" class="form-label fw-semibold">Role</label>
                                        <select class="form-select" id="role" name="role" required>
                                            <option value="">Select Role</option>
                                            <option value="admin" <?= old('role') === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            <option value="teacher" <?= old('role') === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                                            <option value="student" <?= old('role') === 'student' ? 'selected' : '' ?>>Student</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    💾 Create User
                                </button>
                                <a href="<?= base_url('dashboard?action=manageUsers') ?>" class="btn btn-outline-secondary">
                                    ❌ Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Edit User Form (shown when editing) -->
        <?php if ($showEditForm && $editUser): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-warning">
                    <div class="card-header bg-warning text-dark border-0">
                        <h5 class="mb-0">✏️ Edit User: <?= esc($editUser['name']) ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('dashboard?action=editUser&id=' . $editUser['id']) ?>">
                            <div class="row">                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_name" class="form-label fw-semibold">Full Name</label>
                                        <input type="text" class="form-control" id="edit_name" name="name" 
                                               value="<?= old('name', $editUser['name']) ?>" required 
                                               pattern="[A-Za-zñÑ\s]+" 
                                               title="Name can only contain letters (including ñ/Ñ) and spaces"
                                               minlength="3" maxlength="100">
                                        <div class="form-text">Enter full name (letters including ñ/Ñ and spaces only, 3-100 characters)</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_email" class="form-label fw-semibold">Email Address</label>
                                        <input type="email" class="form-control" id="edit_email" name="email" value="<?= old('email', $editUser['email']) ?>" required>
                                    </div>
                                </div>                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_password" class="form-label fw-semibold">Password <small class="text-muted">(leave blank to keep current)</small></label>
                                        <input type="password" class="form-control" id="edit_password" name="password" 
                                               minlength="6">
                                        <div class="form-text">Password must be at least 6 characters long (if changing)</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_role" class="form-label fw-semibold">Role</label>
                                        <select class="form-select" id="edit_role" name="role" required>
                                            <option value="teacher" <?= old('role', $editUser['role']) === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                                            <option value="student" <?= old('role', $editUser['role']) === 'student' ? 'selected' : '' ?>>Student</option>
                                        </select>
                                        <small class="text-muted">Admin accounts cannot be edited</small>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-warning">
                                    💾 Update User
                                </button>
                                <a href="<?= base_url('dashboard?action=manageUsers') ?>" class="btn btn-outline-secondary">
                                    ❌ Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Users List -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">                    <div class="card-header bg-white border-0 pb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0 fw-bold text-dark">👤 All Users</h5>
                                <small class="text-muted">Manage all system users</small>
                            </div>
                            <div class="text-muted small">
                                Total: <?= count($users) ?> users
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="fw-semibold border-0 text-center">ID</th>
                                        <th class="fw-semibold border-0">User</th>
                                        <th class="fw-semibold border-0">Email</th>
                                        <th class="fw-semibold border-0 text-center">Role</th>
                                        <th class="fw-semibold border-0 text-center">Created</th>
                                        <th class="fw-semibold border-0 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($users)): ?>
                                        <?php 
                                        // Sort users by ID in ascending order (1, 2, 3, etc.)
                                        usort($users, function($a, $b) {
                                            return $a['id'] <=> $b['id'];
                                        });
                                        ?>                                        <?php foreach ($users as $user): ?>
                                        <tr class="border-bottom">
                                            <td class="text-center">
                                                <span class="badge bg-secondary rounded-pill px-2 py-1"><?= $user['id'] ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <strong class="text-dark"><?= esc($user['name']) ?></strong>
                                                        <?php if ($user['id'] == $currentAdminID): ?>
                                                            <span class="badge bg-info ms-2 small">You</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-muted"><?= esc($user['email']) ?></td>
                                            <td class="text-center">
                                                <?php
                                                $roleStyles = [
                                                    'admin' => ['color' => 'danger', 'icon' => '👑'],
                                                    'teacher' => ['color' => 'primary', 'icon' => '👨‍🏫'],
                                                    'student' => ['color' => 'success', 'icon' => '🎓']
                                                ];
                                                $style = $roleStyles[$user['role']];
                                                ?>
                                                <span class="badge bg-<?= $style['color'] ?> rounded-pill px-3 py-2">
                                                    <?= $style['icon'] ?> <?= ucfirst($user['role']) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <small class="text-muted">
                                                    <?= date('M j, Y', strtotime($user['created_at'])) ?>
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">                                                    <?php 
                                                    // Check if current admin can edit this user
                                                    $canEdit = ($user['role'] !== 'admin' && $user['id'] != $currentAdminID);
                                                    $canDelete = ($user['role'] !== 'admin' && $user['id'] != $currentAdminID);
                                                    ?>
                                                    
                                                    <!-- Edit Button -->
                                                    <?php if ($canEdit): ?>
                                                        <a href="<?= base_url('dashboard?action=editUser&id=' . $user['id']) ?>" 
                                                           class="btn btn-outline-warning btn-sm me-1" 
                                                           title="Edit User">
                                                            ✏️
                                                        </a>
                                                    <?php else: ?>
                                                        <button class="btn btn-outline-secondary btn-sm me-1" 
                                                                disabled 
                                                                title="Cannot edit admin accounts">
                                                            🔒
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Delete Button -->
                                                    <?php if ($canDelete): ?>
                                                        <a href="<?= base_url('dashboard?action=deleteUser&id=' . $user['id']) ?>" 
                                                           class="btn btn-outline-danger btn-sm" 
                                                           onclick="return confirm('Are you sure you want to delete this user?\n\nUser: <?= esc($user['name']) ?>\nEmail: <?= esc($user['email']) ?>\n\nThis action cannot be undone!')"
                                                           title="Delete User">
                                                            🗑️
                                                        </a>
                                                    <?php else: ?>
                                                        <button class="btn btn-outline-secondary btn-sm" 
                                                                disabled 
                                                                title="Cannot delete admin accounts">
                                                            🛡️
                                                        </button>
                                                    <?php endif; ?>                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">
                                                <div class="mb-3">
                                                    <span style="font-size: 3rem; opacity: 0.3;">👥</span>
                                                </div>
                                                <p class="mb-0">No users found in the system.</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>        
        </div>    
    </div>
</div>

<!-- JavaScript for Enhanced Validation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Name field validation for both create and edit forms
    const nameFields = document.querySelectorAll('input[name="name"]');
      nameFields.forEach(function(field) {
        field.addEventListener('input', function(e) {
            const value = e.target.value;
            const validPattern = /^[A-Za-zñÑ\s]*$/;
            
            // Remove invalid characters as user types
            if (!validPattern.test(value)) {
                e.target.value = value.replace(/[^A-Za-zñÑ\s]/g, '');
            }
            
            // Visual feedback
            if (e.target.value.length >= 3 && validPattern.test(e.target.value)) {
                e.target.classList.remove('is-invalid');
                e.target.classList.add('is-valid');
            } else if (e.target.value.length > 0) {
                e.target.classList.remove('is-valid');
                e.target.classList.add('is-invalid');
            } else {
                e.target.classList.remove('is-valid', 'is-invalid');
            }
        });
    });
    
    // Email field validation
    const emailFields = document.querySelectorAll('input[name="email"]');
    
    emailFields.forEach(function(field) {
        field.addEventListener('input', function(e) {
            const value = e.target.value;
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            // Visual feedback for email
            if (emailPattern.test(value)) {
                e.target.classList.remove('is-invalid');
                e.target.classList.add('is-valid');
            } else if (value.length > 0) {
                e.target.classList.remove('is-valid');
                e.target.classList.add('is-invalid');
            } else {
                e.target.classList.remove('is-valid', 'is-invalid');
            }
        });
    });
    
    // Password field validation
    const passwordFields = document.querySelectorAll('input[name="password"]');
    
    passwordFields.forEach(function(field) {
        field.addEventListener('input', function(e) {
            const value = e.target.value;
            
            // Skip validation for edit password field if empty (optional)
            if (field.id === 'edit_password' && value.length === 0) {
                e.target.classList.remove('is-valid', 'is-invalid');
                return;
            }
            
            // Visual feedback for password
            if (value.length >= 6) {
                e.target.classList.remove('is-invalid');
                e.target.classList.add('is-valid');
            } else if (value.length > 0) {
                e.target.classList.remove('is-valid');
                e.target.classList.add('is-invalid');
            } else {
                e.target.classList.remove('is-valid', 'is-invalid');
            }
        });
    });
});
</script>

