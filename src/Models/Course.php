<?php

namespace App\Models;

use App\Core\Model;
use App\Interfaces\Publishable;
use App\Traits\Validatable;
use App\Core\Database;

class Course extends Model implements Publishable
{
    use Validatable;

    private string $courseCode;
    private string $title;
    private string $description;
    private string $category;
    private int $maxStudents;
    private int $currentEnrolled;
    private string $status;

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->fill($data);
        }
    }

    private function fill(array $data): void
    {
        $this->courseCode = $data['course_code'] ?? '';
        $this->title = $data['title'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->category = $data['category'] ?? '';
        $this->maxStudents = $data['max_students'] ?? 0;
        $this->currentEnrolled = $data['current_enrolled'] ?? 0;
        $this->status = $data['status'] ?? 'draft';
    }

    public function validate(): bool
    {
        $this->clearErrors();

        $this->validateRequired('course_code', $this->courseCode, 'Course Code');
        $this->validateRequired('title', $this->title, 'Title');
        $this->validateRequired('description', $this->description, 'Description');
        $this->validateRequired('category', $this->category, 'Category');

        if ($this->maxStudents <= 0) {
            $this->addError('max_students', 'Max students must be greater than 0');
        }

        if ($this->currentEnrolled < 0) {
            $this->addError('current_enrolled', 'Current enrolled cannot be negative');
        }

        $allowedStatuses = ['draft', 'published', 'archived'];
        if (!in_array($this->status, $allowedStatuses)) {
            $this->addError('status', 'Status must be one of: ' . implode(', ', $allowedStatuses));
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

    public function archive(): void
    {
        $this->status = 'archived';
    }

    public function canEnroll(): bool
    {
        return $this->status === 'published' && $this->currentEnrolled < $this->maxStudents;
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function getAvailableSlots(): int
    {
        return $this->maxStudents - $this->currentEnrolled;
    }

    protected static function getTableName(): string
    {
        return 'courses';
    }

    protected function insert(): bool
    {
        $db = Database::getInstance()->getConnection();
        
        $sql = "INSERT INTO courses (course_code, title, description, category, max_students, current_enrolled, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $this->courseCode,
            $this->title,
            $this->description,
            $this->category,
            $this->maxStudents,
            $this->currentEnrolled,
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
        
        $sql = "UPDATE courses SET course_code=?, title=?, description=?, category=?, max_students=?, current_enrolled=?, status=?, updated_at=? WHERE id=?";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $this->courseCode,
            $this->title,
            $this->description,
            $this->category,
            $this->maxStudents,
            $this->currentEnrolled,
            $this->status,
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
            'course_code' => $this->courseCode,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'max_students' => $this->maxStudents,
            'current_enrolled' => $this->currentEnrolled,
            'available_slots' => $this->getAvailableSlots(),
            'status' => $this->status,
            'is_published' => $this->isPublished(),
            'can_enroll' => $this->canEnroll(),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s')
        ];
    }

    // Getters
    public function getCourseCode(): string { return $this->courseCode; }
    public function getTitle(): string { return $this->title; }
    public function getStatus(): string { return $this->status; }
    public function getMaxStudents(): int { return $this->maxStudents; }
    public function getCurrentEnrolled(): int { return $this->currentEnrolled; }
    public function getCategory(): string { return $this->category; }
}