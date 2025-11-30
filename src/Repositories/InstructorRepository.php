<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Instructor;
use PDO;

class InstructorRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findById(int $id): ?Instructor
    {
        $sql = "SELECT u.*, i.bio 
                FROM users u 
                JOIN instructors i ON u.id = i.id 
                WHERE u.id = ? AND u.role = 'instructor'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    public function findAll(): array
    {
        $sql = "SELECT u.*, i.bio 
                FROM users u 
                JOIN instructors i ON u.id = i.user_id 
                WHERE u.role = 'instructor' 
                ORDER BY u.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $instructors = [];
        while ($data = $stmt->fetch()) {
            $instructors[] = $this->hydrate($data);
        }

        return $instructors;
    }

    public function save(Instructor $instructor): bool
    {
        return $instructor->save();
    }

    private function hydrate(array $data): Instructor
    {
        $instructor = new Instructor([
            'email' => $data['email'],
            'name' => $data['name'],
            'bio' => $data['bio']
        ]);
        $instructor->setId((int)$data['id']);

        if (isset($data['created_at'])) {
            $instructor->setCreatedAt(new \DateTime($data['created_at']));
        }

        if (isset($data['updated_at'])) {
            $instructor->setUpdatedAt(new \DateTime($data['updated_at']));
        }

        return $instructor;
    }
}