<?php

class TicketView
{
    private TicketViewPage $page;

    private array $users;

    public function __construct(TicketViewPage $page)
    {
        $this->page = $page;
        $this->users = getApplication()->getUserRepository()->getAllUsers();
    }

    public function renderPage(): void
    {
        require_once __DIR__ . "/../includes/tickets_header.php";
        require_once __DIR__ . "/../includes/tickets_content.php";
        require_once __DIR__ . "/../includes/tickets_footer.php";
    }

    public function renderPostScripts(): void
    {
        require_once __DIR__ . "/../includes/tickets_postscripts.php";
    }

    public function getPage(): TicketViewPage
    {
        return $this->page;
    }

    public function getUsers(): array
    {
        return $this->users;
    }
}