<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="csrf-hash" content="<?= csrf_hash() ?>">
    <title><?= $title ?? 'MGOD LMS' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" 
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" 
          crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" 
          integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" 
          crossorigin="anonymous">
    <style>
        .navbar-nav .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            color: #fff !important;
        }
        .navbar-nav .nav-link.active:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body class="bg-light">    <?php 
    $session = \Config\Services::session();
    $userRole = $session->get('role');
    $isLoggedIn = $session->get('isLoggedIn');
    
    $request = \Config\Services::request();
    $currentAction = $request->getGet('action');
    $currentUri = uri_string();
    ?>
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">            
            <a class="navbar-brand fw-bold fs-4" href="<?= $isLoggedIn ? base_url($userRole . '/dashboard') : base_url() ?>">
                📚 MGOD LMS
                <?php if ($isLoggedIn): ?>
                    <span class="badge bg-light text-primary ms-2 rounded-pill">
                        <?= ucfirst($userRole) ?>
                    </span>
                <?php endif; ?>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">                  
                <ul class="navbar-nav me-auto">
                    <?php if ($isLoggedIn): ?>                       
                         <li class="nav-item">
                            <?php
                            // Create role-based dashboard URL
                            $dashboardUrl = base_url($userRole . '/dashboard');
                            $isDashboardActive = (strpos($currentUri, $userRole . '/dashboard') !== false) || ($currentUri === 'dashboard');
                            ?>
                            <a class="nav-link px-3 fw-bold <?= $isDashboardActive ? 'active' : '' ?>" href="<?= $dashboardUrl ?>">
                                🏠 Dashboard
                            </a>
                        </li>
                        
                        <?php if ($userRole === 'admin'): ?>
                            <!-- Admin Navigation -->                              
                             <li class="nav-item">
                                <a class="nav-link px-3 fw-bold <?= (strpos($currentUri, 'admin/manage_users') !== false) ? 'active' : '' ?>" href="<?= base_url('admin/manage_users') ?>">
                                    👥 Manage Users
                                </a>
                            </li>                            
                            <li class="nav-item">
                                <a class="nav-link px-3 fw-bold <?= (strpos($currentUri, 'admin/manage_courses') !== false) ? 'active' : '' ?>" href="<?= base_url('admin/manage_courses') ?>">
                                    📚 Manage Courses
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3 fw-bold" href="#">
                                    📊 Reports
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3 fw-bold" href="#">
                                    ⚙️ Settings
                                </a>
                            </li>                        <?php elseif ($userRole === 'teacher'): ?>
                            <!-- Teacher Navigation -->
                            <li class="nav-item">
                                <a class="nav-link px-3 fw-bold <?= (strpos($currentUri, 'teacher/courses') !== false) ? 'active' : '' ?>" href="<?= base_url('teacher/courses') ?>">
                                    📚 My Courses
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3 fw-bold" href="#">
                                    📝 Assignments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3 fw-bold" href="#">
                                    📊 Gradebook
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3 fw-bold" href="#">
                                    👥 Students
                                </a>
                            </li>                        <?php elseif ($userRole === 'student'): ?>
                            <!-- Student Navigation -->
                            <li class="nav-item">
                                <a class="nav-link px-3 fw-bold <?= (strpos($currentUri, 'student/courses') !== false) ? 'active' : '' ?>" href="<?= base_url('student/courses') ?>">
                                    📚 My Courses
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3 fw-bold" href="#">
                                    📝 Assignments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3 fw-bold" href="#">
                                    📊 Grades
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3 fw-bold" href="#">
                                    📅 Schedule
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Public Navigation -->
                        <li class="nav-item">
                            <a class="nav-link px-3 fw-bold" href="<?= base_url() ?>">🏠 Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3 fw-bold" href="<?= base_url('about') ?>">ℹ️ About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3 fw-bold" href="<?= base_url('contact') ?>">📞 Contact</a>
                        </li>
                    <?php endif; ?>
                </ul>                <ul class="navbar-nav">
                    <?php if ($isLoggedIn): ?>
                        <!-- Notifications Dropdown with jQuery/Bootstrap Integration -->
                        <li class="nav-item dropdown me-3">
                            <a class="nav-link position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell fs-5"></i>
                                <!-- Badge to show unread count - initially hidden -->
                                <span id="notificationBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none;">
                                    0
                                    <span class="visually-hidden">unread notifications</span>
                                </span>
                            </a>
                            <!-- Dropdown menu to list notifications - initially empty -->
                            <ul class="dropdown-menu dropdown-menu-end shadow" id="notificationsList" aria-labelledby="notificationsDropdown" style="min-width: 350px; max-height: 450px; overflow-y: auto;">
                                <!-- Notifications will be loaded here dynamically via jQuery -->
                                <li class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mb-0 small text-muted mt-2">Loading notifications...</p>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- Logged In User Menu -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center fw-bold" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="badge bg-light text-primary me-2 rounded-circle" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                    <?= strtoupper(substr($session->get('name'), 0, 1)) ?>
                                </span>
                                <?= esc($session->get('name')) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li>
                                    <h6 class="dropdown-header text-center">
                                        <span class="badge bg-primary rounded-pill">
                                            <?= ucfirst($userRole) ?>
                                        </span>
                                    </h6>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item fw-semibold" href="#">👤 Profile</a></li>
                                <li><a class="dropdown-item fw-semibold" href="#">⚙️ Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger fw-bold" href="<?= base_url('logout') ?>">🚪 Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Public User Menu -->
                        <li class="nav-item">
                            <a class="nav-link fw-bold px-3" href="<?= base_url('login') ?>">
                                🔑 Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-outline-light rounded-pill ms-2 px-3 fw-bold" href="<?= base_url('register') ?>">
                                📝 Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main content container starts here -->
    <div class="container-fluid p-0">
        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="container mt-4">
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= session()->getFlashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="container mt-4">
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="container mt-4">
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>            <?php endif; ?>
    </div>    
    <!-- Include jQuery before Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" 
            integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" 
            crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
            crossorigin="anonymous"></script>
      <?php if ($isLoggedIn): ?>
    <!-- Notification System jQuery Implementation - Step 6 Complete -->
    <script>
        $(document).ready(function() {
            // Step 6.1: Load notifications on page load
            loadNotifications();
            
            // Reload notifications when dropdown is opened
            $('#notificationsDropdown').on('click', function() {
                loadNotifications();
            });
            
            // Step 6.2: Auto-refresh notifications every 60 seconds (real-time updates simulation)
            setInterval(loadNotifications, 60000); // 60000ms = 60 seconds
        });
        
        /**
         * Load notifications using jQuery AJAX
         * Uses $.get() to call /notifications endpoint
         */
        function loadNotifications() {
            $.get('<?= base_url('notifications') ?>', function(data) {
                if (data.success) {
                    // Update badge count
                    updateNotificationBadge(data.unread_count);
                    
                    // Populate dropdown menu with notifications
                    populateNotifications(data.notifications);
                }
            }).fail(function(xhr, status, error) {
                console.error('Failed to load notifications:', error);
                $('#notificationsList').html(`
                    <li class="text-center py-3">
                        <i class="fas fa-exclamation-triangle text-danger fs-3"></i>
                        <p class="mb-0 small text-danger mt-2">Failed to load notifications</p>
                    </li>
                `);
            });
        }
        
        /**
         * Update notification badge count
         * If count is 0, hide badge; otherwise show it
         */
        function updateNotificationBadge(count) {
            const badge = $('#notificationBadge');
            
            if (count > 0) {
                // Show badge with count
                badge.text(count > 99 ? '99+' : count);
                badge.show();
            } else {
                // Hide badge when count is 0
                badge.hide();
            }
        }
        
        /**
         * Populate dropdown menu with notification list
         * Uses Bootstrap alert classes for styling
         */
        function populateNotifications(notifications) {
            const list = $('#notificationsList');
            list.empty();
            
            if (notifications.length === 0) {
                // No notifications - show empty state
                list.html(`
                    <li class="px-3 py-4 text-center">
                        <i class="fas fa-bell-slash text-muted fs-2 mb-2"></i>
                        <p class="mb-0 text-muted">No notifications</p>
                        <small class="text-muted">You're all caught up!</small>
                    </li>
                `);
                return;
            }
            
            // Add each notification to the list
            notifications.forEach(function(notification, index) {
                const alertClass = notification.is_unread ? 'alert-info' : 'alert-light';
                const boldClass = notification.is_unread ? 'fw-bold' : '';
                const iconColor = notification.is_unread ? 'text-primary' : 'text-muted';
                
                const notificationHtml = `
                    <li id="notification-${notification.id}" class="border-bottom">
                        <div class="alert ${alertClass} mb-0 rounded-0 border-0" role="alert">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-info-circle ${iconColor} me-2 mt-1"></i>
                                <div class="flex-grow-1">
                                    <p class="mb-1 small ${boldClass}">
                                        ${escapeHtml(notification.message)}
                                    </p>
                                    <small class="text-muted d-block">
                                        <i class="fas fa-clock me-1"></i>
                                        ${notification.formatted_date}
                                    </small>
                                    ${notification.is_unread ? `
                                        <button class="btn btn-sm btn-primary mt-2 mark-read-btn" 
                                                data-id="${notification.id}"
                                                onclick="markAsRead(${notification.id})">
                                            <i class="fas fa-check me-1"></i>
                                            Mark as Read
                                        </button>
                                    ` : `
                                        <span class="badge bg-success mt-2">
                                            <i class="fas fa-check-circle"></i> Read
                                        </span>
                                    `}
                                </div>
                            </div>
                        </div>
                    </li>
                `;
                
                list.append(notificationHtml);
            });
        }
        
        /**
         * Mark notification as read
         * Uses $.post() to call /notifications/mark_read/{id} endpoint
         * Upon success, removes notification from list and updates badge
         */
        function markAsRead(notificationId) {
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            const csrfHash = $('meta[name="csrf-hash"]').attr('content');
            
            // Disable the button to prevent double-clicks
            $(`button[data-id="${notificationId}"]`).prop('disabled', true).html(`
                <span class="spinner-border spinner-border-sm me-1"></span>
                Marking...
            `);
            
            $.post(
                '<?= base_url('notifications/mark_read/') ?>' + notificationId,
                JSON.stringify({ [csrfToken]: csrfHash }),
                function(data) {
                    if (data.success) {
                        // Remove notification from list with fade effect
                        $(`#notification-${notificationId}`).fadeOut(300, function() {
                            $(this).remove();
                            
                            // Update badge count
                            updateNotificationBadge(data.unread_count);
                            
                            // If no notifications left, show empty state
                            if ($('#notificationsList li').length === 0) {
                                $('#notificationsList').html(`
                                    <li class="px-3 py-4 text-center">
                                        <i class="fas fa-bell-slash text-muted fs-2 mb-2"></i>
                                        <p class="mb-0 text-muted">No notifications</p>
                                        <small class="text-muted">You're all caught up!</small>
                                    </li>
                                `);
                            }
                        });
                        
                        // Show success toast (optional)
                        showToast('Success', 'Notification marked as read', 'success');
                    } else {
                        // Show error message
                        alert('Failed to mark notification as read: ' + data.message);
                        // Re-enable button
                        $(`button[data-id="${notificationId}"]`).prop('disabled', false).html(`
                            <i class="fas fa-check me-1"></i> Mark as Read
                        `);
                    }
                },
                'json'
            ).fail(function(xhr, status, error) {
                console.error('Error marking notification as read:', error);
                alert('An error occurred while marking the notification as read');
                // Re-enable button
                $(`button[data-id="${notificationId}"]`).prop('disabled', false).html(`
                    <i class="fas fa-check me-1"></i> Mark as Read
                `);
            });
        }
        
        /**
         * Helper function to escape HTML and prevent XSS
         */
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
        
        /**
         * Optional: Show toast notification
         */
        function showToast(title, message, type = 'info') {
            // Simple console log for now - can be enhanced with Bootstrap toasts
            console.log(`[${type.toUpperCase()}] ${title}: ${message}`);
        }
    </script>
    <?php endif; ?>
</body>
</html>