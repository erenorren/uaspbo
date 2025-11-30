<?php

namespace App\Interfaces;

interface EnrollAble
{
    public function canEnroll(int $currentEnrolled): bool;
    public function enroll(int $studentId): bool;
}