<?php

namespace App\Interfaces;

interface Publishable
{
    public function publish(): void;
    public function unpublish(): void;
    public function isPublished(): bool;
}