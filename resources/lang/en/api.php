<?php

return [
    'invalid_credentials' => 'Invalid email or password.',
    'something_went_wrong' => 'Something went wrong.',
    'validation_error' => 'Validation error.',
    'invalid_email_verification_link' => 'Invalid/Expired url provider.',
    'email_already_verified' => 'Email already verified.',
    'invalid_reset_password_token' => 'Invalid token provided',
    'group_already_exists' => 'You have already created a group in this gestion.',
    'user_not_teacher' => 'The user is not a teacher.',
    'management_not_found' => 'Management not found.',
    'management_already_exists' => 'A gestion with the same semester and year already exists for this teacher.',
    'management_access_denied' => 'You are not allowed to access this gestion.',
    'not_a_student' => 'You are not a student.',
    'management_code_inactive' => 'The management code is not active yet. You cannot join at this time.',
    'already_enrolled' => 'You are already enrolled in another management.',
    'not_part_of_management' => 'You are not part of any management.',
    'group_not_found' => 'Group not found.',
    'group_full' => 'The group has reached the maximum number of members.',
    'student_already_in_group' => 'You are already a member of group.',
    'student_not_in_management' => 'You are not part of the management of the group.',
    'already_enrolled_group' => 'You are already enrolled in another group.',
    'email_not_found' => 'Email not found.',
    "student_not_in_group_management" => "You are not part of the management of the group.",
    "group_name_already_exists" => "A group with the same name already exists.",
    "not_group_representative" => 'You are not the representative of the group.',
    "member_not_found" => "Member not found.",
    "cannot_remove_self" => "You cannot remove yourself from the group.",
    'task_not_found' => 'Task not found.',
    'task_already_reviewed' => 'This task has been reviewed and cannot be modified.',
    'task_update_failed' => 'Failed to update the task.',
    'task_delete_failed' => 'Failed to delete the task.',
    'sprint_not_found' => 'Sprint not found.',
    'unauthorized' => 'Unauthorized.',
    'evaluation_already_exists' => 'Weekly evaluation already exists for this task.',
    'evaluation_creation_failed' => 'Failed to create weekly evaluation.',
    'evaluation_not_found' => 'Weekly evaluation not found.',
    'sprint_percentage_exceeded' => 'The sum of the percentages of the tasks exceeds 100%.',
    'evaluation_period_ended' => 'The evaluation period has ended.',
    'evaluation_template_retrieval_failed' => 'Failed to retrieve evaluation template.',
    'invalid_week_number' => 'Invalid week number.',
    'invalid_tasks' => 'Invalid tasks.',
    'evaluation_retrieval_failed' => 'Failed to retrieve weekly evaluation.',
    'max_evaluations_reached' => 'Cannot create more evaluations than allowed for this sprint.',
    'sprint_ended' => 'Cannot create evaluations after the sprint end date.',
    'sprint_not_ended' => 'Cannot create evaluations before the sprint end date.',
    'sprint_evaluation_already_exists' => 'Sprint evaluation already exists.',
    'sprint_evaluation_too_early' => 'Sprint evaluation can only be created within 4 days of the sprint end date or after it has ended.',
    'no_weekly_evaluations' => 'At least one weekly evaluation is required before creating the final sprint evaluation.',
    'insufficient_completed_tasks' => 'The student did not complete any tasks in this sprint.',
    'student_no_completed_tasks' => 'The student did not complete any tasks in this sprint.',
    'grade_exceeds_sprint_percentage' => 'The grade cannot exceed the sprint\'s percentage value.',
    'student_not_in_group' => 'The student are not part of the group.',
    'sprint_evaluation_not_found' => 'Sprint evaluation not found.',
    'template_already_exists' => 'An evaluation template of this type already exists for this management.',
    'template_creation_failed' => 'Failed to create the evaluation template.',
    'template_update_failed' => 'Failed to update the evaluation template.',
    'max_invitations_reached' => 'Se ha alcanzado el número máximo de invitaciones para este grupo.',
    'invitation_already_sent' => 'Ya se ha enviado una invitación a este estudiante.',
    'invitation_expired' => 'La invitación ha expirado.',
    'invitation_already_processed' => 'Esta invitación ya ha sido procesada.',
    'student_not_found' => 'Student not found.',
    'student_not_in_same_management' => 'The student is not part of the same management as the group.',
    'evaluation_already_completed' => 'The evaluation has already been completed.',
    'evaluation_submission_failed' => 'Failed to submit the evaluation.',
    'evaluation_period_expired' => 'The evaluation period has expired.',
    'invalid_response_count' => 'Invalid number of responses.',
    'insufficient_group_members' => 'The group does not have enough members to create evaluations.',
    'invalid_project_delivery_date' => 'The project delivery date must be within the management period.',
    'management_date_in_past' => 'The management date is in the past.',
    'management_date_in_future' => 'The management date is in the future.',
    'project_delivery_date_before_start' => 'The project delivery date must be after the management start date.',
    'project_delivery_date_after_end' => 'The project delivery date must be before the management end date.',
];
