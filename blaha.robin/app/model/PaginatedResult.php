<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Wraps a paginated set of items together with pagination metadata.
 */
class PaginatedResult implements JsonSerializable
{
    /** The items on the current page. */
    public readonly array $items;

    /** Total number of items across all pages. */
    public readonly int $total;

    /** Current page number (1-based). */
    public readonly int $page;

    /** Number of items per page. */
    public readonly int $perPage;

    /** Last available page number. */
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
