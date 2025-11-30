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
        $course = $this->courseRepository->findById($courseId);
        if (!$course) {
            throw new NotFoundException("Course with ID {$courseId} not found");
        }

        $student = $this->studentRepository->findById($studentId);
        if (!$student) {
            throw new NotFoundException("Student with ID {$studentId} not found");
        }

        if (!$course->isPublished()) {
            throw new BusinessException("Course is not published");
        }

        $currentEnrollments = $this->courseRepository->countEnrollments($courseId);
        if (!$course->canEnroll($currentEnrollments)) {
            throw new BusinessException("Course is full");
        }

        if ($this->studentRepository->hasActiveEnrollment($studentId, $courseId)) {
            throw new BusinessException("Student is already enrolled in this course");
        }

        $enrollment = new Enrollment([
            'student_id' => $studentId,
            'course_id' => $courseId
        ]);

        if (!$enrollment->validate()) {
            throw new ValidationException('Enrollment validation failed', $enrollment->getErrors());
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