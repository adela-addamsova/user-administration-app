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

        public function createComponentForm(bool $isEditMode): Form
        {
            $form = new Form;
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
                if ($isEditMode) {
                    $form->addPassword('password', 'Password:')
                    ->setNullable();
                } else {$form->addPassword('password', 'Password:')
                    ->setHtmlAttribute('placeholder', '************')
                    ->setRequired()
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
                    );}
            
            $form->addPassword('passwordCheck', 'Confirm password:')
                ->setHtmlAttribute('placeholder', '************')
                ->setRequired()
                ->addRule(Form::Equal, UserErrorMessages::NO_PASSWORD_MATCH, $form['password']);
            $form->addSubmit('send', 'Create new user');

            // $form->onSuccess[] = [$this, 'FormSuccess'];
            // $form->onError[] = [$this, 'FormError'];

            return $form;
        }
    }
