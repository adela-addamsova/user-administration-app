<?php

declare(strict_types=1);

namespace App\Model\Interfaces;

interface ValidateUserInterface
{
    public function isPasswordValid($password): bool;
    public function isEmailTaken($email, int $userId = null): bool;
    public function isLoginTaken($email, int $userId = null): bool;
}