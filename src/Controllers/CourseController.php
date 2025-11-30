<?php
class CourseController {
    public function __construct(
        private CourseService $courseService,
        private ApiResponseBuilder $responseBuilder
    ) {}
    
    public function list() {
        try {
            $courses = $this->courseService->getAllCourses();
            return $this->responseBuilder->success($courses, 'Courses retrieved successfully');
        } catch (Exception $e) {
            return $this->responseBuilder->error($e->getMessage(), 500);
        }
    }
    
    public function get($params) {
        try {
            $course = $this->courseService->getCourseById($params['id']);
            if (!$course) {
                return $this->responseBuilder->error('Course not found', 404);
            }
            return $this->responseBuilder->success($course, 'Course retrieved successfully');
        } catch (Exception $e) {
            return $this->responseBuilder->error($e->getMessage(), 500);
        }
    }
    
    public function create() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $course = $this->courseService->createCourse($data);
            return $this->responseBuilder->success($course, 'Course created successfully', 201);
        } catch (ValidationException $e) {
            return $this->responseBuilder->error($e->getMessage(), 400);
        } catch (Exception $e) {
            return $this->responseBuilder->error($e->getMessage(), 500);
        }
    }
    
    public function update($params) {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $course = $this->courseService->updateCourse($params['id'], $data);
            return $this->responseBuilder->success($course, 'Course updated successfully');
        } catch (ValidationException $e) {
            return $this->responseBuilder->error($e->getMessage(), 400);
        } catch (NotFoundException $e) {
            return $this->responseBuilder->error($e->getMessage(), 404);
        } catch (Exception $e) {
            return $this->responseBuilder->error($e->getMessage(), 500);
        }
    }
    
    public function delete($params) {
        try {
            $this->courseService->deleteCourse($params['id']);
            return $this->responseBuilder->success(null, 'Course deleted successfully');
        } catch (NotFoundException $e) {
            return $this->responseBuilder->error($e->getMessage(), 404);
        } catch (Exception $e) {
            return $this->responseBuilder->error($e->getMessage(), 500);
        }
    }
    
    public function publish($params) {
        try {
            $course = $this->courseService->publishCourse($params['id']);
            return $this->responseBuilder->success($course, 'Course published successfully');
        } catch (NotFoundException $e) {
            return $this->responseBuilder->error($e->getMessage(), 404);
        } catch (Exception $e) {
            return $this->responseBuilder->error($e->getMessage(), 400);
        }
    }
}
?>