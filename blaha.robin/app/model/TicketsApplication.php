<?php /** @noinspection GrazieInspection */
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Contract for the application singleton.
 *
 * Provides access to every repository and a handful of
 * session / page-level utility methods.
 */
interface TicketsApplication
{
    /** @return AssignmentRepository for ticket-to-user assignments */
    public function getAssignmentRepository(): AssignmentRepository;

    /** @return AutoAssignRepository for per-category auto-assign rules */
    public function getAutoAssignRepository(): AutoAssignRepository;

    /** @return CategoryRepository for ticket categories */
    public function getCategoryRepository(): CategoryRepository;

    /** @return PriorityRepository for ticket priorities */
    public function getPriorityRepository(): PriorityRepository;

    /** @return RoomRepository for room/classroom listings */
    public function getRoomRepository(): RoomRepository;

    /** @return SessionRepository for DB-backed sessions */
    public function getSessionRepository(): SessionRepository;

    /** @return TeacherRepository for teacher records */
    public function getTeacherRepository(): TeacherRepository;

    /** @return TicketRepository for ticket CRUD */
    public function getTicketRepository(): TicketRepository;

    /** @return UserRepository for user (technician) accounts */
    public function getUserRepository(): UserRepository;

    /** @return WorkRepository for work log entries */
    public function getWorkRepository(): WorkRepository;

    /** @return ?User Currently authenticated user or null */
    public function getUser(): ?User;

    /**
     * Create a new session for the given user ID.
     *
     * @param int $user_id
     * @return void
     */
    public function createUserSession($user_id): void;

    /** Destroy the current session. */
    public function destroySession(): void;

    /**
     * Redirect to login.php if no user is authenticated.
     *
     * @return void
     */
    public function checkUser(): void;

    /**
     * Redirect internally to a page by its script name (without .php).
     *
     * @param string $page
     * @return void
     */
    public function redirectInternally($page): void;

    /**
     * Set the human-readable page name (used in the sidebar/header).
     *
     * @param string $page
     * @return void
     */
    public function setPageName(string $page): void;

    /** @return string Current page name */
    public function getPageName(): string;

    /**
     * Extract the first two uppercase letters from a string (fallback: first two chars).
     *
     * @param string $string
     * @return string
     */
    public function getInitials(string $string): string;
}