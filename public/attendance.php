<?php
/**
 * Page de gestion des présences
 */

require_once '../config/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'director')) {
    header('Location: login.php');
    exit;
}

$attendance = new Attendance();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Présences - École</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #e74c3c;
            --success: #27ae60;
            --info: #3498db;
            --warning: #f39c12;
        }

        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sidebar {
            background: linear-gradient(135deg, var(--primary) 0%, #34495e 100%);
            min-height: 100vh;
            padding: 20px;
            color: white;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: rgba(255,255,255,0.2);
        }

        .main-content {
            padding: 30px;
        }

        .card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 10px;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary) 0%, #34495e 100%);
            color: white;
            border: none;
        }

        .attendance-btn {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 2px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .attendance-present {
            background-color: #d4edda;
            border-color: var(--success);
            color: var(--success);
        }

        .attendance-absent {
            background-color: #f8d7da;
            border-color: var(--secondary);
            color: var(--secondary);
        }

        .attendance-late {
            background-color: #fff3cd;
            border-color: var(--warning);
            color: var(--warning);
        }

        .attendance-excused {
            background-color: #cfe2ff;
            border-color: var(--info);
            color: var(--info);
        }

        .stats-card {
            text-align: center;
            padding: 20px;
            color: white;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 sidebar">
                <h5><i class="fas fa-graduation-cap"></i> Menu</h5>
                <a href="dashboard.php"><i class="fas fa-chart-line"></i> Tableau de bord</a>
                <a href="students.php"><i class="fas fa-users"></i> Élèves</a>
                <a href="grades.php"><i class="fas fa-star"></i> Notes</a>
                <a href="attendance.php" class="active"><i class="fas fa-clipboard-list"></i> Présences</a>
                <a href="payments.php"><i class="fas fa-money-bill"></i> Paiements</a>
                <hr>
                <a href="../api/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-clipboard-list"></i> Gestion des Présences</h2>
                    <button class="btn btn-primary" onclick="printAttendance()">
                        <i class="fas fa-print"></i> Imprimer
                    </button>
                </div>

                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stats-card bg-success">
                            <h5>Présents</h5>
                            <h3 id="totalPresent">0</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card bg-danger">
                            <h5>Absents</h5>
                            <h3 id="totalAbsent">0</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card bg-warning">
                            <h5>Retards</h5>
                            <h3 id="totalLate">0</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card bg-info">
                            <h5>Taux de Présence</h5>
                            <h3 id="attendanceRate">0%</h3>
                        </div>
                    </div>
                </div>

                <!-- Onglets -->
                <ul class="nav nav-tabs mb-4">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#dailyAttendance">
                            <i class="fas fa-calendar-check"></i> Appel Quotidien
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#monthlyReport">
                            <i class="fas fa-chart-bar"></i> Rapport Mensuel
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#studentReport">
                            <i class="fas fa-file-alt"></i> Rapport Élève
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Appel Quotidien -->
                    <div class="tab-pane fade show active" id="dailyAttendance">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Appel Quotidien</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 mb-4">
                                    <div class="col-md-3">
                                        <label>Classe</label>
                                        <select class="form-control" id="classSelect" onchange="loadDailyAttendance()">
                                            <option value="">-- Sélectionner une classe --</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Date</label>
                                        <input type="date" class="form-control" id="attendanceDate" onchange="loadDailyAttendance()">
                                    </div>
                                    <div class="col-md-3">
                                        <label>&nbsp;</label>
                                        <button class="btn btn-success w-100" onclick="markAllPresent()">
                                            <i class="fas fa-check"></i> Tout Présent
                                        </button>
                                    </div>
                                    <div class="col-md-3">
                                        <label>&nbsp;</label>
                                        <button class="btn btn-danger w-100" onclick="markAllAbsent()">
                                            <i class="fas fa-times"></i> Tout Absent
                                        </button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Matricule</th>
                                                <th>Élève</th>
                                                <th>Présent</th>
                                                <th>Absent</th>
                                                <th>Retard</th>
                                                <th>Excusé</th>
                                            </tr>
                                        </thead>
                                        <tbody id="dailyAttendanceTable">
                                        </tbody>
                                    </table>
                                </div>

                                <div class="text-end mt-3">
                                    <button class="btn btn-primary" onclick="saveDailyAttendance()">
                                        <i class="fas fa-save"></i> Enregistrer l'Appel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rapport Mensuel -->
                    <div class="tab-pane fade" id="monthlyReport">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Rapport Mensuel des Présences</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label>Classe</label>
                                        <select class="form-control" id="monthlyClass" onchange="loadMonthlyReport()">
                                            <option value="">-- Sélectionner une classe --</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Mois</label>
                                        <input type="month" class="form-control" id="monthSelect" onchange="loadMonthlyReport()">
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Élève</th>
                                                <th>Jours Présents</th>
                                                <th>Jours Absents</th>
                                                <th>Retards</th>
                                                <th>Jours Excusés</th>
                                                <th>Taux %</th>
                                            </tr>
                                        </thead>
                                        <tbody id="monthlyTable">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rapport Élève -->
                    <div class="tab-pane fade" id="studentReport">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Rapport Individuel Élève</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label>Élève</label>
                                        <select class="form-control" id="studentSelect" onchange="loadStudentAttendance()">
                                            <option value="">-- Sélectionner un élève --</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Année Scolaire</label>
                                        <input type="text" class="form-control" value="2025-2026" readonly>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Mois</th>
                                                <th>Présent</th>
                                                <th>Absent</th>
                                                <th>Retard</th>
                                                <th>Excusé</th>
                                                <th>Taux %</th>
                                            </tr>
                                        </thead>
                                        <tbody id="studentAttendanceTable">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.getElementById('attendanceDate').valueAsDate = new Date();
        document.getElementById('monthSelect').value = new Date().toISOString().slice(0, 7);

        // Charge les classes
        function loadClasses() {
            fetch('../api/classes/')
                .then(r => r.json())
                .then(data => {
                    const selects = ['classSelect', 'monthlyClass'];
                    selects.forEach(id => {
                        const select = document.getElementById(id);
                        select.innerHTML = '<option value="">-- Sélectionner une classe --</option>';
                        data.data?.forEach(c => {
                            select.innerHTML += `<option value="${c.id}">${c.name}</option>`;
                        });
                    });
                });
        }

        // Charge l'appel quotidien
        function loadDailyAttendance() {
            const classId = document.getElementById('classSelect').value;
            const date = document.getElementById('attendanceDate').value;

            if (!classId) return;

            fetch(`../api/attendance/?action=daily&class_id=${classId}&date=${date}`)
                .then(r => r.json())
                .then(data => {
                    const tbody = document.getElementById('dailyAttendanceTable');
                    tbody.innerHTML = '';

                    data.data?.forEach(s => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${s.matricule}</td>
                                <td>${s.first_name} ${s.last_name}</td>
                                <td><input type="radio" name="attendance_${s.id}" value="present" onchange="updateAttendanceStatus(${s.id}, 'present')"></td>
                                <td><input type="radio" name="attendance_${s.id}" value="absent" onchange="updateAttendanceStatus(${s.id}, 'absent')"></td>
                                <td><input type="radio" name="attendance_${s.id}" value="late" onchange="updateAttendanceStatus(${s.id}, 'late')"></td>
                                <td><input type="radio" name="attendance_${s.id}" value="excused" onchange="updateAttendanceStatus(${s.id}, 'excused')"></td>
                            </tr>
                        `;
                    });
                });
        }

        // Marque tous les élèves comme présents
        function markAllPresent() {
            document.querySelectorAll('input[type="radio"][value="present"]').forEach(input => {
                input.checked = true;
            });
        }

        // Marque tous les élèves comme absents
        function markAllAbsent() {
            document.querySelectorAll('input[type="radio"][value="absent"]').forEach(input => {
                input.checked = true;
            });
        }

        // Sauvegarde l'appel quotidien
        function saveDailyAttendance() {
            const classId = document.getElementById('classSelect').value;
            const date = document.getElementById('attendanceDate').value;
            const attendance = [];

            document.querySelectorAll('tbody tr').forEach((tr, idx) => {
                const checked = tr.querySelector('input[type="radio"]:checked');
                if (checked) {
                    attendance.push({
                        student_id: checked.name.replace('attendance_', ''),
                        status: checked.value
                    });
                }
            });

            fetch('../api/attendance/?action=save_daily', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ class_id: classId, date, attendance })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Appel enregistré avec succès');
                    loadDailyAttendance();
                }
            });
        }

        // Charge le rapport mensuel
        function loadMonthlyReport() {
            const classId = document.getElementById('monthlyClass').value;
            const month = document.getElementById('monthSelect').value;

            if (!classId) return;

            fetch(`../api/attendance/?action=monthly&class_id=${classId}&month=${month}`)
                .then(r => r.json())
                .then(data => {
                    const tbody = document.getElementById('monthlyTable');
                    tbody.innerHTML = '';

                    data.data?.forEach(s => {
                        const rate = ((s.present / (s.present + s.absent + s.late)) * 100).toFixed(1);
                        tbody.innerHTML += `
                            <tr>
                                <td>${s.first_name} ${s.last_name}</td>
                                <td><span class="badge bg-success">${s.present}</span></td>
                                <td><span class="badge bg-danger">${s.absent}</span></td>
                                <td><span class="badge bg-warning">${s.late}</span></td>
                                <td><span class="badge bg-info">${s.excused}</span></td>
                                <td><strong>${rate}%</strong></td>
                            </tr>
                        `;
                    });
                });
        }

        function printAttendance() {
            window.print();
        }

        loadClasses();
    </script>
</body>
</html>
