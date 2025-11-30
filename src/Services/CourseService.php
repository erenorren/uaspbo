<?php

namespace App\Services;

use App\Repositories\CourseRepository;
use App\Repositories\InstructorRepository;
use App\Models\Course;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;
use App\Exceptions\BusinessException;

class CourseService
{
    private CourseRepository $courseRepository;
    private InstructorRepository $instructorRepository;

    public function __construct(
        CourseRepository $courseRepository,
        InstructorRepository $instructorRepository
    ) {
        $this->courseRepository = $courseRepository;
        $this->instructorRepository = $instructorRepository;
    }

    public function createCourse(array $data): Course
    {
        // Validate instructor exists
        $instructor = $this->instructorRepository->findById($data['instructor_id']);
        if (!$instructor) {
            throw new BusinessException("Instructor with ID {$data['instructor_id']} not found");
        }

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

        // Validate instructor if provided
        if (isset($data['instructor_id'])) {
            $instructor = $this->instructorRepository->findById($data['instructor_id']);
            if (!$instructor) {
                throw new BusinessException("Instructor with ID {$data['instructor_id']} not found");
            }
        }

        // Update properties
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
        $course = $this->courseRepository->findById($id);

        if (!$course) {
            throw new NotFoundException("Course with ID {$id} not found");
        }

        $course->publish();
        $this->courseRepository->save($course);

        return $course;
    }

    public function unpublishCourse(int $id): Course
    {
        $course = $this->courseRepository->findById($id);

        if (!$course) {
            throw new NotFoundException("Course with ID {$id} not found");
        }

        $course->unpublish();
        $this->courseRepository->save($course);

        return $course;
    }

    public function canEnrollStudent(int $courseId, int $studentId): bool
    {
        $course = $this->courseRepository->findById($courseId);
        if (!$course) {
            return false;
        }

        $enrolledCount = $this->courseRepository->getEnrolledCount($courseId);
        $isAlreadyEnrolled = $this->courseRepository->isStudentEnrolled($studentId, $courseId);

        return $course->canEnroll($enrolledCount) && !$isAlreadyEnrolled;
    }
}