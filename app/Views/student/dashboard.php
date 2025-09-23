<?= $this->include('templates/header') ?>

<div class="bg-light min-vh-100">
    <div class="container py-4">
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
        </div>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-0 fw-bold text-dark">📖 My Enrolled Courses</h5>
                        <small class="text-muted">Continue your learning journey</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-0 fw-bold text-dark">⏰ Upcoming Deadlines</h5>
                        <small class="text-muted">Don't miss these important dates</small>
                    </div>  
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-0 fw-bold text-dark">🏆 Recent Grades & Feedback</h5>
                        <small class="text-muted">Your latest academic performance</small>
                    </div>
                </div>
            </div>
        </div>
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