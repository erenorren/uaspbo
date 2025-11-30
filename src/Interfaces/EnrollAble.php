<?php

namespace App\Interfaces;

interface Enrollable
{
    public function canEnroll(int $currentEnrolledCount): bool;
}