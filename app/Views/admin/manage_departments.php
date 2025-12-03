<?= $this->include('templates/header') ?>

<!-- Manage Departments View - Admin only functionality for department management -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-2 fw-bold">🏢 Manage Departments</h2>
                                <p class="mb-0 opacity-75">Create, edit, and manage academic departments in the system</p>
                            </div>
                            <div>
                                <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-light btn-sm">
                                    ← Back to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Department Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">🏢</div>
                    <div class="display-5 fw-bold"><?= count($departments) ?></div>
                    <div class="fw-semibold">Total Departments</div>
                    <small class="opacity-75">In the system</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">✅</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($departments, fn($d) => $d['is_active'] == 1)) ?></div>
                    <div class="fw-semibold">Active</div>
                    <small class="opacity-75">Currently active</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">❌</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($departments, fn($d) => $d['is_active'] == 0)) ?></div>
                    <div class="fw-semibold">Inactive</div>
                    <small class="opacity-75">Deactivated</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">👨‍💼</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($departments, fn($d) => !empty($d['head_user_id']))) ?></div>
                    <div class="fw-semibold">With Heads</div>
                    <small class="opacity-75">Assigned heads</small>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold text-dark">⚡ Department Management</h5>
                            <a href="<?= base_url('admin/manage_departments?action=create') ?>" class="btn btn-success">
                                ➕ Create New Department
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Department Form (shown when action=create) -->
        <?php if ($showCreateForm): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-success">
                    <div class="card-header bg-success text-white border-0">
                        <h5 class="mb-0">➕ Create New Department</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('admin/manage_departments?action=create') ?>">
                            <?= csrf_field() ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="department_code" class="form-label fw-semibold">Department Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control text-uppercase" id="department_code" name="department_code" 
                                               value="<?= old('department_code') ?>" required 
                                               pattern="[A-Z0-9\-]+" 
                                               title="Department code must contain only uppercase letters, numbers, and hyphens"
                                               minlength="2" maxlength="20"
                                               placeholder="e.g., CS, IT, ENG">
                                        <small class="text-muted">Unique code (uppercase letters, numbers, hyphens only)</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="department_name" class="form-label fw-semibold">Department Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="department_name" name="department_name" 
                                               value="<?= old('department_name') ?>" required 
                                               minlength="3" maxlength="150"
                                               placeholder="e.g., Computer Science">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="head_user_id" class="form-label fw-semibold">Department Head <small class="text-muted">(optional)</small></label>
                                        <select class="form-select" id="head_user_id" name="head_user_id">
                                            <option value="">-- Select Department Head --</option>
                                            <?php foreach ($instructors as $instructor): ?>
                                                <option value="<?= $instructor['id'] ?>" <?= old('head_user_id') == $instructor['id'] ? 'selected' : '' ?>>
                                                    <?= esc($instructor['first_name'] . ' ' . $instructor['last_name']) ?> 
                                                    (<?= esc($instructor['employee_id']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="description" class="form-label fw-semibold">Description <small class="text-muted">(optional)</small></label>
                                        <textarea class="form-control" id="description" name="description" rows="3" 
                                                  maxlength="500" placeholder="Brief description of the department"><?= old('description') ?></textarea>
                                        <small class="text-muted">Maximum 500 characters</small>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?= base_url('admin/manage_departments') ?>" class="btn btn-secondary">
                                    ❌ Cancel
                                </a>
                                <button type="submit" class="btn btn-success">
                                    ✅ Create Department
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Edit Department Form (shown when editing) -->
        <?php if ($showEditForm && isset($editDepartment)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-warning">
                    <div class="card-header bg-warning text-white border-0">
                        <h5 class="mb-0">✏️ Edit Department: <?= esc($editDepartment['department_name']) ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('admin/manage_departments?action=edit&id=' . $editDepartment['id']) ?>">
                            <?= csrf_field() ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="department_code" class="form-label fw-semibold">Department Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control text-uppercase" id="department_code" name="department_code" 
                                               value="<?= old('department_code', $editDepartment['department_code']) ?>" required 
                                               pattern="[A-Z0-9\-]+" 
                                               title="Department code must contain only uppercase letters, numbers, and hyphens"
                                               minlength="2" maxlength="20">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="department_name" class="form-label fw-semibold">Department Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="department_name" name="department_name" 
                                               value="<?= old('department_name', $editDepartment['department_name']) ?>" required 
                                               minlength="3" maxlength="150">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="head_user_id" class="form-label fw-semibold">Department Head <small class="text-muted">(optional)</small></label>
                                        <select class="form-select" id="head_user_id" name="head_user_id">
                                            <option value="">-- Select Department Head --</option>
                                            <?php foreach ($instructors as $instructor): ?>
                                                <option value="<?= $instructor['id'] ?>" 
                                                    <?= old('head_user_id', $editDepartment['head_user_id']) == $instructor['id'] ? 'selected' : '' ?>>
                                                    <?= esc($instructor['first_name'] . ' ' . $instructor['last_name']) ?> 
                                                    (<?= esc($instructor['employee_id']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="description" class="form-label fw-semibold">Description <small class="text-muted">(optional)</small></label>
                                        <textarea class="form-control" id="description" name="description" rows="3" 
                                                  maxlength="500"><?= old('description', $editDepartment['description']) ?></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?= base_url('admin/manage_departments') ?>" class="btn btn-secondary">
                                    ❌ Cancel
                                </a>
                                <button type="submit" class="btn btn-warning text-white">
                                    💾 Update Department
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Departments Table -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold text-dark">📋 Departments List</h5>
                            <div class="text-muted small">
                                Total: <?= count($departments) ?> departments
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="fw-semibold border-0 text-center">ID</th>
                                        <th class="fw-semibold border-0">Code</th>
                                        <th class="fw-semibold border-0">Department Name</th>
                                        <th class="fw-semibold border-0">Department Head</th>
                                        <th class="fw-semibold border-0">Description</th>
                                        <th class="fw-semibold border-0 text-center">Status</th>
                                        <th class="fw-semibold border-0 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($departments)): ?>
                                        <?php foreach ($departments as $department): ?>
                                        <tr class="border-bottom">
                                            <td class="text-center">
                                                <span class="badge bg-secondary rounded-pill px-2 py-1"><?= $department['id'] ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary fs-6"><?= esc($department['department_code']) ?></span>
                                            </td>
                                            <td>
                                                <strong class="text-dark"><?= esc($department['department_name']) ?></strong>
                                            </td>
                                            <td class="text-muted">
                                                <?php if (!empty($department['head_name'])): ?>
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px;">
                                                            <?= strtoupper(substr($department['first_name'] ?? 'N', 0, 1)) ?>
                                                        </div>
                                                        <div>
                                                            <div><?= esc($department['head_name']) ?></div>
                                                            <small class="text-muted"><?= esc($department['employee_id']) ?></small>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <em class="text-muted">No head assigned</em>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-muted">
                                                <?= !empty($department['description']) ? esc(substr($department['description'], 0, 50)) . (strlen($department['description']) > 50 ? '...' : '') : '<em>No description</em>' ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($department['is_active']): ?>
                                                    <span class="badge bg-success">✅ Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">❌ Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <!-- Edit Button -->
                                                    <a href="<?= base_url('admin/manage_departments?action=edit&id=' . $department['id']) ?>" 
                                                       class="btn btn-outline-warning btn-sm" 
                                                       title="Edit Department">
                                                        ✏️
                                                    </a>
                                                    
                                                    <!-- Toggle Status Button -->
                                                    <a href="<?= base_url('admin/manage_departments?action=toggle_status&id=' . $department['id']) ?>" 
                                                       class="btn btn-outline-<?= $department['is_active'] ? 'secondary' : 'success' ?> btn-sm" 
                                                       onclick="return confirm('Are you sure you want to <?= $department['is_active'] ? 'deactivate' : 'activate' ?> this department?')"
                                                       title="<?= $department['is_active'] ? 'Deactivate' : 'Activate' ?> Department">
                                                        <?= $department['is_active'] ? '🔒' : '🔓' ?>
                                                    </a>
                                                    
                                                    <!-- Delete Button -->
                                                    <a href="<?= base_url('admin/manage_departments?action=delete&id=' . $department['id']) ?>" 
                                                       class="btn btn-outline-danger btn-sm" 
                                                       onclick="return confirm('Are you sure you want to delete this department?\n\nDepartment: <?= esc($department['department_name']) ?>\n\nThis action cannot be undone!')"
                                                       title="Delete Department">
                                                        🗑️
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <div class="text-muted">
                                                    <div class="display-1 mb-3">🏢</div>
                                                    <h5>No departments found</h5>
                                                    <p>Start by creating a new department using the button above.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>            
            </div>
        </div>
    </div>
</div>
