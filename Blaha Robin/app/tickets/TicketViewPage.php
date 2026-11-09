<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Enum identifying which ticket list view is currently active.
 */
enum TicketViewPage
{
    /** All open tickets. */
    case All;

    /** Tickets assigned to the current user. */
    case Assigned;

    /** Closed / resolved tickets. */
    case Closed;
}
