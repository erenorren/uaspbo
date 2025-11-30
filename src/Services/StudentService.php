<?php

namespace App\Services;

use App\Repositories\StudentRepository;
use App\Models\Student;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;
use App\Exceptions\BusinessException;
use App\Core\Database;

class StudentService
{
    private StudentRepository $studentRepository;

    public function __construct(StudentRepository $studentRepository)
    {
        $this->studentRepository = $studentRepository;
    }

    public function isEmailExists(string $email): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }

    public function createStudent(array $data): Student
    {
        // Validasi required fields - Bahasa Indonesia
        if (!isset($data['email']) || empty($data['email'])) {
            throw new ValidationException('Validasi gagal', ['email' => 'Email wajib diisi']);
        }

        if (!isset($data['name']) || empty($data['name'])) {
            throw new ValidationException('Validasi gagal', ['name' => 'Nama wajib diisi']);
        }

        if (!isset($data['password']) || empty($data['password'])) {
            throw new ValidationException('Validasi gagal', ['password' => 'Password wajib diisi']);
        }

        // Check if email already exists
        if ($this->isEmailExists($data['email'])) {
            throw new BusinessException("Email sudah digunakan");
        }

        $data['role'] = 'student';
        $student = new Student($data);

        if (!$student->validate()) {
            throw new ValidationException('Validasi gagal', $student->getErrors());
        }

        $this->studentRepository->save($student);

        return $student;
    }

    public function updateStudent(int $id, array $data): Student
    {
        $student = $this->studentRepository->findById($id);

        if (!$student) {
            throw new NotFoundException("Siswa dengan ID {$id} tidak ditemukan");
        }

        // Check if email is being changed and if it already exists
        if (isset($data['email']) && $data['email'] !== $student->getEmail()) {
            if ($this->isEmailExists($data['email'])) {
                throw new BusinessException("Email sudah digunakan");
            }
        }

        // Update properties
        $updatedStudent = new Student(array_merge($student->toArray(), $data));
        $updatedStudent->setId($id);

        if (!$updatedStudent->validate()) {
            throw new ValidationException('Validasi gagal', $updatedStudent->getErrors());
        }

        $this->studentRepository->save($updatedStudent);

        return $updatedStudent;
    }

    public function deleteStudent(int $id): bool
    {
        $student = $this->studentRepository->findById($id);

        if (!$student) {
            throw new NotFoundException("Siswa dengan ID {$id} tidak ditemukan");
        }

        return $this->studentRepository->delete($id);
    }

    public function getStudentById(int $id): Student
    {
        $student = $this->studentRepository->findById($id);

        if (!$student) {
            throw new NotFoundException("Siswa dengan ID {$id} tidak ditemukan");
        }

        return $student;
    }

    public function getAllStudents(array $filters = []): array
    {
        return $this->studentRepository->findAll($filters);
    }
}