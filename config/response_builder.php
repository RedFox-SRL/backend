<?php
declare(strict_types=1);

use App\ApiCode;

/**
 * Laravel API Response Builder - configuration file
 *
 * See docs/config.md for detailed documentation
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2022 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 *
 * @noinspection PhpCSValidationInspection
 * phpcs:disable Squiz.PHP.CommentedOutCode.Found
 */

return [
    /*
    |-------------------------------------------------------------------------------------------------------------------
    | Code range settings
    |-------------------------------------------------------------------------------------------------------------------
    */
    'min_code' => 100,
    'max_code' => 1024,

    /*
    |-------------------------------------------------------------------------------------------------------------------
    | Error code to message mapping
    |-------------------------------------------------------------------------------------------------------------------
    */
    'map' => [
        ApiCode::INVALID_CREDENTIALS => 'api.invalid_credentials',
        ApiCode::SOMETHING_WENT_WRONG => 'api.something_went_wrong',
        ApiCode::VALIDATION_ERROR => 'api.validation_error',
        ApiCode::INVALID_EMAIL_VERIFICATION_URL => 'api.invalid_email_verification_link',
        ApiCode::EMAIL_ALREADY_VERIFIED => 'api.email_already_verified',
        ApiCode::INVALID_RESET_PASSWORD_TOKEN => 'api.invalid_reset_password_token',
        ApiCode::STUDENT_NOT_IN_GESTION => 'api.student_not_in_gestion',
        ApiCode::GROUP_CREATION_DISABLED => 'api.group_creation_disabled',
        ApiCode::GROUP_ALREADY_EXISTS => 'api.group_already_exists',
        ApiCode::USER_NOT_TEACHER => 'api.user_not_teacher',
        ApiCode::GESTION_NOT_FOUND => 'api.gestion_not_found',
        ApiCode::GESTION_ALREADY_EXISTS => 'api.gestion_already_exists',
        ApiCode::GESTION_ACCESS_DENIED => 'api.gestion_access_denied',
    ],
];
