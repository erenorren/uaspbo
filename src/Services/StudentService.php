<?php

namespace App\Services;

use App\Repositories\StudentRepository;
use App\Models\Student;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;

class StudentService
{
    private StudentRepository $studentRepository;

    public function __construct(StudentRepository $studentRepository)
    {
        $this->studentRepository = $studentRepository;
    }

    public function createStudent(array $data): Student
    {
        $student = new Student($data);

        if (!$student->validate()) {
            throw new ValidationException('Validation failed', $student->getErrors());
        }

        $this->studentRepository->save($student);

        return $student;
    }

    public function updateStudent(int $id, array $data): Student
    {
        $student = $this->studentRepository->findById($id);

        if (!$student) {
            throw new NotFoundException("Student with ID {$id} not found");
        }

        $updatedStudent = new Student(array_merge($student->toArray(), $data));
        $updatedStudent->setId($id);

        if (!$updatedStudent->validate()) {
            throw new ValidationException('Validation failed', $updatedStudent->getErrors());
        }

        $this->studentRepository->save($updatedStudent);

        return $updatedStudent;
    }

    public function deleteStudent(int $id): bool
    {
        $student = $this->studentRepository->findById($id);

        if (!$student) {
            throw new NotFoundException("Student with ID {$id} not found");
        }

        return $this->studentRepository->delete($id);
    }

    public function getStudentById(int $id): Student
    {
        $student = $this->studentRepository->findById($id);

        if (!$student) {
            throw new NotFoundException("Student with ID {$id} not found");
        }

        return $student;
    }

    public function getAllStudents(): array
    {
        return $this->studentRepository->findAll();
    }
}