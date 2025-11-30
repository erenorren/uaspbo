<?php

namespace App\Models;

use App\Core\Database;

class Student extends User
{
    private string $studentNumber;
    private string $phone;
    private int $enrollLimit;

    protected function fill(array $data): void
    {
        parent::fill($data);
        $this->studentNumber = $data['student_number'] ?? $this->generateStudentNumber();
        $this->phone = $data['phone'] ?? '';
        $this->enrollLimit = $data['enroll_limit'] ?? 5;
    }

    public function getRole(): string
    {
        return 'student';
    }

    public function validate(): bool
    {
        $this->clearErrors();

        $this->validateRequired('email', $this->email, 'Email');
        $this->validateEmail('email', $this->email);
        $this->validateRequired('name', $this->name, 'Name');
        $this->validateRequired('phone', $this->phone, 'Phone');
        $this->validateMinLength('password', $this->password ?? '', 6, 'Password');

        if ($this->enrollLimit <= 0) {
            $this->addError('enroll_limit', 'Enroll limit must be greater than 0');
        }

        return !$this->hasErrors();
    }

    private function generateStudentNumber(): string
    {
        return 'STU' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    protected static function getTableName(): string
    {
        return 'students';
    }

    protected function insert(): bool
    {
        $db = Database::getInstance()->getConnection();
        
        $sql = "INSERT INTO students (student_number, email, password, name, phone, enroll_limit, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $this->studentNumber,
            $this->email,
            $this->password,
            $this->name,
            $this->phone,
            $this->enrollLimit,
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
        
        $sql = "UPDATE students SET email=?, name=?, phone=?, enroll_limit=?, updated_at=? WHERE id=?";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $this->email,
            $this->name,
            $this->phone,
            $this->enrollLimit,
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
        $data['phone'] = $this->phone;
        $data['enroll_limit'] = $this->enrollLimit;
        return $data;
    }

    // Getters
    public function getStudentNumber(): string { return $this->studentNumber; }
    public function getPhone(): string { return $this->phone; }
    public function getEnrollLimit(): int { return $this->enrollLimit; }
}