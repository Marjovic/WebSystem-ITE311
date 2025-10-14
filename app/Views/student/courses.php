<?= $this->include('templates/header') ?>

<!-- Student My Courses View - Shows enrolled courses and available courses -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-2 fw-bold">📚 My Courses</h2>
                                <p class="mb-0 opacity-75">View your enrolled courses, track progress, and discover new learning opportunities</p>
                            </div>
                            <div>
                                <a href="<?= base_url('student/dashboard') ?>" class="btn btn-light btn-sm">
                                    ← Back to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">📖</div>
                    <div class="display-5 fw-bold"><?= $totalEnrolled ?></div>
                    <div class="fw-semibold">Enrolled Courses</div>
                    <small class="opacity-75">Currently learning</small>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">🎯</div>
                    <div class="display-5 fw-bold"><?= $totalAvailable ?></div>
                    <div class="fw-semibold">Available Courses</div>
                    <small class="opacity-75">Ready to enroll</small>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <nav>
                            <div class="nav nav-tabs nav-fill" id="nav-tab" role="tablist">
                                <button class="nav-link active px-4 py-3 fw-bold" id="enrolled-tab" data-bs-toggle="tab" data-bs-target="#enrolled-courses" type="button" role="tab">
                                    <i class="fas fa-book-reader me-2"></i>My Enrolled Courses (<?= $totalEnrolled ?>)
                                </button>
                                <button class="nav-link px-4 py-3 fw-bold" id="available-tab" data-bs-toggle="tab" data-bs-target="#available-courses" type="button" role="tab">
                                    <i class="fas fa-plus-circle me-2"></i>Available Courses (<?= $totalAvailable ?>)
                                </button>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content" id="nav-tabContent">
            <!-- Enrolled Courses Tab -->
            <div class="tab-pane fade show active" id="enrolled-courses" role="tabpanel">
                <?php if (!empty($enrolledCourses)): ?>
                    <div class="row">
                        <?php foreach ($enrolledCourses as $course): ?>
                            <div class="col-lg-6 col-xl-4 mb-4">
                                <div class="card h-100 border-0 shadow-sm rounded-3 course-card enrolled-course">
                                    <div class="card-body p-4">
                                        <!-- Course Header -->
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="flex-grow-1">
                                                <h5 class="card-title fw-bold mb-2 text-truncate"><?= esc($course['course_title']) ?></h5>
                                                <p class="text-muted small mb-2">
                                                    <i class="fas fa-code me-1"></i><?= esc($course['course_code']) ?>
                                                </p>
                                            </div>
                                            <?= $course['status_badge'] ?>
                                        </div>

                                        <!-- Course Description -->
                                        <p class="card-text text-muted mb-3" style="font-size: 0.9rem; line-height: 1.4;">
                                            <?= strlen($course['course_description']) > 100 ? esc(substr($course['course_description'], 0, 100)) . '...' : esc($course['course_description']) ?>
                                        </p>

                                        <!-- Course Details -->
                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <div class="bg-light p-2 rounded-2 text-center">
                                                    <div class="fw-bold text-primary"><?= esc($course['credits']) ?></div>
                                                    <small class="text-muted">Credits</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="bg-light p-2 rounded-2 text-center">
                                                    <div class="fw-bold text-info"><?= esc($course['duration_weeks']) ?>w</div>
                                                    <small class="text-muted">Duration</small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Progress Bar -->
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small class="text-muted fw-medium">Course Progress</small>
                                                <small class="fw-bold text-success"><?= $course['progress'] ?>%</small>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $course['progress'] ?>%"></div>
                                            </div>
                                        </div>

                                        <!-- Instructor Information -->
                                        <div class="mb-3">
                                            <small class="text-muted d-block">Instructor</small>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                    <?= strtoupper(substr(esc($course['instructor_name']), 0, 2)) ?>
                                                </div>
                                                <div>
                                                    <div class="fw-medium"><?= esc($course['instructor_name']) ?></div>
                                                    <small class="text-muted"><?= esc($course['instructor_email']) ?></small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Course Dates -->
                                        <div class="mb-3">
                                            <div class="row g-2">
                                                <div class="col-12">
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <strong>Start:</strong> <?= $course['start_date_formatted'] ?>
                                                    </small>
                                                </div>
                                                <div class="col-12">
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar-check me-1"></i>
                                                        <strong>End:</strong> <?= $course['end_date_formatted'] ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>                                        <!-- Enrollment Information -->
                                        <div class="alert alert-success py-2 mb-3">
                                            <small class="mb-0">
                                                <i class="fas fa-check-circle me-1"></i>
                                                <strong>Enrolled:</strong> <?= $course['enrollment_date_formatted'] ?>
                                            </small>
                                        </div>

                                        <!-- Course Materials Section -->
                                        <?php if (!empty($course['materials'])): ?>
                                        <div class="mb-3">
                                            <h6 class="fw-semibold text-primary mb-2">
                                                <i class="fas fa-download me-1"></i>Course Materials (<?= count($course['materials']) ?>)
                                            </h6>
                                            <div class="bg-light p-3 rounded-3">
                                                <?php foreach ($course['materials'] as $material): ?>
                                                <div class="d-flex justify-content-between align-items-center py-2 <?= !end($course['materials']) ? 'border-bottom' : '' ?>">
                                                    <div class="flex-grow-1">
                                                        <div class="fw-medium text-truncate" style="max-width: 200px;" title="<?= esc($material['file_name']) ?>">
                                                            <i class="fas fa-file me-1 text-muted"></i>
                                                            <?= esc($material['file_name']) ?>
                                                        </div>
                                                        <small class="text-muted">
                                                            <i class="fas fa-calendar me-1"></i>
                                                            <?= date('M j, Y', strtotime($material['created_at'])) ?>
                                                        </small>
                                                    </div>
                                                    <a href="<?= base_url('material/download/' . $material['id']) ?>" 
                                                       class="btn btn-outline-primary btn-sm" 
                                                       title="Download <?= esc($material['file_name']) ?>">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <div class="mb-3">
                                            <h6 class="fw-semibold text-muted mb-2">
                                                <i class="fas fa-download me-1"></i>Course Materials
                                            </h6>
                                            <div class="bg-light p-3 rounded-3 text-center">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    No materials available yet
                                                </small>
                                            </div>
                                        </div>
                                        <?php endif; ?>                                        <!-- Course Actions -->
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-primary btn-sm">
                                                <i class="fas fa-play me-1"></i>Continue Learning
                                            </button>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-outline-info btn-sm materials-toggle" 
                                                        data-bs-toggle="collapse" 
                                                        data-bs-target="#materials-<?= $course['course_id'] ?>" 
                                                        aria-expanded="false">
                                                    <i class="fas fa-book me-1"></i>Materials 
                                                    <span class="badge bg-info ms-1"><?= count($course['materials']) ?></span>
                                                </button>
                                                <button class="btn btn-outline-secondary btn-sm">
                                                    <i class="fas fa-tasks me-1"></i>Assignments
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Expanded Materials Section -->
                                        <div class="collapse mt-3" id="materials-<?= $course['course_id'] ?>">
                                            <div class="card border-info">
                                                <div class="card-header bg-info text-white">
                                                    <h6 class="mb-0">
                                                        <i class="fas fa-folder-open me-2"></i>Course Materials
                                                    </h6>
                                                </div>
                                                <div class="card-body p-0">
                                                    <?php if (!empty($course['materials'])): ?>
                                                        <div class="list-group list-group-flush">
                                                            <?php foreach ($course['materials'] as $index => $material): ?>
                                                            <div class="list-group-item">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <div class="flex-grow-1">
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="me-3">
                                                                                <?php
                                                                                $extension = strtolower(pathinfo($material['file_name'], PATHINFO_EXTENSION));
                                                                                $fileIcons = [
                                                                                    'pdf' => ['icon' => '📄', 'color' => 'danger'],
                                                                                    'doc' => ['icon' => '📝', 'color' => 'primary'],
                                                                                    'docx' => ['icon' => '📝', 'color' => 'primary'],
                                                                                    'xls' => ['icon' => '📊', 'color' => 'success'],
                                                                                    'xlsx' => ['icon' => '📊', 'color' => 'success'],
                                                                                    'ppt' => ['icon' => '📋', 'color' => 'warning'],
                                                                                    'pptx' => ['icon' => '📋', 'color' => 'warning'],
                                                                                    'txt' => ['icon' => '📄', 'color' => 'secondary'],
                                                                                    'jpg' => ['icon' => '🖼️', 'color' => 'info'],
                                                                                    'jpeg' => ['icon' => '🖼️', 'color' => 'info'],
                                                                                    'png' => ['icon' => '🖼️', 'color' => 'info'],
                                                                                    'mp4' => ['icon' => '🎥', 'color' => 'dark'],
                                                                                    'mp3' => ['icon' => '🎵', 'color' => 'purple']
                                                                                ];
                                                                                $icon = $fileIcons[$extension] ?? ['icon' => '📎', 'color' => 'secondary'];
                                                                                ?>
                                                                                <span class="badge bg-<?= $icon['color'] ?> rounded-circle p-2">
                                                                                    <?= $icon['icon'] ?>
                                                                                </span>
                                                                            </div>
                                                                            <div>
                                                                                <div class="fw-medium"><?= esc($material['file_name']) ?></div>
                                                                                <small class="text-muted">
                                                                                    <i class="fas fa-calendar me-1"></i>
                                                                                    Uploaded: <?= date('M j, Y g:i A', strtotime($material['created_at'])) ?>
                                                                                </small>
                                                                                <div>
                                                                                    <span class="badge bg-light text-dark">
                                                                                        <?= strtoupper($extension) ?>
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="btn-group btn-group-sm">
                                                                        <a href="<?= base_url('material/download/' . $material['id']) ?>" 
                                                                           class="btn btn-success btn-sm" 
                                                                           title="Download <?= esc($material['file_name']) ?>">
                                                                            <i class="fas fa-download"></i> Download
                                                                        </a>
                                                                        <button class="btn btn-outline-info btn-sm" 
                                                                                onclick="previewFile('<?= esc($material['file_name']) ?>', '<?= $extension ?>')"
                                                                                title="Preview file details">
                                                                            <i class="fas fa-eye"></i> Preview
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                        <div class="card-footer bg-light text-center">
                                                            <small class="text-muted">
                                                                <i class="fas fa-info-circle me-1"></i>
                                                                Total: <?= count($course['materials']) ?> files available for download
                                                            </small>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="p-4 text-center">
                                                            <i class="fas fa-folder-open text-muted mb-3" style="font-size: 2rem;"></i>
                                                            <p class="text-muted mb-0">No materials have been uploaded for this course yet.</p>
                                                            <small class="text-muted">Check back later or contact your instructor.</small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <!-- No Enrolled Courses -->
                    <div class="text-center py-5">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-body p-5">
                                <div class="mb-4">
                                    <i class="fas fa-book-open text-muted" style="font-size: 4rem;"></i>
                                </div>
                                <h4 class="text-muted mb-3">No Enrolled Courses Yet</h4>
                                <p class="text-muted mb-4">
                                    You haven't enrolled in any courses yet. Browse our available courses and start your learning journey today!
                                </p>
                                <button class="btn btn-primary" onclick="document.getElementById('available-tab').click()">
                                    <i class="fas fa-search me-1"></i>Browse Available Courses
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Available Courses Tab -->
            <div class="tab-pane fade" id="available-courses" role="tabpanel">
                <?php if (!empty($availableCourses)): ?>
                    <div class="row">
                        <?php foreach ($availableCourses as $course): ?>
                            <div class="col-lg-6 col-xl-4 mb-4">
                                <div class="card h-100 border-0 shadow-sm rounded-3 course-card available-course">
                                    <div class="card-body p-4">
                                        <!-- Course Header -->
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="flex-grow-1">
                                                <h5 class="card-title fw-bold mb-2 text-truncate"><?= esc($course['title']) ?></h5>
                                                <p class="text-muted small mb-2">
                                                    <i class="fas fa-code me-1"></i><?= esc($course['course_code']) ?>
                                                </p>
                                            </div>
                                            <span class="badge bg-primary">Available</span>
                                        </div>

                                        <!-- Course Description -->
                                        <p class="card-text text-muted mb-3" style="font-size: 0.9rem; line-height: 1.4;">
                                            <?= strlen($course['description']) > 100 ? esc(substr($course['description'], 0, 100)) . '...' : esc($course['description']) ?>
                                        </p>                        <!-- Course Details -->
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <div class="bg-light p-2 rounded-2 text-center">
                                    <div class="fw-bold text-primary"><?= esc($course['credits']) ?></div>
                                    <small class="text-muted">Credits</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-light p-2 rounded-2 text-center">
                                    <div class="fw-bold text-info"><?= esc($course['duration_weeks']) ?>w</div>
                                    <small class="text-muted">Duration</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-light p-2 rounded-2 text-center">
                                    <div class="fw-bold text-success"><?= esc($course['category']) ?></div>
                                    <small class="text-muted">Category</small>
                                </div>
                            </div>
                        </div>                        <!-- Instructor Information -->
                        <div class="mb-3">
                            <small class="text-muted d-block">Instructor</small>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                    <?= strtoupper(substr(esc($course['instructor_name']), 0, 2)) ?>
                                </div>
                                <div>
                                    <div class="fw-medium"><?= esc($course['instructor_name']) ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Course Dates -->
                        <div class="mb-3">
                            <div class="row g-2">
                                <div class="col-12">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        <strong>Starts:</strong> <?= $course['start_date_formatted'] ?>
                                    </small>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-check me-1"></i>
                                        <strong>Ends:</strong> <?= $course['end_date_formatted'] ?>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Enrollment Action -->
                        <div class="d-grid">
                            <button class="btn btn-success enroll-btn" 
                                    data-course-id="<?= $course['id'] ?>" 
                                    data-course-title="<?= esc($course['title']) ?>">
                                <i class="fas fa-plus me-1"></i>Enroll Now
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
                    </div>

                <?php else: ?>
                    <!-- No Available Courses -->
                    <div class="text-center py-5">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-body p-5">
                                <div class="mb-4">
                                    <i class="fas fa-graduation-cap text-muted" style="font-size: 4rem;"></i>
                                </div>
                                <h4 class="text-muted mb-3">No Available Courses</h4>
                                <p class="text-muted mb-4">
                                    Great job! You're enrolled in all available courses. Check back later for new courses or contact your administrator.
                                </p>
                                <button class="btn btn-outline-primary" onclick="document.getElementById('enrolled-tab').click()">
                                    <i class="fas fa-eye me-1"></i>View My Enrolled Courses
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Enrollment Success/Error Modal -->
        <div class="modal fade" id="enrollmentModal" tabindex="-1" aria-labelledby="enrollmentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="enrollmentModalLabel">Course Enrollment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="enrollmentModalBody">
                        <!-- Content will be filled by JavaScript -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
.enrolled-course {
    border-left: 4px solid #198754;
}

.available-course {
    border-left: 4px solid #0d6efd;
}

.available-course:hover {
    border-left-color: #0b5ed7;
}

/* Progress Bar Styling */
.progress {
    border-radius: 10px;
    height: 8px;
}

.progress-bar {
    border-radius: 10px;
}

/* Navigation Tabs */
.nav-tabs {
    border-bottom: none;
}

.nav-tabs .nav-link {
    border: none;
    border-bottom: 3px solid transparent;
    background: none;
    color: #6c757d;
    border-radius: 0;
    padding: 1rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.nav-tabs .nav-link.active {
    background: none;
    border-bottom-color: #0d6efd;
    color: #0d6efd;
}

.nav-tabs .nav-link:hover {
    border-bottom-color: #0d6efd;
    color: #0d6efd;
    background: rgba(13, 110, 253, 0.05);
}

/* Button Groups */
.btn-group .btn {
    flex: 1;
}

/* Alert Styling */
.alert-success {
    border: none;
    background-color: rgba(25, 135, 84, 0.1);
    border-radius: 8px;
}

/* Card Enhancements */
.card {
    border-radius: 12px;
    overflow: hidden;
}

.card-body {
    padding: 1.25rem;
}

/* Button Hover Effects */
.enroll-btn:hover {
    transform: scale(1.02);
    transition: transform 0.2s ease;
}

.btn:hover {
    transition: all 0.2s ease;
}

/* Statistics Cards */
.bg-primary, .bg-success, .bg-info, .bg-warning {
    border-radius: 12px !important;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .course-card {
        margin-bottom: 1rem;
    }
    
    .nav-tabs .nav-link {
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
    }
    
    .display-4 {
        font-size: 2.5rem;
    }
    
    .display-5 {
        font-size: 2rem;
    }
}

/* Loading State for Buttons */
.btn[disabled] {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Enhanced Shadows and Depth */
.shadow-sm {
    box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
}

.rounded-3 {
    border-radius: 12px !important;
}

/* Custom Badge Styling */
.badge {
    font-size: 0.75em;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
}

/* Materials Toggle Button */
.materials-toggle {
    position: relative;
    transition: all 0.3s ease;
}

.materials-toggle:hover {
    transform: translateY(-1px);
}

.materials-toggle .badge {
    font-size: 0.7em;
    padding: 0.2rem 0.4rem;
}

/* Materials Collapse Animation */
.collapse {
    transition: all 0.35s ease;
}

/* File List Styling */
.list-group-item {
    border-left: 3px solid transparent;
    transition: all 0.2s ease;
}

.list-group-item:hover {
    border-left-color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.05);
}

/* File Icons */
.badge.rounded-circle {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

/* Purple color for audio files */
.bg-purple {
    background-color: #6f42c1 !important;
}

/* Download Button Animation */
.btn-group .btn {
    transition: all 0.2s ease;
}

.btn-group .btn:hover {
    transform: scale(1.05);
}
</style>

<!-- Student Courses AJAX Enrollment Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get all enrollment buttons
    const enrollButtons = document.querySelectorAll('.enroll-btn');
    const enrollmentModal = new bootstrap.Modal(document.getElementById('enrollmentModal'));
    const modalBody = document.getElementById('enrollmentModalBody');
    const modalTitle = document.getElementById('enrollmentModalLabel');

    // Materials toggle functionality
    const materialsButtons = document.querySelectorAll('.materials-toggle');
    materialsButtons.forEach(button => {
        button.addEventListener('click', function() {
            const icon = this.querySelector('i');
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            // Toggle icon
            if (isExpanded) {
                icon.className = 'fas fa-book me-1';
            } else {
                icon.className = 'fas fa-book-open me-1';
            }
        });
    });

    enrollButtons.forEach(button => {
        button.addEventListener('click', function() {
            const courseId = this.dataset.courseId;
            const courseTitle = this.dataset.courseTitle;
            const originalButton = this;

            // Disable button and show loading state
            originalButton.disabled = true;
            originalButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Enrolling...';

            // Get CSRF tokens from meta tags
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfHash = document.querySelector('meta[name="csrf-hash"]').getAttribute('content');

            // Make AJAX request to enroll
            fetch('<?= base_url("course/enroll") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    course_id: courseId,
                    csrf_test_name: csrfHash
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Success: Show success modal
                    modalTitle.textContent = 'Enrollment Successful!';
                    modalBody.innerHTML = `
                        <div class="text-center">
                            <div class="mb-3">
                                <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="text-success">Welcome to ${courseTitle}!</h5>
                            <p class="text-muted">You have been successfully enrolled in this course. The page will refresh to show your updated course list.</p>
                            <div class="alert alert-info">
                                <strong>Enrollment Date:</strong> ${data.data.enrollment_date_formatted}
                            </div>
                        </div>
                    `;
                    
                    // Show success modal
                    enrollmentModal.show();
                    
                    // Refresh page after modal is hidden to show updated course lists
                    document.getElementById('enrollmentModal').addEventListener('hidden.bs.modal', function() {
                        window.location.reload();
                    });
                    
                } else {
                    // Error: Show error modal
                    modalTitle.textContent = 'Enrollment Failed';
                    let errorMessage = data.message || 'An unexpected error occurred.';
                    
                    modalBody.innerHTML = `
                        <div class="text-center">
                            <div class="mb-3">
                                <i class="fas fa-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="text-danger">Enrollment Failed</h5>
                            <p class="text-muted">${errorMessage}</p>
                        </div>
                    `;
                    
                    // Reset button state
                    originalButton.disabled = false;
                    originalButton.innerHTML = '<i class="fas fa-plus me-1"></i>Enroll Now';
                    
                    enrollmentModal.show();
                }
                
                // Update CSRF hash for future requests
                if (data.csrf_hash) {
                    document.querySelector('meta[name="csrf-hash"]').setAttribute('content', data.csrf_hash);
                }
            })
            .catch(error => {
                console.error('Enrollment error:', error);
                
                // Error: Show generic error modal
                modalTitle.textContent = 'Enrollment Failed';
                modalBody.innerHTML = `
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="text-danger">Connection Error</h5>
                        <p class="text-muted">Unable to process enrollment. Please check your connection and try again.</p>
                    </div>
                `;
                
                // Reset button state
                originalButton.disabled = false;
                originalButton.innerHTML = '<i class="fas fa-plus me-1"></i>Enroll Now';
                
                enrollmentModal.show();
            });
        });
    });
});

// File preview function
function previewFile(fileName, extension) {
    const modal = new bootstrap.Modal(document.createElement('div'));
    const modalElement = document.createElement('div');
    modalElement.className = 'modal fade';
    modalElement.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file me-2"></i>File Preview: ${fileName}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <div class="mb-3">
                            ${getFileIcon(extension, '4rem')}
                        </div>
                        <h6>${fileName}</h6>
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <strong>File Type</strong><br>
                                        <span class="badge bg-primary">${extension.toUpperCase()}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <strong>Category</strong><br>
                                        <span class="text-muted">${getFileCategory(extension)}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Click download to access this file on your device
                            </p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modalElement);
    const bsModal = new bootstrap.Modal(modalElement);
    bsModal.show();
    
    // Clean up modal after hiding
    modalElement.addEventListener('hidden.bs.modal', function() {
        document.body.removeChild(modalElement);
    });
}

function getFileIcon(extension, size = '1rem') {
    const icons = {
        'pdf': '📄',
        'doc': '📝', 'docx': '📝',
        'xls': '📊', 'xlsx': '📊',
        'ppt': '📋', 'pptx': '📋',
        'txt': '📄',
        'jpg': '🖼️', 'jpeg': '🖼️', 'png': '🖼️', 'gif': '🖼️',
        'mp4': '🎥', 'avi': '🎥', 'mov': '🎥',
        'mp3': '🎵', 'wav': '🎵'
    };
    
    return `<span style="font-size: ${size}">${icons[extension] || '📎'}</span>`;
}

function getFileCategory(extension) {
    const categories = {
        'pdf': 'Document',
        'doc': 'Document', 'docx': 'Document', 'txt': 'Document', 'rtf': 'Document',
        'xls': 'Spreadsheet', 'xlsx': 'Spreadsheet',
        'ppt': 'Presentation', 'pptx': 'Presentation',
        'jpg': 'Image', 'jpeg': 'Image', 'png': 'Image', 'gif': 'Image',
        'mp4': 'Video', 'avi': 'Video', 'mov': 'Video',
        'mp3': 'Audio', 'wav': 'Audio'
    };
    
    return categories[extension] || 'File';
}
</script>

