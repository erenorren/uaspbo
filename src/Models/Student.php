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

    public function validate(): bool
    {
        parent::validate();
        
        $this->validateRequired('student_number', $this->studentNumber, 'Student number');
        
        return !$this->hasErrors();
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
        // First insert into users table
        if (!parent::insert()) {
            return false;
        }

        // Then insert into students table
        $db = Database::getInstance()->getConnection();
        $sql = "INSERT INTO students (id, student_number) VALUES (?, ?)";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $this->id,
            $this->studentNumber
        ]);
    }

    protected function update(): bool
    {
        // Update users table
        if (!parent::update()) {
            return false;
        }

        // Update students table
        $db = Database::getInstance()->getConnection();
        $sql = "UPDATE students SET student_number=? WHERE id=?";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $this->studentNumber,
            $this->id
        ]);
    }

    public function delete(): bool
    {
        // Deleting from users will cascade to students
        return parent::delete();
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        $data['student_number'] = $this->studentNumber;
        return $data;
    }

    public function getStudentNumber(): string { return $this->studentNumber; }
}