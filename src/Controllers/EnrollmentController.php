<?php
class EnrollmentController {
    public function __construct(
        private EnrollmentService $enrollmentService,
        private ApiResponseBuilder $responseBuilder
    ) {}
    
    public function create() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $enrollment = $this->enrollmentService->enrollStudent($data);
            return $this->responseBuilder->success($enrollment, 'Enrollment created successfully', 201);
        } catch (ValidationException $e) {
            return $this->responseBuilder->error($e->getMessage(), 400);
        } catch (EnrollmentException $e) {
            return $this->responseBuilder->error($e->getMessage(), 422);
        } catch (Exception $e) {
            return $this->responseBuilder->error($e->getMessage(), 500);
        }
    }
    
    public function getStudentEnrollments($params) {
        try {
            $enrollments = $this->enrollmentService->getStudentEnrollments($params['id']);
            return $this->responseBuilder->success($enrollments, 'Enrollments retrieved successfully');
        } catch (Exception $e) {
            return $this->responseBuilder->error($e->getMessage(), 500);
        }
    }
    
    public function complete($params) {
        try {
            $enrollment = $this->enrollmentService->completeEnrollment($params['id']);
            return $this->responseBuilder->success($enrollment, 'Enrollment completed successfully');
        } catch (NotFoundException $e) {
            return $this->responseBuilder->error($e->getMessage(), 404);
        } catch (Exception $e) {
            return $this->responseBuilder->error($e->getMessage(), 400);
        }
    }
    
    public function cancel($params) {
        try {
            $enrollment = $this->enrollmentService->cancelEnrollment($params['id']);
            return $this->responseBuilder->success($enrollment, 'Enrollment cancelled successfully');
        } catch (NotFoundException $e) {
            return $this->responseBuilder->error($e->getMessage(), 404);
        } catch (Exception $e) {
            return $this->responseBuilder->error($e->getMessage(), 400);
        }
    }
}
?>