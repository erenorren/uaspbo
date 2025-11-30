<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
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

    public function index(Request $request): void
    {
        try {
            $filters = $request->getQueryParams();
            $courses = $this->courseService->getAllCourses($filters);

            $data = array_map(fn($course) => $course->toArray(), $courses);

            ApiResponseBuilder::success($data, 'Courses retrieved successfully')
                ->addMeta('total', count($data))
                ->send();

        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    public function show(Request $request): void
{
    try {
        // Get ID dari URL parameter
        $id = (int) $request->getQueryParams()['id'] ?? 0;
        
        if ($id <= 0) {
            ApiResponseBuilder::error('Invalid course ID', 400)->send();
            return;
        }

        $course = $this->courseService->getCourseById($id);
        
        ApiResponseBuilder::success($course->toArray(), 'Course retrieved successfully')
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
            $course = $this->courseService->createCourse($data);

            ApiResponseBuilder::created($course->toArray(), 'Course created successfully')
                ->send();

        } catch (ValidationException $e) {
            ApiResponseBuilder::validationError($e->getErrors())->send();
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    public function update(Request $request, int $id): void
    {
        try {
            $data = $request->getBodyParams();
            $course = $this->courseService->updateCourse($id, $data);

            ApiResponseBuilder::success($course->toArray(), 'Course updated successfully')
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
            $this->courseService->deleteCourse($id);

            ApiResponseBuilder::success(null, 'Course deleted successfully')
                ->send();

        } catch (NotFoundException $e) {
            ApiResponseBuilder::notFound($e->getMessage())->send();
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    public function publish(Request $request, int $id): void
    {
        try {
            $course = $this->courseService->publishCourse($id);

            ApiResponseBuilder::success($course->toArray(), 'Course published successfully')
                ->send();

        } catch (NotFoundException $e) {
            ApiResponseBuilder::notFound($e->getMessage())->send();
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    public function unpublish(Request $request, int $id): void
    {
        try {
            $course = $this->courseService->unpublishCourse($id);

            ApiResponseBuilder::success($course->toArray(), 'Course unpublished successfully')
                ->send();

        } catch (NotFoundException $e) {
            ApiResponseBuilder::notFound($e->getMessage())->send();
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }
}