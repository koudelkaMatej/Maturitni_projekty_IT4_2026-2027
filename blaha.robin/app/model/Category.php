<?php

class Category implements JsonSerializable
{
    public readonly int $category_id;
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
