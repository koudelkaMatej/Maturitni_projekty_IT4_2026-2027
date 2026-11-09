<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Enum representing the possible outcomes of a password change attempt.
 */
enum PasswordChangeResult
{
    /** No attempt has been made yet. */
    case Default;

    /** Password was changed successfully. */
    case Success;

    /** The current password provided did not match. */
    case IncorrectCurrentPassword;

    /** The two new password fields did not match each other. */
    case MismatchedNewPasswords;

    /** The new password did not meet validation rules. */
    case InvalidNewPassword;
}