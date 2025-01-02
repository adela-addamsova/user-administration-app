<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Model\Errors\UserErrorMessages;
use App\Model\Interfaces\UserValidationInterface;
use Nette\Application\UI\Form;

class FormFactory
{
    private UserValidationInterface $validateUser;

    /**
     * Constructor
     * 
     * Initializes the FormFactory with the user validation service
     * @param UserValidationInterface $validateUser - Service responsible for user validation logic, such as password format validation
     */
    public function __construct(UserValidationInterface $validateUser)
    {
        $this->validateUser = $validateUser;
    }

    /**
     * Create User Form Component
     * 
     * Builds and returns a form used for user operations such as registration or updating user details
     * Includes fields like id, login, firstname, lastname, email, password, and password confirmation
     * @return Form - Returns an instance of the Nette form configured for user input
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
                    if (!$this->validateUser->isPasswordValid($password)) {
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
