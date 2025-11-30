<?php

namespace App\Services;

use App\Repositories\CourseRepository;
use App\Models\Course;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;
use App\Exceptions\BusinessException;

class CourseService
{
    private CourseRepository $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    public function createCourse(array $data): Course
    {
        $course = new Course($data);

        if (!$course->validate()) {
            throw new ValidationException('Validation failed', $course->getErrors());
        }

        $this->courseRepository->save($course);

        return $course;
    }

    public function updateCourse(int $id, array $data): Course
    {
        $course = $this->courseRepository->findById($id);

        if (!$course) {
            throw new NotFoundException("Course with ID {$id} not found");
        }

        $updatedCourse = new Course(array_merge($course->toArray(), $data));
        $updatedCourse->setId($id);

        if (!$updatedCourse->validate()) {
            throw new ValidationException('Validation failed', $updatedCourse->getErrors());
        }

        $this->courseRepository->save($updatedCourse);

        return $updatedCourse;
    }

    public function deleteCourse(int $id): bool
    {
        $course = $this->courseRepository->findById($id);

        if (!$course) {
            throw new NotFoundException("Course with ID {$id} not found");
        }

        return $this->courseRepository->delete($id);
    }

    public function getCourseById(int $id): Course
    {
        $course = $this->courseRepository->findById($id);

        if (!$course) {
            throw new NotFoundException("Course with ID {$id} not found");
        }

        return $course;
    }

    public function getAllCourses(array $filters = []): array
    {
        return $this->courseRepository->findAll($filters);
    }

    public function publishCourse(int $id): Course
    {
        $course = $this->getCourseById($id);
        $course->publish();
        $this->courseRepository->save($course);

        return $course;
    }

    public function unpublishCourse(int $id): Course
    {
        $course = $this->getCourseById($id);
        $course->unpublish();
        $this->courseRepository->save($course);

        return $course;
    }

    public function canEnrollStudent(int $courseId, int $studentId): bool
    {
        $course = $this->getCourseById($courseId);
        
        if (!$course->isPublished()) {
            throw new BusinessException("Course is not published");
        }

        $currentEnrollments = $this->courseRepository->countEnrollments($courseId);
        
        return $course->canEnroll($currentEnrollments);
    }
}