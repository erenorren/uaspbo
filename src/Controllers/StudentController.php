<?php

namespace App\Controllers;

use App\Core\Controller;
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

    public function index(): void
    {
        try {
            $filters = $this->getQueryParams();
            $students = $this->studentService->getAllStudents($filters);

            $data = array_map(fn($student) => $student->toArray(), $students);

            ApiResponseBuilder::success($data, 'Siswa berhasil diambil')
                ->addMeta('total', count($data))
                ->send();

        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    public function show(int $id): void
    {
        try {
            $student = $this->studentService->getStudentById($id);
            ApiResponseBuilder::success($student->toArray(), 'Siswa berhasil diambil')->send();

        } catch (NotFoundException $e) {
            ApiResponseBuilder::notFound($e->getMessage())->send();
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    public function store(): void
    {
        try {
            $data = $this->getJsonInput();
            $student = $this->studentService->createStudent($data);

            ApiResponseBuilder::created($student->toArray(), 'Siswa berhasil dibuat')->send();

        } catch (ValidationException $e) {
            ApiResponseBuilder::validationError($e->getErrors())->send();
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    public function update(int $id): void
    {
        try {
            $data = $this->getJsonInput();
            $student = $this->studentService->updateStudent($id, $data);

            ApiResponseBuilder::success($student->toArray(), 'Siswa berhasil diperbarui')->send();

        } catch (NotFoundException $e) {
            ApiResponseBuilder::notFound($e->getMessage())->send();
        } catch (ValidationException $e) {
            ApiResponseBuilder::validationError($e->getErrors())->send();
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    public function destroy(int $id): void
    {
        try {
            $this->studentService->deleteStudent($id);
            ApiResponseBuilder::success(null, 'Siswa berhasil dihapus')->send();

        } catch (NotFoundException $e) {
            ApiResponseBuilder::notFound($e->getMessage())->send();
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }
}