<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'MGOD LMS' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
          rel="stylesheet" 
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" 
          crossorigin="anonymous">
</head>
<body class="bg-light">

    <?php 
    $session = \Config\Services::session();
    $userRole = $session->get('role');
    $isLoggedIn = $session->get('isLoggedIn');
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
                            <a class="nav-link px-3 fw-bold" href="<?= base_url($userRole . '/dashboard') ?>">
                                🏠 Dashboard
                            </a>
                        </li>
                        
                        <?php if ($userRole === 'admin'): ?>
                            <!-- Admin Navigation -->
                            <li class="nav-item">
                                <a class="nav-link px-3 fw-bold" href="#">
                                    👥 Manage Users
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3 fw-bold" href="#">
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
                            </li>
                        <?php elseif ($userRole === 'teacher'): ?>
                            <!-- Teacher Navigation -->
                            <li class="nav-item">
                                <a class="nav-link px-3 fw-bold" href="#">
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
                            </li>
                        <?php elseif ($userRole === 'student'): ?>
                            <!-- Student Navigation -->
                            <li class="nav-item">
                                <a class="nav-link px-3 fw-bold" href="#">
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
                </ul>
                  <ul class="navbar-nav">
                    <?php if ($isLoggedIn): ?>
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
            </div>        <?php endif; ?>
    </div>

    <!-- Bootstrap JavaScript with SHA Integrity -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
            crossorigin="anonymous"></script>
</body>
</html>