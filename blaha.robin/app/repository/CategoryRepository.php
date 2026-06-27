<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Repository for ticket category CRUD.
 */
class CategoryRepository extends Repository
{
    /**
     * Get a single category by ID.
     *
     * @param int $category_id
     * @return ?Category
     */
    public function getCategoryById($category_id): ?Category
    {
        $row = $this->database->selectOne(
            "SELECT * FROM categories WHERE category_id = :category_id",
            [":category_id" => $category_id]
        );
        return $row ? new Category($row) : null;
    }

    /**
     * Get all categories.
     *
     * @return Category[]
     */
    public function getAllCategories(): array
    {
        $rows = $this->database->select("SELECT * FROM categories");
        return array_map(fn($r) => new Category($r), $rows);
    }

    /**
     * Create a new category.
     *
     * @param string $category_name
     * @return false|string The new category ID, or false on failure.
     */
    public function addCategory($category_name): false|string
    {
        return $this->database->insert(
            "INSERT INTO categories (category_name) VALUES (:category_name)",
            [":category_name" => $category_name]
        );
    }

    /**
     * Update a category's name.
     *
     * @param int    $category_id
     * @param string $category_name
     */
    public function updateCategory($category_id, $category_name): void
    {
        $this->database->update(
            "UPDATE categories SET category_name = :category_name WHERE category_id = :category_id",
            [":category_id" => $category_id, ":category_name" => $category_name]
        );
    }

    /**
     * Delete a category. Tickets referencing it will have ticket_category set to NULL.
     *
     * @param int $category_id
     */
    public function deleteCategory($category_id): void
    {
        $this->database->delete(
            "DELETE FROM categories WHERE category_id = :category_id",
            [":category_id" => $category_id]
        );
    }
}
