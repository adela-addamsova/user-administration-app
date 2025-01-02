<?php

declare(strict_types=1);

namespace App\Components;

trait RenderVariables
{
    public function injectTemplateVariables(): void
    {
        $this->onRender[] = function (): void {
            $this->template->registrationSuccessful = $this->registrationSuccessful;
            $this->template->isCreateActive = $this->isLinkCurrent('User:create');
            $this->template->isSignupActive = $this->isLinkCurrent('User:signup');
            $this->template->isEditActive = $this->isLinkCurrent('User:edit');
        };
    }
}
