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
        ApiCode::GROUP_ALREADY_EXISTS => 'api.group_already_exists',
        ApiCode::USER_NOT_TEACHER => 'api.user_not_teacher',
        ApiCode::MANAGEMENT_NOT_FOUND => 'api.management_not_found',
        ApiCode::MANAGEMENT_ALREADY_EXISTS => 'api.management_already_exists',
        ApiCode::MANAGEMENT_ACCESS_DENIED => 'api.management_access_denied',
        ApiCode::NOT_A_STUDENT => 'api.not_a_student',
        ApiCode::MANAGEMENT_CODE_INACTIVE => 'api.management_code_inactive',
        ApiCode::ALREADY_ENROLLED => 'api.already_enrolled',
        ApiCode::NOT_PART_OF_MANAGEMENT => 'api.not_part_of_management',
        ApiCode::GROUP_NOT_FOUND => 'api.group_not_found',
        ApiCode::GROUP_FULL => 'api.group_full',
        ApiCode::STUDENT_ALREADY_IN_GROUP => 'api.student_already_in_group',
        ApiCode::STUDENT_NOT_IN_MANAGEMENT => 'api.student_not_in_group_management',
        ApiCode::ALREADY_ENROLLED_GROUP => 'api.already_enrolled_group',
        ApiCode::EMAIL_NOT_FOUND => 'api.email_not_found',
        ApiCode::GROUP_NAME_ALREADY_EXISTS => 'api.group_name_already_exists',
        ApiCode::NOT_GROUP_REPRESENTATIVE => 'api.not_group_representative',
        ApiCode::MEMBER_NOT_FOUND => 'api.member_not_found',
        ApiCode::CANNOT_REMOVE_SELF => 'api.cannot_remove_self',
        ApiCode::TASK_NOT_FOUND => 'api.task_not_found',
        ApiCode::TASK_ALREADY_REVIEWED => 'api.task_already_reviewed',
        ApiCode::TASK_UPDATE_FAILED => 'api.task_update_failed',
        ApiCode::TASK_DELETE_FAILED => 'api.task_delete_failed',
        ApiCode::SPRINT_NOT_FOUND => 'api.sprint_not_found',
        ApiCode::UNAUTHORIZED => 'api.unauthorized',
        ApiCode::EVALUATION_ALREADY_EXISTS => 'api.evaluation_already_exists',
        ApiCode::EVALUATION_CREATION_FAILED => 'api.evaluation_creation_failed',
        ApiCode::EVALUATION_NOT_FOUND => 'api.evaluation_not_found',
        ApiCode::SPRINT_PERCENTAGE_EXCEEDED => 'api.sprint_percentage_exceeded',
        ApiCode::EVALUATION_PERIOD_ENDED => 'api.evaluation_period_ended',
        ApiCode::EVALUATION_TEMPLATE_RETRIEVAL_FAILED => 'api.evaluation_template_retrieval_failed',
        ApiCode::INVALID_WEEK_NUMBER => 'api.invalid_week_number',
        ApiCode::INVALID_TASKS => 'api.invalid_tasks',
        ApiCode::EVALUATION_RETRIEVAL_FAILED => 'api.evaluation_retrieval_failed',
        ApiCode::MAX_EVALUATIONS_REACHED => 'api.sprint_percentage_exceeded',
        ApiCode::SPRINT_ENDED => 'api.sprint_ended',
        ApiCode::SPRINT_NOT_ENDED => 'api.sprint_not_ended',
        ApiCode::SPRINT_EVALUATION_ALREADY_EXISTS => 'api.sprint_evaluation_already_exists',
        ApiCode::SPRINT_EVALUATION_TOO_EARLY => 'api.sprint_evaluation_too_early',
        ApiCode::NO_WEEKLY_EVALUATIONS => 'api.no_weekly_evaluations',
        ApiCode::INSUFFICIENT_COMPLETED_TASKS => 'api.insufficient_completed_tasks',
        ApiCode::STUDENT_NO_COMPLETED_TASKS => 'api.student_no_completed_tasks',
        ApiCode::GRADE_EXCEEDS_SPRINT_PERCENTAGE => 'api.grade_exceeds_sprint_percentage',
        ApiCode::STUDENT_NOT_IN_GROUP => 'api.student_not_in_group',
        ApiCode::SPRINT_EVALUATION_NOT_FOUND => 'api.sprint_evaluation_not_found',
    ],
];
