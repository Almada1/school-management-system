-- ========================================
-- Base de Données - Gestion d'École
-- ========================================

-- Créer la base de données
CREATE DATABASE IF NOT EXISTS school_db;
USE school_db;

-- ========================================
-- TABLE: schools (Établissements)
-- ========================================
CREATE TABLE IF NOT EXISTS schools (
  id INT PRIMARY KEY AUTO_INCREMENT,
  uuid VARCHAR(36) UNIQUE NOT NULL,
  name VARCHAR(255) NOT NULL,
  logo VARCHAR(255),
  email VARCHAR(255),
  phone VARCHAR(20),
  address VARCHAR(255),
  city VARCHAR(100),
  postal_code VARCHAR(10),
  country VARCHAR(100),
  academic_year VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLE: users (Utilisateurs)
-- ========================================
CREATE TABLE IF NOT EXISTS users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  uuid VARCHAR(36) UNIQUE NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  phone VARCHAR(20),
  role ENUM('admin', 'director', 'teacher', 'accountant', 'student', 'parent') NOT NULL,
  status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
  last_login DATETIME,
  email_verified BOOLEAN DEFAULT FALSE,
  email_verified_at DATETIME,
  remember_token VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_role (role),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLE: students (Élèves)
-- ========================================
CREATE TABLE IF NOT EXISTS students (
  id INT PRIMARY KEY AUTO_INCREMENT,
  uuid VARCHAR(36) UNIQUE NOT NULL,
  user_id INT NOT NULL,
  registration_number VARCHAR(50) UNIQUE NOT NULL,
  birth_date DATE,
  gender ENUM('M', 'F', 'Other') DEFAULT 'M',
  nationality VARCHAR(100),
  place_of_birth VARCHAR(100),
  student_number VARCHAR(50),
  blood_type VARCHAR(10),
  photo VARCHAR(255),
  status ENUM('active', 'inactive', 'graduated', 'transferred') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_registration (registration_number),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLE: parents (Parents)
-- ========================================
CREATE TABLE IF NOT EXISTS parents (
  id INT PRIMARY KEY AUTO_INCREMENT,
  uuid VARCHAR(36) UNIQUE NOT NULL,
  user_id INT NOT NULL,
  occupation VARCHAR(100),
  address VARCHAR(255),
  city VARCHAR(100),
  postal_code VARCHAR(10),
  photo VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLE: student_parent (Relation Élève-Parent)
-- ========================================
CREATE TABLE IF NOT EXISTS student_parent (
  id INT PRIMARY KEY AUTO_INCREMENT,
  student_id INT NOT NULL,
  parent_id INT NOT NULL,
  relationship VARCHAR(50),
  is_primary BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (parent_id) REFERENCES parents(id) ON DELETE CASCADE,
  UNIQUE KEY unique_relationship (student_id, parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLE: teachers (Professeurs)
-- ========================================
CREATE TABLE IF NOT EXISTS teachers (
  id INT PRIMARY KEY AUTO_INCREMENT,
  uuid VARCHAR(36) UNIQUE NOT NULL,
  user_id INT NOT NULL,
  employee_number VARCHAR(50) UNIQUE NOT NULL,
  birth_date DATE,
  gender ENUM('M', 'F', 'Other') DEFAULT 'M',
  qualification VARCHAR(255),
  specialization VARCHAR(100),
  salary DECIMAL(10, 2),
  hire_date DATE,
  contract_type ENUM('permanent', 'contract', 'part-time') DEFAULT 'permanent',
  photo VARCHAR(255),
  status ENUM('active', 'inactive', 'on_leave', 'retired') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_employee_number (employee_number),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLE: classes (Classes/Niveaux)
-- ========================================
CREATE TABLE IF NOT EXISTS classes (
  id INT PRIMARY KEY AUTO_INCREMENT,
  uuid VARCHAR(36) UNIQUE NOT NULL,
  name VARCHAR(100) NOT NULL,
  code VARCHAR(50) UNIQUE NOT NULL,
  level INT NOT NULL,
  description TEXT,
  academic_year VARCHAR(20),
  max_students INT DEFAULT 40,
  teacher_id INT,
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL,
  INDEX idx_code (code),
  INDEX idx_level (level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLE: class_student (Inscription Élève-Classe)
-- ========================================
CREATE TABLE IF NOT EXISTS class_student (
  id INT PRIMARY KEY AUTO_INCREMENT,
  student_id INT NOT NULL,
  class_id INT NOT NULL,
  academic_year VARCHAR(20),
  roll_number INT,
  enrollment_date DATE,
  status ENUM('active', 'dropped', 'transferred') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  UNIQUE KEY unique_enrollment (student_id, class_id, academic_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLE: subjects (Matières)
-- ========================================
CREATE TABLE IF NOT EXISTS subjects (
  id INT PRIMARY KEY AUTO_INCREMENT,
  uuid VARCHAR(36) UNIQUE NOT NULL,
  code VARCHAR(50) UNIQUE NOT NULL,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  coefficient INT DEFAULT 1,
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLE: class_subject (Attribution Matière-Classe)
-- ========================================
CREATE TABLE IF NOT EXISTS class_subject (
  id INT PRIMARY KEY AUTO_INCREMENT,
  class_id INT NOT NULL,
  subject_id INT NOT NULL,
  teacher_id INT,
  academic_year VARCHAR(20),
  hours_per_week INT DEFAULT 2,
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
  FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL,
  UNIQUE KEY unique_assignment (class_id, subject_id, academic_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLE: grades (Notes)
-- ========================================
CREATE TABLE IF NOT EXISTS grades (
  id INT PRIMARY KEY AUTO_INCREMENT,
  uuid VARCHAR(36) UNIQUE NOT NULL,
  student_id INT NOT NULL,
  subject_id INT NOT NULL,
  class_id INT NOT NULL,
  teacher_id INT,
  academic_year VARCHAR(20),
  term VARCHAR(50),
  score DECIMAL(5, 2),
  max_score DECIMAL(5, 2) DEFAULT 20,
  exam_type ENUM('exam', 'test', 'assignment', 'project') DEFAULT 'exam',
  weight INT DEFAULT 1,
  recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL,
  INDEX idx_student_subject (student_id, subject_id),
  INDEX idx_academic_year (academic_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLE: attendances (Présences)
-- ========================================
CREATE TABLE IF NOT EXISTS attendances (
  id INT PRIMARY KEY AUTO_INCREMENT,
  uuid VARCHAR(36) UNIQUE NOT NULL,
  student_id INT,
  teacher_id INT,
  class_id INT,
  date DATE NOT NULL,
  status ENUM('present', 'absent', 'late', 'excused') DEFAULT 'present',
  remarks TEXT,
  recorded_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_date (date),
  INDEX idx_student_date (student_id, date),
  INDEX idx_class_date (class_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLE: payments (Paiements)
-- ========================================
CREATE TABLE IF NOT EXISTS payments (
  id INT PRIMARY KEY AUTO_INCREMENT,
  uuid VARCHAR(36) UNIQUE NOT NULL,
  student_id INT NOT NULL,
  academic_year VARCHAR(20),
  amount DECIMAL(10, 2) NOT NULL,
  paid_amount DECIMAL(10, 2) DEFAULT 0,
  payment_date DATETIME,
  due_date DATE,
  payment_method ENUM('cash', 'check', 'transfer', 'online') DEFAULT 'cash',
  reference_number VARCHAR(100),
  status ENUM('pending', 'partial', 'paid', 'overdue') DEFAULT 'pending',
  notes TEXT,
  recorded_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_student (student_id),
  INDEX idx_status (status),
  INDEX idx_payment_date (payment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLE: announcements (Annonces)
-- ========================================
CREATE TABLE IF NOT EXISTS announcements (
  id INT PRIMARY KEY AUTO_INCREMENT,
  uuid VARCHAR(36) UNIQUE NOT NULL,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  created_by INT NOT NULL,
  target_role ENUM('all', 'admin', 'teacher', 'student', 'parent') DEFAULT 'all',
  is_published BOOLEAN DEFAULT FALSE,
  published_at DATETIME,
  expires_at DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_published (is_published),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLE: library_books (Livres Bibliothèque)
-- ========================================
CREATE TABLE IF NOT EXISTS library_books (
  id INT PRIMARY KEY AUTO_INCREMENT,
  uuid VARCHAR(36) UNIQUE NOT NULL,
  title VARCHAR(255) NOT NULL,
  author VARCHAR(255),
  isbn VARCHAR(20),
  publisher VARCHAR(255),
  category VARCHAR(100),
  quantity INT DEFAULT 1,
  available_quantity INT DEFAULT 1,
  acquisition_date DATE,
  price DECIMAL(10, 2),
  description TEXT,
  status ENUM('active', 'inactive', 'archived') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_title (title),
  INDEX idx_isbn (isbn)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLE: library_borrowings (Emprunts)
-- ========================================
CREATE TABLE IF NOT EXISTS library_borrowings (
  id INT PRIMARY KEY AUTO_INCREMENT,
  uuid VARCHAR(36) UNIQUE NOT NULL,
  book_id INT NOT NULL,
  user_id INT NOT NULL,
  borrowed_date DATETIME NOT NULL,
  due_date DATE NOT NULL,
  returned_date DATETIME,
  status ENUM('borrowed', 'returned', 'overdue') DEFAULT 'borrowed',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (book_id) REFERENCES library_books(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_status (user_id, status),
  INDEX idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLE: audit_logs (Audit)
-- ========================================
CREATE TABLE IF NOT EXISTS audit_logs (
  id INT PRIMARY KEY AUTO_INCREMENT,
  uuid VARCHAR(36) UNIQUE NOT NULL,
  user_id INT,
  action VARCHAR(100) NOT NULL,
  entity_type VARCHAR(50),
  entity_id INT,
  old_values JSON,
  new_values JSON,
  ip_address VARCHAR(45),
  user_agent TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_created_at (created_at),
  INDEX idx_entity (entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLE: notifications (Notifications)
-- ========================================
CREATE TABLE IF NOT EXISTS notifications (
  id INT PRIMARY KEY AUTO_INCREMENT,
  uuid VARCHAR(36) UNIQUE NOT NULL,
  user_id INT NOT NULL,
  type VARCHAR(50),
  title VARCHAR(255),
  message TEXT,
  data JSON,
  read_at DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_read_at (read_at),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLE: timetables (Emplois du Temps)
-- ========================================
CREATE TABLE IF NOT EXISTS timetables (
  id INT PRIMARY KEY AUTO_INCREMENT,
  uuid VARCHAR(36) UNIQUE NOT NULL,
  class_id INT NOT NULL,
  subject_id INT,
  teacher_id INT,
  day_of_week INT,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  room VARCHAR(50),
  academic_year VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL,
  FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL,
  INDEX idx_class_day (class_id, day_of_week)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Insérer des Données de Démonstration
-- ========================================

-- Écoles
INSERT INTO schools (uuid, name, email, phone, address, city, postal_code, country) VALUES
('550e8400-e29b-41d4-a716-446655440001', 'École Polytechnique', 'contact@poly.local', '+33612345678', '123 Rue de l\'École', 'Paris', '75001', 'France'),
('550e8400-e29b-41d4-a716-446655440002', 'Lycée Saint-Louis', 'contact@saintlouis.local', '+33612345679', '456 Avenue Royal', 'Lyon', '69001', 'France');

-- Utilisateurs (Admin, Prof, Élève)
INSERT INTO users (uuid, email, password, first_name, last_name, phone, role, status, email_verified, email_verified_at) VALUES
('550e8400-e29b-41d4-a716-446655441001', 'admin@school.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'System', '0612345678', 'admin', 'active', 1, NOW()),
('550e8400-e29b-41d4-a716-446655441002', 'director@school.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jean', 'Directeur', '0612345679', 'director', 'active', 1, NOW()),
('550e8400-e29b-41d4-a716-446655441003', 'prof@school.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Marie', 'Dupont', '0612345680', 'teacher', 'active', 1, NOW()),
('550e8400-e29b-41d4-a716-446655441004', 'accountant@school.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pierre', 'Comptable', '0612345681', 'accountant', 'active', 1, NOW()),
('550e8400-e29b-41d4-a716-446655441005', 'eleve@school.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Luc', 'Étudiant', '0612345682', 'student', 'active', 1, NOW()),
('550e8400-e29b-41d4-a716-446655441006', 'parent@school.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sophie', 'Parent', '0612345683', 'parent', 'active', 1, NOW());

-- Professeurs
INSERT INTO teachers (uuid, user_id, employee_number, hire_date, qualification, specialization, salary, contract_type) VALUES
('550e8400-e29b-41d4-a716-446655442001', 3, 'PROF001', NOW(), 'Master Mathématiques', 'Mathématiques', 2500.00, 'permanent');

-- Élèves
INSERT INTO students (uuid, user_id, registration_number, birth_date, gender, status) VALUES
('550e8400-e29b-41d4-a716-446655443001', 5, 'REG001', '2008-03-15', 'M', 'active');

-- Parents
INSERT INTO parents (uuid, user_id, occupation) VALUES
('550e8400-e29b-41d4-a716-446655444001', 6, 'Ingénieur');

-- Relation Élève-Parent
INSERT INTO student_parent (student_id, parent_id, relationship, is_primary) VALUES
(1, 1, 'Père', 1);

-- Classes
INSERT INTO classes (uuid, name, code, level, teacher_id, academic_year) VALUES
('550e8400-e29b-41d4-a716-446655445001', '1ère S', 'CLASS001', 11, 1, '2025-2026');

-- Inscription Élève-Classe
INSERT INTO class_student (student_id, class_id, academic_year, roll_number, enrollment_date) VALUES
(1, 1, '2025-2026', 1, NOW());

-- Matières
INSERT INTO subjects (uuid, code, name, coefficient) VALUES
('550e8400-e29b-41d4-a716-446655446001', 'MATH', 'Mathématiques', 4),
('550e8400-e29b-41d4-a716-446655446002', 'FRAN', 'Français', 3),
('550e8400-e29b-41d4-a716-446655446003', 'ENG', 'Anglais', 2);

-- Attribution Matière-Classe
INSERT INTO class_subject (class_id, subject_id, teacher_id, academic_year) VALUES
(1, 1, 1, '2025-2026'),
(1, 2, NULL, '2025-2026'),
(1, 3, NULL, '2025-2026');

-- Notes
INSERT INTO grades (uuid, student_id, subject_id, class_id, teacher_id, academic_year, term, score, max_score, exam_type, weight) VALUES
('550e8400-e29b-41d4-a716-446655447001', 1, 1, 1, 1, '2025-2026', 'Trimestre 1', 16, 20, 'exam', 2),
('550e8400-e29b-41d4-a716-446655447002', 1, 2, 1, NULL, '2025-2026', 'Trimestre 1', 14, 20, 'exam', 2);

-- Présences
INSERT INTO attendances (uuid, student_id, class_id, date, status, recorded_by) VALUES
('550e8400-e29b-41d4-a716-446655448001', 1, 1, CURDATE(), 'present', 1);

-- Paiements
INSERT INTO payments (uuid, student_id, academic_year, amount, paid_amount, status) VALUES
('550e8400-e29b-41d4-a716-446655449001', 1, '2025-2026', 5000.00, 5000.00, 'paid');

-- Annonces
INSERT INTO announcements (uuid, title, content, created_by, target_role, is_published, published_at) VALUES
('550e8400-e29b-41d4-a716-446655450001', 'Bienvenue', 'Bienvenue dans notre système de gestion d\'école', 1, 'all', 1, NOW());

-- Livres Bibliothèque
INSERT INTO library_books (uuid, title, author, isbn, publisher, category, quantity, available_quantity) VALUES
('550e8400-e29b-41d4-a716-446655451001', 'Les Misérables', 'Victor Hugo', '978-2-07-036566-6', 'Gallimard', 'Classique', 5, 3);

-- Emploi du Temps
INSERT INTO timetables (uuid, class_id, subject_id, teacher_id, day_of_week, start_time, end_time, room, academic_year) VALUES
('550e8400-e29b-41d4-a716-446655452001', 1, 1, 1, 1, '08:00:00', '09:30:00', 'A101', '2025-2026');

-- Indices pour performance
CREATE INDEX idx_user_role ON users(role);
CREATE INDEX idx_school_id ON classes(id);
CREATE INDEX idx_teacher_salary ON teachers(salary);
CREATE INDEX idx_payment_student ON payments(student_id);
