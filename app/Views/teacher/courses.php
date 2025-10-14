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
        </div>        
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
                                            </div>                                            <!-- Enrolled Students -->
                                            <div class="mb-3">
                                                <h6 class="fw-semibold text-primary mb-2">👥 Enrolled Students (<?= $course['enrolled_students'] ?>)</h6>
                                                <?php if ($course['enrolled_students'] > 0 && !empty($course['students'])): ?>
                                                    <div class="bg-light p-3 rounded-3">
                                                        <div class="small">
                                                            <?php foreach ($course['students'] as $student): ?>
                                                                <div class="d-flex align-items-center justify-content-between mb-2 p-2 bg-white rounded border">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                                             style="width: 30px; height: 30px; font-size: 0.8rem;">
                                                                            👤
                                                                        </div>
                                                                        <div>
                                                                            <div class="fw-semibold"><?= esc($student['name']) ?></div>
                                                                            <div class="text-muted small"><?= esc($student['email']) ?></div>
                                                                            <div class="text-muted small">Enrolled: <?= date('M j, Y', strtotime($student['enrollment_date'])) ?></div>
                                                                        </div>
                                                                    </div>
                                                                    <button type="button" 
                                                                            class="btn btn-outline-danger btn-sm remove-student-btn"
                                                                            data-student-id="<?= $student['user_id'] ?>"
                                                                            data-student-name="<?= esc($student['name']) ?>"
                                                                            data-course-id="<?= $course['id'] ?>"
                                                                            data-course-title="<?= esc($course['title']) ?>"
                                                                            title="Remove student from course">
                                                                        <i class="fas fa-user-minus me-1"></i>Remove
                                                                    </button>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>                                                <?php else: ?>
                                                    <div class="text-center py-3 text-muted">
                                                        <div class="mb-2">
                                                            <i class="fas fa-users text-muted" style="font-size: 2rem;"></i>
                                                        </div>
                                                        <small>No students enrolled yet</small>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <!-- Add Student Button -->
                                                <div class="d-flex justify-content-end mt-2">
                                                    <button type="button" 
                                                            class="btn btn-outline-success btn-sm add-student-btn"
                                                            data-course-id="<?= $course['id'] ?>"
                                                            data-course-title="<?= esc($course['title']) ?>"
                                                            data-course-code="<?= esc($course['course_code']) ?>"
                                                            title="Add student to course">
                                                        <i class="fas fa-user-plus me-1"></i>Add Student
                                                    </button>
                                                </div>
                                            </div><!-- Co-Instructors -->
                                            <?php if (!empty($course['co_instructors'])): ?>
                                            <div class="mb-3">
                                                <h6 class="fw-semibold text-info mb-2">👥 Co-Instructors</h6>
                                                <div class="d-flex flex-wrap gap-1">
                                                    <?php foreach ($course['co_instructors'] as $coInstructor): ?>
                                                        <span class="badge bg-info text-white rounded-pill" title="<?= esc($coInstructor['email']) ?>">
                                                            <i class="fas fa-user-tie me-1"></i><?= esc($coInstructor['name']) ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    This course has multiple instructors working together
                                                </small>
                                            </div>
                                            <?php endif; ?>

                                            <!-- Course Description -->
                                            <?php if ($course['description']): ?>
                                            <div class="mb-3">
                                                <h6 class="fw-semibold text-secondary mb-2">📄 Description</h6>
                                                <p class="small text-muted mb-0"><?= esc($course['description']) ?></p>
                                            </div>
                                            <?php endif; ?></div>                                        <div class="card-footer bg-light border-0">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar-alt me-1"></i>
                                                    Assigned: <?= date('M j, Y', strtotime($course['created_at'])) ?>
                                                </small>
                                            </div>
                                            
                                            <!-- Action Buttons -->
                                            <div class="d-flex gap-2">                                                
                                                <!-- Upload Materials Button -->
                                                <a href="<?= base_url('teacher/course/' . $course['id'] . '/upload') ?>" 
                                                   class="btn btn-outline-success btn-sm flex-fill" 
                                                   title="Upload Course Materials">
                                                    <i class="fas fa-upload me-1"></i>Upload Materials
                                                </a>
                                                
                                                <!-- Unassign Button -->
                                                <form method="post" action="<?= base_url('teacher/courses') ?>" class="flex-fill">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="action" value="unassign_course">
                                                    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100" 
                                                            onclick="return confirm('Are you sure you want to unassign yourself from <?= esc($course['title']) ?> (<?= esc($course['course_code']) ?>)?\n\nThis will remove you as the instructor and make the course available for other teachers to request.')"
                                                            title="Unassign from course">
                                                        <i class="fas fa-times me-1"></i>Unassign
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>                        
                            <?php else: ?>
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
                                            </div>                                            <!-- Enrollment Statistics -->
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        <i class="fas fa-users me-1"></i>Current Students:
                                                    </small>
                                                    <span class="badge bg-secondary"><?= $course['enrolled_students'] ?>/<?= $course['max_students'] ?></span>
                                                </div>
                                            </div>

                                            <!-- Existing Instructors -->
                                            <?php if (!empty($course['existing_instructors'])): ?>
                                            <div class="mb-3">
                                                <h6 class="fw-semibold text-warning mb-2">👨‍🏫 Current Instructors</h6>
                                                <div class="d-flex flex-wrap gap-1">
                                                    <?php foreach ($course['existing_instructors'] as $instructor): ?>
                                                        <span class="badge bg-warning text-dark rounded-pill" title="<?= esc($instructor['email']) ?>">
                                                            <i class="fas fa-user-tie me-1"></i><?= esc($instructor['name']) ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    You can join as a co-instructor
                                                </small>
                                            </div>
                                            <?php endif; ?>

                                            <!-- Assignment Action -->
                                            <form method="post" action="<?= base_url('teacher/courses') ?>" class="d-grid">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="assign_course">
                                                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                                <button type="submit" class="btn btn-success btn-sm" 
                                                        onclick="return confirm('Are you sure you want to request to teach <?= esc($course['title']) ?> (<?= esc($course['course_code']) ?>)?')">
                                                    <i class="fas fa-hand-paper me-1"></i><?= !empty($course['existing_instructors']) ? 'Join as Co-Instructor' : 'Request to Teach' ?>
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

/* Remove Student Button Styling */
.remove-student-btn {
    transition: all 0.2s ease;
}

.remove-student-btn:hover {
    transform: scale(1.05);
}

/* Add Student Button Styling */
.add-student-btn {
    transition: all 0.2s ease;
}

.add-student-btn:hover {
    transform: scale(1.05);
}

/* Student Selection Styling */
.student-selection-item {
    cursor: pointer;
    transition: all 0.2s ease;
}

.student-selection-item:hover {
    background-color: #f8f9fa !important;
}

.student-selection-item.selected {
    background-color: #e3f2fd !important;
    border-color: #2196f3 !important;
}
</style>

<!-- Remove Student Confirmation Modal -->
<div class="modal fade" id="removeStudentModal" tabindex="-1" aria-labelledby="removeStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeStudentModalLabel">Remove Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="removeStudentModalBody">
            </div>            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="confirmRemoveStudent">Remove Student</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStudentModalLabel">Add Student to Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="addStudentModalBody">
                <!-- Content will be filled by JavaScript -->
                <div class="d-flex justify-content-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="confirmAddStudent" disabled>Add Student</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Remove Student Functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get all remove student buttons
    const removeButtons = document.querySelectorAll('.remove-student-btn');
    const removeModal = new bootstrap.Modal(document.getElementById('removeStudentModal'));
    const modalBody = document.getElementById('removeStudentModalBody');
    const modalTitle = document.getElementById('removeStudentModalLabel');
    const confirmButton = document.getElementById('confirmRemoveStudent');
    
    let currentStudentId = null;
    let currentCourseId = null;
    let currentButton = null;

    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const studentId = this.dataset.studentId;
            const studentName = this.dataset.studentName;
            const courseId = this.dataset.courseId;
            const courseTitle = this.dataset.courseTitle;
            
            // Store current operation data
            currentStudentId = studentId;
            currentCourseId = courseId;
            currentButton = this;

            // Update modal content
            modalTitle.textContent = 'Remove Student from Course';
            modalBody.innerHTML = `
                <div class="text-center">
                    <div class="mb-3">
                        <i class="fas fa-user-minus text-warning" style="font-size: 3rem;"></i>
                    </div>
                    <h6>Are you sure you want to remove this student?</h6>
                    <div class="alert alert-warning">
                        <strong>Student:</strong> ${studentName}<br>
                        <strong>Course:</strong> ${courseTitle}
                    </div>
                    <p class="text-muted small">
                        This action will remove the student from your course. The student will lose access to all course materials and activities.
                    </p>
                </div>
            `;

            // Show modal
            removeModal.show();
        });
    });

    // Handle confirm remove button
    confirmButton.addEventListener('click', function() {
        if (!currentStudentId || !currentCourseId || !currentButton) {
            return;
        }

        // Disable button and show loading state
        confirmButton.disabled = true;
        confirmButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Removing...';

        // Get CSRF tokens from meta tags
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfHash = document.querySelector('meta[name="csrf-hash"]').getAttribute('content');

        // Prepare the removal request
        const formData = new FormData();
        formData.append('student_id', currentStudentId);
        formData.append('course_id', currentCourseId);
        formData.append(csrfToken, csrfHash);

        // Make AJAX request
        fetch('<?= base_url('/teacher/course/remove_student') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfHash
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Success: Show success message and remove student from view
                modalTitle.textContent = 'Student Removed Successfully!';
                modalBody.innerHTML = `
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                        </div>
                        <h6 class="text-success">Student Successfully Removed</h6>
                        <div class="alert alert-success">
                            <strong>${data.data.student_name}</strong> has been removed from <strong>${data.data.course_title}</strong>
                        </div>
                        <p class="text-muted small">
                            Removed by: ${data.data.removed_by} on ${data.data.removal_date}
                        </p>
                    </div>
                `;                
                // Hide both buttons on success - user can only close via X or clicking outside
                confirmButton.style.display = 'none';
                document.querySelector('#removeStudentModal .modal-footer [data-bs-dismiss="modal"]').style.display = 'none';

                // Remove the student row from the view
                const studentRow = currentButton.closest('.d-flex.align-items-center.justify-content-between');
                if (studentRow) {
                    studentRow.style.transition = 'opacity 0.5s ease';
                    studentRow.style.opacity = '0';
                    setTimeout(() => {
                        studentRow.remove();
                        
                        // Update enrolled count
                        const courseCard = currentButton.closest('.course-card');
                        const enrolledHeader = courseCard.querySelector('h6.fw-semibold.text-primary');
                        if (enrolledHeader) {
                            const currentCount = parseInt(enrolledHeader.textContent.match(/\d+/)[0]);
                            const newCount = currentCount - 1;
                            enrolledHeader.innerHTML = `👥 Enrolled Students (${newCount})`;
                            
                            // If no students left, show empty message
                            if (newCount === 0) {
                                const studentContainer = courseCard.querySelector('.bg-light');
                                studentContainer.innerHTML = `
                                    <div class="text-center py-3 text-muted">
                                        <div class="mb-2">
                                            <i class="fas fa-users text-muted" style="font-size: 2rem;"></i>
                                        </div>
                                        <small>No students enrolled yet</small>
                                    </div>
                                `;
                            }
                        }
                    }, 500);
                }
                
                setTimeout(() => {
                    removeModal.hide();
                }, 1000);
                
            } else {
                // Error: Show error message
                modalTitle.textContent = 'Removal Failed';
                let errorMessage = data.message || 'An unexpected error occurred.';
                  modalBody.innerHTML = `
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                        </div>
                        <h6 class="text-danger">Failed to Remove Student</h6>
                        <div class="alert alert-danger">
                            ${errorMessage}
                        </div>
                    </div>
                `;

                // Reset confirm button and show both buttons
                confirmButton.disabled = false;
                confirmButton.innerHTML = 'Try Again';
                confirmButton.className = 'btn btn-danger';
                confirmButton.style.display = '';
                document.querySelector('#removeStudentModal .modal-footer [data-bs-dismiss="modal"]').style.display = '';
            }
            
            // Update CSRF hash for future requests
            if (data.csrf_hash) {
                document.querySelector('meta[name="csrf-hash"]').setAttribute('content', data.csrf_hash);
            }
        })
        .catch(error => {
            console.error('Remove student error:', error);
            
            // Error: Show generic error message
            modalTitle.textContent = 'Removal Failed';
            modalBody.innerHTML = `
                <div class="text-center">
                    <div class="mb-3">
                        <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="text-danger">Network Error</h6>
                    <div class="alert alert-danger">
                        Unable to connect to server. Please check your internet connection and try again.
                    </div>
                </div>
            `;
              // Reset confirm button and show both buttons
            confirmButton.disabled = false;
            confirmButton.innerHTML = 'Try Again';
            confirmButton.className = 'btn btn-danger';
            confirmButton.style.display = '';
            document.querySelector('#removeStudentModal .modal-footer [data-bs-dismiss="modal"]').style.display = '';
        });
    });    // Reset modal when it's hidden
    document.getElementById('removeStudentModal').addEventListener('hidden.bs.modal', function() {
        // Reset variables
        currentStudentId = null;
        currentCourseId = null;
        currentButton = null;
          // Reset button state and ensure buttons are visible
        confirmButton.disabled = false;
        confirmButton.innerHTML = 'Remove Student';
        confirmButton.className = 'btn btn-danger';
        confirmButton.style.display = '';
        document.querySelector('#removeStudentModal .modal-footer [data-bs-dismiss="modal"]').style.display = '';
    });

    // ==============================================
    // ADD STUDENT FUNCTIONALITY
    // ==============================================

    // Get all add student buttons
    const addButtons = document.querySelectorAll('.add-student-btn');
    const addModal = new bootstrap.Modal(document.getElementById('addStudentModal'));
    const addModalBody = document.getElementById('addStudentModalBody');
    const addModalTitle = document.getElementById('addStudentModalLabel');
    const addConfirmButton = document.getElementById('confirmAddStudent');
    const addModalFooter = document.querySelector('#addStudentModal .modal-footer');
    
    let currentAddCourseId = null;
    let currentAddButton = null;
    let selectedStudentId = null;
    let availableStudents = [];

    addButtons.forEach(button => {
        button.addEventListener('click', function() {
            const courseId = this.dataset.courseId;
            const courseTitle = this.dataset.courseTitle;
            const courseCode = this.dataset.courseCode;
            
            // Store current operation data
            currentAddCourseId = courseId;
            currentAddButton = this;
            selectedStudentId = null;

            // Update modal title
            addModalTitle.textContent = `Add Student to ${courseCode}`;
            
            // Reset confirm button
            addConfirmButton.disabled = true;
            addConfirmButton.textContent = 'Add Student';
            addConfirmButton.className = 'btn btn-success';
            addConfirmButton.style.display = '';
            // Always show Cancel button
            addModalFooter.querySelector('[data-bs-dismiss="modal"]').style.display = '';

            // Show loading state
            addModalBody.innerHTML = `
                <div class="d-flex justify-content-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading available students...</span>
                    </div>
                </div>
            `;

            // Show modal
            addModal.show();

            // Always reload available students every time modal opens
            loadAvailableStudents(courseId, courseTitle);
        });
    });

    function loadAvailableStudents(courseId, courseTitle) {
        fetch(`<?= base_url('/teacher/course/get_available_students') ?>?course_id=${courseId}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                availableStudents = data.data.students;
                displayAvailableStudents(data.data.students, courseTitle);
            } else {
                showErrorState(data.message || 'Failed to load available students.');
            }
        })
        .catch(error => {
            console.error('Error loading students:', error);
            showErrorState('Network error. Please try again.');
        });
    }

    function displayAvailableStudents(students, courseTitle) {
        if (students.length === 0) {
            addModalBody.innerHTML = `
                <div class="text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-users text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="text-muted">No Available Students</h6>
                    <p class="text-muted small">All students are already enrolled in this course, or there are no students in the system.</p>
                </div>
            `;
            return;
        }

        let studentsHtml = `
            <div class="mb-3">
                <h6 class="fw-semibold text-primary">Select a student to add to "${courseTitle}":</h6>
                <p class="text-muted small">Click on a student to select them for enrollment.</p>
            </div>
            <div class="available-students-list" style="max-height: 400px; overflow-y: auto;">
        `;

        students.forEach(student => {
            studentsHtml += `
                <div class="student-selection-item p-3 mb-2 border rounded-3" 
                     data-student-id="${student.id}" 
                     data-student-name="${student.name}"
                     data-student-email="${student.email}">
                    <div class="d-flex align-items-center">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 40px; height: 40px; font-size: 1rem;">
                            👤
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">${student.name}</div>
                            <div class="text-muted small">${student.email}</div>
                        </div>
                        <div class="ms-auto">
                            <i class="fas fa-plus text-success"></i>
                        </div>
                    </div>
                </div>
            `;
        });

        studentsHtml += '</div>';
        addModalBody.innerHTML = studentsHtml;

        // Add click handlers for student selection
        document.querySelectorAll('.student-selection-item').forEach(item => {
            item.addEventListener('click', function() {
                // Remove previous selection
                document.querySelectorAll('.student-selection-item').forEach(el => {
                    el.classList.remove('selected');
                });

                // Add selection to clicked item
                this.classList.add('selected');

                // Store selected student data
                selectedStudentId = this.dataset.studentId;

                // Enable confirm button
                addConfirmButton.disabled = false;
            });
        });
    }

    function showErrorState(message) {
        addModalBody.innerHTML = `
            <div class="text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                </div>
                <h6 class="text-warning">Error Loading Students</h6>
                <div class="alert alert-warning">
                    ${message}
                </div>
                <button class="btn btn-primary btn-sm" onclick="loadAvailableStudents(currentAddCourseId, 'Course')">
                    <i class="fas fa-retry me-1"></i>Try Again
                </button>
            </div>
        `;
    }

    // Handle confirm add button
    addConfirmButton.addEventListener('click', function() {
        if (!selectedStudentId || !currentAddCourseId || !currentAddButton) {
            return;
        }

        // Get selected student details
        const selectedStudent = availableStudents.find(s => s.id == selectedStudentId);
        if (!selectedStudent) {
            return;
        }

        // Disable button and show loading state
        addConfirmButton.disabled = true;
        addConfirmButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Adding...';

        // Get CSRF tokens from meta tags
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfHash = document.querySelector('meta[name="csrf-hash"]').getAttribute('content');

        // Prepare the addition request
        const formData = new FormData();
        formData.append('student_id', selectedStudentId);
        formData.append('course_id', currentAddCourseId);
        formData.append(csrfToken, csrfHash);

        // Make AJAX request
        fetch('<?= base_url('/teacher/course/add_student') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfHash
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Success: Show success message and add student to view
                addModalTitle.textContent = 'Student Added Successfully!';
                addModalBody.innerHTML = `
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                        </div>
                        <h6 class="text-success">Student Successfully Added</h6>
                        <div class="alert alert-success">
                            <strong>${data.data.student_name}</strong> has been added to <strong>${data.data.course_title}</strong>
                        </div>
                        <p class="text-muted small">
                            Added by: ${data.data.added_by} on ${data.data.addition_date}
                        </p>
                    </div>
                `;                
                // Hide confirm button, only show Cancel
                addConfirmButton.style.display = 'none';
                addModalFooter.querySelector('[data-bs-dismiss="modal"]').style.display = '';
                addStudentToView(data.data);

                // Update CSRF token
                if (data.csrf_hash) {
                    document.querySelector('meta[name="csrf-hash"]').setAttribute('content', data.csrf_hash);
                }

                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                // Error: Show error message
                addModalTitle.textContent = 'Addition Failed';
                addModalBody.innerHTML = `
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="fas fa-exclamation-circle text-danger" style="font-size: 3rem;"></i>
                        </div>
                        <h6 class="text-danger">Failed to Add Student</h6>
                        <div class="alert alert-danger">
                            ${data.message}
                        </div>
                    </div>
                `;

                // Reset confirm button
                addConfirmButton.disabled = false;
                addConfirmButton.innerHTML = 'Try Again';
                addConfirmButton.className = 'btn btn-danger';
                addConfirmButton.style.display = '';
                addModalFooter.querySelector('[data-bs-dismiss="modal"]').style.display = '';

                // Update CSRF token if provided
                if (data.csrf_hash) {
                    document.querySelector('meta[name="csrf-hash"]').setAttribute('content', data.csrf_hash);
                }
            }
        })
        .catch(error => {
            console.error('Error adding student:', error);
            // Network error
            addModalTitle.textContent = 'Network Error';
            addModalBody.innerHTML = `
                <div class="text-center">
                    <div class="mb-3">
                        <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="text-danger">Network Error</h6>
                    <div class="alert alert-danger">
                        Unable to connect to server. Please check your internet connection and try again.
                    </div>
                </div>
            `;
            
            // Reset confirm button
            addConfirmButton.disabled = false;
            addConfirmButton.innerHTML = 'Try Again';
            addConfirmButton.className = 'btn btn-danger';
        });
    });

    function addStudentToView(studentData) {
        const courseCard = currentAddButton.closest('.course-card');
        const enrolledHeader = courseCard.querySelector('h6.fw-semibold.text-primary');
        const studentContainer = courseCard.querySelector('.bg-light');
        
        if (enrolledHeader && studentContainer) {
            // Update enrolled count
            const currentCount = parseInt(enrolledHeader.textContent.match(/\d+/)[0]);
            const newCount = currentCount + 1;
            enrolledHeader.innerHTML = `👥 Enrolled Students (${newCount})`;
            
            // Check if there's currently a "no students" message
            const noStudentsMessage = studentContainer.querySelector('.text-center.py-3.text-muted');
            if (noStudentsMessage) {
                // Replace no students message with student list
                studentContainer.innerHTML = `
                    <div class="small">
                        <div class="d-flex align-items-center justify-content-between mb-2 p-2 bg-white rounded border">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                     style="width: 30px; height: 30px; font-size: 0.8rem;">
                                    👤
                                </div>
                                <div>
                                    <div class="fw-semibold">${studentData.student_name}</div>
                                    <div class="text-muted small">${studentData.student_email}</div>
                                    <div class="text-muted small">Enrolled: ${studentData.enrollment_date_formatted}</div>
                                </div>
                            </div>
                            <button type="button" 
                                    class="btn btn-outline-danger btn-sm remove-student-btn"
                                    data-student-id="${studentData.student_id}"
                                    data-student-name="${studentData.student_name}"
                                    data-course-id="${currentAddCourseId}"
                                    data-course-title="${studentData.course_title}"
                                    title="Remove student from course">
                                <i class="fas fa-user-minus me-1"></i>Remove
                            </button>
                        </div>
                    </div>
                `;
            } else {
                // Add to existing student list
                const studentList = studentContainer.querySelector('.small');
                if (studentList) {
                    const newStudentHtml = `
                        <div class="d-flex align-items-center justify-content-between mb-2 p-2 bg-white rounded border">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                     style="width: 30px; height: 30px; font-size: 0.8rem;">
                                    👤
                                </div>
                                <div>
                                    <div class="fw-semibold">${studentData.student_name}</div>
                                    <div class="text-muted small">${studentData.student_email}</div>
                                    <div class="text-muted small">Enrolled: ${studentData.enrollment_date_formatted}</div>
                                </div>
                            </div>
                            <button type="button" 
                                    class="btn btn-outline-danger btn-sm remove-student-btn"
                                    data-student-id="${studentData.student_id}"
                                    data-student-name="${studentData.student_name}"
                                    data-course-id="${currentAddCourseId}"
                                    data-course-title="${studentData.course_title}"
                                    title="Remove student from course">
                                <i class="fas fa-user-minus me-1"></i>Remove
                            </button>
                        </div>
                    `;
                    studentList.insertAdjacentHTML('beforeend', newStudentHtml);
                }
            }

            // Re-attach event listeners to new remove button
            const newRemoveButton = studentContainer.querySelector('.remove-student-btn[data-student-id="' + studentData.student_id + '"]');
            if (newRemoveButton) {
                newRemoveButton.addEventListener('click', function() {
                    const studentId = this.dataset.studentId;
                    const studentName = this.dataset.studentName;
                    const courseId = this.dataset.courseId;
                    const courseTitle = this.dataset.courseTitle;
                    
                    // Store current operation data for remove functionality
                    currentStudentId = studentId;
                    currentCourseId = courseId;
                    currentButton = this;

                    // Update remove modal content
                    modalTitle.textContent = 'Remove Student from Course';
                    modalBody.innerHTML = `
                        <div class="text-center">
                            <div class="mb-3">
                                <i class="fas fa-user-minus text-warning" style="font-size: 3rem;"></i>
                            </div>
                            <h6>Are you sure you want to remove this student?</h6>
                            <div class="alert alert-warning">
                                <strong>Student:</strong> ${studentName}<br>
                                <strong>Course:</strong> ${courseTitle}
                            </div>
                            <p class="text-muted small">
                                This action will remove the student from your course. The student will lose access to all course materials and activities.
                            </p>
                        </div>
                    `;

                    // Show remove modal
                    removeModal.show();
                });
            }
        }
    }

    // Reset add modal when it's hidden
    document.getElementById('addStudentModal').addEventListener('hidden.bs.modal', function() {
        // Reset variables
        currentAddCourseId = null;
        currentAddButton = null;
        selectedStudentId = null;
        availableStudents = [];
          // Reset button state and ensure buttons are visible
        addConfirmButton.disabled = true;
        addConfirmButton.innerHTML = 'Add Student';
        addConfirmButton.className = 'btn btn-success';
        addConfirmButton.style.display = '';
        addModalFooter.querySelector('[data-bs-dismiss="modal"]').style.display = '';
        
        // Reset modal title
        addModalTitle.textContent = 'Add Student to Course';
    });
});
</script>


