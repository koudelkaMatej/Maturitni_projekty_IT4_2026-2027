<?php

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

if ($action === "close") {
    getApplication()->getTicketRepository()->closeTicket($ticket_id);
} else {
    getApplication()->getTicketRepository()->reopenTicket($ticket_id);
}

echo json_encode(["success" => true]);