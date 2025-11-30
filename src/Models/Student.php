<?php

namespace App\Models;

use App\Core\Database;

class Student extends User
{
    private string $studentNumber;

    protected function fill(array $data): void
    {
        parent::fill($data);
        $this->studentNumber = $data['student_number'] ?? $this->generateStudentNumber();
    }

    public function getRole(): string
    {
        return 'student';
    }

    private function generateStudentNumber(): string
    {
        return 'STD' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    protected static function getTableName(): string
    {
        return 'students';
    }

    protected function insert(): bool
    {
        $db = Database::getInstance()->getConnection();
        
        $sql = "INSERT INTO students (student_number, email, password, name, created_at) 
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $this->studentNumber,
            $this->email,
            $this->password,
            $this->name,
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
        
        $sql = "UPDATE students SET email=?, name=?, updated_at=? WHERE id=?";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $this->email,
            $this->name,
            $this->updatedAt->format('Y-m-d H:i:s'),
            $this->id
        ]);
    }

    public function delete(): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM students WHERE id = ?");
        return $stmt->execute([$this->id]);
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        $data['student_number'] = $this->studentNumber;
        return $data;
    }
}