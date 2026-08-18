<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

require_once __DIR__ . "/../SPSTickets.php";
getApplication()->checkUser();

header("Content-Type: application/json");

$ticket_id = (int)($_POST["ticket_id"] ?? 0);
$action = $_POST["action"] ?? "";

if (!$ticket_id || !in_array($action, ["close", "reopen"])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid parameters"]);
    exit;
}

$ticket = getApplication()->getTicketRepository()->getTicketById($ticket_id);
if (!$ticket) {
    http_response_code(404);
    echo json_encode(["error" => "Ticket not found"]);
    exit;
}

$current_user = getApplication()->getUser();

if ($action === "close") {
    if (!$ticket->ticket_is_open) {
        http_response_code(400);
        echo json_encode(["error" => "Ticket is already closed"]);
        exit;
    }

    getApplication()->getAssignmentRepository()->addAssignment($ticket_id, $current_user->user_id);
    getApplication()->getTicketRepository()->closeTicket($ticket_id);
    getApplication()->getTicketEventRepository()->addEvent($ticket_id, $current_user->user_id, "closed");
} else {
    if ($ticket->ticket_is_open) {
        http_response_code(400);
        echo json_encode(["error" => "Ticket is already open"]);
        exit;
    }

    getApplication()->getTicketRepository()->reopenTicket($ticket_id);
    getApplication()->getTicketEventRepository()->addEvent($ticket_id, $current_user->user_id, "reopened");
}

echo json_encode(["success" => true]);