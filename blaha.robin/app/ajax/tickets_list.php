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

$view = $_GET["view"] ?? "all";
if (!in_array($view, ["all", "assigned", "closed"])) $view = "all";

$options = [
    "view" => $view,
    "user_id" => getApplication()->getUser()->user_id,
    "search" => $_GET["search"] ?? "",
    "category" => $_GET["category"] ?? "",
    "room" => $_GET["room"] ?? "",
    "priority" => $_GET["priority"] ?? "",
    "assignee" => $_GET["assignee"] ?? "",
    "sort" => $_GET["sort"] ?? "created",
    "dir" => $_GET["dir"] ?? "desc",
    "page" => max(1, (int)($_GET["page"] ?? 1)),
    "perPage" => min(500, max(1, (int)($_GET["perPage"] ?? 50))),
];

$result = getApplication()->getTicketRepository()->queryTickets($options);

echo json_encode($result, JSON_UNESCAPED_UNICODE);
