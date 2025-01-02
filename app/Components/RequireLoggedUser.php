<?php

declare(strict_types=1);

namespace App\Components;

use App\Model\Errors\UserErrorMessages;

trait RequireLoggedUser
{
    public function requireUserLogged(): void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage(UserErrorMessages::NOT_LOGGED_IN, 'warning');
            $this->redirect('Login:default');
        };
    }
}
trait RequireUnloggedUser
{
    public function requireUserUnlogged(): void
    {

        if ($this->getUser()->isLoggedIn()) {
            $this->flashMessage(UserErrorMessages::LOGGED_IN, 'warning');
            $this->redirect('Dashboard:');
        };
    }
}
