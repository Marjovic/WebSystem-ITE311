<?= $this->include('templates/header') ?>

<!-- Teacher Courses View - Shows assigned courses and available courses -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-success text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-2 fw-bold">👨‍🏫 My Courses</h2>
                                <p class="mb-0 opacity-75">Manage your assigned courses and request new course assignments</p>
                            </div>
                            <div>
                                <a href="<?= base_url('teacher/dashboard') ?>" class="btn btn-light btn-sm">
                                    ← Back to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>        <!-- Course Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">📚</div>
                    <div class="display-5 fw-bold"><?= count($assignedCourses) ?></div>
                    <div class="fw-semibold">My Courses</div>
                    <small class="opacity-75">Assigned to me</small>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">👥</div>
                    <div class="display-5 fw-bold"><?= array_sum(array_column($assignedCourses, 'enrolled_students')) ?></div>
                    <div class="fw-semibold">Total Students</div>
                    <small class="opacity-75">Enrolled in my courses</small>
                </div>
            </div>
        </div>

        <!-- My Assigned Courses Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 pb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0 fw-bold text-dark">📋 My Assigned Courses</h5>
                                <small class="text-muted">Courses you are currently teaching</small>
                            </div>
                            <div class="text-muted small">
                                Total: <?= count($assignedCourses) ?> courses
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <?php if (!empty($assignedCourses)): ?>
                            <div class="row">
                                <?php foreach ($assignedCourses as $course): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-0 shadow-sm h-100 course-card">
                                        <div class="card-header bg-primary text-white border-0">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 fw-bold"><?= esc($course['title']) ?></h6>
                                                    <small class="opacity-75"><?= esc($course['course_code']) ?></small>
                                                </div>
                                                <?php
                                                $statusStyles = [
                                                    'draft' => ['color' => 'warning', 'icon' => '📝'],
                                                    'active' => ['color' => 'success', 'icon' => '✅'],
                                                    'completed' => ['color' => 'secondary', 'icon' => '🎯'],
                                                    'cancelled' => ['color' => 'danger', 'icon' => '❌']
                                                ];
                                                $style = $statusStyles[$course['status']] ?? ['color' => 'secondary', 'icon' => '❓'];
                                                ?>
                                                <span class="badge bg-<?= $style['color'] ?> rounded-pill ms-2">
                                                    <?= $style['icon'] ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <!-- Course Details -->
                                            <div class="mb-3">
                                                <?php if ($course['category']): ?>
                                                    <div class="badge bg-light text-dark mb-2"><?= esc($course['category']) ?></div>
                                                <?php endif; ?>
                                                <div class="small text-muted">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>📅 Duration:</span>
                                                        <span><?= $course['duration_weeks'] ?> weeks</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>⭐ Credits:</span>
                                                        <span><?= $course['credits'] ?></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>👥 Max Students:</span>
                                                        <span><?= $course['max_students'] ?></span>
                                                    </div>
                                                    <?php if ($course['start_date']): ?>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>🚀 Start Date:</span>
                                                        <span><?= date('M j, Y', strtotime($course['start_date'])) ?></span>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Enrolled Students -->
                                            <div class="mb-3">
                                                <h6 class="fw-semibold text-primary mb-2">👥 Enrolled Students (<?= $course['enrolled_students'] ?>)</h6>
                                                <?php if ($course['enrolled_students'] > 0): ?>
                                                    <div class="bg-light p-3 rounded-3">
                                                        <div class="small">
                                                            <?php if ($course['student_list']): ?>
                                                                <?php 
                                                                $students = explode(', ', $course['student_list']);
                                                                foreach ($students as $student): 
                                                                ?>
                                                                    <div class="d-flex align-items-center mb-1">
                                                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                                             style="width: 24px; height: 24px; font-size: 0.7rem;">
                                                                            👤
                                                                        </div>
                                                                        <span><?= esc($student) ?></span>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="text-center py-3 text-muted">
                                                        <div class="mb-2">
                                                            <i class="fas fa-users text-muted" style="font-size: 2rem;"></i>
                                                        </div>
                                                        <small>No students enrolled yet</small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Course Description -->
                                            <?php if ($course['description']): ?>
                                            <div class="mb-3">
                                                <h6 class="fw-semibold text-secondary mb-2">📄 Description</h6>
                                                <p class="small text-muted mb-0"><?= esc($course['description']) ?></p>
                                            </div>
                                            <?php endif; ?>                                        </div>
                                        <div class="card-footer bg-light border-0 d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                Assigned: <?= date('M j, Y', strtotime($course['created_at'])) ?>
                                            </small>
                                            
                                            <!-- Unassign Button -->
                                            <form method="post" action="<?= base_url('teacher/courses') ?>" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="unassign_course">
                                                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm" 
                                                        onclick="return confirm('Are you sure you want to unassign yourself from <?= esc($course['title']) ?> (<?= esc($course['course_code']) ?>)?\n\nThis will remove you as the instructor and make the course available for other teachers to request.')"
                                                        title="Unassign from course">
                                                    <i class="fas fa-times me-1"></i>Unassign
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <div class="mb-3">
                                    <i class="fas fa-chalkboard-teacher text-muted" style="font-size: 3rem;"></i>
                                </div>
                                <h6 class="text-muted">No courses assigned yet</h6>
                                <p class="text-muted small mb-0">Check available courses below to start teaching!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Courses Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 pb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0 fw-bold text-dark">🎯 Available Courses</h5>
                                <small class="text-muted">Active courses looking for instructors</small>
                            </div>
                            <div class="text-muted small">
                                Available: <?= count($availableCourses) ?> courses
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <?php if (!empty($availableCourses)): ?>
                            <div class="row">
                                <?php foreach ($availableCourses as $course): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-0 shadow-sm h-100 course-card available-course">
                                        <div class="card-header bg-info text-white border-0">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 fw-bold"><?= esc($course['title']) ?></h6>
                                                    <small class="opacity-75"><?= esc($course['course_code']) ?></small>
                                                </div>
                                                <span class="badge bg-success rounded-pill ms-2">
                                                    ✨ Available
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <p class="text-muted small mb-3">
                                                <?= strlen($course['description']) > 100 ? esc(substr($course['description'], 0, 100)) . '...' : esc($course['description']) ?>
                                            </p>
                                            
                                            <!-- Course Details -->
                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <div class="bg-light p-2 rounded-2 text-center">
                                                        <div class="fw-bold text-primary"><?= $course['credits'] ?></div>
                                                        <small class="text-muted">Credits</small>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="bg-light p-2 rounded-2 text-center">
                                                        <div class="fw-bold text-info"><?= $course['duration_weeks'] ?>w</div>
                                                        <small class="text-muted">Duration</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Course Dates -->
                                            <div class="mb-3">
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    <strong>Start:</strong> <?= $course['start_date_formatted'] ?>
                                                </small>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-calendar-check me-1"></i>
                                                    <strong>End:</strong> <?= $course['end_date_formatted'] ?>
                                                </small>
                                            </div>

                                            <!-- Enrollment Statistics -->
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        <i class="fas fa-users me-1"></i>Current Students:
                                                    </small>
                                                    <span class="badge bg-secondary"><?= $course['enrolled_students'] ?>/<?= $course['max_students'] ?></span>
                                                </div>
                                            </div>

                                            <!-- Assignment Action -->
                                            <form method="post" action="<?= base_url('teacher/courses') ?>" class="d-grid">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="assign_course">
                                                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                                <button type="submit" class="btn btn-success btn-sm" 
                                                        onclick="return confirm('Are you sure you want to request to teach <?= esc($course['title']) ?> (<?= esc($course['course_code']) ?>)?')">
                                                    <i class="fas fa-hand-paper me-1"></i>Request to Teach
                                                </button>
                                            </form>
                                        </div>
                                        <div class="card-footer bg-light border-0">
                                            <small class="text-muted">
                                                <i class="fas fa-plus-circle me-1"></i>
                                                Created: <?= date('M j, Y', strtotime($course['created_at'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <div class="mb-3">
                                    <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                                </div>
                                <h6 class="text-success">All Courses Have Instructors</h6>
                                <p class="text-muted small mb-0">Currently, all active courses have been assigned to instructors. New opportunities will appear here when available.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Styles -->
<style>
/* Course Card Styling */
.course-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    cursor: default;
    border-radius: 12px !important;
}

.course-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.15) !important;
}

/* Course Type Indicators */
.available-course {
    border-left: 4px solid #17a2b8;
}

.available-course:hover {
    border-left-color: #138496;
}

/* Button Hover Effects */
.available-course .btn:hover {
    transform: scale(1.02);
    transition: transform 0.2s ease;
}

.btn:hover {
    transition: all 0.2s ease;
}

/* Card Enhancements */
.card {
    border-radius: 12px;
    overflow: hidden;
}

.card-body {
    padding: 1.25rem;
}

.card-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

.card-footer {
    padding: 0.75rem 1.25rem;
    background-color: rgba(0,0,0,0.03) !important;
}

/* Statistics Cards */
.bg-primary, .bg-success, .bg-info, .bg-warning {
    border-radius: 12px !important;
}

/* Enhanced Shadows and Depth */
.shadow-sm {
    box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
}

.rounded-3 {
    border-radius: 12px !important;
}

/* Badge Styling */
.badge {
    font-size: 0.75em;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .course-card {
        margin-bottom: 1rem;
    }
    
    .display-4 {
        font-size: 2.5rem;
    }
    
    .display-5 {
        font-size: 2rem;
    }
}

/* Form Button Styling */
.btn-outline-danger:hover {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
}

.btn-success:hover {
    background-color: #157347;
    border-color: #146c43;
}

/* Loading State for Buttons */
.btn[disabled] {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Student List Styling */
.bg-light {
    background-color: rgba(0,0,0,0.03) !important;
    border-radius: 8px;
}

/* Icon Styling */
.fas, .fa {
    margin-right: 0.25rem;
}
</style>


