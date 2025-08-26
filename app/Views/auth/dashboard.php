<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard - MGOD LMS' ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="bg-white text-dark">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= base_url() ?>">MGOD LMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="<?= base_url('dashboard') ?>">Dashboard</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <?= esc($user['name']) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Profile</a></li>
                            <li><a class="dropdown-item" href="#">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= base_url('logout') ?>">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <div class="card border-dark">
                    <div class="card-header bg-dark text-white">
                        <h4 class="mb-0">Welcome to MGOD LMS Dashboard</h4>
                    </div>
                    <div class="card-body">
                        <h5>Hello, <?= esc($user['name']) ?>!</h5>
                        <p class="lead">Welcome to your learning management system dashboard.</p>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card border-secondary">
                                    <div class="card-header">
                                        <h6 class="mb-0">User Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Name:</strong> <?= esc($user['name']) ?></p>
                                        <p><strong>Email:</strong> <?= esc($user['email']) ?></p>
                                        <p><strong>Role:</strong> 
                                            <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : 'primary' ?>">
                                                <?= ucfirst(esc($user['role'])) ?>
                                            </span>
                                        </p>
                                        <p class="mb-0"><strong>User ID:</strong> <?= esc($user['userID']) ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-secondary">
                                    <div class="card-header">
                                        <h6 class="mb-0">Quick Actions</h6>
                                    </div>
                                    <div class="card-body">
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <a href="#" class="btn btn-outline-dark me-2 mb-2">Manage Users</a>
                                            <a href="#" class="btn btn-outline-dark me-2 mb-2">System Settings</a>
                                            <a href="#" class="btn btn-outline-dark me-2 mb-2">View Reports</a>
                                        <?php else: ?>
                                            <a href="#" class="btn btn-outline-dark me-2 mb-2">My Courses</a>
                                            <a href="#" class="btn btn-outline-dark me-2 mb-2">Browse Courses</a>
                                            <a href="#" class="btn btn-outline-dark me-2 mb-2">Assignments</a>
                                        <?php endif; ?>
                                        <a href="#" class="btn btn-outline-dark me-2 mb-2">View Profile</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>