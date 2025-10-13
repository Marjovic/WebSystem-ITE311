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
                        <small class="opacity-75"><?= ($activeCourses ?? '0') ?> active, <?= ($draftCourses ?? '0') ?> draft</small>
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
                        <small class="opacity-75"><?= ($activeCourses ?? '0') ?> active courses</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                        <div class="display-4 mb-2">👥</div>
                        <div class="display-5 fw-bold"><?= $totalStudents ?? '0' ?></div>
                        <div class="fw-semibold">My Students</div>
                        <small class="opacity-75">Enrolled in my courses</small>
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
        <!-- Additional Content Section - Role-specific content -->
        <div class="row">
              <!-- ADMIN ADDITIONAL CONTENT -->
            <?php if ($user['role'] === 'admin'): ?>
                <!-- Course Statistics Breakdown -->
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="mb-0 fw-bold text-dark">📚 Course Overview</h5>
                            <small class="text-muted">Course distribution by status</small>
                        </div>
                        <div class="card-body pt-3">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="d-flex align-items-center p-3 bg-success bg-opacity-10 rounded-3">
                                        <div class="me-3">
                                            <span class="badge bg-success rounded-circle p-2">✅</span>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-success"><?= $activeCourses ?? '0' ?></div>
                                            <small class="text-muted">Active Courses</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center p-3 bg-warning bg-opacity-10 rounded-3">
                                        <div class="me-3">
                                            <span class="badge bg-warning rounded-circle p-2">📝</span>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-warning"><?= $draftCourses ?? '0' ?></div>
                                            <small class="text-muted">Draft Courses</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center p-3 bg-info bg-opacity-10 rounded-3">
                                        <div class="me-3">
                                            <span class="badge bg-info rounded-circle p-2">🎯</span>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-info"><?= $completedCourses ?? '0' ?></div>
                                            <small class="text-muted">Completed</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center p-3 bg-primary bg-opacity-10 rounded-3">
                                        <div class="me-3">
                                            <span class="badge bg-primary rounded-circle p-2">📚</span>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-primary"><?= $totalCourses ?? '0' ?></div>
                                            <small class="text-muted">Total Courses</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity Section -->
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
                                                <?php                                                // Activity type colors for different activity types
                                                $activityTypeColors = [
                                                    'user_registration' => 'success',   // Green for new registrations
                                                    'user_creation' => 'info',          // Blue for admin-created users
                                                    'user_update' => 'warning',         // Yellow for updates
                                                    'user_deletion' => 'danger',        // Red for deletions
                                                    'course_creation' => 'primary',     // Blue for course creation
                                                    'course_update' => 'warning',       // Yellow for course updates  
                                                    'course_deletion' => 'danger',      // Red for course deletions
                                                    'course_assignment' => 'success',   // Green for teacher course assignments
                                                    'course_unassignment' => 'info'     // Blue for teacher course unassignments
                                                ];
                                                
                                                // Role colors for role badges
                                                $roleColors = [
                                                    'admin' => 'danger',
                                                    'teacher' => 'primary', 
                                                    'student' => 'success'
                                                ];
                                                  // Get colors
                                                $activityColor = $activityTypeColors[$activity['type']] ?? 'secondary';
                                                $roleColor = isset($activity['user_role']) ? ($roleColors[$activity['user_role']] ?? 'secondary') : 'info';
                                                ?>
                                                <div class="d-flex flex-column gap-1">
                                                    <!-- Activity Type Badge -->
                                                    <span class="badge bg-<?= $activityColor ?> rounded-pill small">
                                                        <?php                                                        $activityLabels = [
                                                            'user_registration' => 'Registration',
                                                            'user_creation' => 'User Created',
                                                            'user_update' => 'User Updated',
                                                            'user_deletion' => 'User Deleted',
                                                            'course_creation' => 'Course Created',
                                                            'course_update' => 'Course Updated',
                                                            'course_deletion' => 'Course Deleted',
                                                            'course_assignment' => 'Course Assigned',
                                                            'course_unassignment' => 'Course Unassigned'
                                                        ];
                                                        echo $activityLabels[$activity['type']] ?? 'Activity';
                                                        ?>
                                                    </span>
                                                    <!-- Role/Type Badge -->
                                                    <?php if (isset($activity['user_role'])): ?>
                                                        <span class="badge bg-<?= $roleColor ?> rounded-pill small">
                                                            <?= ucfirst($activity['user_role']) ?>
                                                        </span>
                                                    <?php elseif (isset($activity['course_code'])): ?>
                                                        <span class="badge bg-<?= $roleColor ?> rounded-pill small">
                                                            <?= esc($activity['course_code']) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-<?= $roleColor ?> rounded-pill small">
                                                            System
                                                        </span>
                                                    <?php endif; ?>
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
                <!-- Course Management Section -->
                <div class="col-md-8 mb-4">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white border-0 pb-0">
                            <div class="d-flex justify-content-between align-items-center">                                <div>
                                    <h5 class="mb-0 fw-bold text-dark">📚 Course Management</h5>
                                    <small class="text-muted">View and manage your assigned courses</small>
                                </div>
                                <a href="<?= base_url('teacher/courses') ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>View All Courses
                                </a>
                            </div>
                        </div>
                        <div class="card-body pt-3">
                            <div class="row g-3">
                                <!-- Quick Course Stats -->
                                <div class="col-sm-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                        <div class="me-3">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-chalkboard-teacher"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark"><?= $totalCourses ?? 0 ?></div>
                                            <small class="text-muted">My Courses</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                        <div class="me-3">
                                            <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-users"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark"><?= $totalStudents ?? 0 ?></div>
                                            <small class="text-muted">My Students</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                              <!-- Quick Actions -->
                            <div class="mt-4">
                                <h6 class="fw-semibold mb-3">🚀 Quick Actions</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="<?= base_url('teacher/courses') ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-book me-1"></i>View My Courses
                                    </a>
                                    <button class="btn btn-outline-info btn-sm" disabled>
                                        <i class="fas fa-chart-bar me-1"></i>Course Analytics
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm" disabled>
                                        <i class="fas fa-upload me-1"></i>Upload Materials
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity Section -->
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="mb-0 fw-bold text-dark">🔔 Recent Activity</h5>
                            <small class="text-muted">Latest course activities</small>
                        </div>
                        <div class="card-body pt-3">
                            <div class="activity-list">
                                <?php if (!empty($assignment_activities)): ?>
                                    <?php foreach (array_slice($assignment_activities, 0, 3) as $activity): ?>
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="me-3">
                                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                    <?= $activity['icon'] ?? '📚' ?>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold small"><?= esc($activity['title'] ?? 'Course Activity') ?></div>
                                                <div class="text-muted small"><?= esc($activity['description'] ?? 'Course-related activity') ?></div>
                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                    <?= date('M j, g:i A', strtotime($activity['time'] ?? 'now')) ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <div class="text-muted mb-2">
                                            <i class="fas fa-clock" style="font-size: 2rem;"></i>
                                        </div>
                                        <p class="text-muted small mb-0">No recent activities yet.<br>Start managing courses to see activities here.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div><!-- STUDENT ADDITIONAL CONTENT -->
            <?php elseif ($user['role'] === 'student'): ?>
                <!-- Enrolled Courses Section -->
                <div class="col-12 mb-4">                    
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-0 pb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0 fw-bold text-dark">📖 My Enrolled Courses</h5>
                                    <small class="text-muted">Continue your learning journey</small>
                                </div>
                                <a href="<?= base_url('student/courses') ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>View All Courses
                                </a>
                            </div>
                        </div>
                        <div class="card-body pt-3">
                            <?php if (!empty($enrolledCoursesData)): ?>
                                <div class="row g-3">
                                    <?php foreach ($enrolledCoursesData as $course): ?>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card h-100 border-0 shadow-sm">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="card-title fw-bold text-primary mb-0"><?= esc($course['course_title']) ?></h6>
                                                        <span class="badge bg-success rounded-pill small">Enrolled</span>
                                                    </div>
                                                    <p class="text-muted small mb-2"><?= esc($course['course_code']) ?> • <?= esc($course['instructor_name']) ?></p>
                                                    <p class="card-text text-muted small mb-3"><?= esc(substr($course['course_description'], 0, 100)) ?>...</p>
                                                    
                                                    <!-- Progress Bar -->
                                                    <div class="mb-3">
                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                            <small class="text-muted">Progress</small>
                                                            <small class="fw-bold"><?= $course['progress'] ?>%</small>
                                                        </div>
                                                        <div class="progress" style="height: 6px;">
                                                            <div class="progress-bar" role="progressbar" style="width: <?= $course['progress'] ?>%" aria-valuenow="<?= $course['progress'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <small class="text-muted">
                                                            <i class="fas fa-calendar"></i> Enrolled: <?= $course['enrollment_date_formatted'] ?>
                                                        </small>
                                                        <a href="<?= base_url('student/courses') ?>" class="btn btn-sm btn-outline-primary">Continue</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <div class="mb-3">
                                        <span style="font-size: 3rem; opacity: 0.3;">📚</span>
                                    </div>
                                    <p class="mb-0">No enrolled courses yet</p>
                                    <small class="text-muted">Browse available courses below to start learning!</small>
                                </div>                            
                            <?php endif; ?> 
                        </div>
                    </div>
                </div>

                <!-- Available Courses Section -->
                <div class="col-12 mb-4">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="mb-0 fw-bold text-dark">🎯 Available Courses</h5>
                            <small class="text-muted">Discover new learning opportunities</small>
                        </div>
                        <div class="card-body pt-3">
                            <?php if (!empty($availableCoursesData)): ?>
                                <div class="row g-3">
                                    <?php foreach ($availableCoursesData as $course): ?>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card h-100 border-0 shadow-sm course-card" data-course-id="<?= $course['id'] ?>">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="card-title fw-bold text-dark mb-0"><?= esc($course['title']) ?></h6>
                                                        <span class="badge bg-info rounded-pill small"><?= ucfirst($course['status']) ?></span>
                                                    </div>
                                                    <p class="text-muted small mb-2"><?= esc($course['course_code']) ?> • <?= esc($course['instructor_name']) ?></p>
                                                    <p class="card-text text-muted small mb-3"><?= esc(substr($course['description'], 0, 100)) ?>...</p>
                                                    
                                                    <!-- Course Details -->
                                                    <div class="mb-3">
                                                        <div class="row text-center">
                                                            <div class="col-4">
                                                                <small class="text-muted d-block">Credits</small>
                                                                <strong class="small"><?= $course['credits'] ?></strong>
                                                            </div>
                                                            <div class="col-4">
                                                                <small class="text-muted d-block">Duration</small>
                                                                <strong class="small"><?= $course['duration_weeks'] ?>w</strong>
                                                            </div>
                                                            <div class="col-4">
                                                                <small class="text-muted d-block">Students</small>
                                                                <strong class="small"><?= $course['max_students'] ?></strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Course Dates -->
                                                    <?php if ($course['start_date']): ?>
                                                        <div class="mb-3">
                                                            <small class="text-muted">
                                                                <i class="fas fa-calendar"></i> 
                                                                <?= $course['start_date_formatted'] ?> - <?= $course['end_date_formatted'] ?>
                                                            </small>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Enrollment Button -->
                                                    <button class="btn btn-primary btn-sm w-100 enroll-btn" 
                                                            data-course-id="<?= $course['id'] ?>"
                                                            data-course-title="<?= esc($course['title']) ?>">
                                                        <i class="fas fa-plus-circle me-1"></i>
                                                        Enroll in Course
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <div class="mb-3">
                                        <span style="font-size: 3rem; opacity: 0.3;">🎯</span>
                                    </div>
                                    <p class="mb-0">No available courses at the moment</p>
                                    <small class="text-muted">Check back later for new learning opportunities!</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Learning Statistics -->
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
            <?php endif; ?>
            
        </div>
    </div>
</div>

<!-- Student Dashboard AJAX Enrollment Script -->
<?php if ($user['role'] === 'student'): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get all enrollment buttons
    const enrollButtons = document.querySelectorAll('.enroll-btn');
    const enrollmentModal = new bootstrap.Modal(document.getElementById('enrollmentModal'));
    const modalBody = document.getElementById('enrollmentModalBody');
    const modalTitle = document.getElementById('enrollmentModalLabel');

    enrollButtons.forEach(button => {
        button.addEventListener('click', function() {
            const courseId = this.dataset.courseId;
            const courseTitle = this.dataset.courseTitle;
            const originalButton = this;

            // Disable button and show loading state
            originalButton.disabled = true;
            originalButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Enrolling...';            // Get CSRF tokens from meta tags
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfHash = document.querySelector('meta[name="csrf-hash"]').getAttribute('content');

            // Prepare the enrollment request with CSRF protection
            const formData = new FormData();
            formData.append('course_id', courseId);
            formData.append(csrfToken, csrfHash); // Add CSRF token to form data

            // Make AJAX request with CSRF headers
            fetch('<?= base_url('/course/enroll') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfHash // CSRF token to headers as well
                }
            })            .then(response => response.json())
            .then(data => {
                // Update CSRF token if provided in response
                if (data.csrf_hash) {
                    document.querySelector('meta[name="csrf-hash"]').setAttribute('content', data.csrf_hash);
                }
                
                if (data.success) {
                    // Success: Show success modal and update UI
                    modalTitle.textContent = 'Enrollment Successful!';
                    modalBody.innerHTML = `
                        <div class="text-center">
                            <div class="mb-3">
                                <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="text-success">Welcome to ${courseTitle}!</h5>
                            <p class="text-muted">You have been successfully enrolled in this course. You can now access course materials and start learning.</p>                            <div class="alert alert-info">
                                <strong>Enrollment Date:</strong> ${data.data.enrollment_date_formatted}
                            </div>
                            <div class="mt-3">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.location.reload()">
                                    <i class="fas fa-sync-alt me-1"></i>Refresh Page
                                </button>
                                <small class="text-muted d-block mt-2">Click refresh to see updated course list</small>
                            </div>
                        </div>
                    `;
                      // Update the course card to show enrolled status
                    const courseCard = originalButton.closest('.course-card');
                    if (courseCard) {
                        // Update the badge to show enrolled status
                        const badge = courseCard.querySelector('.badge');
                        if (badge) {
                            badge.className = 'badge bg-success rounded-pill small';
                            badge.textContent = 'Enrolled';
                        }
                        
                        // Replace enrollment button with enrolled status
                        originalButton.outerHTML = `
                            <div class="btn btn-success btn-sm w-100" style="pointer-events: none;">
                                <i class="fas fa-check-circle me-1"></i>
                                Successfully Enrolled!
                            </div>
                        `;
                        
                        // Add a subtle visual indicator
                        courseCard.style.border = '2px solid #198754';
                        courseCard.style.borderRadius = '0.375rem';
                    }
                      // Show success modal
                    enrollmentModal.show();
                    
                    // Note: Page refresh removed for testing purposes
                    // The course will remain visible even after enrollment
                    
                } else {
                    // Error: Show error modal
                    modalTitle.textContent = 'Enrollment Failed';
                    let errorMessage = data.message || 'An unexpected error occurred.';
                    
                    // Handle specific error types
                    if (data.error_code === 'ALREADY_ENROLLED') {
                        modalBody.innerHTML = `
                            <div class="text-center">
                                <div class="mb-3">
                                    <i class="fas fa-info-circle text-warning" style="font-size: 3rem;"></i>
                                </div>
                                <h5 class="text-warning">Already Enrolled</h5>
                                <p class="text-muted">${errorMessage}</p>
                            </div>
                        `;
                    } else {
                        modalBody.innerHTML = `
                            <div class="text-center">
                                <div class="mb-3">
                                    <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                                </div>
                                <h5 class="text-danger">Enrollment Error</h5>
                                <p class="text-muted">${errorMessage}</p>
                                <small class="text-muted">Please try again later or contact support if the problem persists.</small>
                            </div>
                        `;
                    }
                    
                    enrollmentModal.show();
                    
                    // Reset button state
                    originalButton.disabled = false;
                    originalButton.innerHTML = '<i class="fas fa-plus-circle me-1"></i>Enroll in Course';
                }
            })
            .catch(error => {
                console.error('Enrollment error:', error);
                
                // Network or other error
                modalTitle.textContent = 'Connection Error';
                modalBody.innerHTML = `
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="fas fa-wifi text-danger" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="text-danger">Connection Failed</h5>
                        <p class="text-muted">Unable to process your enrollment request. Please check your internet connection and try again.</p>
                    </div>
                `;
                
                enrollmentModal.show();
                
                // Reset button state
                originalButton.disabled = false;
                originalButton.innerHTML = '<i class="fas fa-plus-circle me-1"></i>Enroll in Course';
            });
        });
    });    // Note: Auto-refresh disabled for testing purposes
    // document.getElementById('enrollmentModal').addEventListener('hidden.bs.modal', function() {
    //     if (modalTitle.textContent === 'Enrollment Successful!') {
    //         window.location.reload();
    //     }
    // });
});
</script>
<?php endif; ?>