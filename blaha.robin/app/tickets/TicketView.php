<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * View controller for ticket list pages.
 *
 * Determines which ticket listing (All / Assigned / Closed) to display
 * and renders the corresponding header, content table, footer, and
 * post-scripts partials.
 */
class TicketView
{
    /** The currently active view mode. */
    private TicketViewPage $page;

    /** All user accounts (used by the JavaScript for assignee dropdowns). */
    private array $users;

    public function __construct(TicketViewPage $page)
    {
        $this->page = $page;
        $this->users = getApplication()->getUserRepository()->getAllUsers();
    }

    /** Render the ticket header, table content, and modal footer. */
    public function renderPage(): void
    {
        require_once __DIR__ . "/../includes/tickets_header.php";
        require_once __DIR__ . "/../includes/tickets_content.php";
        require_once __DIR__ . "/../includes/tickets_footer.php";
    }

    /** Render the post-body scripts partial. */
    public function renderPostScripts(): void
    {
        require_once __DIR__ . "/../includes/tickets_postscripts.php";
    }

    /** @return TicketViewPage The currently active view page. */
    public function getPage(): TicketViewPage
    {
        return $this->page;
    }

    /** @return User[] All users (for assignee selectors). */
    public function getUsers(): array
    {
        return $this->users;
    }
}