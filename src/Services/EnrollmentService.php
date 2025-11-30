<?php

namespace App\Services;

use App\Repositories\EnrollmentRepository;
use App\Repositories\CourseRepository;
use App\Repositories\StudentRepository;
use App\Models\Enrollment;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;
use App\Exceptions\BusinessException;

class EnrollmentService
{
    private EnrollmentRepository $enrollmentRepository;
    private CourseRepository $courseRepository;
    private StudentRepository $studentRepository;

    public function __construct(
        EnrollmentRepository $enrollmentRepository,
        CourseRepository $courseRepository,
        StudentRepository $studentRepository
    ) {
        $this->enrollmentRepository = $enrollmentRepository;
        $this->courseRepository = $courseRepository;
        $this->studentRepository = $studentRepository;
    }

    public function enrollStudent(int $studentId, int $courseId): Enrollment
    {
        // Validate student exists
        $student = $this->studentRepository->findById($studentId);
        if (!$student) {
            throw new NotFoundException("Student with ID {$studentId} not found");
        }

        // Validate course exists
        $course = $this->courseRepository->findById($courseId);
        if (!$course) {
            throw new NotFoundException("Course with ID {$courseId} not found");
        }

        // Business rules validation
        if (!$course->isPublished()) {
            throw new BusinessException("Course is not published");
        }

        $enrolledCount = $this->courseRepository->getEnrolledCount($courseId);
        if (!$course->canEnroll($enrolledCount)) {
            throw new BusinessException("Course is full");
        }

        $isAlreadyEnrolled = $this->courseRepository->isStudentEnrolled($studentId, $courseId);
        if ($isAlreadyEnrolled) {
            throw new BusinessException("Student is already enrolled in this course");
        }

        // Create enrollment
        $enrollment = new Enrollment([
            'student_id' => $studentId,
            'course_id' => $courseId,
            'status' => 'active'
        ]);

        if (!$enrollment->validate()) {
            throw new ValidationException('Validation failed', $enrollment->getErrors());
        }

        $this->enrollmentRepository->save($enrollment);

        return $enrollment;
    }

    public function completeEnrollment(int $enrollmentId): Enrollment
    {
        $enrollment = $this->enrollmentRepository->findById($enrollmentId);

        if (!$enrollment) {
            throw new NotFoundException("Enrollment with ID {$enrollmentId} not found");
        }

        if ($enrollment->getStatus() !== 'active') {
            throw new BusinessException("Only active enrollments can be completed");
        }

        $enrollment->complete();
        $this->enrollmentRepository->save($enrollment);

        return $enrollment;
    }

    public function cancelEnrollment(int $enrollmentId): Enrollment
    {
        $enrollment = $this->enrollmentRepository->findById($enrollmentId);

        if (!$enrollment) {
            throw new NotFoundException("Enrollment with ID {$enrollmentId} not found");
        }

        if ($enrollment->getStatus() !== 'active') {
            throw new BusinessException("Only active enrollments can be cancelled");
        }

        $enrollment->cancel();
        $this->enrollmentRepository->save($enrollment);

        return $enrollment;
    }

    public function getStudentEnrollments(int $studentId): array
    {
        $student = $this->studentRepository->findById($studentId);
        if (!$student) {
            throw new NotFoundException("Student with ID {$studentId} not found");
        }

        return $this->enrollmentRepository->findByStudentId($studentId);
    }

    public function getEnrollmentById(int $id): Enrollment
    {
        $enrollment = $this->enrollmentRepository->findById($id);

        if (!$enrollment) {
            throw new NotFoundException("Enrollment with ID {$id} not found");
        }

        return $enrollment;
    }
}