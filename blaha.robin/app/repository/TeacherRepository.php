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
}
