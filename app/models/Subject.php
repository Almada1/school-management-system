<?php
/**
 * Classe Subject - Gestion des matières
 */

class Subject {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Crée une nouvelle matière
     */
    public function create($data) {
        $sql = 'INSERT INTO subjects (
            code, name, description, coefficient, is_mandatory, status, created_at
        ) VALUES (
            :code, :name, :description, :coefficient, :is_mandatory, :status, NOW()
        )';

        $params = [
            ':code' => $data['code'] ?? '',
            ':name' => $data['name'] ?? '',
            ':description' => $data['description'] ?? '',
            ':coefficient' => $data['coefficient'] ?? 1.00,
            ':is_mandatory' => $data['is_mandatory'] ?? 1,
            ':status' => $data['status'] ?? 'active',
        ];

        $this->db->query($sql);
        $result = $this->db->bind($params)->execute();

        return $result ? ['success' => true, 'id' => $this->db->lastInsertId()] : ['success' => false];
    }

    /**
     * Récupère une matière par ID
     */
    public function getById($id) {
        $this->db->query('SELECT * FROM subjects WHERE id = :id');
        return $this->db->bind([':id' => $id])->fetch();
    }

    /**
     * Liste toutes les matières
     */
    public function getAll($filters = []) {
        $sql = 'SELECT * FROM subjects WHERE 1=1';
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= ' AND status = :status';
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['mandatory'])) {
            $sql .= ' AND is_mandatory = :mandatory';
            $params[':mandatory'] = $filters['mandatory'];
        }

        $sql .= ' ORDER BY name';

        $this->db->query($sql);
        return $this->db->bind($params)->fetchAll();
    }

    /**
     * Met à jour une matière
     */
    public function update($id, $data) {
        $updates = [];
        $params = [':id' => $id];

        $fields = ['code', 'name', 'description', 'coefficient', 'is_mandatory', 'status'];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($updates)) {
            return ['success' => false];
        }

        $sql = 'UPDATE subjects SET ' . implode(', ', $updates) . ', updated_at = NOW() WHERE id = :id';
        $this->db->query($sql);
        $result = $this->db->bind($params)->execute();

        return $result ? ['success' => true] : ['success' => false];
    }

    /**
     * Supprime une matière
     */
    public function delete($id) {
        $this->db->query('DELETE FROM subjects WHERE id = :id');
        $result = $this->db->bind([':id' => $id])->execute();

        return $result ? ['success' => true] : ['success' => false];
    }

    /**
     * Récupère les matières d'une classe
     */
    public function getByClass($classId) {
        $sql = 'SELECT s.*, cs.teacher_id, cs.hours_per_week,
                    u.first_name, u.last_name
                FROM subjects s
                JOIN class_subjects cs ON s.id = cs.subject_id
                LEFT JOIN users u ON cs.teacher_id = u.id
                WHERE cs.class_id = :class_id
                ORDER BY s.name';

        $this->db->query($sql);
        return $this->db->bind([':class_id' => $classId])->fetchAll();
    }

    /**
     * Attribue une matière à une classe
     */
    public function assignToClass($classId, $subjectId, $teacherId = null, $hoursPerWeek = 2) {
        $sql = 'INSERT INTO class_subjects (
            class_id, subject_id, teacher_id, hours_per_week, academic_year, created_at
        ) VALUES (
            :class_id, :subject_id, :teacher_id, :hours_per_week, :academic_year, NOW()
        )';

        $params = [
            ':class_id' => $classId,
            ':subject_id' => $subjectId,
            ':teacher_id' => $teacherId,
            ':hours_per_week' => $hoursPerWeek,
            ':academic_year' => date('Y'),
        ];

        $this->db->query($sql);
        $result = $this->db->bind($params)->execute();

        return $result ? ['success' => true, 'id' => $this->db->lastInsertId()] : ['success' => false];
    }

    /**
     * Retire une matière d'une classe
     */
    public function removeFromClass($classSubjectId) {
        $this->db->query('DELETE FROM class_subjects WHERE id = :id');
        $result = $this->db->bind([':id' => $classSubjectId])->execute();

        return $result ? ['success' => true] : ['success' => false];
    }

    /**
     * Obtient les statistiques des matières
     */
    public function getStatistics() {
        $this->db->query('SELECT COUNT(*) as total FROM subjects WHERE status = "active"');
        $stats['total_active'] = $this->db->fetch()['total'] ?? 0;

        $this->db->query('SELECT COUNT(*) as total FROM subjects WHERE is_mandatory = 1');
        $stats['mandatory'] = $this->db->fetch()['total'] ?? 0;

        return $stats;
    }
}

?>
