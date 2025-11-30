<?php

namespace App\Models;

use App\Core\Model;
use App\Interfaces\Publishable;
use App\Traits\Validatable;
use App\Core\Database;

class Course extends Model implements Publishable
{
    use Validatable;

    private string $title;
    private string $description;
    private int $maxStudents;
    private string $status;
    private int $instructorId;

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->fill($data);
        }
    }

    private function fill(array $data): void
    {
        $this->title = $data['title'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->maxStudents = $data['max_students'] ?? 30;
        $this->status = $data['status'] ?? 'draft';
        $this->instructorId = $data['instructor_id'] ?? 0;
    }

    public function validate(): bool
    {
        $this->clearErrors();

        $this->validateRequired('title', $this->title, 'Title');
        $this->validateRequired('description', $this->description, 'Description');
        $this->validateRequired('instructor_id', $this->instructorId, 'Instructor ID');

        if ($this->maxStudents <= 0) {
            $this->addError('max_students', 'Max students must be greater than 0');
        }

        return !$this->hasErrors();
    }

    public function publish(): void
    {
        $this->status = 'published';
    }

    public function unpublish(): void
    {
        $this->status = 'draft';
    }

    public function canEnroll(int $currentEnrolledCount): bool
    {
        return $this->status === 'published' && $currentEnrolledCount < $this->maxStudents;
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    protected static function getTableName(): string
    {
        return 'courses';
    }

    protected function insert(): bool
    {
        $db = Database::getInstance()->getConnection();
        
        $sql = "INSERT INTO courses (title, description, max_students, status, instructor_id, created_at) 
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $this->title,
            $this->description,
            $this->maxStudents,
            $this->status,
            $this->instructorId,
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
        
        $sql = "UPDATE courses SET title=?, description=?, max_students=?, status=?, instructor_id=?, updated_at=? WHERE id=?";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $this->title,
            $this->description,
            $this->maxStudents,
            $this->status,
            $this->instructorId,
            $this->updatedAt->format('Y-m-d H:i:s'),
            $this->id
        ]);
    }

    public function delete(): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM courses WHERE id = ?");
        return $stmt->execute([$this->id]);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'max_students' => $this->maxStudents,
            'status' => $this->status,
            'instructor_id' => $this->instructorId,
            'is_published' => $this->isPublished(),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s')
        ];
    }

    public function getTitle(): string { return $this->title; }
    public function getStatus(): string { return $this->status; }
    public function getMaxStudents(): int { return $this->maxStudents; }
    public function getInstructorId(): int { return $this->instructorId; }
}