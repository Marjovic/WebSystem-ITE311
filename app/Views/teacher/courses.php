<?= $this->include('templates/header') ?>

<!-- Teacher Courses View - Shows courses assigned by admin -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-1 fw-bold">📚 Courses I Teach</h3>
                                <p class="mb-0 opacity-75">Courses assigned to me by the administrator</p>
                            </div>
                            <div class="text-end">
                                <i class="fas fa-chalkboard-teacher" style="font-size: 3rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">📖</div>
                    <div class="display-5 fw-bold"><?= count($assignedCourses) ?></div>
                    <div class="fw-semibold">Total Courses</div>
                    <small class="opacity-75">Assigned to me</small>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">👥</div>
                    <div class="display-5 fw-bold"><?= array_sum(array_column($assignedCourses, 'enrolled_students')) ?></div>
                    <div class="fw-semibold">Total Students</div>
                    <small class="opacity-75">Across all courses</small>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">⭐</div>
                    <div class="display-5 fw-bold"><?= count(array_filter($assignedCourses, fn($c) => $c['is_primary'] == 1)) ?></div>
                    <div class="fw-semibold">Primary Instructor</div>
                    <small class="opacity-75">Lead courses</small>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Courses List -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 pb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0 fw-bold text-dark">📋 My Assigned Courses</h5>
                                <small class="text-muted">Courses I'm currently teaching</small>
                            </div>
                            <div class="text-muted small">
                                Total: <?= count($assignedCourses) ?> course<?= count($assignedCourses) !== 1 ? 's' : '' ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <?php if (!empty($assignedCourses)): ?>
                            <div class="row">
                                <?php foreach ($assignedCourses as $course): ?>
                                <div class="col-lg-6 mb-4">
                                    <div class="card border-0 shadow-sm h-100 hover-shadow">
                                        <!-- Card Header -->
                                        <div class="card-header bg-primary text-white border-0">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="flex-grow-1">
                                                    <h5 class="mb-1 fw-bold"><?= esc($course['course_code']) ?></h5>
                                                    <p class="mb-0 small opacity-75"><?= esc($course['title']) ?></p>
                                                </div>
                                                <div>
                                                    <?php if ($course['is_primary'] == 1): ?>
                                                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                                                            <i class="fas fa-star me-1"></i>Primary
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-dark rounded-pill px-3 py-2">
                                                            <i class="fas fa-user-tie me-1"></i>Co-Instructor
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Card Body -->
                                        <div class="card-body">
                                            <!-- Course Details -->
                                            <div class="mb-3">
                                                <!-- Badges -->
                                                <div class="mb-3">
                                                    <?php if ($course['category']): ?>
                                                        <span class="badge bg-light text-dark border">
                                                            <i class="fas fa-tag me-1"></i><?= esc($course['category']) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if ($course['academic_year']): ?>
                                                        <span class="badge bg-primary ms-1">
                                                            <i class="fas fa-calendar me-1"></i><?= esc($course['academic_year']) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if ($course['semester_name']): ?>
                                                        <span class="badge bg-info ms-1">
                                                            <i class="fas fa-calendar-alt me-1"></i><?= esc($course['semester_name']) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if ($course['term_name']): ?>
                                                        <span class="badge bg-secondary ms-1">
                                                            <?= esc($course['term_name']) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Course Info Grid -->
                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <div class="p-2 bg-light rounded-3 text-center">
                                                            <div class="text-muted small mb-1"><i class="fas fa-book-open me-1"></i>Credits</div>
                                                            <div class="fw-bold text-dark"><?= $course['credits'] ?></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="p-2 bg-light rounded-3 text-center">
                                                            <div class="text-muted small mb-1"><i class="fas fa-users me-1"></i>Max Students</div>
                                                            <div class="fw-bold text-dark"><?= $course['max_students'] ?? 'N/A' ?></div>
                                                        </div>
                                                    </div>
                                                    <?php if ($course['start_date']): ?>
                                                    <div class="col-6">
                                                        <div class="p-2 bg-light rounded-3 text-center">
                                                            <div class="text-muted small mb-1"><i class="fas fa-calendar-check me-1"></i>Start Date</div>
                                                            <div class="fw-bold text-dark"><?= date('M j, Y', strtotime($course['start_date'])) ?></div>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                    <?php if ($course['end_date']): ?>
                                                    <div class="col-6">
                                                        <div class="p-2 bg-light rounded-3 text-center">
                                                            <div class="text-muted small mb-1"><i class="fas fa-calendar-times me-1"></i>End Date</div>
                                                            <div class="fw-bold text-dark"><?= date('M j, Y', strtotime($course['end_date'])) ?></div>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Enrolled Students -->
                                            <div class="mb-3">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <h6 class="fw-bold text-primary mb-0">
                                                        <i class="fas fa-users me-2"></i>Enrolled Students
                                                    </h6>
                                                    <span class="badge bg-primary rounded-pill px-3 py-2">
                                                        <?= $course['enrolled_students'] ?> Student<?= $course['enrolled_students'] !== 1 ? 's' : '' ?>
                                                    </span>
                                                </div>
                                                
                                                <?php if ($course['enrolled_students'] > 0 && !empty($course['students'])): ?>
                                                    <div class="bg-light p-3 rounded-3">
                                                        <div class="student-list" style="max-height: 300px; overflow-y: auto;">
                                                            <?php foreach ($course['students'] as $student): ?>
                                                                <div class="student-item mb-2 p-2 bg-white rounded border d-flex align-items-center">
                                                                    <div class="student-avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                                         style="width: 35px; height: 35px; min-width: 35px;">
                                                                        <i class="fas fa-user"></i>
                                                                    </div>                                                                    <div class="flex-grow-1">
                                                                        <div class="fw-semibold text-dark small"><?= esc($student['full_name']) ?></div>
                                                                        <div class="text-muted" style="font-size: 0.75rem;">
                                                                            <i class="fas fa-envelope me-1"></i><?= esc($student['email']) ?>
                                                                        </div>
                                                                    </div>
                                                                    <span class="badge bg-success rounded-pill">
                                                                        <?= ucfirst($student['enrollment_status']) ?>
                                                                    </span>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="text-center py-3 bg-light rounded-3">
                                                        <i class="fas fa-user-graduate text-muted mb-2" style="font-size: 2rem; opacity: 0.3;"></i>
                                                        <p class="text-muted mb-0 small">No students enrolled yet</p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Co-Instructors -->
                                            <?php if (!empty($course['co_instructors'])): ?>
                                            <div class="mb-3">
                                                <h6 class="fw-semibold text-info mb-2">
                                                    <i class="fas fa-user-tie me-2"></i>Co-Instructors
                                                </h6>
                                                <div class="d-flex flex-wrap gap-1">                                                    <?php foreach ($course['co_instructors'] as $coInstructor): ?>
                                                        <span class="badge bg-info text-white rounded-pill" title="<?= esc($coInstructor['email']) ?>">
                                                            <i class="fas fa-user-tie me-1"></i><?= esc($coInstructor['full_name']) ?>
                                                            <?php if ($coInstructor['is_primary'] == 1): ?>
                                                                <i class="fas fa-star ms-1"></i>
                                                            <?php endif; ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>

                                            <!-- Course Description -->
                                            <?php if ($course['description']): ?>
                                            <div class="mb-3">
                                                <h6 class="fw-semibold text-secondary mb-2">
                                                    <i class="fas fa-info-circle me-2"></i>Description
                                                </h6>
                                                <p class="small text-muted mb-0"><?= esc($course['description']) ?></p>
                                            </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Card Footer -->
                                        <div class="card-footer bg-light border-0">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar-alt me-1"></i>
                                                    Assigned: <?= date('M j, Y', strtotime($course['assigned_date'])) ?>
                                                </small>
                                                <small class="text-muted">
                                                    Status: 
                                                    <?php
                                                    $statusBadges = [
                                                        'open' => '<span class="badge bg-success">Open</span>',
                                                        'closed' => '<span class="badge bg-secondary">Closed</span>',
                                                        'draft' => '<span class="badge bg-warning text-dark">Draft</span>',
                                                        'archived' => '<span class="badge bg-dark">Archived</span>'
                                                    ];
                                                    echo $statusBadges[$course['offering_status']] ?? '<span class="badge bg-secondary">'.ucfirst($course['offering_status']).'</span>';
                                                    ?>
                                                </small>
                                            </div>
                                            
                                            <!-- Action Buttons -->
                                            <div class="d-flex gap-2">
                                                <a href="<?= base_url('teacher/course/' . $course['offering_id'] . '/view') ?>" 
                                                   class="btn btn-primary btn-sm flex-fill">
                                                    <i class="fas fa-eye me-1"></i>View Course
                                                </a>
                                                <a href="<?= base_url('teacher/course/' . $course['offering_id'] . '/gradebook') ?>" 
                                                   class="btn btn-outline-success btn-sm flex-fill">
                                                    <i class="fas fa-book me-1"></i>Gradebook
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <i class="fas fa-chalkboard-teacher text-muted" style="font-size: 5rem; opacity: 0.3;"></i>
                                </div>
                                <h5 class="text-muted mb-3">No Courses Assigned Yet</h5>
                                <p class="text-muted mb-4">
                                    You haven't been assigned to any courses yet.<br>
                                    Please contact the administrator to get course assignments.
                                </p>
                                <a href="<?= base_url('teacher/dashboard') ?>" class="btn btn-primary">
                                    <i class="fas fa-home me-2"></i>Back to Dashboard
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-shadow {
    transition: all 0.3s ease;
}

.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
}

.student-list::-webkit-scrollbar {
    width: 6px;
}

.student-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.student-list::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.student-list::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>



