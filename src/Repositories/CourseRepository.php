<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Course;
use PDO;

class CourseRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findById(int $id): ?Course
    {
        $stmt = $this->db->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        return $data ? $this->hydrate($data) : null;
    }

    public function findAll(array $filters = []): array
    {
        $sql = "SELECT * FROM courses WHERE 1=1";
        $params = [];

        if (isset($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        if (isset($filters['instructor_id'])) {
            $sql .= " AND instructor_id = ?";
            $params[] = $filters['instructor_id'];
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $courses = [];
        while ($data = $stmt->fetch()) {
            $courses[] = $this->hydrate($data);
        }

        return $courses;
    }

    public function save(Course $course): bool
    {
        return $course->save();
    }

    public function delete(int $id): bool
    {
        $course = $this->findById($id);
        return $course ? $course->delete() : false;
    }

    public function countEnrollments(int $courseId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM enrollments 
            WHERE course_id = ? AND status = 'active'
        ");
        $stmt->execute([$courseId]);
        $result = $stmt->fetch();
        
        return $result['count'] ?? 0;
    }

    private function hydrate(array $data): Course
{
    $course = new Course($data);
    $course->setId((int)$data['id']);

    if (isset($data['created_at'])) {
        $course->setCreatedAt(new \DateTime($data['created_at']));
    }

    if (isset($data['updated_at'])) {
        $course->setUpdatedAt(new \DateTime($data['updated_at']));
    }

    return $course;
}
}