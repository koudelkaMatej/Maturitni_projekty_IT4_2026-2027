<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

require_once __DIR__ . "/app/SPSTickets.php";

getApplication()->checkUser();

?>

<h1><?php echo getApplication()->getUser()->teacher_name ?></h1>
<button onclick="window.location.href='logout.php'">Odhlásit se</button>