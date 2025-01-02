<?php

declare(strict_types=1);

namespace App\Model\Facades;

use App\Model\Interfaces\UserManagementInterface;
use App\Model\Interfaces\UserValidationInterface;
use App\Model\Interfaces\UserDataInterface;
use Nette\Database\Explorer;
use Nette\Database\Table\Selection;
use Nette\Security\User;
use Nette\Security\Passwords;
use Nette\Http\Request;

/**
 * Class UsersFacade
 * Handles user-related operations like authentication, registration, and data management
 */
class UsersFacade implements UserDataInterface, UserManagementInterface, UserValidationInterface
{
    private Explorer $database;
    private User $user;
    private Passwords $password;
    private Request $httpRequest;

    /**
     * UsersFacade constructor
     * Initializes the UsersFacade with dependencies for database, user service, password hashing, and HTTP request handling
     * 
     * @param Explorer $database - Database service to interact with user data
     * @param User $user - User service to manage user authentication and sessions
     * @param Passwords $password - Password hashing and validation service
     * @param Request $httpRequest - HTTP request service for obtaining client-side data
     */
    public function __construct(Explorer $database, User $user, Passwords $password, Request $httpRequest)
    {
        $this->database = $database;
        $this->user = $user;
        $this->password = $password;
        $this->httpRequest = $httpRequest;
    }

    /**
     * Get User service instance - returns the instance of the Nette\Security\User service
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Get Passwords service instance - returns the instance of Nette\Security\Passwords for hashing and verifying passwords
     */
    public function getPassword(): Passwords
    {
        return $this->password;
    }

    /**
     * Get HTTP Request service instance - returns the instance of the Nette\Http\Request service to interact with the HTTP request data
     */
    public function getHttpRequest(): Request
    {
        return $this->httpRequest;
    }

    /**
     * Login user
     * 
     * Authenticates the user using their login and password. It also sets session expiration and logs the login attempt
     */
    public function login(string $login, string $password, bool $remember): bool
    {
        try {
            $this->user->login($login, $password, $remember);

            if ($remember) {
                $this->user->setExpiration('14 days', false);
            } else {
                $this->user->setExpiration('20 minutes', true);
            }

            $this->logLoginAttempt();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /** 
     * Log Login Attempt
     * 
     * Logs details about a user's login attempt, including the user ID and the IP address of the attempt
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
     * Logout current user, end user's session
     */
    public function logout(): bool
    {
        try {
            $this->user->logout();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get all users' data - retrieves all user data from the database table users, excluding users marked as deleted
     */
    public function getUsersData(): Selection
    {
        return $this->database->table('users')->where('deleted_at IS NULL');
    }

    /**
     * Check if email is already taken - verifies whether the provided email is already used by another user in the users table
     * If updates user, excludes ID of the user that is being updated from the check
     */
    public function isEmailTaken($email, int $userId = null): bool
    {
        $query = $this->database->table('users')->where('email', $email);
        if ($userId !== null) {
            $query->where('id != ?', $userId);
        }
        return $query->count() > 0;
    }

    /**
     * Check if login is already taken - verifies whether the provided login is already used by another user in the users table
     * If updates user, excludes ID of the user that is being updated from the check
     */
    public function isLoginTaken($login, int $userId = null): bool
    {
        $query = $this->database->table('users')->where('login', $login);
        if ($userId !== null) {
            $query->where('id != ?', $userId);
        }
        return $query->count() > 0;
    }

    /**
     * Check if password is valid - validates if password meets the required criteria (at least 8 characters and include numbers, lowercase, and uppercase letters)
     */
    public function isPasswordValid($password): bool
    {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password) === 1;
    }

    /**
     * Update specific user data in users table
     */
    public function update($id, $updateData): bool
    {
        try {
            $this->database->table('users')->where('id', $id)->update($updateData);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Register new user - registers a new user by verifying the login/email, hashing the password, and saving the user to the usets table
     */
    public function register(array $data): bool
    {
        try {
            $hashedPassword = $this->password->hash($data['password']);

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
     * Delete user - soft deletes a user by updating the 'deleted_at' timestamp in the database
     */
    public function delete($id): bool
    {
        try {
            $this->database->table('users')->where('id', $id)->update(['deleted_at' => new \DateTime()]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
