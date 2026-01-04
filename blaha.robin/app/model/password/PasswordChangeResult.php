<?php

enum PasswordChangeResult
{
    case Default;
    case Success;
    case IncorrectCurrentPassword;
    case MismatchedNewPasswords;
    case InvalidNewPassword;
}