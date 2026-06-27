<?php

require_once __DIR__ . "/../SPSTickets.php";
getApplication()->checkUser();

header("Content-Type: application/json");

$ticket_id = (int)($_POST["ticket_id"] ?? 0);
if (!$ticket_id) {
    http_response_code(400);
    echo json_encode(["error" => "Missing ticket ID"]);
    exit;
}

$nullable_fields = ["ticket_category", "ticket_room", "ticket_priority", "ticket_deadline"];
$data = [];
foreach (["ticket_category", "ticket_room", "ticket_priority", "ticket_title", "ticket_description", "ticket_deadline"] as $field) {
    if (isset($_POST[$field])) {
        $data[$field] = in_array($field, $nullable_fields) && $_POST[$field] === "" ? null : $_POST[$field];
    }
}

if (empty($data)) {
    http_response_code(400);
    echo json_encode(["error" => "No fields to update"]);
    exit;
}

getApplication()->getTicketRepository()->updateTicket($ticket_id, $data);

echo json_encode(["success" => true]);