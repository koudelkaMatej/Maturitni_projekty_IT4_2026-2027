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
}
