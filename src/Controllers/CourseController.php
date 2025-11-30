<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\CourseService;
use App\Builders\ApiResponseBuilder;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;
use App\Exceptions\BusinessException;

class CourseController extends Controller
{
    private CourseService $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    public function index(): void
    {
        try {
            $filters = $this->getQueryParams();
            $courses = $this->courseService->getAllCourses($filters);

            $data = array_map(fn($course) => $course->toArray(), $courses);

            ApiResponseBuilder::success($data, 'Kursus berhasil diambil')
                ->addMeta('total', count($data))
                ->send();

        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    public function show(int $id): void
    {
        try {
            $course = $this->courseService->getCourseById($id);
            ApiResponseBuilder::success($course->toArray(), 'Kursus berhasil diambil')->send();

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
            $course = $this->courseService->createCourse($data);

            ApiResponseBuilder::created($course->toArray(), 'Kursus berhasil diambil')->send();

        } catch (ValidationException $e) {
            ApiResponseBuilder::validationError($e->getErrors())->send();
        } catch (BusinessException $e) {
            ApiResponseBuilder::error($e->getMessage(), $e->getCode())->send();
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    public function update(int $id): void
    {
        try {
            $data = $this->getJsonInput();
            $course = $this->courseService->updateCourse($id, $data);

            ApiResponseBuilder::success($course->toArray(), 'Kursus berhasil diubah')->send();

        } catch (NotFoundException $e) {
            ApiResponseBuilder::notFound($e->getMessage())->send();
        } catch (ValidationException $e) {
            ApiResponseBuilder::validationError($e->getErrors())->send();
        } catch (BusinessException $e) {
            ApiResponseBuilder::error($e->getMessage(), $e->getCode())->send();
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    public function destroy(int $id): void
    {
        try {
            $this->courseService->deleteCourse($id);
            ApiResponseBuilder::success(null, 'Kursus berhasil dihapus')->send();

        } catch (NotFoundException $e) {
            ApiResponseBuilder::notFound($e->getMessage())->send();
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    public function publish(int $id): void
    {
        try {
            $course = $this->courseService->publishCourse($id);
            ApiResponseBuilder::success($course->toArray(), 'Kursus berhasil dipublish')->send();

        } catch (NotFoundException $e) {
            ApiResponseBuilder::notFound($e->getMessage())->send();
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    public function unpublish(int $id): void
    {
        try {
            $course = $this->courseService->unpublishCourse($id);
            ApiResponseBuilder::success($course->toArray(), 'Kursus berhasil unpublish')->send();

        } catch (NotFoundException $e) {
            ApiResponseBuilder::notFound($e->getMessage())->send();
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }
}