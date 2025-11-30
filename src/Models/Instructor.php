<?php

namespace App\Models;

use App\Core\Database;

class Instructor extends User
{
    private string $bio;

    protected function fill(array $data): void
    {
        parent::fill($data);
        $this->bio = $data['bio'] ?? '';
    }

    public function getRole(): string
    {
        return 'instructor';
    }

    protected static function getTableName(): string
    {
        return 'instructors';
    }

    protected function insert(): bool
    {
        $db = Database::getInstance()->getConnection();
        
        $sql = "INSERT INTO instructors (email, password, name, bio, created_at) 
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $this->email,
            $this->password,
            $this->name,
            $this->bio,
            $this->createdAt->format('Y-m-d H:i:s')
        ]);

        if ($result) {
            $this->id = (int)$db->lastInsertId();
        }

        return $result;
    }

    protected function update(): bool
    {
        $db = Database::getInstance()->getConnection();
        
        $sql = "UPDATE instructors SET email=?, name=?, bio=?, updated_at=? WHERE id=?";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $this->email,
            $this->name,
            $this->bio,
            $this->updatedAt->format('Y-m-d H:i:s'),
            $this->id
        ]);
    }

    public function delete(): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM instructors WHERE id = ?");
        return $stmt->execute([$this->id]);
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        $data['bio'] = $this->bio;
        return $data;
    }
}