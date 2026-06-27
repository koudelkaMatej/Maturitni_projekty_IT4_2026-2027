<?php

class Teacher implements JsonSerializable
{
    public readonly int $teacher_id;
    public readonly string $teacher_name;
    public readonly ?string $teacher_code;

    public function __construct(array $row)
    {
        $this->teacher_id = (int)($row["teacher_id"] ?? 0);
        $this->teacher_name = $row["teacher_name"] ?? "";
        $this->teacher_code = $row["teacher_code"] ?? null;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
