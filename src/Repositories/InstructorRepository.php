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
        $stmt = $this->db->prepare("SELECT * FROM instructors WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        return $data ? $this->hydrate($data) : null;
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM instructors ORDER BY created_at DESC");
        
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

    public function delete(int $id): bool
{
    $instructor = $this->findById($id);
    return $instructor ? $instructor->delete() : false;
}

    private function hydrate(array $data): Instructor
    {
        $instructor = new Instructor($data);
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