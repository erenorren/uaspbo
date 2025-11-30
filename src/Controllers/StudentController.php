<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\StudentService;
use App\Builders\ApiResponseBuilder;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;

class StudentController extends Controller
{
    private StudentService $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    public function index(Request $request): void
    {
        try {
            $students = $this->studentService->getAllStudents();
            $data = array_map(fn($student) => $student->toArray(), $students);

            ApiResponseBuilder::success($data, 'Students retrieved successfully')
                ->addMeta('total', count($data))
                ->send();

        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    public function show(Request $request, int $id): void
    {
        try {
            $student = $this->studentService->getStudentById($id);
            
            ApiResponseBuilder::success($student->toArray(), 'Student retrieved successfully')
                ->send();

        } catch (NotFoundException $e) {
            ApiResponseBuilder::notFound($e->getMessage())->send();
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    public function store(Request $request): void
    {
        try {
            $data = $request->getBodyParams();
            $student = $this->studentService->createStudent($data);

            ApiResponseBuilder::created($student->toArray(), 'Student created successfully')
                ->send();

        } catch (ValidationException $e) {
            ApiResponseBuilder::validationError($e->getErrors())->send();
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    public function update( Request $request, int $id): void
    {
        try {
            $data = $this->getJsonInput();
            $student = $this->studentService->updateStudent($id, $data);

            ApiResponseBuilder::success($student->toArray(), 'Student updated successfully')
                ->send();

        } catch (NotFoundException $e) {
            ApiResponseBuilder::notFound($e->getMessage())->send();
        } catch (ValidationException $e) {
            ApiResponseBuilder::validationError($e->getErrors())->send();
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    public function destroy(Request $request, int $id): void
    {
        try {
            $this->studentService->deleteStudent($id);

            ApiResponseBuilder::success(null, 'Student deleted successfully')
                ->send();

        } catch (NotFoundException $e) {
            ApiResponseBuilder::notFound($e->getMessage())->send();
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }
}