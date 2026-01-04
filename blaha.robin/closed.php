<?php

require_once __DIR__ . "/app/SPSTickets.php";
require_once __DIR__ . "/app/tickets/TicketView.php";
require_once __DIR__ . "/app/tickets/TicketViewPage.php";

getApplication()->checkUser();
getApplication()->setPageName("Přehled ticketů");

$view = new TicketView(TicketViewPage::Closed);

?>

<?php require_once __DIR__ . "/app/includes/header.php"; ?>
<?php require_once __DIR__ . "/app/includes/sidebar.php"; ?>
<?php $view->renderPage(); ?>
<?php require_once __DIR__ . "/app/includes/scripts.php"; ?>
<?php $view->renderPostScripts(); ?>
<?php require_once __DIR__ . "/app/includes/footer.php"; ?>