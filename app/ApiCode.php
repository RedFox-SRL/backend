<?php

namespace App;

class ApiCode
{
    public const INVALID_CREDENTIALS = 251;
    public const SOMETHING_WENT_WRONG = 250;
    public const VALIDATION_ERROR = 252;
    public const INVALID_EMAIL_VERIFICATION_URL = 253;
    public const EMAIL_ALREADY_VERIFIED = 254;
    public const INVALID_RESET_PASSWORD_TOKEN = 255;
    public const STUDENT_NOT_IN_GESTION = 256;
    public const GROUP_CREATION_DISABLED = 257;
    public const GROUP_ALREADY_EXISTS = 258;
    public const USER_NOT_TEACHER = 259; // Nuevo código de error
    public const GESTION_NOT_FOUND = 260; // Nuevo código de error
    public const GESTION_ALREADY_EXISTS = 261; // Nuevo código de error
    public const GESTION_ACCESS_DENIED = 262; // Nuevo código de error
}
