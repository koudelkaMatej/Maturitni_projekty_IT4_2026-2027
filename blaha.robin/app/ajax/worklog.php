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

$action = $_POST["action"] ?? "";

if ($action === "add") {
    $ticket_id = (int)($_POST["ticket_id"] ?? 0);
    $user_id = getApplication()->getUser()->user_id;
    $minutes = (int)($_POST["minutes"] ?? 0);
    $description = trim($_POST["description"] ?? "");

    if (!$ticket_id || !$user_id || !$minutes || !$description) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required fields"]);
        exit;
    }

    $ticket = getApplication()->getTicketRepository()->getTicketById($ticket_id);
    if (!$ticket) {
        http_response_code(404);
        echo json_encode(["error" => "Ticket not found"]);
        exit;
    }
    if (!$ticket->ticket_is_open) {
        http_response_code(403);
        echo json_encode(["error" => "Ticket is closed; reopen it before adding comments"]);
        exit;
    }

    // Commenting on a ticket implicitly assigns the commenter to it, if not already assigned.
    getApplication()->getAssignmentRepository()->addAssignment($ticket_id, $user_id);

    getApplication()->getWorkRepository()->addWork($ticket_id, $user_id, $minutes, $description);
    echo json_encode(["success" => true]);
    exit;
}

if ($action === "update") {
    $work_id = (int)($_POST["work_id"] ?? 0);
    $minutes = (int)($_POST["minutes"] ?? 0);
    $description = trim($_POST["description"] ?? "");

    if (!$work_id || !$minutes || !$description) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required fields"]);
        exit;
    }

    $work = getApplication()->getWorkRepository()->getWorkById($work_id);
    if (!$work) {
        http_response_code(404);
        echo json_encode(["error" => "Work entry not found"]);
        exit;
    }
    $ticket = getApplication()->getTicketRepository()->getTicketById($work->work_ticket);
    if (!$ticket || !$ticket->ticket_is_open) {
        http_response_code(403);
        echo json_encode(["error" => "Ticket is closed; reopen it before editing comments"]);
        exit;
    }

    getApplication()->getWorkRepository()->updateWork($work_id, $minutes, $description);
    echo json_encode(["success" => true]);
    exit;
}

if ($action === "delete") {
    $work_id = (int)($_POST["work_id"] ?? 0);
    if (!$work_id) {
        http_response_code(400);
        echo json_encode(["error" => "Missing work ID"]);
        exit;
    }

    $work = getApplication()->getWorkRepository()->getWorkById($work_id);
    if (!$work) {
        http_response_code(404);
        echo json_encode(["error" => "Work entry not found"]);
        exit;
    }
    $ticket = getApplication()->getTicketRepository()->getTicketById($work->work_ticket);
    if (!$ticket || !$ticket->ticket_is_open) {
        http_response_code(403);
        echo json_encode(["error" => "Ticket is closed; reopen it before deleting comments"]);
        exit;
    }

    getApplication()->getWorkRepository()->deleteWork($work_id);
    echo json_encode(["success" => true]);
    exit;
}

http_response_code(400);
echo json_encode(["error" => "Invalid action"]);