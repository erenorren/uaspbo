<?php

require_once __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);
set_exception_handler(function($e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'status_code' => 500,
        'message' => 'Internal server error',
        'error' => $e->getMessage()
    ]);
});

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

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

$courseRepository = new CourseRepository();
$studentRepository = new StudentRepository();
$enrollmentRepository = new EnrollmentRepository();
$instructorRepository = new InstructorRepository();

$courseService = new CourseService($courseRepository);
$studentService = new StudentService($studentRepository);
$enrollmentService = new EnrollmentService(
    $enrollmentRepository,
    $courseRepository,
    $studentRepository
);

$courseController = new CourseController($courseService);
$studentController = new StudentController($studentService);
$enrollmentController = new EnrollmentController($enrollmentService);

$router = new Router();

$router->get('/courses', [$courseController, 'index']);
$router->get('/courses/:id', [$courseController, 'show']);
$router->post('/courses', [$courseController, 'store']);
$router->put('/courses/:id', [$courseController, 'update']);
$router->delete('/courses/:id', [$courseController, 'destroy']);
$router->put('/courses/:id/publish', [$courseController, 'publish']);
$router->put('/courses/:id/unpublish', [$courseController, 'unpublish']);

$router->get('/students', [$studentController, 'index']);
$router->get('/students/:id', [$studentController, 'show']);
$router->post('/students', [$studentController, 'store']);
$router->put('/students/:id', [$studentController, 'update']);
$router->delete('/students/:id', [$studentController, 'destroy']);

$router->post('/enrollments', [$enrollmentController, 'store']);
$router->get('/students/:id/enrollments', [$enrollmentController, 'studentEnrollments']);
$router->put('/enrollments/:id/complete', [$enrollmentController, 'complete']);
$router->put('/enrollments/:id/cancel', [$enrollmentController, 'cancel']);

$router->dispatch();