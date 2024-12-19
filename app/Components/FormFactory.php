<?php

namespace App\Components;

use App\Model\UserErrorMessages;
use App\Model\UserService;
use Nette\Application\UI\Form;

class FormFactory
{
    private $userService;
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Creates a form component for user operations
     * @return Form 
     */
    public function createComponentForm(): Form
    {
        $form = new Form;
        $form->addText('id', 'Id:')
            ->setDisabled();
        $form->addText('login', 'Login:')
            ->setHtmlAttribute('placeholder', 'Login')
            ->setRequired();
        $form->addText('firstname', 'Firstname:')
            ->setHtmlAttribute('placeholder', 'Firstname')
            ->setRequired();
        $form->addText('lastname', 'Lastname:')
            ->setHtmlAttribute('placeholder', 'Lastname')
            ->setRequired();
        $form->addEmail('email', 'E-mail:')
            ->setHtmlAttribute('placeholder', 'E-mail')
            ->setRequired();
        $form->addPassword('password', 'Password:')
            ->setHtmlAttribute('placeholder', '************')
            ->addRule(
                function ($passwordFormat): bool {
                    $password = $passwordFormat->getValue();
                    if (!$this->userService->isPasswordValid($password)) {
                        return false;
                    } else {
                        return true;
                    }
                },
                UserErrorMessages::WRONG_PASSWORD_FORMAT
            );

        $form->addPassword('passwordCheck', 'Confirm password:')
            ->setHtmlAttribute('placeholder', '************')
            ->addRule(Form::Equal, UserErrorMessages::NO_PASSWORD_MATCH, $form['password']);

        return $form;
    }
}
