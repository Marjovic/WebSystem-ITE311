<?= $this->include('templates/header') ?>

<!-- Manage Courses View - Admin only functionality for course management -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-2 fw-bold">📚 Manage Courses</h2>
                                <p class="mb-0 opacity-75">Create, edit, and manage courses in the learning management system</p>
                            </div>                            <div>
                                <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-light btn-sm">
                                    ← Back to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>        </div>

        <!-- Course Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">📚</div>
                    <div class="display-5 fw-bold"><?= count($courses) ?></div>
                    <div class="fw-semibold">Total Courses</div>
                    <small class="opacity-75">In the system</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">✅</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($courses, fn($c) => $c['status'] === 'active')) ?></div>
                    <div class="fw-semibold">Active</div>
                    <small class="opacity-75">Currently running</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">📝</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($courses, fn($c) => $c['status'] === 'draft')) ?></div>
                    <div class="fw-semibold">Draft</div>
                    <small class="opacity-75">Being prepared</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-secondary text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">🎯</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($courses, fn($c) => $c['status'] === 'completed')) ?></div>
                    <div class="fw-semibold">Completed</div>
                    <small class="opacity-75">Finished courses</small>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 pb-0">
                        <div class="d-flex justify-content-between align-items-center">                            <h5 class="mb-0 fw-bold text-dark">⚡ Course Management</h5>
                            <a href="<?= base_url('admin/manage_courses?action=create') ?>" class="btn btn-success">
                                ➕ Create New Course
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Course Form (shown when action=create) -->
        <?php if ($showCreateForm): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-success">
                    <div class="card-header bg-success text-white border-0">
                        <h5 class="mb-0">➕ Create New Course</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('admin/manage_courses?action=create') ?>">
                            <?= csrf_field() ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="title" class="form-label fw-semibold">Course Title</label>                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?= old('title') ?>" required 
                                               pattern="[a-zA-Z\s\-\.]+"
                                               minlength="3" maxlength="200"
                                               title="Course title can only contain letters, spaces, hyphens, and periods">
                                        <div class="form-text">Enter the full course title (letters, spaces, hyphens, periods only)</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="course_code" class="form-label fw-semibold">Course Code</label>                                        <input type="text" class="form-control" id="course_code" name="course_code" 
                                               value="<?= old('course_code') ?>" required 
                                               pattern="[A-Z]+\-?[0-9]+"
                                               minlength="3" maxlength="20" 
                                               placeholder="e.g., CS101, MATH201"                                               
                                               title="Course code must start with letters followed by numbers (e.g., CS101, CS-101)">
                                        <div class="form-text">Letters followed by numbers, with optional hyphen (e.g., CS101, CS-101, MATH201)</div>
                                    </div>
                                </div>                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="instructor_id" class="form-label fw-semibold">Instructor <span class="text-muted">(Optional)</span></label>
                                        <select class="form-select" id="instructor_id" name="instructor_id">
                                            <option value="">No instructor assigned</option>
                                            <?php foreach ($teachers as $teacher): ?>
                                                <option value="<?= $teacher['id'] ?>" <?= old('instructor_id') == $teacher['id'] ? 'selected' : '' ?>>
                                                    <?= esc($teacher['name']) ?> (<?= esc($teacher['email']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">Choose the course instructor (optional - can be assigned later)</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="category" class="form-label fw-semibold">Category</label>                                        <input type="text" class="form-control" id="category" name="category" 
                                               value="<?= old('category') ?>" 
                                               pattern="[a-zA-Z\s\-\.]+"
                                               maxlength="100" 
                                               placeholder="e.g., Computer Science, Mathematics"
                                               title="Category can only contain letters, spaces, hyphens, and periods">
                                        <div class="form-text">Letters, spaces, hyphens, periods only (max 100 characters)</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="credits" class="form-label fw-semibold">Credits</label>
                                        <input type="number" class="form-control" id="credits" name="credits" 
                                               value="<?= old('credits', 3) ?>" 
                                               min="1" max="9">
                                        <div class="form-text">Credit hours (1-9, default: 3)</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="duration_weeks" class="form-label fw-semibold">Duration (weeks)</label>
                                        <input type="number" class="form-control" id="duration_weeks" name="duration_weeks" 
                                               value="<?= old('duration_weeks', 16) ?>" 
                                               min="1" max="99">
                                        <div class="form-text">Course length (1-99, default: 16)</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="max_students" class="form-label fw-semibold">Max Students</label>
                                        <input type="number" class="form-control" id="max_students" name="max_students" 
                                               value="<?= old('max_students', 30) ?>" 
                                               min="1" max="999">
                                        <div class="form-text">Maximum enrollment (1-999, default: 30)</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="status" class="form-label fw-semibold">Status</label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="draft" <?= old('status') === 'draft' ? 'selected' : '' ?>>📝 Draft</option>
                                            <option value="active" <?= old('status') === 'active' ? 'selected' : '' ?>>✅ Active</option>
                                            <option value="completed" <?= old('status') === 'completed' ? 'selected' : '' ?>>🎯 Completed</option>
                                            <option value="cancelled" <?= old('status') === 'cancelled' ? 'selected' : '' ?>>❌ Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="start_date" class="form-label fw-semibold">Start Date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" 
                                               value="<?= old('start_date') ?>">
                                        <div class="form-text">Course start date (optional)</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="end_date" class="form-label fw-semibold">End Date</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" 
                                               value="<?= old('end_date') ?>">
                                        <div class="form-text">Course end date (optional)</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="description" class="form-label fw-semibold">Description</label>
                                        <textarea class="form-control" id="description" name="description" 
                                                  rows="4" placeholder="Enter course description..."><?= old('description') ?></textarea>
                                        <div class="form-text">Detailed course description (optional)</div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">                                <button type="submit" class="btn btn-success">
                                    💾 Create Course
                                </button>
                                <a href="<?= base_url('admin/manage_courses') ?>" class="btn btn-outline-secondary">
                                    ❌ Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Edit Course Form (shown when editing) -->
        <?php if ($showEditForm && $editCourse): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-warning">
                    <div class="card-header bg-warning text-dark border-0">
                        <h5 class="mb-0">✏️ Edit Course: <?= esc($editCourse['title']) ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('admin/manage_courses?action=edit&id=' . $editCourse['id']) ?>">
                            <?= csrf_field() ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_title" class="form-label fw-semibold">Course Title</label>                                        <input type="text" class="form-control" id="edit_title" name="title" 
                                               value="<?= old('title', $editCourse['title']) ?>" required 
                                               pattern="[a-zA-Z\s\-\.]+"
                                               minlength="3" maxlength="200"
                                               title="Course title can only contain letters, spaces, hyphens, and periods">
                                        <div class="form-text">Letters, spaces, hyphens, periods only (3-200 characters)</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_course_code" class="form-label fw-semibold">Course Code</label>                                        <input type="text" class="form-control" id="edit_course_code" name="course_code" 
                                               value="<?= old('course_code', $editCourse['course_code']) ?>" required 
                                               pattern="[A-Z]+\-?[0-9]+"
                                               minlength="3" maxlength="20"                                               title="Course code must start with letters followed by numbers (e.g., CS101, CS-101)">
                                        <div class="form-text">Letters followed by numbers, with optional hyphen (e.g., CS101, CS-101)</div>
                                    </div>
                                </div>                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_instructor_id" class="form-label fw-semibold">Instructor <span class="text-muted">(Optional)</span></label>
                                        <select class="form-select" id="edit_instructor_id" name="instructor_id">
                                            <option value="">No instructor assigned</option>
                                            <?php foreach ($teachers as $teacher): ?>
                                                <option value="<?= $teacher['id'] ?>" <?= old('instructor_id', $editCourse['instructor_id']) == $teacher['id'] ? 'selected' : '' ?>>
                                                    <?= esc($teacher['name']) ?> (<?= esc($teacher['email']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">Choose the course instructor (optional - can be assigned later)</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_category" class="form-label fw-semibold">Category</label>                                        <input type="text" class="form-control" id="edit_category" name="category" 
                                               value="<?= old('category', $editCourse['category']) ?>" 
                                               pattern="[a-zA-Z\s\-\.]+"
                                               maxlength="100"
                                               title="Category can only contain letters, spaces, hyphens, and periods">
                                        <div class="form-text">Letters, spaces, hyphens, periods only (max 100 characters)</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="edit_credits" class="form-label fw-semibold">Credits</label>
                                        <input type="number" class="form-control" id="edit_credits" name="credits" 
                                               value="<?= old('credits', $editCourse['credits']) ?>" 
                                               min="1" max="9">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="edit_duration_weeks" class="form-label fw-semibold">Duration (weeks)</label>
                                        <input type="number" class="form-control" id="edit_duration_weeks" name="duration_weeks" 
                                               value="<?= old('duration_weeks', $editCourse['duration_weeks']) ?>" 
                                               min="1" max="99">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="edit_max_students" class="form-label fw-semibold">Max Students</label>
                                        <input type="number" class="form-control" id="edit_max_students" name="max_students" 
                                               value="<?= old('max_students', $editCourse['max_students']) ?>" 
                                               min="1" max="999">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="edit_status" class="form-label fw-semibold">Status</label>
                                        <select class="form-select" id="edit_status" name="status" required>
                                            <option value="draft" <?= old('status', $editCourse['status']) === 'draft' ? 'selected' : '' ?>>📝 Draft</option>
                                            <option value="active" <?= old('status', $editCourse['status']) === 'active' ? 'selected' : '' ?>>✅ Active</option>
                                            <option value="completed" <?= old('status', $editCourse['status']) === 'completed' ? 'selected' : '' ?>>🎯 Completed</option>
                                            <option value="cancelled" <?= old('status', $editCourse['status']) === 'cancelled' ? 'selected' : '' ?>>❌ Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_start_date" class="form-label fw-semibold">Start Date</label>
                                        <input type="date" class="form-control" id="edit_start_date" name="start_date" 
                                               value="<?= old('start_date', $editCourse['start_date']) ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_end_date" class="form-label fw-semibold">End Date</label>
                                        <input type="date" class="form-control" id="edit_end_date" name="end_date" 
                                               value="<?= old('end_date', $editCourse['end_date']) ?>">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="edit_description" class="form-label fw-semibold">Description</label>
                                        <textarea class="form-control" id="edit_description" name="description" rows="4"><?= old('description', $editCourse['description']) ?></textarea>
                                        <div class="form-text">Detailed course description (optional)</div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">                                <button type="submit" class="btn btn-warning">
                                    💾 Update Course
                                </button>
                                <a href="<?= base_url('admin/manage_courses') ?>" class="btn btn-outline-secondary">
                                    ❌ Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Courses List -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 pb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0 fw-bold text-dark">📋 All Courses</h5>
                                <small class="text-muted">Manage and monitor all courses in the system</small>
                            </div>
                            <div class="text-muted small">
                                Total: <?= count($courses) ?> courses
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <?php if (!empty($courses)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Course</th>
                                            <th>Instructor</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Credits</th>
                                            <th class="text-center">Duration</th>
                                            <th class="text-center">Max Students</th>
                                            <th class="text-center">Created</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($courses as $course): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                             style="width: 40px; height: 40px; font-size: 1.2rem;">
                                                            📚
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark"><?= esc($course['title']) ?></div>
                                                        <small class="text-muted"><?= esc($course['course_code']) ?></small>
                                                        <?php if ($course['category']): ?>
                                                            <br><small class="text-info"><?= esc($course['category']) ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-muted">
                                                <?= esc($course['instructor_name'] ?: 'Not Assigned') ?>
                                            </td>
                                            <td class="text-center">
                                                <?php
                                                $statusStyles = [
                                                    'draft' => ['color' => 'warning', 'icon' => '📝'],
                                                    'active' => ['color' => 'success', 'icon' => '✅'],
                                                    'completed' => ['color' => 'secondary', 'icon' => '🎯'],
                                                    'cancelled' => ['color' => 'danger', 'icon' => '❌']
                                                ];
                                                $style = $statusStyles[$course['status']] ?? ['color' => 'secondary', 'icon' => '❓'];
                                                ?>
                                                <span class="badge bg-<?= $style['color'] ?> rounded-pill px-3 py-2">
                                                    <?= $style['icon'] ?> <?= ucfirst($course['status']) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info rounded-pill"><?= $course['credits'] ?></span>
                                            </td>
                                            <td class="text-center">
                                                <small class="text-muted"><?= $course['duration_weeks'] ?> weeks</small>
                                            </td>
                                            <td class="text-center">
                                                <small class="text-muted"><?= $course['max_students'] ?></small>
                                            </td>
                                            <td class="text-center">
                                                <small class="text-muted">
                                                    <?= date('M j, Y', strtotime($course['created_at'])) ?>
                                                </small>
                                            </td>                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <!-- Upload Materials Button -->
                                                    <a href="<?= base_url('admin/course/' . $course['id'] . '/upload') ?>" 
                                                       class="btn btn-outline-success btn-sm me-1" 
                                                       title="Upload Course Materials">
                                                        📁
                                                    </a>
                                                    
                                                    <!-- Edit Button -->
                                                    <a href="<?= base_url('admin/manage_courses?action=edit&id=' . $course['id']) ?>" 
                                                       class="btn btn-outline-warning btn-sm me-1" 
                                                       title="Edit Course">
                                                        ✏️
                                                    </a>
                                                    
                                                    <!-- Delete Button -->
                                                    <a href="<?= base_url('admin/manage_courses?action=delete&id=' . $course['id']) ?>" 
                                                       class="btn btn-outline-danger btn-sm"
                                                       onclick="return confirm('Are you sure you want to delete this course?\n\nCourse: <?= esc($course['title']) ?>\nCode: <?= esc($course['course_code']) ?>\n\nThis action cannot be undone and will affect all enrolled students!')"
                                                       title="Delete Course">
                                                        🗑️
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <div class="mb-3">
                                    <i class="fas fa-book-open text-muted" style="font-size: 3rem;"></i>
                                </div>
                                <h6 class="text-muted">No courses found</h6>
                                <p class="text-muted small mb-0">Create your first course using the button above.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Enhanced Validation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Course title validation - only letters, spaces, hyphens, periods
    const titleFields = document.querySelectorAll('input[name="title"]');
    titleFields.forEach(function(field) {
        field.addEventListener('input', function(e) {
            const value = e.target.value;
            const titlePattern = /^[a-zA-Z\s\-\.]+$/;
            
            // Remove invalid characters as user types
            const sanitized = value.replace(/[^a-zA-Z\s\-\.]/g, '');
            if (sanitized !== value) {
                e.target.value = sanitized;
            }
            
            // Visual feedback
            if (sanitized.length >= 3 && sanitized.length <= 200 && titlePattern.test(sanitized)) {
                e.target.classList.remove('is-invalid');
                e.target.classList.add('is-valid');
                hideCustomError(e.target);
            } else if (sanitized.length > 0) {
                e.target.classList.remove('is-valid');
                e.target.classList.add('is-invalid');
                if (!titlePattern.test(sanitized)) {
                    showCustomError(e.target, 'Only letters, spaces, hyphens, and periods are allowed');
                }
            } else {
                e.target.classList.remove('is-valid', 'is-invalid');
                hideCustomError(e.target);
            }
        });
    });
      // Course code validation - letters followed by optional hyphen and numbers
    const codeFields = document.querySelectorAll('input[name="course_code"]');
    codeFields.forEach(function(field) {
        field.addEventListener('input', function(e) {
            const value = e.target.value;
            const codePattern = /^[A-Z]+\-?[0-9]+$/;
            
            // Convert to uppercase and remove invalid characters (allow hyphen between letters and numbers)
            let sanitized = value.toUpperCase().replace(/[^A-Z0-9\-]/g, '');
            
            // Ensure proper format: letters, optional hyphen, numbers
            const match = sanitized.match(/^([A-Z]*)([\-]?)([0-9]*)(.*)$/);
            if (match) {
                let letters = match[1];
                let hyphen = match[2];
                let numbers = match[3];
                let extra = match[4];
                
                // If there are extra characters after numbers, remove them
                if (extra) {
                    sanitized = letters + hyphen + numbers;
                }
                
                // Ensure only one hyphen and it's in the right place
                const hyphenCount = (sanitized.match(/\-/g) || []).length;
                if (hyphenCount > 1) {
                    // Keep only first hyphen
                    const parts = sanitized.split('-');
                    sanitized = parts[0] + '-' + parts.slice(1).join('');
                }
            }
            
            e.target.value = sanitized;
            
            // Visual feedback
            if (sanitized.length >= 3 && sanitized.length <= 20 && codePattern.test(sanitized)) {
                e.target.classList.remove('is-invalid');
                e.target.classList.add('is-valid');
                hideCustomError(e.target);
            } else if (sanitized.length > 0) {
                e.target.classList.remove('is-valid');
                e.target.classList.add('is-invalid');
                if (!codePattern.test(sanitized)) {
                    showCustomError(e.target, 'Must start with letters followed by numbers (e.g., CS101, CS-101)');
                }
            } else {
                e.target.classList.remove('is-valid', 'is-invalid');
                hideCustomError(e.target);
            }
        });
    });
    // Category validation - only letters, spaces, hyphens, periods (no quotes, asterisks, numbers)
    const categoryFields = document.querySelectorAll('input[name="category"]');
    categoryFields.forEach(function(field) {
        field.addEventListener('input', function(e) {
            const value = e.target.value;
            const categoryPattern = /^[a-zA-Z\s\-\.]*$/;
            
            // Remove invalid characters as user types
            const sanitized = value.replace(/[^a-zA-Z\s\-\.]/g, '');
            if (sanitized !== value) {
                e.target.value = sanitized;
            }
            
            // Visual feedback
            if (sanitized.length <= 100 && categoryPattern.test(sanitized)) {
                e.target.classList.remove('is-invalid');
                e.target.classList.add('is-valid');
                hideCustomError(e.target);
            } else if (sanitized.length > 0) {
                e.target.classList.remove('is-valid');
                e.target.classList.add('is-invalid');
                if (!categoryPattern.test(sanitized)) {
                    showCustomError(e.target, 'Only letters, spaces, hyphens, and periods are allowed');
                } else if (sanitized.length > 100) {
                    showCustomError(e.target, 'Category cannot exceed 100 characters');
                }
            } else {
                e.target.classList.remove('is-valid', 'is-invalid');
                hideCustomError(e.target);
            }
        });
    });      // Description validation - letters, numbers, spaces, hyphens, and limited punctuation
    const descriptionFields = document.querySelectorAll('textarea[name="description"]');
    descriptionFields.forEach(function(field) {
        field.addEventListener('input', function(e) {
            const value = e.target.value;
            // Allow letters, numbers, spaces, hyphens, periods, commas, colons, semicolons, exclamation marks, question marks, and bullet points
            const descriptionPattern = /^[a-zA-Z0-9\s\.\,\:\;\!\?\n\r•\-]*$/;
            
            // Remove invalid characters (excluding hyphens now)
            const sanitized = value.replace(/[^a-zA-Z0-9\s\.\,\:\;\!\?\n\r•\-]/g, '');
            if (sanitized !== value) {
                e.target.value = sanitized;
            }
            
            // Visual feedback
            if (descriptionPattern.test(sanitized)) {
                e.target.classList.remove('is-invalid');
                e.target.classList.add('is-valid');
                hideCustomError(e.target);            } else if (sanitized.length > 0) {
                e.target.classList.remove('is-valid');
                e.target.classList.add('is-invalid');
                showCustomError(e.target, 'Only letters, numbers, spaces, hyphens, and basic punctuation allowed (periods, commas, colons, semicolons, exclamation marks, question marks, and bullet points •)');
            } else {
                e.target.classList.remove('is-valid', 'is-invalid');
                hideCustomError(e.target);
            }
        });
    });
      // Instructor validation (optional field)
    const instructorFields = document.querySelectorAll('select[name="instructor_id"]');
    instructorFields.forEach(function(field) {
        field.addEventListener('change', function(e) {
            const value = e.target.value;
            
            // Visual feedback - always valid since field is optional
            if (value) {
                e.target.classList.remove('is-invalid');
                e.target.classList.add('is-valid');
            } else {
                // Remove any validation classes when no instructor is selected (which is valid)
                e.target.classList.remove('is-valid', 'is-invalid');
            }
        });
    });
    
    // Date validation - ensure end date is after start date
    const startDateFields = document.querySelectorAll('input[name="start_date"]');
    const endDateFields = document.querySelectorAll('input[name="end_date"]');
    
    function validateDates() {
        startDateFields.forEach(function(startField, index) {
            const endField = endDateFields[index];
            if (startField && endField && startField.value && endField.value) {
                const startDate = new Date(startField.value);
                const endDate = new Date(endField.value);
                
                if (endDate <= startDate) {
                    endField.classList.add('is-invalid');
                    endField.classList.remove('is-valid');
                    
                    // Add custom error message
                    let errorMsg = endField.parentNode.querySelector('.invalid-feedback');
                    if (!errorMsg) {
                        errorMsg = document.createElement('div');
                        errorMsg.className = 'invalid-feedback';
                        endField.parentNode.appendChild(errorMsg);
                    }
                    errorMsg.textContent = 'End date must be after start date.';
                } else {
                    endField.classList.remove('is-invalid');
                    endField.classList.add('is-valid');
                    
                    // Remove error message
                    const errorMsg = endField.parentNode.querySelector('.invalid-feedback');
                    if (errorMsg) {
                        errorMsg.remove();
                    }
                }
            }
        });
    }
    
    startDateFields.forEach(function(field) {
        field.addEventListener('change', validateDates);
    });
      endDateFields.forEach(function(field) {
        field.addEventListener('change', validateDates);
    });
    
    // Helper functions for custom error messages
    function showCustomError(field, message) {
        hideCustomError(field); // Remove any existing error
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback d-block';
        errorDiv.textContent = message;
        errorDiv.setAttribute('data-custom-error', 'true');
        field.parentNode.appendChild(errorDiv);
    }
    
    function hideCustomError(field) {
        const existingError = field.parentNode.querySelector('[data-custom-error="true"]');
        if (existingError) {
            existingError.remove();
        }
    }
});
</script>

