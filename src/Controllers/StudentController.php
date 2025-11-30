<?php
class StudentController {
    public function __construct(
        private StudentService $studentService,
        private ApiResponseBuilder $responseBuilder
    ) {}
    
    public function list() {
        try {
            $students = $this->studentService->getAllStudents();
            return $this->responseBuilder->success($students, 'Students retrieved successfully');
        } catch (Exception $e) {
            return $this->responseBuilder->error($e->getMessage(), 500);
        }
    }
    
    public function get($params) {
        try {
            $student = $this->studentService->getStudentById($params['id']);
            if (!$student) {
                return $this->responseBuilder->error('Student not found', 404);
            }
            return $this->responseBuilder->success($student, 'Student retrieved successfully');
        } catch (Exception $e) {
            return $this->responseBuilder->error($e->getMessage(), 500);
        }
    }
    
    public function create() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $student = $this->studentService->createStudent($data);
            return $this->responseBuilder->success($student, 'Student created successfully', 201);
        } catch (ValidationException $e) {
            return $this->responseBuilder->error($e->getMessage(), 400);
        } catch (Exception $e) {
            return $this->responseBuilder->error($e->getMessage(), 500);
        }
    }
}
?>