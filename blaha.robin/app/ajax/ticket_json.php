<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

require_once __DIR__ . "/../SPSTickets.php";
getApplication()->checkUser();

$ticket_id = (int)($_GET["id"] ?? 0);
if (!$ticket_id) {
    http_response_code(400);
    echo json_encode(["error" => "Missing ticket ID"]);
    exit;
}

$ticket = getApplication()->getTicketRepository()->getTicketById($ticket_id);
if (!$ticket) {
    http_response_code(404);
    echo json_encode(["error" => "Ticket not found"]);
    exit;
}

$assignments = getApplication()->getAssignmentRepository()->getAssignedUsersToTicket($ticket_id);
$work_log = getApplication()->getWorkRepository()->getWorksByTicketWithUsers($ticket_id);
$all_users = getApplication()->getUserRepository()->getAllUsers();
$categories = getApplication()->getCategoryRepository()->getAllCategories();
$rooms = getApplication()->getRoomRepository()->getAllRooms();
$priorities = getApplication()->getPriorityRepository()->getAllPriorities();

echo json_encode([
    "ticket" => $ticket,
    "assignments" => $assignments,
    "work_log" => $work_log,
    "all_users" => $all_users,
    "categories" => $categories,
    "rooms" => $rooms,
    "priorities" => $priorities,
], JSON_UNESCAPED_UNICODE);