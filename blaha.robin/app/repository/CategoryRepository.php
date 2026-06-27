<?php

class CategoryRepository extends Repository
{
    public function getCategoryById($category_id): ?Category
    {
        $row = $this->database->selectOne(
            "SELECT * FROM categories WHERE category_id = :category_id",
            [":category_id" => $category_id]
        );
        return $row ? new Category($row) : null;
    }

    public function getAllCategories(): array
    {
        $rows = $this->database->select("SELECT * FROM categories");
        return array_map(fn($r) => new Category($r), $rows);
    }

    public function addCategory($category_name): false|string
    {
        return $this->database->insert(
            "INSERT INTO categories (category_name) VALUES (:category_name)",
            [":category_name" => $category_name]
        );
    }

    public function updateCategory($category_id, $category_name): void
    {
        $this->database->update(
            "UPDATE categories SET category_name = :category_name WHERE category_id = :category_id",
            [":category_id" => $category_id, ":category_name" => $category_name]
        );
    }

    public function deleteCategory($category_id): void
    {
        $this->database->delete(
            "DELETE FROM categories WHERE category_id = :category_id",
            [":category_id" => $category_id]
        );
    }
}
