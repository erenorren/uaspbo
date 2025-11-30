<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

set_exception_handler(function($e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'status_code' => 500,
        'message' => 'Internal server error',
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    exit;
});

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

use App\Core\Router;
use App\Controllers\CourseController;
use App\Controllers\StudentController;
use App\Controllers\EnrollmentController;
use App\Services\CourseService;
use App\Services\StudentService;
use App\Services\EnrollmentService;
use App\Repositories\CourseRepository;
use App\Repositories\StudentRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\InstructorRepository;

try {
    // Dependency Injection Container (Simple)
    $courseRepository = new CourseRepository();
    $studentRepository = new StudentRepository();
    $enrollmentRepository = new EnrollmentRepository();
    $instructorRepository = new InstructorRepository();

    $courseService = new CourseService($courseRepository, $instructorRepository);
    $studentService = new StudentService($studentRepository);
    $enrollmentService = new EnrollmentService($enrollmentRepository, $courseRepository, $studentRepository);

    $courseController = new CourseController($courseService);
    $studentController = new StudentController($studentService);
    $enrollmentController = new EnrollmentController($enrollmentService);

    // Router Setup
    $router = new Router();

    // Course Routes
    $router->get('/courses', [$courseController, 'index']);
    $router->get('/courses/:id', [$courseController, 'show']);
    $router->post('/courses', [$courseController, 'store']);
    $router->put('/courses/:id', [$courseController, 'update']);
    $router->delete('/courses/:id', [$courseController, 'destroy']);
    $router->put('/courses/:id/publish', [$courseController, 'publish']);
    $router->put('/courses/:id/unpublish', [$courseController, 'unpublish']);

    // Student Routes
    $router->get('/students', [$studentController, 'index']);
    $router->get('/students/:id', [$studentController, 'show']);
    $router->post('/students', [$studentController, 'store']);
    $router->put('/students/:id', [$studentController, 'update']);
    $router->delete('/students/:id', [$studentController, 'destroy']);

    // Enrollment Routes
    $router->post('/enrollments', [$enrollmentController, 'store']);
    $router->get('/students/:id/enrollments', [$enrollmentController, 'studentEnrollments']);
    $router->put('/enrollments/:id/complete', [$enrollmentController, 'complete']);
    $router->put('/enrollments/:id/cancel', [$enrollmentController, 'cancel']);

    // Test route
    $router->get('/', function() {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'E-Learning API is running!',
            'endpoints' => [
                'GET /courses' => 'Get all courses',
                'POST /courses' => 'Create course',
                'GET /courses/:id' => 'Get course by ID',
                'PUT /courses/:id' => 'Update course',
                'DELETE /courses/:id' => 'Delete course',
                'PUT /courses/:id/publish' => 'Publish course',
                'PUT /courses/:id/unpublish' => 'Unpublish course',
                'GET /students' => 'Get all students',
                'POST /students' => 'Create student',
                'GET /students/:id' => 'Get student by ID',
                'PUT /students/:id' => 'Update student',
                'DELETE /students/:id' => 'Delete student',
                'POST /enrollments' => 'Enroll student to course',
                'GET /students/:id/enrollments' => 'Get student enrollments',
                'PUT /enrollments/:id/complete' => 'Complete enrollment',
                'PUT /enrollments/:id/cancel' => 'Cancel enrollment'
            ]
        ]);
    });

    // Dispatch
    $router->dispatch();

} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'status_code' => 500,
        'message' => 'Application error',
        'error' => $e->getMessage()
    ]);
}