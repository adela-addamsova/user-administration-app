<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Security\User;
use Nette\Security\Passwords;
use Nette\Http\Request;
use Nette\Security\AuthenticationException;

/**
 * Class UserService
 * Handles user-related operations - authentication, registration, and data management. 
 */
class UserService
{
    private Explorer $database;
    private User $user;
    private Passwords $password;
    private Request $httpRequest;


    /** * Constructor
     * Initializes the UserService with database, user, password, and HTTP request handling dependencies
     * @param Explorer $database - Database explorer for interacting with the database.
     * @param User $user - User service for managing user sessions and authentication
     * @param Passwords $password - Service for password hashing and verification
     * @param Request $httpRequest - HTTP request service for handling request data
     */
    public function __construct(Explorer $database, User $user, Passwords $password, Request $httpRequest)
    {
        $this->database = $database;
        $this->user = $user;
        $this->password = $password;
        $this->httpRequest = $httpRequest;
    }

    /**
     * Check Login Status 
     * Checks if the current user is logged in
     * @return bool - Returns true if the user is logged in, false otherwise
     */
    public function checkLoginStatus(): bool
    {
        return $this->user->isLoggedIn();
    }

    /**
     * Login
     * Authenticates a user with the provided username and password, sets session expiration, and logs the login attempt
     * @param string $username - User's username
     * @param string $password - User's password
     * @param bool $remember - Flag indicating whether to remember the user for a longer period
     * @return bool - Returns true if the login is successful
     * @throws AuthenticationException - Thrown if authentication fails */
    public function login(string $username, string $password, bool $remember): bool
    {
        try {
            $this->user->login($username, $password, $remember);

            if ($remember) {
                $this->user->setExpiration('14 days', false);
            } else {
                $this->user->setExpiration('20 minutes', true);
            }

            $this->logLoginAttempt();

            return true;
        } catch (AuthenticationException $e) {
            throw $e;
        }
    }

    /** 
     * Log Login Attempt
     * Logs the details of the user's login attempt - user ID, IP address, and time
     */
    private function logLoginAttempt(): void
    {
        $userId = $this->user->getId();
        $ipAddress = $this->httpRequest->getRemoteAddress();
        $this->database->table('login_logs')->insert([
            'user_id' => $userId,
            'ip_address' => $ipAddress,
        ]);
    }

    /** 
     * Logout User
     * Logs out the current user, ending their session 
     */
    public function logoutUser(): void
    {
        $this->user->logout();
    }

    /**
     * Get User's Data 
     * Retrieves all user data from the database
     * @return - returns a selection of all users from the database 
     */
    public function getUsersData()
    {
        return $this->database->table('users')->where( 'deleted_at IS NULL');
    }

    /** 
     * Get User by ID 
     * Retrieves a user's data by their ID
     * @param int $id - The ID of the user to retrieve
     * @return - Returns the user data as an ActiveRow object or null if the user is not found 
     */
    public function getUserById($id)
    {
        return $this->database->table('users')->get($id);
    }

    /**
     * Is Login Taken
     * Checks if a given login is already taken by another user
     * @param string $login - Login to check
     * @return bool - Returns true if the login is taken, false otherwise
     */
    public function isLoginTaken($login): bool
    {
        return (bool) $this->database->table('users')->where('login', $login)->fetch();
    }

    /**
     * Is Email Taken 
     * Checks if a given email is already taken by another user
     * @param string $email - Email to check
     * @return bool - Returns true if the email is taken, false otherwise
     */
    public function isEmailTaken($email): bool
    {
        return (bool) $this->database->table('users')->where('email', $email)->fetch();
    }

    /**
     * Is Password Valid
     * Validates if the provided password meets the required criteria (at least 8 characters, containing numbers, lowercase, and uppercase letters)
     * @param string $password - Password to validate
     * @return bool - Returns true if the password is valid
     */
    public function isPasswordValid($password): bool
    {
        if (preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {

            return true;
        } else {
            return false;
        }
    }

    /**
     * Update User 
     * Updates the user's data in the database based on the provided user ID and update data
     * @param int|string $id - The ID of the user to update
     * @param array $updateData - An associative array of the data to update
     * @return void
     */
    public function updateUser($id, $updateData): void
    {
        $this->database->table('users')->where('id', $id)->update($updateData);
    }

    /**
     * Register User
     * Registers a new user by checking if the login or email already exists, hashing the password, and inserting the user into the database
     * @param array $data - An associative array containing the user's data
     * @return bool - Returns true if the registration is successful, false otherwise
     */
    public function registerUser(array $data): bool
    {
        if ($this->isLoginTaken($data['login']) || $this->isEmailTaken($data['email'])) {
            return false;
        }

        $hashedPassword = $this->password->hash($data['password']);

        try {
            $this->database->table('users')->insert([
                'login' => $data['login'],
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'email' => $data['email'],
                'password' => $hashedPassword,
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete user
     * Deletes a user that according to id
     * @param mixed $id
     * @return void
     */
    public function deleteUser($id): void
    {
        $this->database->table('users')->where('id', $id)->update(['deleted_at' => new \DateTime()]);
    }
}
