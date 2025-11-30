<?php

namespace App\Models;

use App\Core\Model;
use App\Interfaces\Authenticatable;
use App\Traits\Validatable;

abstract class User extends Model implements Authenticatable
{
    use Validatable;

    protected string $email;
    protected string $password;
    protected string $name;
    protected string $role;

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->fill($data);
        }
    }

    protected function fill(array $data): void
    {
        $this->email = $data['email'] ?? '';
        $this->name = $data['name'] ?? '';
        $this->role = $data['role'] ?? '';

        if (isset($data['password'])) {
            $this->setPassword($data['password']);
        }
    }

    public function setPassword(string $password): void
    {
        $this->password = password_hash($password, PASSWORD_BCRYPT);
    }

    public function authenticate(string $email, string $password): bool
    {
        return $this->email === $email && password_verify($password, $this->password);
    }

    public function validate(): bool
    {
        $this->clearErrors();

        $this->validateRequired('email', $this->email, 'Email');
        $this->validateEmail('email', $this->email);
        $this->validateRequired('name', $this->name, 'Name');
        $this->validateMinLength('password', $this->password, 6, 'Password');

        return !$this->hasErrors();
    }

    abstract public function getRole(): string;

    public function getEmail(): string { return $this->email; }
    public function getName(): string { return $this->name; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'role' => $this->getRole(),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s')
        ];
    }
}