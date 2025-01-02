<?php

namespace App\Model\Interfaces;

use Nette\Database\Table\Selection;
use Nette\Http\Request as HttpRequest;
use Nette\Security\Passwords;
use Nette\Security\User;

interface UserDataInterface
{
    public function getUsersData(): Selection;
    public function getUser(): User;
    public function getPassword(): Passwords;
    public function getHttpRequest(): HttpRequest;    
}
