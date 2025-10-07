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
            <?php endif; ?>        </div>

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
                            <?php if (!empty($recentActivities)): ?>   
                                
                                <div class="activity-feed" style="max-height: 400px; overflow-y: auto;">
                                    <?php foreach ($recentActivities as $activity): ?>
                                        <div class="activity-item d-flex align-items-start mb-3 pb-3 border-bottom">
                                            <div class="activity-icon me-3 mt-1">
                                                <span class="badge rounded-circle p-2" style="font-size: 1.2em;">
                                                    <?= $activity['icon'] ?>
                                                </span>
                                            </div>
                                            <div class="activity-content flex-grow-1">
                                                <div class="activity-title fw-semibold text-dark mb-1">
                                                    <?= $activity['title'] ?>
                                                </div>
                                                <div class="activity-description text-muted small mb-1">
                                                    <?= $activity['description'] ?>
                                                </div>                                                <div class="activity-time text-muted" style="font-size: 0.75rem;">
                                                    <?php
                                                    $timeAgo = time() - strtotime($activity['time']);
                                                    if ($timeAgo < 60) {
                                                        echo 'Just now';
                                                    } elseif ($timeAgo < 3600) {
                                                        echo floor($timeAgo / 60) . ' minutes ago';
                                                    } elseif ($timeAgo < 86400) {
                                                        echo floor($timeAgo / 3600) . ' hours ago';
                                                    } elseif ($timeAgo < 2592000) {
                                                        echo floor($timeAgo / 86400) . ' days ago';
                                                    } else {
                                                        echo date('M j, Y', strtotime($activity['time']));
                                                    }
                                                    ?>
                                                </div>
                                            </div>                                            <div class="activity-badge">
                                                <?php
                                                // Activity type colors for different activity types
                                                $activityTypeColors = [
                                                    'user_registration' => 'success',   // Green for new registrations
                                                    'user_creation' => 'info',          // Blue for admin-created users
                                                    'user_update' => 'warning',         // Yellow for updates
                                                    'user_deletion' => 'danger'         // Red for deletions
                                                ];
                                                
                                                // Role colors for role badges
                                                $roleColors = [
                                                    'admin' => 'danger',
                                                    'teacher' => 'primary', 
                                                    'student' => 'success'
                                                ];
                                                
                                                // Get colors
                                                $activityColor = $activityTypeColors[$activity['type']] ?? 'secondary';
                                                $roleColor = $roleColors[$activity['user_role']] ?? 'secondary';
                                                ?>
                                                <div class="d-flex flex-column gap-1">
                                                    <!-- Activity Type Badge -->
                                                    <span class="badge bg-<?= $activityColor ?> rounded-pill small">
                                                        <?php
                                                        $activityLabels = [
                                                            'user_registration' => 'Registration',
                                                            'user_creation' => 'Created',
                                                            'user_update' => 'Updated',
                                                            'user_deletion' => 'Deleted'
                                                        ];
                                                        echo $activityLabels[$activity['type']] ?? 'Activity';
                                                        ?>
                                                    </span>
                                                    <!-- Role Badge -->
                                                    <span class="badge bg-<?= $roleColor ?> rounded-pill small">
                                                        <?= ucfirst($activity['user_role']) ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <?php if (count($recentActivities) >= 8): ?>
                                    <div class="text-center mt-3">
                                        <small class="text-muted">Showing latest 8 activities</small>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="text-center py-4 text-muted">
                                    <div class="mb-3">
                                        <span style="font-size: 3rem; opacity: 0.3;">⏰</span>
                                    </div>
                                    <p class="mb-0">No recent activities to display</p>
                                    <small>User activities will appear here as they occur</small>
                                </div>                            
                                <?php endif; ?>
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