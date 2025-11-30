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
            throw new NotFoundException("Siswa dengan ID {$studentId} tidak ditemukan");
        }

        // Validate course exists
        $course = $this->courseRepository->findById($courseId);
        if (!$course) {
            throw new NotFoundException("Kursus dengan ID {$courseId} tidak ditemukan");
        }

        // Business rules validation
        if (!$course->isPublished()) {
            throw new BusinessException("Kursus belum dipublikasikan");
        }

        $enrolledCount = $this->courseRepository->getEnrolledCount($courseId);
        if (!$course->canEnroll($enrolledCount)) {
            throw new BusinessException("Kursus Penuh");
        }

        $isAlreadyEnrolled = $this->courseRepository->isStudentEnrolled($studentId, $courseId);
        if ($isAlreadyEnrolled) {
            throw new BusinessException("Siswa sudah terdaftar dalam kursus ini");
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
            throw new NotFoundException("Pendaftaran ID {$enrollmentId} tidak ditemukan");
        }

        if ($enrollment->getStatus() !== 'active') {
            throw new BusinessException("Hanya pendaftaran aktif yang dapat diselesaikan");
        }

        $enrollment->complete();
        $this->enrollmentRepository->save($enrollment);

        return $enrollment;
    }

    public function cancelEnrollment(int $enrollmentId): Enrollment
    {
        $enrollment = $this->enrollmentRepository->findById($enrollmentId);

        if (!$enrollment) {
            throw new NotFoundException("Pendaftaran dengan ID {$enrollmentId} tidak ditemukan");
        }

        if ($enrollment->getStatus() !== 'active') {
            throw new BusinessException("Hanya pendaftaran aktif yang dapat dibatalkan");
        }

        $enrollment->cancel();
        $this->enrollmentRepository->save($enrollment);

        return $enrollment;
    }

    public function getStudentEnrollments(int $studentId): array
    {
        $student = $this->studentRepository->findById($studentId);
        if (!$student) {
            throw new NotFoundException("Siswa dengan ID {$studentId} tidak ditemukan");
        }

        return $this->enrollmentRepository->findByStudentId($studentId);
    }

    public function getEnrollmentById(int $id): Enrollment
    {
        $enrollment = $this->enrollmentRepository->findById($id);

        if (!$enrollment) {
            throw new NotFoundException("Pendaftaran dengan ID {$id} tidak ditemukan");
        }

        return $enrollment;
    }
}