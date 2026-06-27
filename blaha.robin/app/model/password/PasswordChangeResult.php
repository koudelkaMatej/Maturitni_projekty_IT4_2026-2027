<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

enum PasswordChangeResult
{
    case Default;
    case Success;
    case IncorrectCurrentPassword;
    case MismatchedNewPasswords;
    case InvalidNewPassword;
}