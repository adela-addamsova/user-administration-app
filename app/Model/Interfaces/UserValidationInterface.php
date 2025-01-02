<?php

declare(strict_types=1);

namespace App\Model\Interfaces;

/**
 * Interface UserValidationInterface
 * 
 * This interface defines methods related to user validation operations. 
 * Implementing classes should provide functionality to check whether a user's password is valid, 
 * whether an email is already taken, and whether a login is already taken
 */
interface UserValidationInterface
{
    /**
     * Validate Password
     * 
     * Checks if the given password meets the required criteria (e.g., length, complexity, etc.)
     *
     * @param string $password The password to validate
     * @return bool Returns true if the password is valid, false otherwise
     */
    public function isPasswordValid($password): bool;

    /**
     * Check if Email is Taken
     * 
     * Verifies if a given email address is already associated with an existing user in the system
     * Optionally, a user ID can be provided to exclude the current user from the check
     *
     * @param string $email The email address to check
     * @param int|null $userId The user ID to exclude from the check, if any (default is null)
     * @return bool Returns true if the email is already taken, false otherwise
     */
    public function isEmailTaken($email, int $userId = null): bool;

    /**
     * Check if Login is Taken
     * 
     * Verifies if a given loginis already associated with an existing user in the system
     * Optionally, a user ID can be provided to exclude the current user from the check
     *
     * @param string $login The login to check
     * @param int|null $userId The user ID to exclude from the check, if any (default is null)
     * @return bool Returns true if the login is already taken, false otherwise
     */
    public function isLoginTaken($email, int $userId = null): bool;
}
