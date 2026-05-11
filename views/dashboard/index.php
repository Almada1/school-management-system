<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Gestion d'École</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <div class="container-fluid">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <i class="fas fa-graduation-cap"></i>
                <h4>EMS</h4>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="<?php echo APP_URL; ?>/dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                
                <?php if (hasPermission('students')): ?>
                <li><a href="<?php echo APP_URL; ?>/students"><i class="fas fa-users-slash"></i> Élèves</a></li>
                <?php endif; ?>
                
                <?php if (hasPermission('students')): ?>
                <li><a href="<?php echo APP_URL; ?>/teachers"><i class="fas fa-chalkboard-user"></i> Professeurs</a></li>
                <?php endif; ?>
                
                <?php if (hasPermission('students')): ?>
                <li><a href="<?php echo APP_URL; ?>/classes"><i class="fas fa-door-open"></i> Classes</a></li>
                <?php endif; ?>
                
                <?php if (hasPermission('students')): ?>
                <li><a href="<?php echo APP_URL; ?>/grades"><i class="fas fa-chart-bar"></i> Notes</a></li>
                <?php endif; ?>
                
                <?php if (hasPermission('students')): ?>
                <li><a href="<?php echo APP_URL; ?>/attendance"><i class="fas fa-clipboard-check"></i> Présences</a></li>
                <?php endif; ?>
                
                <?php if (hasPermission('students')): ?>
                <li><a href="<?php echo APP_URL; ?>/payments"><i class="fas fa-credit-card"></i> Paiements</a></li>
                <?php endif; ?>
                
                <?php if (hasPermission('students')): ?>
                <li><a href="<?php echo APP_URL; ?>/announcements"><i class="fas fa-bullhorn"></i> Annonces</a></li>
                <?php endif; ?>
                
                <?php if (hasPermission('students')): ?>
                <li><a href="<?php echo APP_URL; ?>/library"><i class="fas fa-book"></i> Bibliothèque</a></li>
                <?php endif; ?>
                
                <?php if (hasRole(ROLE_ADMIN)): ?>
                <li><a href="<?php echo APP_URL; ?>/settings"><i class="fas fa-cog"></i> Paramètres</a></li>
                <?php endif; ?>
                
                <li><hr class="my-2"></li>
                <li><a href="<?php echo APP_URL; ?>/profile.php"><i class="fas fa-user"></i> Mon Profil</a></li>
                <li><a href="<?php echo APP_URL; ?>/logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Topbar -->
            <div class="topbar">
                <div class="topbar-left">
                    <button class="btn btn-sm sidebar-toggle" style="display: none;">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>Tableau de Bord</h1>
                </div>
                
                <div class="topbar-right">
                    <div class="user-profile">
                        <span><?php echo escape(getCurrentUser()['first_name'] . ' ' . getCurrentUser()['last_name']); ?></span>
                        <div class="user-avatar">
                            <?php echo strtoupper(substr(getCurrentUser()['first_name'], 0, 1)); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Content -->
            <div class="content">
                <!-- Afficher les messages flash -->
                <?php if (displayFlashMessage()): ?>
                    <?php echo displayFlashMessage(); ?>
                <?php endif; ?>
                
                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-md-3 col-sm-6">
                        <div class="stat-card success">
                            <div class="stat-icon"><i class="fas fa-users"></i></div>
                            <div class="stat-value"><?php echo $stats['total_students']; ?></div>
                            <div class="stat-label">Élèves</div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6">
                        <div class="stat-card info">
                            <div class="stat-icon"><i class="fas fa-chalkboard-user"></i></div>
                            <div class="stat-value"><?php echo $stats['total_teachers']; ?></div>
                            <div class="stat-label">Professeurs</div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6">
                        <div class="stat-card warning">
                            <div class="stat-icon"><i class="fas fa-door-open"></i></div>
                            <div class="stat-value"><?php echo $stats['total_classes']; ?></div>
                            <div class="stat-label">Classes</div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6">
                        <div class="stat-card danger">
                            <div class="stat-icon"><i class="fas fa-book"></i></div>
                            <div class="stat-value"><?php echo $stats['total_subjects']; ?></div>
                            <div class="stat-label">Matières</div>
                        </div>
                    </div>
                </div>
                
                <!-- Présences Aujourd'hui -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Présences Aujourd'hui</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Présents:</strong> <span class="badge bg-success"><?php echo $stats['present_today']; ?></span></p>
                                <p><strong>Absents:</strong> <span class="badge bg-danger"><?php echo $stats['absent_today']; ?></span></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Paiements</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Revenus:</strong> <span class="text-success"><?php echo formatCurrency($stats['total_revenue']); ?></span></p>
                                <p><strong>En Attente:</strong> <span class="text-warning"><?php echo formatCurrency($stats['pending_payments']); ?></span></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Graphiques -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Paiements Mensuels</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="monthlyPaymentsChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Élèves par Classe</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="studentsByClassChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Annonces Récentes -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Annonces Récentes</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($announcements)): ?>
                            <div class="list-group">
                                <?php foreach ($announcements as $announcement): ?>
                                    <div class="list-group-item">
                                        <h6 class="mb-1"><?php echo escape($announcement['title']); ?></h6>
                                        <p class="mb-1 text-muted"><?php echo substr(escape($announcement['content']), 0, 100); ?>...</p>
                                        <small class="text-secondary"><?php echo formatDateTime($announcement['created_at']); ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Aucune annonce pour le moment.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo ASSETS_URL; ?>/js/app.js"></script>
    
    <script>
        // Graphique Paiements Mensuels
        const monthlyPaymentsCtx = document.getElementById('monthlyPaymentsChart');
        if (monthlyPaymentsCtx) {
            new Chart(monthlyPaymentsCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_map(fn($p) => 'Mois ' . $p['month'], $monthlyPayments)); ?>,
                    datasets: [{
                        label: 'Paiements',
                        data: <?php echo json_encode(array_column($monthlyPayments, 'total')); ?>,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: true }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }
        
        // Graphique Élèves par Classe
        const studentsByClassCtx = document.getElementById('studentsByClassChart');
        if (studentsByClassCtx) {
            new Chart(studentsByClassCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($studentsByClass, 'class_name')); ?>,
                    datasets: [{
                        label: 'Nombre d\'Élèves',
                        data: <?php echo json_encode(array_column($studentsByClass, 'student_count')); ?>,
                        backgroundColor: ['#667eea', '#764ba2', '#28a745', '#ffc107', '#17a2b8'],
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: true }
                    }
                }
            });
        }
        
        // Masquer/Afficher la sidebar sur mobile
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        if (window.innerWidth < 768) {
            sidebarToggle.style.display = 'block';
        }
        
        window.addEventListener('resize', function() {
            if (window.innerWidth < 768) {
                sidebarToggle.style.display = 'block';
            } else {
                sidebarToggle.style.display = 'none';
            }
        });
    </script>
</body>
</html>
