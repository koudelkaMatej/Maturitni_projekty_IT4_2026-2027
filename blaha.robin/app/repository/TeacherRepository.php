<?php

class TeacherRepository extends Repository
{
    public function getAllTeachers(): array
    {
        $rows = $this->database->select("SELECT * FROM teachers");
        return array_map(fn($r) => new Teacher($r), $rows);
    }

    public function getTeacherById($teacher_id): ?Teacher
    {
        $row = $this->database->selectOne(
            "SELECT * FROM teachers WHERE teacher_id = :teacher_id",
            [":teacher_id" => $teacher_id]
        );
        return $row ? new Teacher($row) : null;
    }

    public function addTeacher(string $teacher_code, string $teacher_name): false|string
    {
        return $this->database->insert(
            "INSERT INTO teachers (teacher_code, teacher_name) VALUES (:teacher_code, :teacher_name)",
            [":teacher_code" => $teacher_code, ":teacher_name" => $teacher_name]
        );
    }

    public function updateTeacher(int $teacher_id, string $teacher_code, string $teacher_name): void
    {
        $this->database->update(
            "UPDATE teachers SET teacher_code = :teacher_code, teacher_name = :teacher_name WHERE teacher_id = :teacher_id",
            [":teacher_id" => $teacher_id, ":teacher_code" => $teacher_code, ":teacher_name" => $teacher_name]
        );
    }

    public function deleteTeacher(int $teacher_id): void
    {
        $this->database->delete(
            "DELETE FROM teachers WHERE teacher_id = :teacher_id",
            [":teacher_id" => $teacher_id]
        );
    }
}
