<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Enum representing the possible outcomes of a login attempt.
 */
enum LoginResult
{
    /** No attempt has been made yet. */
    case Default;

    /** Login was successful. */
    case Success;

    /** Login failed (wrong username or password). */
    case Failed;
}
