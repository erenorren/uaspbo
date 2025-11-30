<?php

namespace App\Models;

use App\Core\Database;

class Instructor extends User
{
    private string $instructorCode;
    private string $phone;
    private string $expertise;

    protected function fill(array $data): void
    {
        parent::fill($data);
        $this->instructorCode = $data['instructor_code'] ?? $this->generateInstructorCode();
        $this->phone = $data['phone'] ?? '';
        $this->expertise = $data['expertise'] ?? '';
    }

    public function getRole(): string
    {
        return 'instructor';
    }

    public function validate(): bool
    {
        $this->clearErrors();

        $this->validateRequired('email', $this->email, 'Email');
        $this->validateEmail('email', $this->email);
        $this->validateRequired('name', $this->name, 'Name');
        $this->validateRequired('phone', $this->phone, 'Phone');
        $this->validateRequired('expertise', $this->expertise, 'Expertise');

        return !$this->hasErrors();
    }

    private function generateInstructorCode(): string
    {
        return 'INS' . date('Y') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    }

    protected static function getTableName(): string
    {
        return 'instructors';
    }

    protected function insert(): bool
    {
        $db = Database::getInstance()->getConnection();
        
        $sql = "INSERT INTO instructors (instructor_code, email, password, name, phone, expertise, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $this->instructorCode,
            $this->email,
            $this->password,
            $this->name,
            $this->phone,
            $this->expertise,
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
        
        $sql = "UPDATE instructors SET email=?, name=?, phone=?, expertise=?, updated_at=? WHERE id=?";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $this->email,
            $this->name,
            $this->phone,
            $this->expertise,
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
        $data['instructor_code'] = $this->instructorCode;
        $data['phone'] = $this->phone;
        $data['expertise'] = $this->expertise;
        return $data;
    }

    // Getters
    public function getInstructorCode(): string { return $this->instructorCode; }
    public function getPhone(): string { return $this->phone; }
    public function getExpertise(): string { return $this->expertise; }
}