<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Enrollment;
use PDO;

class EnrollmentRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findById(int $id): ?Enrollment
    {
        $stmt = $this->db->prepare("SELECT * FROM enrollments WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        return $data ? $this->hydrate($data) : null;
    }

    public function findByStudentId(int $studentId): array
    {
        $stmt = $this->db->prepare("
            SELECT e.*, c.title as course_title 
            FROM enrollments e 
            JOIN courses c ON e.course_id = c.id 
            WHERE e.student_id = ? 
            ORDER BY e.enrolled_at DESC
        ");
        $stmt->execute([$studentId]);

        $enrollments = [];
        while ($data = $stmt->fetch()) {
            $enrollments[] = $this->hydrate($data);
        }

        return $enrollments;
    }

    public function save(Enrollment $enrollment): bool
    {
        return $enrollment->save();
    }

    public function delete(int $id): bool
    {
        $enrollment = $this->findById($id);
        return $enrollment ? $enrollment->delete() : false;
    }

    private function hydrate(array $data): Enrollment
    {
        $enrollment = new Enrollment($data);
        $enrollment->setId((int)$data['id']);

        if (isset($data['created_at'])) {
            $enrollment->setCreatedAt(new \DateTime($data['created_at']));
        }

        if (isset($data['updated_at'])) {
            $enrollment->setUpdatedAt(new \DateTime($data['updated_at']));
        }

        return $enrollment;
    }
}