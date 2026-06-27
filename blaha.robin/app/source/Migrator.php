<?php

class Migrator
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function migrate(): void
    {
        $this->createTables();
        $this->addIndexes();
        $this->ensureAutoIncrement();
        $this->addForeignKeys();
    }

    private function execOrSkip(string $sql): void
    {
        try {
            $this->db->exec($sql);
        } catch (PDOException $e) {
            // Silently skip if constraint/index already exists
        }
    }

    private function createTables(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS teachers (
                teacher_id   int(11)      NOT NULL AUTO_INCREMENT,
                teacher_code varchar(6)   NOT NULL,
                teacher_name varchar(255) NOT NULL,
                PRIMARY KEY (teacher_id),
                UNIQUE KEY teacher_code (teacher_code)
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_czech_ci
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS categories (
                category_id   int(11)      NOT NULL AUTO_INCREMENT,
                category_name varchar(255) NOT NULL DEFAULT 'Nová kategorie',
                PRIMARY KEY (category_id)
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_czech_ci
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS priorities (
                priority_id     int(11)      NOT NULL AUTO_INCREMENT,
                priority_name   varchar(255) NOT NULL DEFAULT 'Nová priorita',
                priority_weight int(11)      NOT NULL DEFAULT 0,
                priority_color  varchar(255) NOT NULL DEFAULT 'gray',
                PRIMARY KEY (priority_id)
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_czech_ci
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS rooms (
                room_id   int(11)      NOT NULL AUTO_INCREMENT,
                room_name varchar(255) NOT NULL DEFAULT 'Nová místnost',
                PRIMARY KEY (room_id)
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_czech_ci
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS users (
                user_id              int(11)      NOT NULL,
                user_username        varchar(255) NOT NULL,
                user_password        varchar(255) NOT NULL,
                user_change_password tinyint(1)   NOT NULL DEFAULT 0,
                PRIMARY KEY (user_id),
                UNIQUE KEY user_username (user_username)
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_czech_ci
        ");

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

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS assignments (
                assignment_ticket   int(11)  NOT NULL,
                assignment_user     int(11)  NOT NULL,
                assignment_creation datetime NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (assignment_ticket, assignment_user)
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_czech_ci
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS autoassigns (
                autoassign_user     int(11) NOT NULL,
                autoassign_category int(11) NOT NULL,
                PRIMARY KEY (autoassign_user, autoassign_category)
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_czech_ci
        ");

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

    private function addIndexes(): void
    {
        $this->execOrSkip("ALTER TABLE assignments ADD KEY assignments_ibfk_user (assignment_user)");
        $this->execOrSkip("ALTER TABLE autoassigns ADD KEY autoassigns_ibfk_category (autoassign_category)");
        $this->execOrSkip("ALTER TABLE sessions ADD KEY sessions_ibfk_user (session_user)");
        $this->execOrSkip("ALTER TABLE tickets ADD KEY tickets_ibfk_category (ticket_category)");
        $this->execOrSkip("ALTER TABLE tickets ADD KEY tickets_ibfk_origin (ticket_origin)");
        $this->execOrSkip("ALTER TABLE tickets ADD KEY tickets_ibfk_priority (ticket_priority)");
        $this->execOrSkip("ALTER TABLE tickets ADD KEY tickets_ibfk_room (ticket_room)");
        $this->execOrSkip("ALTER TABLE works ADD KEY works_ibfk_ticket (work_ticket)");
        $this->execOrSkip("ALTER TABLE works ADD KEY works_ibfk_user (work_user)");
    }

    private function ensureAutoIncrement(): void
    {
        $this->execOrSkip("ALTER TABLE categories MODIFY category_id int(11) NOT NULL AUTO_INCREMENT");
        $this->execOrSkip("ALTER TABLE priorities MODIFY priority_id int(11) NOT NULL AUTO_INCREMENT");
        $this->execOrSkip("ALTER TABLE rooms MODIFY room_id int(11) NOT NULL AUTO_INCREMENT");
        $this->execOrSkip("ALTER TABLE sessions MODIFY session_id int(11) NOT NULL AUTO_INCREMENT");
        $this->execOrSkip("ALTER TABLE teachers MODIFY teacher_id int(11) NOT NULL AUTO_INCREMENT");
        $this->execOrSkip("ALTER TABLE tickets MODIFY ticket_id int(11) NOT NULL AUTO_INCREMENT");
        $this->execOrSkip("ALTER TABLE works MODIFY work_id int(11) NOT NULL AUTO_INCREMENT");
    }

    private function addForeignKeys(): void
    {
        $this->execOrSkip("ALTER TABLE users ADD CONSTRAINT users_ibfk_teacher FOREIGN KEY (user_id) REFERENCES teachers (teacher_id) ON DELETE CASCADE ON UPDATE CASCADE");

        $this->execOrSkip("ALTER TABLE tickets ADD CONSTRAINT tickets_ibfk_category FOREIGN KEY (ticket_category) REFERENCES categories (category_id) ON DELETE SET NULL ON UPDATE CASCADE");
        $this->execOrSkip("ALTER TABLE tickets ADD CONSTRAINT tickets_ibfk_origin FOREIGN KEY (ticket_origin) REFERENCES teachers (teacher_id) ON DELETE SET NULL ON UPDATE CASCADE");
        $this->execOrSkip("ALTER TABLE tickets ADD CONSTRAINT tickets_ibfk_priority FOREIGN KEY (ticket_priority) REFERENCES priorities (priority_id) ON DELETE SET NULL ON UPDATE CASCADE");
        $this->execOrSkip("ALTER TABLE tickets ADD CONSTRAINT tickets_ibfk_room FOREIGN KEY (ticket_room) REFERENCES rooms (room_id) ON DELETE SET NULL ON UPDATE CASCADE");

        $this->execOrSkip("ALTER TABLE assignments ADD CONSTRAINT assignments_ibfk_ticket FOREIGN KEY (assignment_ticket) REFERENCES tickets (ticket_id) ON DELETE CASCADE ON UPDATE CASCADE");
        $this->execOrSkip("ALTER TABLE assignments ADD CONSTRAINT assignments_ibfk_user FOREIGN KEY (assignment_user) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE");

        $this->execOrSkip("ALTER TABLE autoassigns ADD CONSTRAINT autoassigns_ibfk_category FOREIGN KEY (autoassign_category) REFERENCES categories (category_id) ON DELETE CASCADE ON UPDATE CASCADE");
        $this->execOrSkip("ALTER TABLE autoassigns ADD CONSTRAINT autoassigns_ibfk_user FOREIGN KEY (autoassign_user) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE");

        $this->execOrSkip("ALTER TABLE sessions ADD CONSTRAINT sessions_ibfk_user FOREIGN KEY (session_user) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE");

        $this->execOrSkip("ALTER TABLE works ADD CONSTRAINT works_ibfk_ticket FOREIGN KEY (work_ticket) REFERENCES tickets (ticket_id) ON DELETE CASCADE ON UPDATE CASCADE");
        $this->execOrSkip("ALTER TABLE works ADD CONSTRAINT works_ibfk_user FOREIGN KEY (work_user) REFERENCES users (user_id) ON DELETE SET NULL ON UPDATE CASCADE");
    }
}
