<?php

class PaginatedResult implements JsonSerializable
{
    public readonly array $items;
    public readonly int $total;
    public readonly int $page;
    public readonly int $perPage;
    public readonly int $lastPage;

    public function __construct(array $items, int $total, int $page, int $perPage)
    {
        $this->items = $items;
        $this->total = $total;
        $this->page = $page;
        $this->perPage = $perPage;
        $this->lastPage = max(1, (int)ceil($total / $perPage));
    }

    public function jsonSerialize(): array
    {
        return [
            "items" => $this->items,
            "total" => $this->total,
            "page" => $this->page,
            "perPage" => $this->perPage,
            "lastPage" => $this->lastPage,
        ];
    }
}
