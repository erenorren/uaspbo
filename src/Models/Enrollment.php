<?php

namespace App\Models;

use App\Core\Model;
use App\Traits\Validatable;
use App\Core\Database;

class Enrollment extends Model
{
    use Validatable;

    private int $studentId;
    private int $courseId;
    private string $status;
    private \DateTime $enrolledAt;

    // Constructor sudah diwarisi dari Model

    protected function fill(array $data): void
    {
        $this->studentId = $data['student_id'] ?? 0;
        $this->courseId = $data['course_id'] ?? 0;
        $this->status = $data['status'] ?? 'active';
        $this->enrolledAt = isset($data['enrolled_at']) 
            ? new \DateTime($data['enrolled_at']) 
            : new \DateTime();
    }

    public function complete(): void
    {
        $this->status = 'completed';
    }

    public function cancel(): void
    {
        $this->status = 'cancelled';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function validate(): bool
    {
        $this->clearErrors();

        if ($this->studentId <= 0) {
            $this->addError('student_id', 'Valid student ID is required');
        }

        if ($this->courseId <= 0) {
            $this->addError('course_id', 'Valid course ID is required');
        }

        if (!in_array($this->status, ['active', 'completed', 'cancelled'])) {
            $this->addError('status', 'Status must be active, completed, or cancelled');
        }

        return !$this->hasErrors();
    }

    protected static function getTableName(): string
    {
        return 'enrollments';
    }

    protected function insert(): bool
    {
        $db = Database::getInstance()->getConnection();
        $sql = "INSERT INTO enrollments (student_id, course_id, enrolled_at, status, created_at) 
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $this->studentId,
            $this->courseId,
            $this->enrolledAt->format('Y-m-d H:i:s'),
            $this->status,
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
        $sql = "UPDATE enrollments SET status=?, updated_at=? WHERE id=?";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $this->status,
            $this->updatedAt->format('Y-m-d H:i:s'),
            $this->id
        ]);
    }

    public function delete(): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM enrollments WHERE id = ?");
        return $stmt->execute([$this->id]);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->studentId,
            'course_id' => $this->courseId,
            'enrolled_at' => $this->enrolledAt->format('Y-m-d H:i:s'),
            'status' => $this->status,
            'is_active' => $this->isActive(),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s')
        ];
    }

    // Getters
    public function getStudentId(): int { return $this->studentId; }
    public function getCourseId(): int { return $this->courseId; }
    public function getStatus(): string { return $this->status; }
}