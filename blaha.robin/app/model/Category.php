<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Data transfer object representing a ticket category.
 */
class Category implements JsonSerializable
{
    /** Auto-increment primary key. */
    public readonly int $category_id;

    /** Human-readable category name. */
    public readonly string $category_name;

    public function __construct(array $row)
    {
        $this->category_id = (int)($row["category_id"] ?? 0);
        $this->category_name = $row["category_name"] ?? "";
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
