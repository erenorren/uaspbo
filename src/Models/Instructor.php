<?php

namespace App\Models;

use App\Core\Database;

class Instructor extends User
{
    private string $bio;

    // Constructor sudah diwarisi dari Model

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
        // First insert into users table
        if (!parent::insert()) {
            return false;
        }

        // Then insert into instructors table
        $db = Database::getInstance()->getConnection();
        $sql = "INSERT INTO instructors (id, bio) VALUES (?, ?)";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $this->id,
            $this->bio
        ]);
    }

    protected function update(): bool
    {
        // Update users table
        if (!parent::update()) {
            return false;
        }

        // Update instructors table
        $db = Database::getInstance()->getConnection();
        $sql = "UPDATE instructors SET bio=? WHERE id=?";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $this->bio,
            $this->id
        ]);
    }

    public function delete(): bool
    {
        return parent::delete();
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        $data['bio'] = $this->bio;
        return $data;
    }

    public function getBio(): string { return $this->bio; }
}