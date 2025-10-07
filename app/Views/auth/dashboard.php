<?= $this->include('templates/header') ?>

<!-- Unified Dashboard View - This single file handles all user roles (Admin, Teacher, Student) -->
<!-- Uses conditional PHP statements to show different content based on user's role -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        
        <!-- Dynamic Header Section - Changes based on user role -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <!-- Admin Header -->
                        <?php if ($user['role'] === 'admin'): ?>
                            <h2 class="mb-2 fw-bold">📊 Admin Dashboard</h2>
                            <p class="mb-0 opacity-75">Welcome back, <?= esc($user['name']) ?>! Manage your learning management system with powerful tools.</p>
                        <!-- Teacher Header -->
                        <?php elseif ($user['role'] === 'teacher'): ?>
                            <h2 class="mb-2 fw-bold">👨‍🏫 Teacher Dashboard</h2>
                            <p class="mb-0 opacity-75">Welcome back, <?= esc($user['name']) ?>! Manage your courses and students with ease.</p>
                        <!-- Student Header -->
                        <?php elseif ($user['role'] === 'student'): ?>
                            <h2 class="mb-2 fw-bold">🎓 Student Dashboard</h2>
                            <p class="mb-0 opacity-75">Welcome back, <?= esc($user['name']) ?>! Continue your learning journey and achieve your goals.</p>
                        <!-- Default Header for unknown roles -->
                        <?php else: ?>
                            <h2 class="mb-2 fw-bold">🏠 Dashboard</h2>
                            <p class="mb-0 opacity-75">Welcome back, <?= esc($user['name']) ?>!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards Section - Different stats based on user role -->
        <div class="row mb-4">
            
            <!-- ADMIN STATISTICS CARDS -->
            <?php if ($user['role'] === 'admin'): ?>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                        <div class="display-4 mb-2">👥</div>
                        <div class="display-5 fw-bold"><?= $totalUsers ?></div>
                        <div class="fw-semibold">Total Users</div>
                        <small class="opacity-75">Active in system</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                        <div class="display-4 mb-2">📚</div>
                        <div class="display-5 fw-bold"><?= $totalCourses ?? '0' ?></div>
                        <div class="fw-semibold">Total Courses</div>
                        <small class="opacity-75">Available to students</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                        <div class="display-4 mb-2">👨‍🏫</div>
                        <div class="display-5 fw-bold"><?= $totalTeachers ?></div>
                        <div class="fw-semibold">Teachers</div>
                        <small class="opacity-75">Creating content</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                        <div class="display-4 mb-2">🎓</div>
                        <div class="display-5 fw-bold"><?= $totalStudents ?></div>
                        <div class="fw-semibold">Students</div>
                        <small class="opacity-75">Learning actively</small>
                    </div>
                </div>

            <!-- TEACHER STATISTICS CARDS -->
            <?php elseif ($user['role'] === 'teacher'): ?>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                        <div class="display-4 mb-2">📚</div>
                        <div class="display-5 fw-bold"><?= $totalCourses ?? '0' ?></div>
                        <div class="fw-semibold">My Courses</div>
                        <small class="opacity-75">Active courses</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                        <div class="display-4 mb-2">👥</div>
                        <div class="display-5 fw-bold"><?= $totalStudents ?? '3' ?></div>
                        <div class="fw-semibold">Students</div>
                        <small class="opacity-75">Enrolled students</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                        <div class="display-4 mb-2">📝</div>
                        <div class="display-5 fw-bold"><?= $pendingAssignments ?? '0' ?></div>
                        <div class="fw-semibold">Pending</div>
                        <small class="opacity-75">To be graded</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                        <div class="display-4 mb-2">📊</div>
                        <div class="display-5 fw-bold"><?= $averageGrade ?? '0' ?>%</div>
                        <div class="fw-semibold">Avg Grade</div>
                        <small class="opacity-75">Class average</small>
                    </div>
                </div>

            <!-- STUDENT STATISTICS CARDS -->
            <?php elseif ($user['role'] === 'student'): ?>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                        <div class="display-4 mb-2">📚</div>
                        <div class="display-5 fw-bold"><?= $enrolledCourses ?? '0' ?></div>
                        <div class="fw-semibold">Enrolled Courses</div>
                        <small class="opacity-75">Active learning paths</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                        <div class="display-4 mb-2">✅</div>
                        <div class="display-5 fw-bold"><?= $completedAssignments ?? '0' ?></div>
                        <div class="fw-semibold">Completed</div>
                        <small class="opacity-75">Assignments finished</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                        <div class="display-4 mb-2">⏰</div>
                        <div class="display-5 fw-bold"><?= $pendingAssignments ?? '0' ?></div>
                        <div class="fw-semibold">Pending</div>
                        <small class="opacity-75">Awaiting completion</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                        <div class="display-4 mb-2">📊</div>
                        <div class="display-5 fw-bold"><?= $averageGrade ?? '0' ?>%</div>
                        <div class="fw-semibold">Average Grade</div>
                        <small class="opacity-75">Overall performance</small>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <!-- Quick Actions Section - Different actions based on user role -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-0 fw-bold text-dark">⚡ Quick Actions</h5>
                        
                        <!-- Role-specific subtitle -->
                        <?php if ($user['role'] === 'admin'): ?>
                            <small class="text-muted">Manage your LMS components</small>
                        <?php elseif ($user['role'] === 'teacher'): ?>
                            <small class="text-muted">Manage your teaching activities</small>
                        <?php elseif ($user['role'] === 'student'): ?>
                            <small class="text-muted">Shortcuts to important features</small>
                        <?php endif; ?>
                    </div>
                    <div class="card-body pt-3">                          <!-- ADMIN QUICK ACTIONS -->
                        <?php if ($user['role'] === 'admin'): ?>
                            <div class="d-grid gap-3">
                                <a href="<?= base_url('dashboard?action=manageUsers') ?>" class="btn btn-outline-primary rounded-pill fw-semibold py-3 d-flex align-items-center justify-content-center">
                                    <span class="me-2">👥</span> Manage Users
                                </a>
                                <a href="<?= base_url('admin/courses') ?>" class="btn btn-outline-success rounded-pill fw-semibold py-3 d-flex align-items-center justify-content-center">
                                    <span class="me-2">📚</span> Manage Courses
                                </a>
                                <a href="<?= base_url('admin/reports') ?>" class="btn btn-outline-info rounded-pill fw-semibold py-3 d-flex align-items-center justify-content-center">
                                    <span class="me-2">📊</span> View Reports
                                </a>
                                <a href="<?= base_url('admin/settings') ?>" class="btn btn-outline-warning rounded-pill fw-semibold py-3 d-flex align-items-center justify-content-center">
                                    <span class="me-2">⚙️</span> System Settings
                                </a>
                            </div>

                        <!-- TEACHER QUICK ACTIONS -->
                        <?php elseif ($user['role'] === 'teacher'): ?>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <a href="<?= base_url('teacher/courses/create') ?>" class="btn btn-outline-success rounded-pill w-100 fw-semibold py-3 d-flex align-items-center justify-content-center">
                                        <span class="me-2">➕</span> Create Course
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="<?= base_url('teacher/lessons/create') ?>" class="btn btn-outline-primary rounded-pill w-100 fw-semibold py-3 d-flex align-items-center justify-content-center">
                                        <span class="me-2">📝</span> Create Lesson
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="<?= base_url('teacher/assignments/create') ?>" class="btn btn-outline-info rounded-pill w-100 fw-semibold py-3 d-flex align-items-center justify-content-center">
                                        <span class="me-2">📋</span> Create Assignment
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="<?= base_url('teacher/gradebook') ?>" class="btn btn-outline-warning rounded-pill w-100 fw-semibold py-3 d-flex align-items-center justify-content-center">
                                        <span class="me-2">📊</span> Gradebook
                                    </a>
                                </div>
                            </div>

                        <!-- STUDENT QUICK ACTIONS -->
                        <?php elseif ($user['role'] === 'student'): ?>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <a href="#" class="btn btn-outline-primary rounded-pill w-100 fw-semibold py-3 d-flex align-items-center justify-content-center">
                                        <span class="me-2">🔍</span> Browse Courses
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="#" class="btn btn-outline-success rounded-pill w-100 fw-semibold py-3 d-flex align-items-center justify-content-center">
                                        <span class="me-2">📝</span> Submit Assignment
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="#" class="btn btn-outline-info rounded-pill w-100 fw-semibold py-3 d-flex align-items-center justify-content-center">
                                        <span class="me-2">📅</span> View Schedule
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="#" class="btn btn-outline-secondary rounded-pill w-100 fw-semibold py-3 d-flex align-items-center justify-content-center">
                                        <span class="me-2">💬</span> Contact Teacher
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Content Section - Role-specific content -->
        <div class="row">
            
            <!-- ADMIN ADDITIONAL CONTENT -->
            <?php if ($user['role'] === 'admin'): ?>
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="mb-0 fw-bold text-dark">⏰ Recent Activity</h5>
                            <small class="text-muted">Latest system activities</small>
                        </div>
                        <div class="card-body pt-3">
                            <p class="text-muted">Recent user registrations and system updates will appear here.</p>
                        </div>
                    </div>
                </div>

            <!-- TEACHER ADDITIONAL CONTENT -->
            <?php elseif ($user['role'] === 'teacher'): ?>
                <div class="col-md-8 mb-4">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="mb-0 fw-bold text-dark">📚 My Courses</h5>
                            <small class="text-muted">Manage your active courses</small>
                        </div>
                        <div class="card-body pt-3">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="fw-semibold border-0">Course</th>
                                            <th class="fw-semibold border-0">Students</th>
                                            <th class="fw-semibold border-0">Status</th>
                                            <th class="fw-semibold border-0">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="4" class="text-muted text-center py-4">No courses available. Create your first course to get started!</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="mb-0 fw-bold text-dark">🔔 Recent Activity</h5>
                            <small class="text-muted">Latest student activities</small>
                        </div>
                        <div class="card-body pt-3">
                            <p class="text-muted">Student submissions and course activities will appear here.</p>
                        </div>
                    </div>
                </div>

            <!-- STUDENT ADDITIONAL CONTENT -->
            <?php elseif ($user['role'] === 'student'): ?>
                <div class="col-12 mb-4">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="mb-0 fw-bold text-dark">📖 My Enrolled Courses</h5>
                            <small class="text-muted">Continue your learning journey</small>
                        </div>
                        <div class="card-body pt-3">
                            <p class="text-muted">Your enrolled courses will appear here once you join some courses.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="mb-0 fw-bold text-dark">⏰ Upcoming Deadlines</h5>
                            <small class="text-muted">Don't miss these important dates</small>
                        </div>
                        <div class="card-body pt-3">
                            <p class="text-muted">Assignment deadlines and important dates will appear here.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="mb-0 fw-bold text-dark">🏆 Recent Grades & Feedback</h5>
                            <small class="text-muted">Your latest academic performance</small>
                        </div>
                        <div class="card-body pt-3">
                            <p class="text-muted">Your grades and teacher feedback will appear here.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
        </div>
    </div>
</div>