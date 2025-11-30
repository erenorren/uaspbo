<?php

namespace App\Core;

use App\Traits\Timestampable;

abstract class Model
{
    use Timestampable;

    protected ?int $id = null;

    // Constructor yang akan diwarisi oleh semua child classes
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->fill($data);
        }
        
        // Initialize timestamps
        $this->updateTimestamps();
    }

    // Abstract method yang harus diimplementasi child classes
    abstract protected function fill(array $data): void;
    abstract public function validate(): bool;
    abstract public function toArray(): array;
    abstract protected static function getTableName(): string;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $this->updateTimestamps();

        if ($this->id === null) {
            return $this->insert();
        }

        return $this->update();
    }

    abstract protected function insert(): bool;
    abstract protected function update(): bool;
    abstract public function delete(): bool;

    public static function find(int $id): ?static
    {
        $db = Database::getInstance()->getConnection();
        $tableName = static::getTableName();
        
        $stmt = $db->prepare("SELECT * FROM {$tableName} WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        $model = new static($data);
        $model->setId($id);
        
        if (isset($data['created_at'])) {
            $model->setCreatedAt(new \DateTime($data['created_at']));
        }
        
        if (isset($data['updated_at'])) {
            $model->setUpdatedAt(new \DateTime($data['updated_at']));
        }

        return $model;
    }

    public static function all(): array
    {
        $db = Database::getInstance()->getConnection();
        $tableName = static::getTableName();
        
        $stmt = $db->prepare("SELECT * FROM {$tableName}");
        $stmt->execute();
        
        $models = [];
        while ($data = $stmt->fetch()) {
            $model = new static($data);
            $model->setId($data['id']);
            
            if (isset($data['created_at'])) {
                $model->setCreatedAt(new \DateTime($data['created_at']));
            }
            
            if (isset($data['updated_at'])) {
                $model->setUpdatedAt(new \DateTime($data['updated_at']));
            }
            
            $models[] = $model;
        }

        return $models;
    }

    public static function where(string $field, $value): array
    {
        $db = Database::getInstance()->getConnection();
        $tableName = static::getTableName();
        
        $stmt = $db->prepare("SELECT * FROM {$tableName} WHERE {$field} = ?");
        $stmt->execute([$value]);
        
        $models = [];
        while ($data = $stmt->fetch()) {
            $model = new static($data);
            $model->setId($data['id']);
            
            if (isset($data['created_at'])) {
                $model->setCreatedAt(new \DateTime($data['created_at']));
            }
            
            if (isset($data['updated_at'])) {
                $model->setUpdatedAt(new \DateTime($data['updated_at']));
            }
            
            $models[] = $model;
        }

        return $models;
    }
}