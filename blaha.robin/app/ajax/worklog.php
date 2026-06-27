<?php

require_once __DIR__ . "/../SPSTickets.php";
getApplication()->checkUser();

header("Content-Type: application/json");

$action = $_POST["action"] ?? "";

if ($action === "add") {
    $ticket_id = (int)($_POST["ticket_id"] ?? 0);
    $user_id = (int)($_POST["user_id"] ?? 0);
    $minutes = (int)($_POST["minutes"] ?? 0);
    $description = trim($_POST["description"] ?? "");

    if (!$ticket_id || !$user_id || !$minutes || !$description) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required fields"]);
        exit;
    }

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

    getApplication()->getWorkRepository()->deleteWork($work_id);
    echo json_encode(["success" => true]);
    exit;
}

http_response_code(400);
echo json_encode(["error" => "Invalid action"]);