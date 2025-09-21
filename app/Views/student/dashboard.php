<?= $this->include('templates/header') ?>

<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <h2 class="mb-2 fw-bold">🎓 Student Dashboard</h2>
                        <p class="mb-0 opacity-75">Welcome back, <?= esc($user['name']) ?>! Continue your learning journey and achieve your goals.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">📚</div>
                    <div class="display-5 fw-bold"><?= $enrolledCourses ?? '3' ?></div>
                    <div class="fw-semibold">Enrolled Courses</div>
                    <small class="opacity-75">Active learning paths</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">✅</div>
                    <div class="display-5 fw-bold"><?= $completedAssignments ?? '15' ?></div>
                    <div class="fw-semibold">Completed</div>
                    <small class="opacity-75">Assignments finished</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">⏰</div>
                    <div class="display-5 fw-bold"><?= $pendingAssignments ?? '3' ?></div>
                    <div class="fw-semibold">Pending</div>
                    <small class="opacity-75">Awaiting completion</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-2">📊</div>
                    <div class="display-5 fw-bold"><?= $averageGrade ?? '85' ?>%</div>
                    <div class="fw-semibold">Average Grade</div>
                    <small class="opacity-75">Overall performance</small>
                </div>
            </div>
        </div>

        <!-- Enrolled Courses -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-0 fw-bold text-dark">📖 My Enrolled Courses</h5>
                        <small class="text-muted">Continue your learning journey</small>
                    </div>
                    <div class="card-body pt-3">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-primary text-white border-0">
                                        <h6 class="mb-1 fw-bold">Web Development Basics</h6>
                                        <small class="opacity-75">HTML, CSS, and JavaScript fundamentals</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="progress mb-3" style="height: 8px;">
                                            <div class="progress-bar bg-success rounded" style="width: 75%"></div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <small class="text-muted fw-semibold">75% Complete</small>
                                            <span class="badge bg-success rounded-pill">Active</span>
                                        </div>
                                        <a href="#" class="btn btn-success rounded-pill w-100 fw-semibold">Continue Learning</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-primary text-white border-0">
                                        <h6 class="mb-1 fw-bold">PHP Fundamentals</h6>
                                        <small class="opacity-75">Server-side programming with PHP</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="progress mb-3" style="height: 8px;">
                                            <div class="progress-bar bg-primary rounded" style="width: 45%"></div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <small class="text-muted fw-semibold">45% Complete</small>
                                            <span class="badge bg-primary rounded-pill">In Progress</span>
                                        </div>
                                        <a href="#" class="btn btn-primary rounded-pill w-100 fw-semibold">Continue Learning</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-primary text-white border-0">
                                        <h6 class="mb-1 fw-bold">Database Design</h6>
                                        <small class="opacity-75">MySQL and database fundamentals</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="progress mb-3" style="height: 8px;">
                                            <div class="progress-bar bg-warning rounded" style="width: 20%"></div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <small class="text-muted fw-semibold">20% Complete</small>
                                            <span class="badge bg-warning rounded-pill">Just Started</span>
                                        </div>
                                        <a href="#" class="btn btn-warning rounded-pill w-100 fw-semibold">Continue Learning</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Deadlines and Recent Grades -->
        <div class="row mb-4">
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-0 fw-bold text-dark">⏰ Upcoming Deadlines</h5>
                        <small class="text-muted">Don't miss these important dates</small>
                    </div>
                    <div class="card-body pt-3">
                        <div class="deadline-item border-start border-danger border-3 ps-3 mb-3 bg-light rounded-end p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1 text-dark fw-semibold">JavaScript Quiz</h6>
                                    <small class="text-muted">Web Development Basics</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-danger rounded-pill">Due Tomorrow</span>
                                    <br><small class="text-muted">Sept 23, 2025</small>
                                </div>
                            </div>
                        </div>
                        <div class="deadline-item border-start border-warning border-3 ps-3 mb-3 bg-light rounded-end p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1 text-dark fw-semibold">PHP Project</h6>
                                    <small class="text-muted">PHP Fundamentals</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-warning rounded-pill">Due in 3 days</span>
                                    <br><small class="text-muted">Sept 25, 2025</small>
                                </div>
                            </div>
                        </div>
                        <div class="deadline-item border-start border-info border-3 ps-3 mb-0 bg-light rounded-end p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1 text-dark fw-semibold">Database Assignment</h6>
                                    <small class="text-muted">Database Design</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-info rounded-pill">Due in 1 week</span>
                                    <br><small class="text-muted">Sept 29, 2025</small>
                                </div>
                            </div>
                        </div>
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
                        <div class="grade-item border-start border-success border-3 ps-3 mb-3 bg-light rounded-end p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 text-dark fw-semibold">HTML/CSS Assignment</h6>
                                    <small class="text-muted">Web Development Basics</small>
                                    <p class="mb-0 mt-1"><small class="text-success">Great work on styling!</small></p>
                                </div>
                                <span class="badge bg-success fs-6 rounded-pill">A</span>
                            </div>
                        </div>
                        <div class="grade-item border-start border-warning border-3 ps-3 mb-3 bg-light rounded-end p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 text-dark fw-semibold">Variables Quiz</h6>
                                    <small class="text-muted">PHP Fundamentals</small>
                                    <p class="mb-0 mt-1"><small class="text-warning">Review array concepts</small></p>
                                </div>
                                <span class="badge bg-warning fs-6 rounded-pill">B+</span>
                            </div>
                        </div>
                        <div class="grade-item border-start border-success border-3 ps-3 mb-0 bg-light rounded-end p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 text-dark fw-semibold">ER Diagram Exercise</h6>
                                    <small class="text-muted">Database Design</small>
                                    <p class="mb-0 mt-1"><small class="text-success">Excellent understanding!</small></p>
                                </div>
                                <span class="badge bg-success fs-6 rounded-pill">A-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-0 fw-bold text-dark">⚡ Quick Actions</h5>
                        <small class="text-muted">Shortcuts to important features</small>
                    </div>
                    <div class="card-body pt-3">
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->include('templates/footer') ?>