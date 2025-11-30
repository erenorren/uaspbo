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
        $sql = "SELECT u.*, s.student_number 
                FROM users u 
                JOIN students s ON u.id = s.id 
                WHERE u.id = ? AND u.role = 'student'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    public function findAll(array $filters = []): array
    {
        $sql = "SELECT u.*, s.student_number 
                FROM users u 
                JOIN students s ON u.id = s.id 
                WHERE u.role = 'student'";
        $params = [];

        if (isset($filters['student_number'])) {
            $sql .= " AND s.student_number LIKE ?";
            $params[] = "%{$filters['student_number']}%";
        }

        $sql .= " ORDER BY u.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

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

    public function delete(int $id): bool
    {
        $student = $this->findById($id);
        if (!$student) {
            return false;
        }

        return $student->delete();
    }

    private function hydrate(array $data): Student
    {
        $student = new Student([
            'email' => $data['email'],
            'name' => $data['name'],
            'student_number' => $data['student_number'],
            'password' => '' // Password tidak dihydrate untuk keamanan
        ]);
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