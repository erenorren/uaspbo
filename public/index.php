<?php

// Try to load composer autoload, fallback to manual autoload
$autoloadPaths = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../autoload.php'
];

$autoloadLoaded = false;
foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $autoloadLoaded = true;
        break;
    }
}

// If no autoload file found, use manual autoloader
if (!$autoloadLoaded) {
    spl_autoload_register(function ($class) {
        $prefix = 'App\\';
        $base_dir = __DIR__ . '/../src/';
        
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        
        if (file_exists($file)) {
            require $file;
        }
    });
}

// Use statements harus di luar block try-catch
use App\Core\Router;
use App\Core\Response;
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

error_reporting(E_ALL);
ini_set('display_errors', 1);

set_exception_handler(function($e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'status_code' => 500,
        'message' => 'Internal server error',
        'error' => $e->getMessage()
    ]);
});

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Initialize repositories
    $courseRepository = new CourseRepository();
    $studentRepository = new StudentRepository();
    $enrollmentRepository = new EnrollmentRepository();
    $instructorRepository = new InstructorRepository();

    // Initialize services
    $courseService = new CourseService($courseRepository);
    $studentService = new StudentService($studentRepository);
    $enrollmentService = new EnrollmentService(
        $enrollmentRepository,
        $courseRepository,
        $studentRepository
    );

    // Initialize controllers
    $courseController = new CourseController($courseService);
    $studentController = new StudentController($studentService);
    $enrollmentController = new EnrollmentController($enrollmentService);

    // Initialize router
    $router = new Router();

    // Test route - untuk debugging
    $router->get('/', function($request) {
        $response = new Response();
        $response->json([
            'success' => true,
            'message' => 'E-Learning API is running!',
            'endpoints' => [
                'GET /courses' => 'Get all courses',
                'GET /courses/:id' => 'Get course by ID',
                'POST /courses' => 'Create new course',
                'PUT /courses/:id' => 'Update course',
                'DELETE /courses/:id' => 'Delete course',
                'GET /students' => 'Get all students',
                'POST /students' => 'Create new student',
                'POST /enrollments' => 'Enroll student in course'
            ]
        ]);
    });

    // Course routes
    $router->get('/courses', [$courseController, 'index']);
    $router->get('/courses/:id', [$courseController, 'show']);
    $router->post('/courses', [$courseController, 'store']);
    $router->put('/courses/:id', [$courseController, 'update']);
    $router->delete('/courses/:id', [$courseController, 'destroy']);
    $router->put('/courses/:id/publish', [$courseController, 'publish']);
    $router->put('/courses/:id/unpublish', [$courseController, 'unpublish']);

    // Student routes
    $router->get('/students', [$studentController, 'index']);
    $router->get('/students/:id', [$studentController, 'show']);
    $router->post('/students', [$studentController, 'store']);
    $router->put('/students/:id', [$studentController, 'update']);
    $router->delete('/students/:id', [$studentController, 'destroy']);

    // Enrollment routes
    $router->post('/enrollments', [$enrollmentController, 'store']);
    $router->get('/students/:id/enrollments', [$enrollmentController, 'studentEnrollments']);
    $router->put('/enrollments/:id/complete', [$enrollmentController, 'complete']);
    $router->put('/enrollments/:id/cancel', [$enrollmentController, 'cancel']);

    $router->dispatch();

} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'status_code' => 500,
        'message' => 'Application error',
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}