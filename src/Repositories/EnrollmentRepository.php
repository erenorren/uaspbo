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

        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    public function findByStudentId(int $studentId): array
    {
        $sql = "
            SELECT e.*, c.title as course_title, c.description as course_description
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            WHERE e.student_id = ?
            ORDER BY e.enrolled_at DESC
        ";

        $stmt = $this->db->prepare($sql);
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
        if (!$enrollment) {
            return false;
        }

        return $enrollment->delete();
    }

    public function findActiveByStudentAndCourse(int $studentId, int $courseId): ?Enrollment
    {
        $stmt = $this->db->prepare("
            SELECT * FROM enrollments 
            WHERE student_id = ? AND course_id = ? AND status = 'active'
        ");
        $stmt->execute([$studentId, $courseId]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    private function hydrate(array $data): Enrollment
    {
        $enrollment = new Enrollment([
            'student_id' => (int)$data['student_id'],
            'course_id' => (int)$data['course_id'],
            'status' => $data['status'],
            'enrolled_at' => $data['enrolled_at']
        ]);
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