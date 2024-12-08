<?php

declare(strict_types=1);

namespace App\UI\SignUp;

use App\Model\UserService;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;

/** 
 * Class SignUpPresenter
 * Handles user sign-up functionality - form creation, validation, and user registration
 */
class SignUpPresenter extends Presenter
{
    private UserService $userService;
    public bool $registrationSuccessful = false;

    /**
     * Constructor
     * Initializes the SignUpPresenter with user service dependency
     * @param UserService $userService - User service for managing user-related operations
     */
    public function __construct(UserService $userService)
    {
        parent::__construct();
        $this->userService = $userService;
    }

    /**
     * Create Component SignUp Form 
     * Creates a sign-up form with fields for login, firstname, lastname, email, password, and password confirmation
     * Adds validation rules and sets up a success callback
     * @return Form - Returns an instance of Nette\Application\UI\Form configured with user registration fields and validation
     */
    protected function createComponentSignUpForm(): Form
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
        $form->addPassword('password', 'Password:')
            ->setHtmlAttribute('placeholder', '************')
            ->setRequired();
        $form->addPassword('passwordCheck', 'Confirm password:')
            ->setHtmlAttribute('placeholder', '************')
            ->setRequired()
            ->addRule(
                $form::EQUAL,
                'Passwords do not match',
                $form['password']
            );
        $form->addSubmit('send', 'Create new user');

        $form->onSuccess[] = [$this, 'signUpFormSuccess'];

        return $form;
    }

    /** 
     * Sign Up Form Success 
     * Handles the successful submission of the sign-up form. Validates the password, prepares the user data, and calls the UserService to register the user. Sets a success or error message based on the registration result. 
     * @param Form $form - The submitted form instance
     * @param \stdClass $values - The submitted form values
     * @return void 
     */
    public function signUpFormSuccess(Form $form, $values): void
    {
        if (!$this->userService->isPasswordValid($values->password)) {
            $this->flashMessage('Password must have at least 8 characters and include numbers, lowercase, and uppercase letters.', 'danger');
            $this->redirect('this');
            return;
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            foreach ($form->getErrors() as $error) {
                echo $error;
            }
        }

        $data = [
            'login' => $values->login,
            'firstname' => $values->firstname,
            'lastname' => $values->lastname,
            'email' => $values->email,
            'password' => $values->password
        ];

        $registrationSuccess = $this->userService->registerUser($data);

        if ($registrationSuccess) {
            $this->registrationSuccessful = true;
            $this->flashMessage('Registration successful!', 'success');
        } else {
            $this->registrationSuccessful = false;
            $this->flashMessage('Username or E-mail is already taken!', 'danger');
        }
    }

    /**
     * Render SignUp 
     * Sets template variables for the registration status and login status
     * @return void 
     */
    public function renderSignUp(): void
    {
        $this->template->registrationSuccessful = $this->registrationSuccessful;
        $this->template->isLoggedIn = $this->user->isLoggedIn();
    }

    /**
     * Render Create 
     * Sets template variables for the registration status and login status
     * @return void 
     */
    public function renderCreate(): void
    {
        $this->template->registrationSuccessful = $this->registrationSuccessful;
        $this->template->isLoggedIn = $this->user->isLoggedIn();
    }
}
