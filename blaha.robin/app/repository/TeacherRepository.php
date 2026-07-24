<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Repository for teacher record CRUD.
 */
class TeacherRepository extends Repository
{
    /**
     * Get all teachers.
     *
     * @return Teacher[]
     */
    public function getAllTeachers(): array
    {
        $rows = $this->database->select("SELECT * FROM teachers");
        return array_map(fn($r) => new Teacher($r), $rows);
    }

    /**
     * Get a single teacher by ID.
     *
     * @param int $teacher_id
     * @return ?Teacher
     */
    public function getTeacherById($teacher_id): ?Teacher
    {
        $row = $this->database->selectOne(
            "SELECT * FROM teachers WHERE teacher_id = :teacher_id",
            [":teacher_id" => $teacher_id]
        );
        return $row ? new Teacher($row) : null;
    }

    /**
     * Create a new teacher.
     *
     * @param string $teacher_name Full name.
     * @return false|string The new teacher ID, or false on failure.
     */
    public function addTeacher(string $teacher_name): false|string
    {
        return $this->database->insert(
            "INSERT INTO teachers (teacher_name) VALUES (:teacher_name)",
            [":teacher_name" => $teacher_name]
        );
    }

    /**
     * Update a teacher's name.
     *
     * @param int    $teacher_id
     * @param string $teacher_name
     */
    public function updateTeacher(int $teacher_id, string $teacher_name): void
    {
        $this->database->update(
            "UPDATE teachers SET teacher_name = :teacher_name WHERE teacher_id = :teacher_id",
            [":teacher_id" => $teacher_id, ":teacher_name" => $teacher_name]
        );
    }

    /**
     * Delete a teacher. The linked user account (if any) is cascade-deleted.
     * Tickets reported by this teacher will have ticket_origin set to NULL.
     *
     * @param int $teacher_id
     */
    public function deleteTeacher(int $teacher_id): void
    {
        $this->database->delete(
            "DELETE FROM teachers WHERE teacher_id = :teacher_id",
            [":teacher_id" => $teacher_id]
        );
    }
}
