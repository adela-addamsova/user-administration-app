<?php

declare(strict_types=1);

namespace App\Model\Interfaces;

/**
 * Interface UserManagementInterface
 * 
 * This interface defines methods for managing user actions such as logging in, logging out, registering, updating,
 * and deleting a user. Implementing classes should provide the actual logic for these actions.
 */
interface UserManagementInterface
{
    /**
     * Login a User
     * 
     * Authenticates the user based on their login and password and logs them in
     * Optionally, a remember flag can be set to keep the user logged in for a longer period
     *
     * @param string $login The login of the user trying to log in
     * @param string $password The password of the user trying to log in
     * @param bool $remember If true, the user will be kept logged in for a longer duration.
     * @return bool Returns true if login is successful, false otherwise
     */
    public function login(string $login, string $password, bool $remember): bool;

    /**
     * Logout the current user
     * 
     * Logs out the currently authenticated user, ending their session
     *
     * @return bool Returns true if logout is successful, false otherwise
     */
    public function logout(): bool;

    /**
     * Register a new User
     * 
     * Registers a new user by accepting an array of user data, performing necessary validations,
     * and storing the new user in the database
     *
     * @param array $data The user data for registering the user
     * @return bool Returns true if registration is successful, false otherwise
     */
    public function register(array $data): bool;

    /**
     * Update User Data
     * 
     * Updates the details of an existing user based on the provided user ID and new data
     *
     * @param mixed $id The ID of the user whose data is to be updated
     * @param array $updateData The updated data for the user
     * @return bool Returns true if the update is successful, false otherwise
     */
    public function update($id, $updateData): bool;

    /**
     * Delete User
     * 
     * Marks a user as deleted based on the provided ID.
     *
     * @param mixed $id The ID of the user to be deleted
     * @return bool Returns true if deletion is successful, false otherwise
     */
    public function delete($id): bool;
}
