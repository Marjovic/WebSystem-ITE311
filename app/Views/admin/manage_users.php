<?= $this->include('templates/header') ?>

<!-- Manage Users View - Admin only functionality for user management -->
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
                            </div>                            <div>
                                <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-light btn-sm">
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
                    <div class="display-5 fw-bold"><?= count(array_filter($users, fn($u) => strtolower($u['role_name'] ?? '') === 'admin')) ?></div>
                    <div class="fw-semibold">Admins</div>
                    <small class="opacity-75">System administrators</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">👨‍🏫</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($users, fn($u) => in_array(strtolower($u['role_name'] ?? ''), ['teacher', 'instructor']))) ?></div>
                    <div class="fw-semibold">Teachers</div>
                    <small class="opacity-75">Creating content</small>
                </div>
            </div>            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">🎓</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($users, fn($u) => strtolower($u['role_name'] ?? '') === 'student')) ?></div>
                    <div class="fw-semibold">Students</div>
                    <small class="opacity-75">Learning actively</small>
                </div>
            </div>
        </div>        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 pb-0">
                        <div class="d-flex justify-content-between align-items-center">                            <h5 class="mb-0 fw-bold text-dark">⚡ User Management</h5>
                            <a href="<?= base_url('admin/manage_users?action=create') ?>" class="btn btn-success">
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
                    <div class="card-body">                        <form method="post" action="<?= base_url('admin/manage_users?action=create') ?>">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="first_name" class="form-label fw-semibold">First Name</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" 
                                               value="<?= old('first_name') ?>" required 
                                               pattern="[A-Za-zñÑ\s\-\.]+" 
                                               title="First name can only contain letters, spaces, hyphens, and periods"
                                               minlength="2" maxlength="100">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="middle_name" class="form-label fw-semibold">Middle Name <small class="text-muted">(optional)</small></label>
                                        <input type="text" class="form-control" id="middle_name" name="middle_name" 
                                               value="<?= old('middle_name') ?>" 
                                               pattern="[A-Za-zñÑ\s\-\.]+" 
                                               title="Middle name can only contain letters, spaces, hyphens, and periods"
                                               maxlength="100">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="last_name" class="form-label fw-semibold">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" 
                                               value="<?= old('last_name') ?>" required 
                                               pattern="[A-Za-zñÑ\s\-\.]+" 
                                               title="Last name can only contain letters, spaces, hyphens, and periods"
                                               minlength="2" maxlength="100">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label fw-semibold">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?= old('email') ?>" 
                                        required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label fw-semibold">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" 
                                               required minlength="6">
                                        <div class="form-text">Password must be at least 6 characters long</div>
                                    </div>
                                </div>                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="role" class="form-label fw-semibold">Role</label>
                                        <select class="form-select" id="role" name="role" required>
                                            <option value="">Select Role</option>
                                            <option value="admin" <?= old('role') === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            <option value="teacher" <?= old('role') === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                                            <option value="student" <?= old('role') === 'student' ? 'selected' : '' ?>>Student</option>
                                        </select>
                                    </div>
                                </div>                                <div class="col-md-6" id="year_level_container" style="display: none;">
                                    <div class="mb-3">
                                        <label for="year_level_id" class="form-label fw-semibold">Year Level <span class="text-danger">*</span></label>
                                        <select class="form-select" id="year_level_id" name="year_level_id">
                                            <option value="">Select Year Level</option>
                                            <?php foreach ($yearLevels as $level): ?>
                                                <option value="<?= $level['id'] ?>" <?= old('year_level_id') == $level['id'] ? 'selected' : '' ?>>
                                                    <?= esc($level['year_level_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">Required for student accounts</div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    💾 Create User
                                </button>
                                <a href="<?= base_url('admin/manage_users') ?>" class="btn btn-outline-secondary">
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
                <div class="card border-0 shadow-sm rounded-3 border-warning">                    <div class="card-header bg-warning text-dark border-0">
                        <?php 
                        $editUserFullName = trim(($editUser['first_name'] ?? '') . ' ' . ($editUser['middle_name'] ?? '') . ' ' . ($editUser['last_name'] ?? ''));
                        ?>
                        <h5 class="mb-0">✏️ Edit User: <?= esc($editUserFullName) ?></h5>
                    </div>
                    <div class="card-body">                        <form method="post" action="<?= base_url('admin/manage_users?action=edit&id=' . $editUser['id']) ?>">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="edit_first_name" class="form-label fw-semibold">First Name</label>
                                        <input type="text" class="form-control" id="edit_first_name" name="first_name" 
                                               value="<?= old('first_name', $editUser['first_name'] ?? '') ?>" required 
                                               pattern="[A-Za-zñÑ\s\-\.]+" 
                                               title="First name can only contain letters, spaces, hyphens, and periods"
                                               minlength="2" maxlength="100">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="edit_middle_name" class="form-label fw-semibold">Middle Name <small class="text-muted">(optional)</small></label>
                                        <input type="text" class="form-control" id="edit_middle_name" name="middle_name" 
                                               value="<?= old('middle_name', $editUser['middle_name'] ?? '') ?>" 
                                               pattern="[A-Za-zñÑ\s\-\.]+" 
                                               title="Middle name can only contain letters, spaces, hyphens, and periods"
                                               maxlength="100">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="edit_last_name" class="form-label fw-semibold">Last Name</label>
                                        <input type="text" class="form-control" id="edit_last_name" name="last_name" 
                                               value="<?= old('last_name', $editUser['last_name'] ?? '') ?>" required 
                                               pattern="[A-Za-zñÑ\s\-\.]+" 
                                               title="Last name can only contain letters, spaces, hyphens, and periods"
                                               minlength="2" maxlength="100">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_email" class="form-label fw-semibold">Email Address</label>
                                        <input type="email" class="form-control" id="edit_email" name="email" value="<?= old('email', $editUser['email']) ?>" 
                                        required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_password" class="form-label fw-semibold">Password <small class="text-muted">(leave blank to keep current)</small></label>
                                        <input type="password" class="form-control" id="edit_password" name="password" 
                                               minlength="6">
                                        <div class="form-text">Password must be at least 6 characters long (if changing)</div>
                                    </div>
                                </div>                                <div class="col-md-6">                                    <div class="mb-3">
                                        <label for="edit_role" class="form-label fw-semibold">Role</label>
                                        <select class="form-select" id="edit_role" name="role" required>
                                            <option value="admin" <?= old('role', strtolower($editUser['role_name'] ?? '')) === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            <option value="teacher" <?= old('role', strtolower($editUser['role_name'] ?? '')) === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                                            <option value="student" <?= old('role', strtolower($editUser['role_name'] ?? '')) === 'student' ? 'selected' : '' ?>>Student</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6" id="edit_year_level_container" style="display: <?= old('role', strtolower($editUser['role_name'] ?? '')) === 'student' ? 'block' : 'none' ?>;">
                                    <div class="mb-3">
                                        <label for="edit_year_level" class="form-label fw-semibold">Year Level <span class="text-danger">*</span></label>
                                        <select class="form-select" id="edit_year_level" name="year_level_id">
                                            <option value="">Select Year Level</option>
                                            <?php foreach ($yearLevels as $level): ?>
                                                <option value="<?= $level['id'] ?>" <?= old('year_level_id', $roleSpecificData['year_level_id'] ?? '') == $level['id'] ? 'selected' : '' ?>>
                                                    <?= esc($level['year_level_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">Required for student accounts</div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-warning">
                                    💾 Update User
                                </button>
                                <a href="<?= base_url('admin/manage_users') ?>" class="btn btn-outline-secondary">
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
                <div class="card border-0 shadow-sm rounded-3">                    
                    <div class="card-header bg-white border-0 pb-3">
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
                                        ?>                                        
                                        <?php foreach ($users as $user): ?>
                                        <tr class="border-bottom">
                                            <td class="text-center">
                                                <span class="badge bg-secondary rounded-pill px-2 py-1"><?= $user['id'] ?></span>
                                            </td>                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php 
                                                    $fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['middle_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                                                    $initial = strtoupper(substr($user['first_name'] ?? 'U', 0, 1));
                                                    ?>
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                        <?= $initial ?>
                                                    </div>
                                                    <div>
                                                        <strong class="text-dark"><?= esc($fullName) ?></strong>
                                                        <?php if ($user['id'] == $currentAdminID): ?>
                                                            <span class="badge bg-info ms-2 small">You</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-muted"><?= esc($user['email']) ?></td>                                            <td class="text-center">
                                                <?php
                                                $roleStyles = [
                                                    'admin' => ['color' => 'danger', 'icon' => '👑'],
                                                    'teacher' => ['color' => 'primary', 'icon' => '👨‍🏫'],
                                                    'instructor' => ['color' => 'primary', 'icon' => '👨‍🏫'],
                                                    'student' => ['color' => 'success', 'icon' => '🎓']
                                                ];
                                                $userRole = strtolower($user['role_name'] ?? 'student');
                                                $style = $roleStyles[$userRole] ?? ['color' => 'secondary', 'icon' => '👤'];
                                                ?>
                                                <span class="badge bg-<?= $style['color'] ?> rounded-pill px-3 py-2">
                                                    <?= $style['icon'] ?> <?= esc($user['role_name'] ?? 'User') ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <small class="text-muted">
                                                    <?= date('M j, Y', strtotime($user['created_at'])) ?>
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">                                                      <?php 
                                                    // Check if current admin can edit this user
                                                    $canEdit = ($user['id'] != $currentAdminID);
                                                    $canDelete = (strtolower($user['role_name'] ?? '') !== 'admin' && $user['id'] != $currentAdminID);
                                                    ?>
                                                    
                                                    <!-- Edit Button -->                                                    
                                                     <?php if ($canEdit): ?>
                                                        <a href="<?= base_url('admin/manage_users?action=edit&id=' . $user['id']) ?>" 
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
                                                        <?php 
                                                        $deleteUserName = trim(($user['first_name'] ?? '') . ' ' . ($user['middle_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                                                        ?>
                                                         <a href="<?= base_url('admin/manage_users?action=delete&id=' . $user['id']) ?>" 
                                                           class="btn btn-outline-danger btn-sm" 
                                                           onclick="return confirm('Are you sure you want to delete this user?\n\nUser: <?= esc($deleteUserName) ?>\nEmail: <?= esc($user['email']) ?>\n\nThis action cannot be undone!')"
                                                           title="Delete User">
                                                            🗑️
                                                        </a>
                                                    <?php else: ?>
                                                        <button class="btn btn-outline-secondary btn-sm" 
                                                                disabled 
                                                                title="Cannot delete admin accounts">
                                                            🛡️
                                                        </button>
                                                    <?php endif; ?>                                                
                                                </div>
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
document.addEventListener('DOMContentLoaded', function() {    // Show/Hide year_level field based on role selection - CREATE FORM
    const roleSelect = document.getElementById('role');
    const yearLevelContainer = document.getElementById('year_level_container');
    const yearLevelSelect = document.getElementById('year_level_id');
    
    if (roleSelect && yearLevelContainer) {
        roleSelect.addEventListener('change', function() {
            if (this.value === 'student') {
                yearLevelContainer.style.display = 'block';
                yearLevelSelect.setAttribute('required', 'required');
            } else {
                yearLevelContainer.style.display = 'none';
                yearLevelSelect.removeAttribute('required');
                yearLevelSelect.value = '';
            }
        });
        
        // Trigger on page load if role is already selected
        if (roleSelect.value === 'student') {
            yearLevelContainer.style.display = 'block';
            yearLevelSelect.setAttribute('required', 'required');
        }
    }
    
    // Show/Hide year_level field based on role selection - EDIT FORM
    const editRoleSelect = document.getElementById('edit_role');
    const editYearLevelContainer = document.getElementById('edit_year_level_container');
    const editYearLevelSelect = document.getElementById('edit_year_level_id');
    
    if (editRoleSelect && editYearLevelContainer) {
        editRoleSelect.addEventListener('change', function() {
            if (this.value === 'student') {
                editYearLevelContainer.style.display = 'block';
                editYearLevelSelect.setAttribute('required', 'required');
            } else {
                editYearLevelContainer.style.display = 'none';
                editYearLevelSelect.removeAttribute('required');
                editYearLevelSelect.value = '';
            }
        });
        
        // Trigger on page load if role is already student
        if (editRoleSelect.value === 'student') {
            editYearLevelContainer.style.display = 'block';
            editYearLevelSelect.setAttribute('required', 'required');
        }
    }
    
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

