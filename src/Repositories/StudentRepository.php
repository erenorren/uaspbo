<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Student;
use PDO;

class StudentRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findById(int $id): ?Student
    {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        return $data ? $this->hydrate($data) : null;
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM students ORDER BY created_at DESC");
        
        $students = [];
        while ($data = $stmt->fetch()) {
            $students[] = $this->hydrate($data);
        }

        return $students;
    }

    public function save(Student $student): bool
    {
        return $student->save();
    }

    // TAMBAHKAN METHOD DELETE INI
    public function delete(int $id): bool
    {
        $student = $this->findById($id);
        return $student ? $student->delete() : false;
    }

    public function hasActiveEnrollment(int $studentId, int $courseId): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM enrollments 
            WHERE student_id = ? AND course_id = ? AND status = 'active'
        ");
        $stmt->execute([$studentId, $courseId]);
        $result = $stmt->fetch();
        
        return ($result['count'] ?? 0) > 0;
    }

    private function hydrate(array $data): Student
{
    $student = new Student($data);
    $student->setId((int)$data['id']);

    if (isset($data['created_at'])) {
        $student->setCreatedAt(new \DateTime($data['created_at']));
    }

    if (isset($data['updated_at'])) {
        $student->setUpdatedAt(new \DateTime($data['updated_at']));
    }

    return $student;
}
}