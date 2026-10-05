<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Repository for the ticket activity log (created / closed / reopened events).
 */
class TicketEventRepository extends Repository
{
    /**
     * Record a new lifecycle event for a ticket.
     *
     * @param int $event_ticket FK to tickets.ticket_id.
     * @param int|null $event_user FK to users.user_id who triggered the event.
     * @param string $event_type "created", "closed", or "reopened".
     * @return false|string The new event ID, or false on failure.
     */
    public function addEvent($event_ticket, $event_user, string $event_type): false|string
    {
        return $this->database->insert(
            "INSERT INTO ticket_events (event_ticket, event_user, event_type) VALUES (:event_ticket, :event_user, :event_type)",
            [":event_ticket" => $event_ticket, ":event_user" => $event_user, ":event_type" => $event_type]
        );
    }

    /**
     * Get all events for a ticket, joined with teacher names, oldest first.
     *
     * @param int $event_ticket
     * @return TicketEvent[]
     */
    public function getEventsByTicket($event_ticket): array
    {
        $rows = $this->database->select(
            "SELECT ticket_events.*, teachers.teacher_name FROM ticket_events LEFT JOIN users ON event_user = user_id LEFT JOIN teachers ON user_id = teacher_id WHERE event_ticket = :event_ticket ORDER BY event_id ASC",
            [":event_ticket" => $event_ticket]
        );
        return array_map(fn($r) => new TicketEvent($r), $rows);
    }

    /**
     * Get the most recent events triggered by a user, joined with the ticket title, newest first.
     *
     * @param int $event_user
     * @param int $limit
     * @return array[] Each row is a TicketEvent's fields plus ticket_title.
     */
    public function getRecentEventsByUser($event_user, int $limit = 8): array
    {
        return $this->database->select(
            "SELECT ticket_events.*, tickets.ticket_title FROM ticket_events INNER JOIN tickets ON event_ticket = ticket_id WHERE event_user = :event_user ORDER BY event_id DESC LIMIT " . (int)$limit,
            [":event_user" => $event_user]
        );
    }
}
