<?php

namespace App\Factories;

use App\Models\Course;

class CourseFactory
{
    public static function createBasicCourse(array $data): Course
    {
        $defaults = [
            'max_students' => 30,
            'status' => 'draft'
        ];

        return new Course(array_merge($defaults, $data));
    }

    public static function createPremiumCourse(array $data): Course
    {
        $defaults = [
            'max_students' => 10,
            'status' => 'draft'
        ];

        return new Course(array_merge($defaults, $data));
    }

    public static function createWorkshopCourse(array $data): Course
    {
        $defaults = [
            'max_students' => 20,
            'status' => 'draft'
        ];

        return new Course(array_merge($defaults, $data));
    }
}