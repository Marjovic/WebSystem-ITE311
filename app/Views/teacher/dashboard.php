<?= $this->include('templates/header') ?>

<div class="bg-light min-vh-100">
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <h2 class="mb-2 fw-bold">👨‍🏫 Teacher Dashboard</h2>
                        <p class="mb-0 opacity-75">Welcome back, <?= esc($user['name']) ?>! Manage your courses and students with ease.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-4">
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
        </div>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-0 fw-bold text-dark">⚡ Quick Actions</h5>
                        <small class="text-muted">Manage your teaching activities</small>
                    </div>
                    <div class="card-body pt-3">
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
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
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
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->include('templates/footer') ?>