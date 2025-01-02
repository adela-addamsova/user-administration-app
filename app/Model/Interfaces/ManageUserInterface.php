<?php

declare(strict_types=1);

namespace App\Model\Interfaces;

interface ManageUserInterface {
    public function login(string $username, string $password, bool $remember): void;
    public function logoutUser(): void;
    public function registerUser(array $data);
    public function updateUser($id, $updateData): void;
    public function deleteUser($id): void;

}