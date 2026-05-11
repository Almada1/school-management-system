# 📚 Système de Gestion d'École - Guide de Démarrage Rapide

## ⚡ Installation en 5 minutes

### 1. **Prérequis**
- PHP 8.0+
- MySQL 5.7+
- Apache avec mod_rewrite
- Composer (optionnel)

### 2. **Téléchargement**
```bash
# Clone le repository
git clone https://github.com/Almada1/school-management-system.git
cd school-management-system
```

### 3. **Configuration XAMPP**
```bash
# Copier dans htdocs
cp -r . C:/xampp/htdocs/school-management-system

# Ou pour Linux
cp -r . /var/www/html/school-management-system
```

### 4. **Accès à l'installation**
1. Démarrer Apache et MySQL dans XAMPP
2. Ouvrir le navigateur : `http://localhost/school-management-system/install`
3. Remplir les paramètres :
   - **Hôte** : localhost
   - **Utilisateur** : root
   - **Mot de passe** : (laisser vide pour XAMPP)
   - **Base de données** : school_db

### 5. **Connexion**
- **URL** : http://localhost/school-management-system
- **Email** : admin@school.local
- **Mot de passe** : Admin@123

---

## 🔑 Comptes de Démonstration

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| 👨‍💼 Admin | admin@school.local | Admin@123 |
| 👔 Directeur | directeur@school.local | Admin@123 |
| 👨‍🏫 Professeur | prof@school.local | Admin@123 |
| 💰 Comptable | comptable@school.local | Admin@123 |
| 👨‍🎓 Élève | eleve@school.local | Admin@123 |
| 👨‍👩‍👧 Parent | parent@school.local | Admin@123 |

---

## 📁 Structure du Projet

```
school-management-system/
├── config/              # Configuration
├── controllers/         # Logique métier
├── models/              # Modèles de données
├── views/               # Templates HTML
├── public/              # Assets (CSS, JS, images)
├── sql/                 # Scripts SQL
├── includes/            # Fonctions utilitaires
├── install/             # Installation
├── index.php            # Point d'entrée
└── .env.example         # Configuration exemple
```

---

## 🎯 Fonctionnalités Principales

### 📊 Tableau de Bord
- Statistiques en temps réel
- Graphiques interactifs
- Vue personnalisée par rôle

### 👥 Gestion des Utilisateurs
- **Administrateur** : Accès complet
- **Directeur** : Gestion pédagogique
- **Professeur** : Notes et présences
- **Comptable** : Gestion financière
- **Élève** : Consulter grades et paiements
- **Parent** : Suivi enfant

### 📚 Gestion Pédagogique
- ✅ Élèves (CRUD, import/export)
- ✅ Professeurs et salaires
- ✅ Classes et matières
- ✅ Notes et bulletins
- ✅ Présences
- ✅ Emploi du temps

### 💳 Gestion Financière
- ✅ Suivi des frais de scolarité
- ✅ Paiements partiels
- ✅ Reçus PDF
- ✅ Rapports financiers

### 📚 Bibliothèque
- ✅ Gestion des livres
- ✅ Emprunts et retours
- ✅ Historique

### 📢 Communication
- ✅ Annonces scolaires
- ✅ Notifications
- ✅ Email et SMS

---

## 🔒 Sécurité

✅ Authentification robuste (bcrypt)
✅ Protection XSS et CSRF
✅ Injection SQL prévenue (PDO)
✅ Sessions sécurisées
✅ Contrôle d'accès par rôle
✅ Audit des actions
✅ Chiffrement sensible

---

## 🛠️ Dépannage Courant

### Erreur "Connexion à la BD impossible"
```php
Vérifier les identifiants dans .env
Créer la BD : CREATE DATABASE school_db;
Relancer le navigateur
```

### Erreur "Permission refusée"
```bash
# Linux/Mac
chmod 755 public/uploads
chmod 755 logs
chmod 755 backups

# Windows
Clic droit → Propriétés → Sécurité → Modifier permissions
```

### Erreur d'upload de fichiers
```bash
# Créer les répertoires
mkdir -p public/uploads/students
mkdir -p public/uploads/documents
chmod 755 public/uploads/*
```

### Page blanche
```php
Vérifier les logs : logs/error.log
Activer debug dans .env : APP_DEBUG=true
Vérifier la version PHP : php -v
```

---

## 📚 Documentation Complète

Pour plus d'informations :
- `/docs/API.md` - Documentation API
- `/docs/INSTALLATION.md` - Installation détaillée
- `/docs/GUIDE.md` - Guide complet
- `/docs/DEVELOPPEMENT.md` - Pour développeurs

---

## 🚀 Déploiement

### Sur cPanel
1. Uploader les fichiers via File Manager
2. Créer une BD dans PHPMyAdmin
3. Modifier .env avec les identifiants cPanel
4. Visiter le dossier

### Sur Heroku
```bash
git push heroku main
heroku run php install.php
```

### Sur DigitalOcean
```bash
# SSH sur le serveur
ssh root@votre_ip

# Installer dépendances
apt-get update && apt-get install php php-mysql apache2

# Cloner le projet
cd /var/www/html
git clone ...
```

---

## 📞 Support

**Issues** : GitHub Issues
**Email** : support@school.local
**Documentation** : https://docs.school.local

---

## 📄 Licence

MIT License - Libre d'utilisation

---

**Version** : 1.0.0
**Dernière mise à jour** : 2026-05-11
**Status** : Production Ready ✅
