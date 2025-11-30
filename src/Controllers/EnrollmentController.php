<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\EnrollmentService;
use App\Builders\ApiResponseBuilder;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;
use App\Exceptions\BusinessException;

class EnrollmentController extends Controller
{
    private EnrollmentService $enrollmentService;

    public function __construct(EnrollmentService $enrollmentService)
    {
        $this->enrollmentService = $enrollmentService;
    }

    public function store(): void
    {
        try {
            $data = $this->getJsonInput();

            if (!isset($data['student_id']) || !isset($data['course_id'])) {
                ApiResponseBuilder::error('student_id and course_id are required', 400)->send();
            }

            $enrollment = $this->enrollmentService->enrollStudent(
                (int)$data['student_id'],
                (int)$data['course_id']
            );

            ApiResponseBuilder::created($enrollment->toArray(), 'Enrollment created successfully')
                ->send();

        } catch (NotFoundException $e) {
            ApiResponseBuilder::notFound($e->getMessage())->send();
        } catch (BusinessException $e) {
            ApiResponseBuilder::error($e->getMessage(), 400)->send();
        } catch (ValidationException $e) {
            ApiResponseBuilder::validationError($e->getErrors())->send();
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    public function studentEnrollments(int $studentId): void
    {
        try {
            $enrollments = $this->enrollmentService->getStudentEnrollments($studentId);
            $data = array_map(fn($enrollment) => $enrollment->toArray(), $enrollments);

            ApiResponseBuilder::success($data, 'Student enrollments retrieved successfully')
                ->addMeta('total', count($data))
                ->send();

        } catch (NotFoundException $e) {
            ApiResponseBuilder::notFound($e->getMessage())->send();
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    public function complete(int $id): void
    {
        try {
            $enrollment = $this->enrollmentService->completeEnrollment($id);

            ApiResponseBuilder::success($enrollment->toArray(), 'Enrollment completed successfully')
                ->send();

        } catch (NotFoundException $e) {
            ApiResponseBuilder::notFound($e->getMessage())->send();
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    public function cancel(int $id): void
    {
        try {
            $enrollment = $this->enrollmentService->cancelEnrollment($id);

            ApiResponseBuilder::success($enrollment->toArray(), 'Enrollment cancelled successfully')
                ->send();

        } catch (NotFoundException $e) {
            ApiResponseBuilder::notFound($e->getMessage())->send();
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }
}