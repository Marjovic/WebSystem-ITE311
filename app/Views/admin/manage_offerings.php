<?= $this->include('templates/header') ?>

<!-- Manage Course Offerings View - Admin only functionality for course offering management -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-2 fw-bold">📅 Manage Course Offerings</h2>
                                <p class="mb-0 opacity-75">Create and manage course offerings for each term</p>
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

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>✅ Success!</strong> <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>❌ Error!</strong> <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>❌ Validation Errors:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Offering Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">📚</div>
                    <div class="display-5 fw-bold"><?= $statistics['total'] ?></div>
                    <div class="fw-semibold">Total Offerings</div>
                    <small class="opacity-75">In the system</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-secondary text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">📝</div>
                    <div class="display-5 fw-bold"><?= $statistics['draft'] ?></div>
                    <div class="fw-semibold">Draft</div>
                    <small class="opacity-75">Being prepared</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">✅</div>
                    <div class="display-5 fw-bold"><?= $statistics['open'] ?></div>
                    <div class="fw-semibold">Open</div>
                    <small class="opacity-75">Accepting students</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-danger text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">🔒</div>
                    <div class="display-5 fw-bold"><?= $statistics['closed'] ?></div>
                    <div class="fw-semibold">Closed</div>
                    <small class="opacity-75">Not accepting</small>
                </div>
            </div>
        </div>

        <!-- Term Filter and Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body">
                        <div class="row align-items-center">                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="termFilter" class="form-label fw-semibold mb-2">Filter by Term:</label>
                                <select class="form-select" id="termFilter" onchange="filterByTerm(this.value)">
                                    <option value="">All Terms</option>
                                    <?php foreach ($terms as $term): ?>
                                        <option value="<?= $term['id'] ?>" <?= $selectedTermId == $term['id'] ? 'selected' : '' ?>>
                                            <?= esc($term['term_name']) ?>
                                            <?php if ($term['start_date'] && $term['end_date']): ?>
                                                (<?= date('M Y', strtotime($term['start_date'])) ?> - <?= date('M Y', strtotime($term['end_date'])) ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <a href="<?= base_url('admin/manage_offerings?action=create' . ($selectedTermId ? '&term_id=' . $selectedTermId : '')) ?>" class="btn btn-success">
                                    ➕ Add Course Offering
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selected Term Info -->
        <?php if ($selectedTerm): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-primary">                    <div class="card-body bg-light">
                        <h5 class="mb-2 fw-bold text-primary">📆 Current Term:</h5>
                        <h4 class="mb-1"><?= esc($selectedTerm['term_name']) ?></h4>
                        <p class="text-muted mb-0">
                            <?php if ($selectedTerm['start_date'] && $selectedTerm['end_date']): ?>
                                <strong>Period:</strong> <?= date('M d, Y', strtotime($selectedTerm['start_date'])) ?> - <?= date('M d, Y', strtotime($selectedTerm['end_date'])) ?> | 
                            <?php endif; ?>
                            <strong>Offerings:</strong> <?= count($offerings) ?> |
                            <strong>Status:</strong> <span class="badge bg-<?= $selectedTerm['is_active'] ? 'success' : 'secondary' ?>"><?= $selectedTerm['is_active'] ? 'Active' : 'Inactive' ?></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Create Offering Form (shown when action=create) -->
        <?php if ($showCreateForm): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-success">
                    <div class="card-header bg-success text-white border-0">
                        <h5 class="mb-0">➕ Add Course Offering</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('admin/manage_offerings?action=create') ?>">
                            <?= csrf_field() ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="course_id" class="form-label fw-semibold">Course <span class="text-danger">*</span></label>
                                        <select class="form-select" id="course_id" name="course_id" required>
                                            <option value="">-- Select Course --</option>
                                            <?php foreach ($courses as $course): ?>
                                                <option value="<?= $course['id'] ?>" <?= old('course_id') == $course['id'] ? 'selected' : '' ?>>
                                                    <?= esc($course['course_code']) ?> - <?= esc($course['title']) ?> (<?= $course['credits'] ?> credits)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">Select the course to offer</small>
                                    </div>
                                </div>                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="term_id" class="form-label fw-semibold">Term <span class="text-danger">*</span></label>
                                        <select class="form-select" id="term_id" name="term_id" required>
                                            <option value="">-- Select Term --</option>
                                            <?php foreach ($terms as $term): ?>
                                                <option value="<?= $term['id'] ?>" <?= (old('term_id') == $term['id'] || $selectedTermId == $term['id']) ? 'selected' : '' ?>>
                                                    <?= esc($term['term_name']) ?>
                                                    <?php if ($term['start_date'] && $term['end_date']): ?>
                                                        (<?= date('M Y', strtotime($term['start_date'])) ?> - <?= date('M Y', strtotime($term['end_date'])) ?>)
                                                    <?php endif; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">Select the academic term</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="section" class="form-label fw-semibold">Section</label>
                                        <input type="text" class="form-control" id="section" name="section" 
                                               value="<?= old('section') ?>" maxlength="50" placeholder="e.g., A, B, 1-A">
                                        <small class="text-muted">Optional section identifier</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="max_students" class="form-label fw-semibold">Max Students <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="max_students" name="max_students" 
                                               value="<?= old('max_students', 30) ?>" min="1" required>
                                        <small class="text-muted">Maximum enrollment capacity</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="room" class="form-label fw-semibold">Room</label>
                                        <input type="text" class="form-control" id="room" name="room" 
                                               value="<?= old('room') ?>" maxlength="100" placeholder="e.g., Room 101, Lab A">
                                        <small class="text-muted">Classroom or location</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="draft" <?= old('status') == 'draft' ? 'selected' : '' ?>>📝 Draft</option>
                                            <option value="open" <?= old('status') == 'open' ? 'selected' : '' ?>>✅ Open</option>
                                            <option value="closed" <?= old('status') == 'closed' ? 'selected' : '' ?>>🔒 Closed</option>
                                            <option value="cancelled" <?= old('status') == 'cancelled' ? 'selected' : '' ?>>❌ Cancelled</option>
                                            <option value="completed" <?= old('status') == 'completed' ? 'selected' : '' ?>>✔️ Completed</option>
                                        </select>
                                        <small class="text-muted">Current offering status</small>
                                    </div>
                                </div>                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="start_date" class="form-label fw-semibold">Start Date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" 
                                               value="<?= old('start_date') ?>" min="<?= date('Y-m-d') ?>">
                                        <small class="text-muted">Must be today or future date</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="end_date" class="form-label fw-semibold">End Date</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" 
                                               value="<?= old('end_date') ?>" min="<?= date('Y-m-d') ?>">
                                        <small class="text-muted">Must be today or future date</small>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?= base_url('admin/manage_offerings' . ($selectedTermId ? '?term_id=' . $selectedTermId : '')) ?>" class="btn btn-secondary">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-success">
                                    ➕ Create Offering
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Edit Offering Form (shown when action=edit) -->
        <?php if ($showEditForm && isset($editOffering)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-warning">
                    <div class="card-header bg-warning text-dark border-0">
                        <h5 class="mb-0">✏️ Edit Course Offering</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('admin/manage_offerings?action=edit&id=' . $editOffering['id']) ?>">
                            <?= csrf_field() ?>
                            
                            <!-- Display course and term info (non-editable) -->
                            <div class="alert alert-info mb-3">
                                <strong>Course:</strong> <?= esc($course['course_code']) ?> - <?= esc($course['title']) ?><br>
                                <strong>Term:</strong> <?= esc($term['term_name']) ?>
                                <small class="d-block mt-1 text-muted">Course and term cannot be changed. Create a new offering if needed.</small>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="section" class="form-label fw-semibold">Section</label>
                                        <input type="text" class="form-control" id="section" name="section" 
                                               value="<?= old('section', $editOffering['section']) ?>" maxlength="50">
                                        <small class="text-muted">Optional section identifier</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="max_students" class="form-label fw-semibold">Max Students <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="max_students" name="max_students" 
                                               value="<?= old('max_students', $editOffering['max_students']) ?>" min="1" required>
                                        <small class="text-muted">Current enrollment: <?= $editOffering['current_enrollment'] ?></small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="room" class="form-label fw-semibold">Room</label>
                                        <input type="text" class="form-control" id="room" name="room" 
                                               value="<?= old('room', $editOffering['room']) ?>" maxlength="100">
                                        <small class="text-muted">Classroom or location</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="draft" <?= old('status', $editOffering['status']) == 'draft' ? 'selected' : '' ?>>📝 Draft</option>
                                            <option value="open" <?= old('status', $editOffering['status']) == 'open' ? 'selected' : '' ?>>✅ Open</option>
                                            <option value="closed" <?= old('status', $editOffering['status']) == 'closed' ? 'selected' : '' ?>>🔒 Closed</option>
                                            <option value="cancelled" <?= old('status', $editOffering['status']) == 'cancelled' ? 'selected' : '' ?>>❌ Cancelled</option>
                                            <option value="completed" <?= old('status', $editOffering['status']) == 'completed' ? 'selected' : '' ?>>✔️ Completed</option>
                                        </select>
                                    </div>
                                </div>                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="start_date" class="form-label fw-semibold">Start Date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" 
                                               value="<?= old('start_date', $editOffering['start_date']) ?>" 
                                               min="<?= $editOffering['start_date'] && strtotime($editOffering['start_date']) < strtotime('today') ? $editOffering['start_date'] : date('Y-m-d') ?>">
                                        <small class="text-muted">Must be today or future date</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="end_date" class="form-label fw-semibold">End Date</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" 
                                               value="<?= old('end_date', $editOffering['end_date']) ?>" 
                                               min="<?= $editOffering['end_date'] && strtotime($editOffering['end_date']) < strtotime('today') ? $editOffering['end_date'] : date('Y-m-d') ?>">
                                        <small class="text-muted">Must be today or future date</small>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?= base_url('admin/manage_offerings?term_id=' . $editOffering['term_id']) ?>" class="btn btn-secondary">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-warning">
                                    💾 Update Offering
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Offerings Table -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold">📋 Course Offerings List</h5>
                        <small class="text-muted">Total: <?= count($offerings) ?> offering(s)</small>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($offerings)): ?>
                            <div class="text-center py-5">
                                <div class="display-1 mb-3">📭</div>
                                <h4 class="text-muted">No Course Offerings Found</h4>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="px-4 py-3">Course</th>
                                            <th class="py-3">Term</th>
                                            <th class="py-3">Section</th>
                                            <th class="py-3">Enrollment</th>
                                            <th class="py-3">Room</th>
                                            <th class="py-3">Status</th>
                                            <th class="py-3">Dates</th>
                                            <th class="py-3 text-end px-4">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($offerings as $offering): ?>
                                            <tr>
                                                <td class="px-4 py-3">
                                                    <strong><?= esc($offering['course_code']) ?></strong><br>
                                                    <small class="text-muted"><?= esc($offering['course_title']) ?></small><br>
                                                    <small class="badge bg-secondary"><?= $offering['credits'] ?> credits</small>
                                                </td>
                                                <td class="py-3">
                                                    <strong><?= esc($offering['term_name']) ?></strong>
                                                </td>
                                                <td class="py-3">
                                                    <?= $offering['section'] ? esc($offering['section']) : '<span class="text-muted">-</span>' ?>
                                                </td>
                                                <td class="py-3">
                                                    <?php
                                                    $enrolled = isset($offering['enrolled_count']) ? $offering['enrolled_count'] : $offering['current_enrollment'];
                                                    $maxStudents = $offering['max_students'];
                                                    $percentage = $maxStudents > 0 ? ($enrolled / $maxStudents) * 100 : 0;
                                                    $progressColor = $percentage >= 90 ? 'danger' : ($percentage >= 70 ? 'warning' : 'success');
                                                    ?>
                                                    <div class="d-flex align-items-center">
                                                        <small class="me-2"><?= $enrolled ?>/<?= $maxStudents ?></small>
                                                        <div class="progress flex-grow-1" style="height: 8px; width: 60px;">
                                                            <div class="progress-bar bg-<?= $progressColor ?>" style="width: <?= min($percentage, 100) ?>%"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3">
                                                    <?= $offering['room'] ? esc($offering['room']) : '<span class="text-muted">-</span>' ?>
                                                </td>
                                                <td class="py-3">
                                                    <?php
                                                    $statusBadges = [
                                                        'draft' => 'secondary',
                                                        'open' => 'success',
                                                        'closed' => 'danger',
                                                        'cancelled' => 'dark',
                                                        'completed' => 'info'
                                                    ];
                                                    $badgeClass = $statusBadges[$offering['status']] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?= $badgeClass ?>"><?= ucfirst($offering['status']) ?></span>
                                                </td>
                                                <td class="py-3">
                                                    <?php if ($offering['start_date'] && $offering['end_date']): ?>
                                                        <small>
                                                            <?= date('M d', strtotime($offering['start_date'])) ?> -<br>
                                                            <?= date('M d, Y', strtotime($offering['end_date'])) ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="py-3 text-end px-4">
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="<?= base_url('admin/manage_offerings?action=toggle_status&id=' . $offering['id']) ?>" 
                                                           class="btn btn-outline-primary" 
                                                           title="Toggle Status"
                                                           onclick="return confirm('Change status to next state?')">
                                                            🔄
                                                        </a>
                                                        <a href="<?= base_url('admin/manage_offerings?action=edit&id=' . $offering['id']) ?>" 
                                                           class="btn btn-outline-warning" 
                                                           title="Edit">
                                                            ✏️
                                                        </a>
                                                        <a href="<?= base_url('admin/manage_offerings?action=delete&id=' . $offering['id']) ?>" 
                                                           class="btn btn-outline-danger" 
                                                           title="Delete"
                                                           onclick="return confirm('Are you sure you want to delete this offering?\n\nCourse: <?= esc($offering['course_code']) ?>\nTerm: <?= esc($offering['term_name']) ?>\nSection: <?= esc($offering['section']) ?>')">
                                                            🗑️
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm bg-light rounded-3">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">ℹ️ Course Offering Management Guide:</h6>
                        <ul class="mb-0 small">
                            <li><strong>Draft:</strong> Offering is being prepared and not visible to students</li>
                            <li><strong>Open:</strong> Students can enroll in this offering</li>
                            <li><strong>Closed:</strong> Enrollment is closed, but class is ongoing</li>
                            <li><strong>Cancelled:</strong> Offering was cancelled and won't proceed</li>
                            <li><strong>Completed:</strong> Class has finished for this term</li>
                            <li><strong>Toggle Status:</strong> Cycles through draft → open → closed → completed</li>
                            <li><strong>Deletion:</strong> Offerings with enrolled students cannot be deleted</li>
                            <li><strong>Dates:</strong> Start and end dates must be today or in the future (past dates are not allowed)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function filterByTerm(termId) {
    if (termId) {
        window.location.href = '<?= base_url('admin/manage_offerings') ?>?term_id=' + termId;
    } else {
        window.location.href = '<?= base_url('admin/manage_offerings') ?>';
    }
}
</script>