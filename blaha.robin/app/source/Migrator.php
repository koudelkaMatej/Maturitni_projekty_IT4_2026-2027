<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Auto-creates missing database tables, indexes, and foreign keys on boot.
 *
 * Checks for each required table individually and creates any that
 * are missing. Supports both MySQL and SQLite drivers.
 */
class Migrator
{
    private Database $db;

    private const ALL_TABLES = [
        'teachers', 'categories', 'priorities', 'rooms', 'users',
        'tickets', 'assignments', 'autoassigns', 'sessions', 'works', 'ticket_events',
    ];

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function migrate(): void
    {
        $missing = $this->getMissingTables();

        if (!empty($missing)) {
            if ($this->db->isSQLite()) {
                $this->db->exec("PRAGMA foreign_keys = OFF");
                $this->createTablesSQLite($missing);
                $this->addIndexesSQLite($missing);
                $this->db->exec("PRAGMA foreign_keys = ON");
            } else {
                $this->createTablesMySQL($missing);
                $this->addIndexesMySQL($missing);
                $this->ensureAutoIncrementMySQL($missing);
                $this->addForeignKeysMySQL($missing);
            }

            if (in_array('teachers', $missing) && in_array('users', $missing)) {
                $this->seedAdmin();
            }
        }

        $this->ensureUserAdminColumn();
    }

    private function getMissingTables(): array
    {
        $missing = [];
        if ($this->db->isSQLite()) {
            $existing = $this->db->select("SELECT name FROM sqlite_master WHERE type='table'");
            $existingNames = array_column($existing, 'name');
            foreach (self::ALL_TABLES as $table) {
                if (!in_array($table, $existingNames)) $missing[] = $table;
            }
        } else {
            foreach (self::ALL_TABLES as $table) {
                $row = $this->db->selectOne("SHOW TABLES LIKE '$table'");
                if (empty($row)) $missing[] = $table;
            }
        }
        return $missing;
    }

    private function execOrSkip(string $sql): void
    {
        try {
            $this->db->exec($sql);
        } catch (PDOException $e) {
        }
    }

    // ==================== SQLite ====================

    private function createTablesSQLite(array $tables): void
    {
        if (in_array('teachers', $tables)) {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS teachers (
                    teacher_id   INTEGER PRIMARY KEY AUTOINCREMENT,
                    teacher_name TEXT NOT NULL
                )
            ");
        }
        if (in_array('categories', $tables)) {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS categories (
                    category_id   INTEGER PRIMARY KEY AUTOINCREMENT,
                    category_name TEXT NOT NULL DEFAULT 'Nová kategorie'
                )
            ");
        }
        if (in_array('priorities', $tables)) {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS priorities (
                    priority_id     INTEGER PRIMARY KEY AUTOINCREMENT,
                    priority_name   TEXT NOT NULL DEFAULT 'Nová priorita',
                    priority_weight INTEGER NOT NULL DEFAULT 0,
                    priority_color  TEXT NOT NULL DEFAULT 'gray'
                )
            ");
        }
        if (in_array('rooms', $tables)) {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS rooms (
                    room_id   INTEGER PRIMARY KEY AUTOINCREMENT,
                    room_name TEXT NOT NULL DEFAULT 'Nová místnost'
                )
            ");
        }
        if (in_array('users', $tables)) {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS users (
                    user_id              INTEGER PRIMARY KEY,
                    user_username        TEXT NOT NULL UNIQUE,
                    user_password        TEXT NOT NULL,
                    user_change_password INTEGER NOT NULL DEFAULT 0,
                    user_admin           INTEGER NOT NULL DEFAULT 0,
                    FOREIGN KEY (user_id) REFERENCES teachers (teacher_id) ON DELETE CASCADE ON UPDATE CASCADE
                )
            ");
        }
        if (in_array('tickets', $tables)) {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS tickets (
                    ticket_id          INTEGER PRIMARY KEY AUTOINCREMENT,
                    ticket_creation    TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    ticket_origin      INTEGER DEFAULT NULL,
                    ticket_category    INTEGER DEFAULT NULL,
                    ticket_room        INTEGER DEFAULT NULL,
                    ticket_priority    INTEGER DEFAULT NULL,
                    ticket_deadline    TEXT DEFAULT NULL,
                    ticket_title       TEXT NOT NULL DEFAULT '',
                    ticket_description TEXT NOT NULL,
                    ticket_is_open     INTEGER NOT NULL DEFAULT 1,
                    FOREIGN KEY (ticket_category) REFERENCES categories (category_id) ON DELETE SET NULL ON UPDATE CASCADE,
                    FOREIGN KEY (ticket_origin) REFERENCES teachers (teacher_id) ON DELETE SET NULL ON UPDATE CASCADE,
                    FOREIGN KEY (ticket_priority) REFERENCES priorities (priority_id) ON DELETE SET NULL ON UPDATE CASCADE,
                    FOREIGN KEY (ticket_room) REFERENCES rooms (room_id) ON DELETE SET NULL ON UPDATE CASCADE
                )
            ");
        }
        if (in_array('assignments', $tables)) {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS assignments (
                    assignment_ticket   INTEGER NOT NULL,
                    assignment_user     INTEGER NOT NULL,
                    assignment_creation TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (assignment_ticket, assignment_user),
                    FOREIGN KEY (assignment_ticket) REFERENCES tickets (ticket_id) ON DELETE CASCADE ON UPDATE CASCADE,
                    FOREIGN KEY (assignment_user) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE
                )
            ");
        }
        if (in_array('autoassigns', $tables)) {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS autoassigns (
                    autoassign_user     INTEGER NOT NULL,
                    autoassign_category INTEGER NOT NULL,
                    PRIMARY KEY (autoassign_user, autoassign_category),
                    FOREIGN KEY (autoassign_category) REFERENCES categories (category_id) ON DELETE CASCADE ON UPDATE CASCADE,
                    FOREIGN KEY (autoassign_user) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE
                )
            ");
        }
        if (in_array('sessions', $tables)) {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS sessions (
                    session_id       INTEGER PRIMARY KEY AUTOINCREMENT,
                    session_user     INTEGER NOT NULL,
                    session_address  TEXT NOT NULL,
                    session_creation TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    session_last_use TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (session_user) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE
                )
            ");
        }
        if (in_array('works', $tables)) {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS works (
                    work_id          INTEGER PRIMARY KEY AUTOINCREMENT,
                    work_ticket      INTEGER NOT NULL,
                    work_user        INTEGER DEFAULT NULL,
                    work_minutes     INTEGER NOT NULL DEFAULT 0,
                    work_description TEXT NOT NULL DEFAULT '',
                    FOREIGN KEY (work_ticket) REFERENCES tickets (ticket_id) ON DELETE CASCADE ON UPDATE CASCADE,
                    FOREIGN KEY (work_user) REFERENCES users (user_id) ON DELETE SET NULL ON UPDATE CASCADE
                )
            ");
        }
        if (in_array('ticket_events', $tables)) {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS ticket_events (
                    event_id       INTEGER PRIMARY KEY AUTOINCREMENT,
                    event_ticket   INTEGER NOT NULL,
                    event_user     INTEGER DEFAULT NULL,
                    event_type     TEXT NOT NULL,
                    event_creation TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (event_ticket) REFERENCES tickets (ticket_id) ON DELETE CASCADE ON UPDATE CASCADE,
                    FOREIGN KEY (event_user) REFERENCES users (user_id) ON DELETE SET NULL ON UPDATE CASCADE
                )
            ");
        }
    }

    private function addIndexesSQLite(array $tables): void
    {
        $map = [
            'assignments' => ['idx_assignments_user ON assignments (assignment_user)'],
            'autoassigns' => ['idx_autoassigns_category ON autoassigns (autoassign_category)'],
            'sessions' => ['idx_sessions_user ON sessions (session_user)'],
            'tickets' => [
                'idx_tickets_category ON tickets (ticket_category)',
                'idx_tickets_origin ON tickets (ticket_origin)',
                'idx_tickets_priority ON tickets (ticket_priority)',
                'idx_tickets_room ON tickets (ticket_room)',
            ],
            'works' => [
                'idx_works_ticket ON works (work_ticket)',
                'idx_works_user ON works (work_user)',
            ],
            'ticket_events' => [
                'idx_ticket_events_ticket ON ticket_events (event_ticket)',
                'idx_ticket_events_user ON ticket_events (event_user)',
            ],
        ];
        foreach ($tables as $table) {
            if (isset($map[$table])) {
                foreach ($map[$table] as $idx) {
                    $this->execOrSkip("CREATE INDEX IF NOT EXISTS $idx");
                }
            }
        }
    }

    // ==================== MySQL ====================

    private function createTablesMySQL(array $tables): void
    {
        if (in_array('teachers', $tables)) {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS teachers (
                    teacher_id   int(11)      NOT NULL AUTO_INCREMENT,
                    teacher_name varchar(255) NOT NULL,
                    PRIMARY KEY (teacher_id)
                ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_czech_ci
            ");
        }
        if (in_array('categories', $tables)) {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS categories (
                    category_id   int(11)      NOT NULL AUTO_INCREMENT,
                    category_name varchar(255) NOT NULL DEFAULT 'Nová kategorie',
                    PRIMARY KEY (category_id)
                ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_czech_ci
            ");
        }
        if (in_array('priorities', $tables)) {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS priorities (
                    priority_id     int(11)      NOT NULL AUTO_INCREMENT,
                    priority_name   varchar(255) NOT NULL DEFAULT 'Nová priorita',
                    priority_weight int(11)      NOT NULL DEFAULT 0,
                    priority_color  varchar(255) NOT NULL DEFAULT 'gray',
                    PRIMARY KEY (priority_id)
                ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_czech_ci
            ");
        }
        if (in_array('rooms', $tables)) {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS rooms (
                    room_id   int(11)      NOT NULL AUTO_INCREMENT,
                    room_name varchar(255) NOT NULL DEFAULT 'Nová místnost',
                    PRIMARY KEY (room_id)
                ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_czech_ci
            ");
        }
        if (in_array('users', $tables)) {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS users (
                    user_id              int(11)      NOT NULL,
                    user_username        varchar(255) NOT NULL,
                    user_password        varchar(255) NOT NULL,
                    user_change_password tinyint(1)   NOT NULL DEFAULT 0,
                    user_admin           tinyint(1)   NOT NULL DEFAULT 0,
                    PRIMARY KEY (user_id),
                    UNIQUE KEY user_username (user_username)
                ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_czech_ci
            ");
        }
        if (in_array('tickets', $tables)) {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS tickets (
                    ticket_id          int(11)      NOT NULL AUTO_INCREMENT,
                    ticket_creation    datetime     NOT NULL DEFAULT current_timestamp(),
                    ticket_origin      int(11)      DEFAULT NULL,
                    ticket_category    int(11)      DEFAULT NULL,
                    ticket_room        int(11)      DEFAULT NULL,
                    ticket_priority    int(11)      DEFAULT NULL,
                    ticket_deadline    date         DEFAULT NULL,
                    ticket_title       varchar(255) NOT NULL DEFAULT '',
                    ticket_description text         NOT NULL,
                    ticket_is_open     tinyint(1)   NOT NULL DEFAULT 1,
                    PRIMARY KEY (ticket_id)
                ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_czech_ci
            ");
        }
        if (in_array('assignments', $tables)) {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS assignments (
                    assignment_ticket   int(11)  NOT NULL,
                    assignment_user     int(11)  NOT NULL,
                    assignment_creation datetime NOT NULL DEFAULT current_timestamp(),
                    PRIMARY KEY (assignment_ticket, assignment_user)
                ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_czech_ci
            ");
        }
        if (in_array('autoassigns', $tables)) {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS autoassigns (
                    autoassign_user     int(11) NOT NULL,
                    autoassign_category int(11) NOT NULL,
                    PRIMARY KEY (autoassign_user, autoassign_category)
                ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_czech_ci
            ");
        }
        if (in_array('sessions', $tables)) {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS sessions (
                    session_id       int(11)      NOT NULL AUTO_INCREMENT,
                    session_user     int(11)      NOT NULL,
                    session_address  varchar(255) NOT NULL,
                    session_creation datetime     NOT NULL DEFAULT current_timestamp(),
                    session_last_use datetime     NOT NULL DEFAULT current_timestamp(),
                    PRIMARY KEY (session_id)
                ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_czech_ci
            ");
        }
        if (in_array('works', $tables)) {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS works (
                    work_id          int(11) NOT NULL AUTO_INCREMENT,
                    work_ticket      int(11) NOT NULL,
                    work_user        int(11) DEFAULT NULL,
                    work_minutes     int(11) NOT NULL DEFAULT 0,
                    work_description text    NOT NULL DEFAULT '',
                    PRIMARY KEY (work_id)
                ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_czech_ci
            ");
        }
        if (in_array('ticket_events', $tables)) {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS ticket_events (
                    event_id       int(11)      NOT NULL AUTO_INCREMENT,
                    event_ticket   int(11)      NOT NULL,
                    event_user     int(11)      DEFAULT NULL,
                    event_type     varchar(50)  NOT NULL,
                    event_creation datetime     NOT NULL DEFAULT current_timestamp(),
                    PRIMARY KEY (event_id)
                ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_czech_ci
            ");
        }
    }

    private function addIndexesMySQL(array $tables): void
    {
        $map = [
            'assignments' => ['ALTER TABLE assignments ADD KEY assignments_ibfk_user (assignment_user)'],
            'autoassigns' => ['ALTER TABLE autoassigns ADD KEY autoassigns_ibfk_category (autoassign_category)'],
            'sessions' => ['ALTER TABLE sessions ADD KEY sessions_ibfk_user (session_user)'],
            'tickets' => [
                'ALTER TABLE tickets ADD KEY tickets_ibfk_category (ticket_category)',
                'ALTER TABLE tickets ADD KEY tickets_ibfk_origin (ticket_origin)',
                'ALTER TABLE tickets ADD KEY tickets_ibfk_priority (ticket_priority)',
                'ALTER TABLE tickets ADD KEY tickets_ibfk_room (ticket_room)',
            ],
            'works' => [
                'ALTER TABLE works ADD KEY works_ibfk_ticket (work_ticket)',
                'ALTER TABLE works ADD KEY works_ibfk_user (work_user)',
            ],
            'ticket_events' => [
                'ALTER TABLE ticket_events ADD KEY ticket_events_ibfk_ticket (event_ticket)',
                'ALTER TABLE ticket_events ADD KEY ticket_events_ibfk_user (event_user)',
            ],
        ];
        foreach ($tables as $table) {
            if (isset($map[$table])) {
                foreach ($map[$table] as $sql) {
                    $this->execOrSkip($sql);
                }
            }
        }
    }

    private function ensureAutoIncrementMySQL(array $tables): void
    {
        $map = [
            'categories' => 'ALTER TABLE categories MODIFY category_id int(11) NOT NULL AUTO_INCREMENT',
            'priorities' => 'ALTER TABLE priorities MODIFY priority_id int(11) NOT NULL AUTO_INCREMENT',
            'rooms' => 'ALTER TABLE rooms MODIFY room_id int(11) NOT NULL AUTO_INCREMENT',
            'sessions' => 'ALTER TABLE sessions MODIFY session_id int(11) NOT NULL AUTO_INCREMENT',
            'teachers' => 'ALTER TABLE teachers MODIFY teacher_id int(11) NOT NULL AUTO_INCREMENT',
            'tickets' => 'ALTER TABLE tickets MODIFY ticket_id int(11) NOT NULL AUTO_INCREMENT',
            'works' => 'ALTER TABLE works MODIFY work_id int(11) NOT NULL AUTO_INCREMENT',
            'ticket_events' => 'ALTER TABLE ticket_events MODIFY event_id int(11) NOT NULL AUTO_INCREMENT',
        ];
        foreach ($tables as $table) {
            if (isset($map[$table])) $this->execOrSkip($map[$table]);
        }
    }

    private function addForeignKeysMySQL(array $tables): void
    {
        $map = [
            'users' => ['ALTER TABLE users ADD CONSTRAINT users_ibfk_teacher FOREIGN KEY (user_id) REFERENCES teachers (teacher_id) ON DELETE CASCADE ON UPDATE CASCADE'],
            'tickets' => [
                'ALTER TABLE tickets ADD CONSTRAINT tickets_ibfk_category FOREIGN KEY (ticket_category) REFERENCES categories (category_id) ON DELETE SET NULL ON UPDATE CASCADE',
                'ALTER TABLE tickets ADD CONSTRAINT tickets_ibfk_origin FOREIGN KEY (ticket_origin) REFERENCES teachers (teacher_id) ON DELETE SET NULL ON UPDATE CASCADE',
                'ALTER TABLE tickets ADD CONSTRAINT tickets_ibfk_priority FOREIGN KEY (ticket_priority) REFERENCES priorities (priority_id) ON DELETE SET NULL ON UPDATE CASCADE',
                'ALTER TABLE tickets ADD CONSTRAINT tickets_ibfk_room FOREIGN KEY (ticket_room) REFERENCES rooms (room_id) ON DELETE SET NULL ON UPDATE CASCADE',
            ],
            'assignments' => [
                'ALTER TABLE assignments ADD CONSTRAINT assignments_ibfk_ticket FOREIGN KEY (assignment_ticket) REFERENCES tickets (ticket_id) ON DELETE CASCADE ON UPDATE CASCADE',
                'ALTER TABLE assignments ADD CONSTRAINT assignments_ibfk_user FOREIGN KEY (assignment_user) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE',
            ],
            'autoassigns' => [
                'ALTER TABLE autoassigns ADD CONSTRAINT autoassigns_ibfk_category FOREIGN KEY (autoassign_category) REFERENCES categories (category_id) ON DELETE CASCADE ON UPDATE CASCADE',
                'ALTER TABLE autoassigns ADD CONSTRAINT autoassigns_ibfk_user FOREIGN KEY (autoassign_user) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE',
            ],
            'sessions' => ['ALTER TABLE sessions ADD CONSTRAINT sessions_ibfk_user FOREIGN KEY (session_user) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE'],
            'works' => [
                'ALTER TABLE works ADD CONSTRAINT works_ibfk_ticket FOREIGN KEY (work_ticket) REFERENCES tickets (ticket_id) ON DELETE CASCADE ON UPDATE CASCADE',
                'ALTER TABLE works ADD CONSTRAINT works_ibfk_user FOREIGN KEY (work_user) REFERENCES users (user_id) ON DELETE SET NULL ON UPDATE CASCADE',
            ],
            'ticket_events' => [
                'ALTER TABLE ticket_events ADD CONSTRAINT ticket_events_ibfk_ticket FOREIGN KEY (event_ticket) REFERENCES tickets (ticket_id) ON DELETE CASCADE ON UPDATE CASCADE',
                'ALTER TABLE ticket_events ADD CONSTRAINT ticket_events_ibfk_user FOREIGN KEY (event_user) REFERENCES users (user_id) ON DELETE SET NULL ON UPDATE CASCADE',
            ],
        ];
        foreach ($tables as $table) {
            if (isset($map[$table])) {
                foreach ($map[$table] as $sql) {
                    $this->execOrSkip($sql);
                }
            }
        }
    }

    private function seedAdmin(): void
    {
        $teacherId = $this->db->insert(
            "INSERT INTO teachers (teacher_name) VALUES (:name)",
            [":name" => "Administrator"]
        );
        $this->db->insert(
            "INSERT INTO users (user_id, user_username, user_password, user_admin) VALUES (:id, :username, :password, 1)",
            [":id" => $teacherId, ":username" => "Administrator", ":password" => password_hash("initpass", PASSWORD_DEFAULT)]
        );
    }

    private function ensureUserAdminColumn(): void
    {
        if ($this->db->isSQLite()) {
            $has = false;
            $cols = $this->db->select("PRAGMA table_info(users)");
            foreach ($cols as $c) {
                if ($c["name"] === "user_admin") {
                    $has = true;
                    break;
                }
            }
            if (!$has) $this->execOrSkip("ALTER TABLE users ADD COLUMN user_admin INTEGER NOT NULL DEFAULT 0");
        } else {
            $this->execOrSkip("ALTER TABLE users ADD COLUMN user_admin tinyint(1) NOT NULL DEFAULT 0");
        }
    }
}
