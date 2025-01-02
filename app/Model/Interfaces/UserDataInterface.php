<?php

declare(strict_types=1);

namespace App\Model\Interfaces;

use Nette\Database\Table\Selection;
use Nette\Http\Request as HttpRequest;
use Nette\Security\Passwords;
use Nette\Security\User;

/**
 * Interface UserDataInterface
 * 
 * This interface defines methods for accessing and retrieving user-related data,
 * such as retrieving user information from the database, the authenticated user,
 * password handling, and the HTTP request.
 */
interface UserDataInterface
{
    /**
     * Get All Users' Data
     * 
     * Retrieves all user data from the database
     *
     * @return Selection Returns a selection of all users from the database
     */
    public function getUsersData(): Selection;

    /**
     * Get the Current User
     * 
     * Retrieves the currently authenticated user object
     *
     * @return User Returns the current user
     */
    public function getUser(): User;

    /**
     * Get Password Handling Service
     * 
     * Retrieves the password hashing and verification service used for user authentication
     *
     * @return Passwords Returns the password service
     */
    public function getPassword(): Passwords;

    /**
     * Get HTTP Request
     * 
     * Retrieves the HTTP request object, which contains details about the current HTTP request
     *
     * @return HttpRequest Returns the HTTP request object
     */
    public function getHttpRequest(): HttpRequest;
}
