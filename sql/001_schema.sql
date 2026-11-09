-- Databázové schéma
-- Výběr databáze
-- Jednorázový import
-- DDL operace
SET NAMES utf8mb4;

-- Uživatelské účty
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(254) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    contact_name VARCHAR(120) NOT NULL,
    company_name VARCHAR(180) NOT NULL,
    company_number VARCHAR(32) NULL,
    phone VARCHAR(32) NULL,
    role VARCHAR(16) NOT NULL DEFAULT 'customer',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_users_role CHECK (role IN ('customer', 'admin')),
    CONSTRAINT chk_users_active CHECK (is_active IN (0, 1))
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- Kategorie produktů
CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- Katalog produktů
CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NOT NULL,
    sku VARCHAR(64) NOT NULL UNIQUE,
    name VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    color VARCHAR(60) NOT NULL,
    environment VARCHAR(16) NOT NULL DEFAULT 'indoor',
    image_path VARCHAR(255) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id)
        REFERENCES categories(id) ON DELETE RESTRICT,
    CONSTRAINT chk_products_price CHECK (unit_price >= 0),
    CONSTRAINT chk_products_environment CHECK (environment IN ('indoor', 'outdoor', 'both')),
    CONSTRAINT chk_products_active CHECK (is_active IN (0, 1)),
    INDEX idx_products_catalog (category_id, is_active)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- Produktové tagy
CREATE TABLE tags (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- Vazby tagů
CREATE TABLE product_tags (
    product_id BIGINT UNSIGNED NOT NULL,
    tag_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (product_id, tag_id),
    CONSTRAINT fk_product_tags_product FOREIGN KEY (product_id)
        REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_product_tags_tag FOREIGN KEY (tag_id)
        REFERENCES tags(id) ON DELETE CASCADE,
    INDEX idx_product_tags_tag (tag_id)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- Projekty a šablony
CREATE TABLE projects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    kind VARCHAR(16) NOT NULL DEFAULT 'customer',
    status VARCHAR(16) NOT NULL DEFAULT 'active',
    image_path VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_projects_owner FOREIGN KEY (owner_id)
        REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT chk_projects_kind CHECK (kind IN ('customer', 'template')),
    CONSTRAINT chk_projects_status CHECK (status IN ('active', 'archived')),
    INDEX idx_projects_owner_status (owner_id, status)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- Položky projektů
CREATE TABLE project_items (
    project_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (project_id, product_id),
    CONSTRAINT fk_project_items_project FOREIGN KEY (project_id)
        REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_project_items_product FOREIGN KEY (product_id)
        REFERENCES products(id) ON DELETE RESTRICT,
    CONSTRAINT chk_project_items_quantity CHECK (quantity BETWEEN 1 AND 9999)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- Cenové nabídky
CREATE TABLE quotes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    submission_token CHAR(36) NOT NULL UNIQUE,
    project_name_snapshot VARCHAR(180) NOT NULL,
    contact_name_snapshot VARCHAR(120) NOT NULL,
    company_name_snapshot VARCHAR(180) NOT NULL,
    company_number_snapshot VARCHAR(32) NULL,
    email_snapshot VARCHAR(254) NOT NULL,
    phone_snapshot VARCHAR(32) NULL,
    status VARCHAR(16) NOT NULL DEFAULT 'new',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_quotes_project FOREIGN KEY (project_id)
        REFERENCES projects(id) ON DELETE RESTRICT,
    CONSTRAINT chk_quotes_status CHECK (status IN ('new', 'contacted', 'closed')),
    INDEX idx_quotes_status_created (status, created_at)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- Položky nabídek
CREATE TABLE quote_items (
    quote_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    sku_snapshot VARCHAR(64) NOT NULL,
    product_name_snapshot VARCHAR(180) NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    discount_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (quote_id, product_id),
    CONSTRAINT fk_quote_items_quote FOREIGN KEY (quote_id)
        REFERENCES quotes(id) ON DELETE RESTRICT,
    CONSTRAINT fk_quote_items_product FOREIGN KEY (product_id)
        REFERENCES products(id) ON DELETE RESTRICT,
    CONSTRAINT chk_quote_items_price CHECK (unit_price >= 0),
    CONSTRAINT chk_quote_items_quantity CHECK (quantity BETWEEN 1 AND 9999),
    CONSTRAINT chk_quote_items_discount CHECK (discount_percent BETWEEN 0 AND 100)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
