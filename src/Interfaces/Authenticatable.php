<?php

namespace App\Interfaces;

interface Authenticatable
{
    public function authenticate(string $email, string $password): bool;
    public function getRole(): string;
}