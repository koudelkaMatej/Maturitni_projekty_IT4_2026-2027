<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

class Work implements JsonSerializable
{
    public readonly int $work_id;
    public readonly int $work_ticket;
    public readonly int $work_user;
    public readonly int $work_minutes;
    public readonly string $work_description;
    public readonly ?string $teacher_name;

    public function __construct(array $row)
    {
        $this->work_id = (int)($row["work_id"] ?? 0);
        $this->work_ticket = (int)($row["work_ticket"] ?? 0);
        $this->work_user = (int)($row["work_user"] ?? 0);
        $this->work_minutes = (int)($row["work_minutes"] ?? 0);
        $this->work_description = $row["work_description"] ?? "";
        $this->teacher_name = $row["teacher_name"] ?? null;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
