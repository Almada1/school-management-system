<?php
/**
 * Page de gestion des paiements
 */

require_once '../config/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'accountant' && $_SESSION['role'] !== 'director')) {
    header('Location: login.php');
    exit;
}

$payment = new Payment();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Paiements - École</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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

        .stats-card {
            text-align: center;
            padding: 20px;
            color: white;
            border-radius: 10px;
        }

        .badge-success { background-color: var(--success); }
        .badge-danger { background-color: var(--secondary); }
        .badge-info { background-color: var(--info); }
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
                <a href="attendance.php"><i class="fas fa-clipboard-list"></i> Présences</a>
                <a href="payments.php" class="active"><i class="fas fa-money-bill"></i> Paiements</a>
                <hr>
                <a href="../api/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-money-bill"></i> Gestion des Paiements</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                        <i class="fas fa-plus"></i> Nouveau Paiement
                    </button>
                </div>

                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stats-card bg-success">
                            <h5>Total Encaissé</h5>
                            <h3 id="totalCollected">0 €</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card bg-danger">
                            <h5>Solde Impayé</h5>
                            <h3 id="totalOutstanding">0 €</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card bg-info">
                            <h5>Paiements du Mois</h5>
                            <h3 id="monthlyPayments">0 €</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card bg-warning">
                            <h5>Taux de Recouvrement</h5>
                            <h3 id="collectionRate">0%</h3>
                        </div>
                    </div>
                </div>

                <!-- Filtres -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label>Période</label>
                                <div class="input-group">
                                    <input type="date" class="form-control" id="startDate">
                                    <input type="date" class="form-control" id="endDate" onchange="loadPayments()">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>Type de Paiement</label>
                                <select class="form-control" id="paymentType" onchange="loadPayments()">
                                    <option value="">-- Tous --</option>
                                    <option value="tuition">Scolarité</option>
                                    <option value="uniform">Uniforme</option>
                                    <option value="books">Livres</option>
                                    <option value="activities">Activités</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Statut</label>
                                <select class="form-control" id="status" onchange="loadPayments()">
                                    <option value="">-- Tous --</option>
                                    <option value="completed">Complétés</option>
                                    <option value="pending">En attente</option>
                                    <option value="cancelled">Annulés</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tableau des paiements -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Historique des Paiements</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Référence</th>
                                        <th>Élève</th>
                                        <th>Type</th>
                                        <th>Montant</th>
                                        <th>Méthode</th>
                                        <th>Date</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="paymentsTable">
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Chargement des paiements...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Élèves avec solde impayé -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Élèves avec Solde Impayé</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Matricule</th>
                                        <th>Élève</th>
                                        <th>Total Frais</th>
                                        <th>Paiements</th>
                                        <th>Solde</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="outstandingTable">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ajouter Paiement -->
    <div class="modal fade" id="addPaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Nouveau Paiement</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addPaymentForm">
                        <div class="mb-3">
                            <label>Élève *</label>
                            <select class="form-control" id="studentId" required>
                                <option value="">-- Sélectionner un élève --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Type de Paiement *</label>
                            <select class="form-control" id="paymentTypeModal" required>
                                <option value="">-- Sélectionner --</option>
                                <option value="tuition">Scolarité</option>
                                <option value="uniform">Uniforme</option>
                                <option value="books">Livres</option>
                                <option value="activities">Activités</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Montant *</label>
                            <input type="number" class="form-control" id="amount" step="0.01" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label>Méthode de Paiement *</label>
                            <select class="form-control" id="paymentMethod" required>
                                <option value="cash">Espèces</option>
                                <option value="check">Chèque</option>
                                <option value="transfer">Virement</option>
                                <option value="card">Carte Bancaire</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Date de Paiement</label>
                            <input type="date" class="form-control" id="paymentDate">
                        </div>
                        <div class="mb-3">
                            <label>Notes</label>
                            <textarea class="form-control" id="paymentNotes" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="submitPayment()">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.getElementById('startDate').valueAsDate = new Date(new Date().setDate(new Date().getDate() - 30));
        document.getElementById('endDate').valueAsDate = new Date();

        function loadPayments() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const paymentType = document.getElementById('paymentType').value;

            fetch(`../api/payments/?action=by_period&start_date=${startDate}&end_date=${endDate}&payment_type=${paymentType}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) displayPayments(data.data);
                });
        }

        function displayPayments(payments) {
            const tbody = document.getElementById('paymentsTable');
            tbody.innerHTML = '';

            let total = 0;
            payments.forEach(p => {
                total += parseFloat(p.amount);
                const statusBadge = `<span class="badge badge-${p.status === 'completed' ? 'success' : p.status === 'pending' ? 'warning' : 'danger'}">${p.status}</span>`;
                tbody.innerHTML += `
                    <tr>
                        <td>${p.reference_number}</td>
                        <td>${p.first_name} ${p.last_name}</td>
                        <td>${p.payment_type}</td>
                        <td>${parseFloat(p.amount).toFixed(2)} €</td>
                        <td>${p.payment_method}</td>
                        <td>${new Date(p.payment_date).toLocaleDateString('fr-FR')}</td>
                        <td>${statusBadge}</td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="printReceipt(${p.id})"><i class="fas fa-receipt"></i></button>
                            <button class="btn btn-sm btn-danger" onclick="deletePayment(${p.id})"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
            });

            document.getElementById('monthlyPayments').textContent = total.toFixed(2) + ' €';
        }

        function loadOutstandingBalance() {
            fetch('../api/payments/?action=outstanding&min_balance=1')
                .then(r => r.json())
                .then(data => {
                    if (data.success) displayOutstanding(data.data);
                });
        }

        function displayOutstanding(students) {
            const tbody = document.getElementById('outstandingTable');
            tbody.innerHTML = '';

            let totalOutstanding = 0;
            students.forEach(s => {
                totalOutstanding += parseFloat(s.balance);
                tbody.innerHTML += `
                    <tr>
                        <td>${s.matricule}</td>
                        <td>${s.first_name} ${s.last_name}</td>
                        <td>${parseFloat(s.total_fees).toFixed(2)} €</td>
                        <td>${parseFloat(s.total_paid).toFixed(2)} €</td>
                        <td><span class="badge badge-danger">${parseFloat(s.balance).toFixed(2)} €</span></td>
                        <td><button class="btn btn-sm btn-primary" onclick="recordPayment(${s.id})"><i class="fas fa-plus"></i></button></td>
                    </tr>
                `;
            });

            document.getElementById('totalOutstanding').textContent = totalOutstanding.toFixed(2) + ' €';
        }

        function submitPayment() {
            const data = {
                student_id: document.getElementById('studentId').value,
                payment_type: document.getElementById('paymentTypeModal').value,
                amount: document.getElementById('amount').value,
                payment_method: document.getElementById('paymentMethod').value,
                payment_date: document.getElementById('paymentDate').value,
                notes: document.getElementById('paymentNotes').value
            };

            fetch('../api/payments/?action=add', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Paiement enregistré avec succès');
                    bootstrap.Modal.getInstance(document.getElementById('addPaymentModal')).hide();
                    loadPayments();
                    loadOutstandingBalance();
                } else {
                    alert('Erreur: ' + data.error);
                }
            });
        }

        function printReceipt(paymentId) {
            fetch(`../api/payments/?action=receipt&payment_id=${paymentId}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const win = window.open('', '', 'height=500,width=800');
                        win.document.write(data.html);
                        win.document.close();
                        win.print();
                    }
                });
        }

        // Chargement initial
        loadPayments();
        loadOutstandingBalance();
    </script>
</body>
</html>
