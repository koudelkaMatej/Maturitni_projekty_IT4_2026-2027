<?php

require_once __DIR__ . "/app/SPSTickets.php";

getApplication()->checkUser();

?>

<h1><?php echo getApplication()->getUser()["teacher_name"] ?></h1>
<button onclick="window.location.href='logout.php'">Odhlásit se</button>