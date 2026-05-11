<?php
/**
 * Page d'installation - Installation Automatique
 */

// Vérifier si la base de données existe déjà
$setup_complete = false;
$error = '';
$step = getParam('step', 1, 'int');

// Étape 1: Vérifier la configuration
if ($step === 1) {
    $checks = [
        'PHP Version' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'PDO Extension' => extension_loaded('pdo'),
        'MySQL Extension' => extension_loaded('pdo_mysql'),
        'OpenSSL' => extension_loaded('openssl'),
        'JSON' => extension_loaded('json'),
        'Writable Uploads' => is_writable(UPLOAD_DIR) || @mkdir(UPLOAD_DIR, 0755, true),
        'Writable Logs' => is_writable(ROOT_PATH . '/logs') || @mkdir(ROOT_PATH . '/logs', 0755, true)
    ];
    
    // Vérifier les permissions des répertoires
    foreach ([UPLOAD_STUDENTS, UPLOAD_TEACHERS, UPLOAD_DOCUMENTS, UPLOAD_REPORTS] as $dir) {
        @mkdir($dir, 0755, true);
    }
}

// Étape 2: Configuration de la base de données
if ($step === 2) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $db_host = sanitize($_POST['db_host'] ?? 'localhost');
        $db_user = sanitize($_POST['db_user'] ?? 'root');
        $db_pass = $_POST['db_pass'] ?? '';
        $db_name = sanitize($_POST['db_name'] ?? 'school_db');
        
        // Tester la connexion
        try {
            $test_db = new PDO(
                "mysql:host=$db_host",
                $db_user,
                $db_pass
            );
            
            // Créer la base de données
            $test_db->exec("CREATE DATABASE IF NOT EXISTS $db_name");
            
            // Sauvegarder la configuration
            $env_content = "APP_NAME=Gestion École\n";
            $env_content .= "APP_URL=http://localhost/school-management-system\n";
            $env_content .= "DB_HOST=$db_host\n";
            $env_content .= "DB_USER=$db_user\n";
            $env_content .= "DB_PASS=$db_pass\n";
            $env_content .= "DB_NAME=$db_name\n";
            $env_content .= "DB_PORT=3306\n";
            $env_content .= "DEBUG=false\n";
            
            file_put_contents(ROOT_PATH . '/.env', $env_content);
            
            // Rediriger vers l'étape 3
            redirect(APP_URL . '/install.php?step=3&db=' . urlencode($db_name));
        } catch (PDOException $e) {
            $error = 'Erreur de connexion à la base de données: ' . $e->getMessage();
        }
    }
}

// Étape 3: Créer les tables
if ($step === 3) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || true) {
        try {
            // Recharger la configuration
            require_once ROOT_PATH . '/config/database.php';
            
            $db_instance = Database::getInstance();
            $pdo = $db_instance->getConnection();
            
            // Lire le fichier SQL
            $sql_file = ROOT_PATH . '/sql/database.sql';
            if (file_exists($sql_file)) {
                $sql = file_get_contents($sql_file);
                
                // Exécuter les requêtes
                $pdo->exec($sql);
                
                $setup_complete = true;
                redirect(APP_URL . '/install.php?step=4');
            }
        } catch (Exception $e) {
            $error = 'Erreur lors de la création des tables: ' . $e->getMessage();
        }
    }
}

// Étape 4: Créer le compte administrateur
if ($step === 4) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $admin_email = sanitize($_POST['admin_email'] ?? '');
        $admin_password = $_POST['admin_password'] ?? '';
        $admin_password_confirm = $_POST['admin_password_confirm'] ?? '';
        $school_name = sanitize($_POST['school_name'] ?? 'Gestion École');
        
        if (empty($admin_email) || !validateEmail($admin_email)) {
            $error = 'Email invalide.';
        } elseif (empty($admin_password) || !validatePassword($admin_password)) {
            $error = 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.';
        } elseif ($admin_password !== $admin_password_confirm) {
            $error = 'Les mots de passe ne correspondent pas.';
        } else {
            try {
                require_once ROOT_PATH . '/config/database.php';
                $db_instance = Database::getInstance();
                
                // Créer l'administrateur
                $userData = [
                    'uuid' => generateUUID(),
                    'email' => $admin_email,
                    'password' => hashPassword($admin_password),
                    'first_name' => 'Admin',
                    'last_name' => 'System',
                    'role' => 'admin',
                    'status' => 'active',
                    'email_verified' => true,
                    'email_verified_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $db_instance->insert('users', $userData);
                
                // Mettre à jour le nom de l'école
                $school_data = [
                    'name' => $school_name,
                    'email' => $admin_email,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $db_instance->insert('schools', $school_data);
                
                // Créer un fichier de marquage pour indiquer que l'installation est complète
                file_put_contents(ROOT_PATH . '/INSTALLED', date('Y-m-d H:i:s'));
                
                redirect(APP_URL . '/install.php?step=5');
            } catch (Exception $e) {
                $error = 'Erreur lors de la création du compte administrateur: ' . $e->getMessage();
            }
        }
    }
}

// Vérifier si l'installation est complète
if (file_exists(ROOT_PATH . '/INSTALLED')) {
    $setup_complete = true;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Gestion d'École</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .install-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            max-width: 700px;
            width: 100%;
            margin: 20px;
        }
        .install-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .install-header h1 {
            margin: 0;
            font-size: 2rem;
        }
        .install-body {
            padding: 40px;
        }
        .step-indicator {
            display: flex;
            gap: 15px;
            margin-bottom: 40px;
        }
        .step {
            flex: 1;
            text-align: center;
        }
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: bold;
        }
        .step.active .step-number {
            background: #667eea;
            color: white;
        }
        .step.complete .step-number {
            background: #28a745;
            color: white;
        }
        .check-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .check-item.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
        }
        .check-item.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        .btn-next {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
        }
        .completion-icon {
            font-size: 3rem;
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <i class="fas fa-graduation-cap" style="font-size: 2.5rem; margin-bottom: 10px;"></i>
            <h1>Installation Gestion d'École</h1>
        </div>
        
        <div class="install-body">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo escape($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Indicateur d'étape -->
            <div class="step-indicator">
                <div class="step <?php echo $step >= 1 ? 'complete' : ''; ?>">
                    <div class="step-number">1</div>
                    <small>Vérification</small>
                </div>
                <div class="step <?php echo $step >= 2 ? ($step == 2 ? 'active' : 'complete') : ''; ?>">
                    <div class="step-number">2</div>
                    <small>Base de Données</small>
                </div>
                <div class="step <?php echo $step >= 3 ? ($step == 3 ? 'active' : 'complete') : ''; ?>">
                    <div class="step-number">3</div>
                    <small>Création Tables</small>
                </div>
                <div class="step <?php echo $step >= 4 ? ($step == 4 ? 'active' : 'complete') : ''; ?>">
                    <div class="step-number">4</div>
                    <small>Admin</small>
                </div>
            </div>
            
            <?php if ($step === 1): ?>
                <!-- Étape 1: Vérification -->
                <h3 class="mb-4">Vérification de l'Environnement</h3>
                
                <?php foreach ($checks as $name => $passed): ?>
                    <div class="check-item <?php echo $passed ? 'success' : 'error'; ?>">
                        <span><?php echo escape($name); ?></span>
                        <i class="fas <?php echo $passed ? 'fa-check-circle text-success' : 'fa-times-circle text-danger'; ?>"></i>
                    </div>
                <?php endforeach; ?>
                
                <?php if (array_reduce($checks, fn($carry, $item) => $carry && $item, true)): ?>
                    <form method="GET">
                        <input type="hidden" name="step" value="2">
                        <button type="submit" class="btn btn-next w-100 mt-4">Continuer <i class="fas fa-arrow-right"></i></button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning mt-4">
                        <strong>Problème détecté!</strong> Veuillez corriger les éléments en rouge avant de continuer.
                    </div>
                <?php endif; ?>
                
            <?php elseif ($step === 2): ?>
                <!-- Étape 2: Configuration Base de Données -->
                <h3 class="mb-4">Configuration Base de Données</h3>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Hôte Base de Données</label>
                        <input type="text" class="form-control" name="db_host" value="localhost" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Utilisateur</label>
                        <input type="text" class="form-control" name="db_user" value="root" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" name="db_pass">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nom Base de Données</label>
                        <input type="text" class="form-control" name="db_name" value="school_db" required>
                    </div>
                    
                    <button type="submit" class="btn btn-next w-100">Continuer <i class="fas fa-arrow-right"></i></button>
                </form>
                
            <?php elseif ($step === 3): ?>
                <!-- Étape 3: Création des Tables -->
                <h3 class="mb-4">Création des Tables</h3>
                
                <p>Les tables de la base de données sont en cours de création...</p>
                
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                </div>
                
                <form method="POST" style="display: none;">
                    <button type="submit"></button>
                </form>
                
                <script>
                    document.forms[0].submit();
                </script>
                
            <?php elseif ($step === 4): ?>
                <!-- Étape 4: Créer Admin -->
                <h3 class="mb-4">Créer le Compte Administrateur</h3>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Email Administrateur</label>
                        <input type="email" class="form-control" name="admin_email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" name="admin_password" required>
                        <small class="text-muted">Min 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Confirmer Mot de passe</label>
                        <input type="password" class="form-control" name="admin_password_confirm" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nom de l'École</label>
                        <input type="text" class="form-control" name="school_name" value="Gestion École" required>
                    </div>
                    
                    <button type="submit" class="btn btn-next w-100">Finaliser <i class="fas fa-check"></i></button>
                </form>
                
            <?php elseif ($step === 5 || $setup_complete): ?>
                <!-- Étape 5: Complété -->
                <div class="text-center">
                    <div class="completion-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="mt-3 mb-3">Installation Complète!</h3>
                    <p>L'installation est terminée avec succès. Vous pouvez maintenant vous connecter.</p>
                    
                    <div class="alert alert-info" role="alert">
                        <strong>Identifiants de Connexion:</strong><br>
                        Email: admin@school.local (ou celui que vous avez entré)<br>
                        Mot de passe: Celui que vous avez défini
                    </div>
                    
                    <a href="<?php echo APP_URL; ?>/login.php" class="btn btn-next">Se Connecter <i class="fas fa-sign-in-alt"></i></a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
