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

    // Constructor sudah diwarisi dari Model

    abstract public function getRole(): string;

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
        $this->validateMinLength('name', $this->name, 2, 'Name');

        return !$this->hasErrors();
    }

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

    protected static function getTableName(): string
    {
        return 'users';
    }

    protected function insert(): bool
    {
        $db = \App\Core\Database::getInstance()->getConnection();
        $sql = "INSERT INTO users (email, password, name, role, created_at) VALUES (?, ?, ?, ?, ?)";

        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $this->email,
            $this->password,
            $this->name,
            $this->getRole(),
            $this->createdAt->format('Y-m-d H:i:s')
        ]);

        if ($result) {
            $this->id = (int)$db->lastInsertId();
        }

        return $result;
    }

    protected function update(): bool
    {
        $db = \App\Core\Database::getInstance()->getConnection();
        $sql = "UPDATE users SET email=?, name=?, updated_at=? WHERE id=?";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $this->email,
            $this->name,
            $this->updatedAt->format('Y-m-d H:i:s'),
            $this->id
        ]);
    }

    public function delete(): bool
    {
        $db = \App\Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$this->id]);
    }
}