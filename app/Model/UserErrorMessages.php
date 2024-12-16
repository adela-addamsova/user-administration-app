<?php

declare(strict_types=1);

namespace App\Model;

/**
 * User Error Messages
 * Class set constants that represents error messages for various actions that can occur while user logs in or registers
 */
class UserErrorMessages {
    const DELETED_USER = 'Your account was deleted. You can not login!';
    const INVALID_PASSWORD = 'The password is incorrect.';
    const INVALID_LOGIN = 'User does not exist.';
    const NOT_LOGGED_IN = 'You must be logged in to access this section.';
    const LOGGED_IN = 'This section is only for users that are not logged in.';
    const WRONG_PASSWORD_FORMAT = 'Password must have at least 8 characters and include numbers, lowercase, and uppercase letters.';
    const NO_PASSWORD_MATCH = 'Passwords do not match.';
    const LOGIN_TAKEN = 'Username is already taken!';
    const EMAIL_TAKEN = 'Email is already taken!';
    const UNEXPECTED_ERROR = 'An unexpected error occurred. Please try again later.';
    const INVALID_EMAIL = 'Enter a valid e-mail address';
}
