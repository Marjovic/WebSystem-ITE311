<?= $this->include('templates/header') ?>

<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <h2 class="mb-2 fw-bold">📊 Admin Dashboard</h2>
                        <p class="mb-0 opacity-75">Welcome back, <?= esc($user['name']) ?>! Manage your learning management system with powerful tools.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
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
        </div>

        <!-- Management Links and Recent Activity -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-0 fw-bold text-dark">⚙️ Quick Actions</h5>
                        <small class="text-muted">Manage your LMS components</small>
                    </div>
                    <div class="card-body pt-3">
                        <div class="d-grid gap-3">
                            <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-primary rounded-pill fw-semibold py-3 d-flex align-items-center justify-content-center">
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
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-0 fw-bold text-dark">⏰ Recent Activity</h5>
                        <small class="text-muted">Latest system activities</small>
                    </div>                  
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->include('templates/footer') ?>