<?php

class TeacherRepository extends Repository
{
    public function getAllTeachers(): array
    {
        return $this->database->select(
            "SELECT * FROM teachers"
        );
    }

    public function getTeacherById($teacher_id): array
    {
        return $this->database->selectOne(
            "SELECT * FROM teachers WHERE teacher_id = :teacher_id",
            [
                ":teacher_id" => $teacher_id,
            ]
        );
    }
}