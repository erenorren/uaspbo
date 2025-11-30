<?php

namespace App\Models;

use App\Core\Model;
use App\Traits\Validatable;
use App\Core\Database;
use DateTime;

class Enrollment extends Model
{
    use Validatable;

    private int $courseId;
    private int $studentId;
    private DateTime $enrolledAt;
    private ?DateTime $completedAt;
    private string $status;
    private ?float $grade;

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->fill($data);
        }
    }

    private function fill(array $data): void
    {
        $this->courseId = $data['course_id'] ?? 0;
        $this->studentId = $data['student_id'] ?? 0;
        
        $this->enrolledAt = isset($data['enrolled_at']) 
            ? new DateTime($data['enrolled_at'])
            : new DateTime();
            
        $this->completedAt = isset($data['completed_at']) 
            ? new DateTime($data['completed_at'])
            : null;
            
        $this->status = $data['status'] ?? 'active';
        $this->grade = $data['grade'] ?? null;
    }

    public function validate(): bool
    {
        $this->clearErrors();

        if ($this->courseId <= 0) {
            $this->addError('course_id', 'Valid course ID is required');
        }

        if ($this->studentId <= 0) {
            $this->addError('student_id', 'Valid student ID is required');
        }

        $allowedStatuses = ['active', 'completed', 'cancelled'];
        if (!in_array($this->status, $allowedStatuses)) {
            $this->addError('status', 'Status must be one of: ' . implode(', ', $allowedStatuses));
        }

        if ($this->grade !== null && ($this->grade < 0 || $this->grade > 100)) {
            $this->addError('grade', 'Grade must be between 0 and 100');
        }

        return !$this->hasErrors();
    }

    public function complete(?float $grade = null): void
    {
        $this->status = 'completed';
        $this->completedAt = new DateTime();
        if ($grade !== null) {
            $this->grade = $grade;
        }
    }

    public function cancel(): void
    {
        $this->status = 'cancelled';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    protected static function getTableName(): string
    {
        return 'enrollments';
    }

    protected function insert(): bool
    {
        $db = Database::getInstance()->getConnection();
        
        $sql = "INSERT INTO enrollments (course_id, student_id, enrolled_at, completed_at, status, grade, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $this->courseId,
            $this->studentId,
            $this->enrolledAt->format('Y-m-d H:i:s'),
            $this->completedAt?->format('Y-m-d H:i:s'),
            $this->status,
            $this->grade,
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
        
        $sql = "UPDATE enrollments SET completed_at=?, status=?, grade=?, updated_at=? WHERE id=?";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $this->completedAt?->format('Y-m-d H:i:s'),
            $this->status,
            $this->grade,
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
            'course_id' => $this->courseId,
            'student_id' => $this->studentId,
            'enrolled_at' => $this->enrolledAt->format('Y-m-d H:i:s'),
            'completed_at' => $this->completedAt?->format('Y-m-d H:i:s'),
            'status' => $this->status,
            'grade' => $this->grade,
            'is_active' => $this->isActive(),
            'is_completed' => $this->isCompleted(),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s')
        ];
    }

    // Getters
    public function getCourseId(): int { return $this->courseId; }
    public function getStudentId(): int { return $this->studentId; }
    public function getStatus(): string { return $this->status; }
    public function getGrade(): ?float { return $this->grade; }
    public function getEnrolledAt(): DateTime { return $this->enrolledAt; }
}