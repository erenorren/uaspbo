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
            'max_students' => 15,
            'status' => 'draft'
        ];

        return new Course(array_merge($defaults, $data));
    }

    public static function createPublishedCourse(array $data): Course
    {
        $defaults = [
            'max_students' => 25,
            'status' => 'published'
        ];

        return new Course(array_merge($defaults, $data));
    }
}