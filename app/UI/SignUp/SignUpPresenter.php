<?php

declare(strict_types=1);

namespace App\UI\SignUp;

use App\Model\UserService;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Http\UrlScript;
use Nette\Database\UniqueConstraintViolationException;
use App\Model\UserErrorMessages;

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
     * Get Url
     * @return UrlScript;
     */
    public function getUrl(): UrlScript
    {
        $url = $this->userService->getHttpRequest()->getUrl();
        return $url;
    }

    /**
     * Startup
     * Checks if the user is logged in and redirects to the dashboard page if is authenticated
     * @return void 
     */
    public function startup()
    {
        parent::startup();

        $url = $this->getUrl();

        if (!$this->user->loggedIn && strpos($url->getPath(), 'create')) {
            $this->flashMessage(UserErrorMessages::NOT_LOGGED_IN, 'warning');
            $this->redirect('Login:login');
        } elseif ($this->user->loggedIn && strpos($url->getPath(), 'sign-up')) {
            $this->flashMessage(UserErrorMessages::LOGGED_IN, 'warning');
            $this->redirect('Dashboard:dashboard');
        }
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
            ->setRequired()
            ->addRule(function ($login) {
                $login = $login->getValue();
                if ($this->userService->isLoginTaken($login)) {
                    return false;
                } else {
                    return true;
                }
            }, UserErrorMessages::LOGIN_TAKEN);
        $form->addText('firstname', 'Firstname:')
            ->setHtmlAttribute('placeholder', 'Firstname')
            ->setRequired();
        $form->addText('lastname', 'Lastname:')
            ->setHtmlAttribute('placeholder', 'Lastname')
            ->setRequired();
        $form->addEmail('email', 'E-mail:')
            ->setHtmlAttribute('placeholder', 'E-mail')
            ->setRequired()
            ->addRule(Form::Email, UserErrorMessages::INVALID_EMAIL)
            ->addRule(function ($email) {
                $email = $email->getValue();
                if ($this->userService->isEmailTaken($email)) {
                    return false;
                } else {
                    return true;
                }
            }, UserErrorMessages::EMAIL_TAKEN);
        $form->addPassword('password', 'Password:')
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
            }, UserErrorMessages::WRONG_PASSWORD_FORMAT);
        $form->addPassword('passwordCheck', 'Confirm password:')
            ->setHtmlAttribute('placeholder', '************')
            ->setRequired()
            ->addRule(Form::Equal, UserErrorMessages::NO_PASSWORD_MATCH, $form['password']);
        $form->addSubmit('send', 'Create new user');

        $form->onSuccess[] = [$this, 'signUpFormSuccess'];
        $form->onError[] = [$this, 'signUpFormError'];

        return $form;
    }

    /**
     * Sign Up Form Error
     * Handles the unsuccessful submission of the sign-up form and form validation erros
     * @param Form $form
     * @return void
     */
    public function signUpFormError(Form $form): void
    {
        foreach ($form->getErrors() as $error) {
            $this->flashMessage($error, 'danger');
        }
    }

    /** 
     * Sign Up Form Success 
     * Handles the successful submission of the sign-up form. Validates the password, prepares the user data, and calls the UserService to register the user. 
     * @param Form $form - The submitted form instance
     * @param \stdClass $values - The submitted form values
     * @return void 
     */
    public function signUpFormSuccess(Form $form, $values): void
    {
            $data = [
                'login' => $values->login,
                'firstname' => $values->firstname,
                'lastname' => $values->lastname,
                'email' => $values->email,
                'password' => $values->password
            ];
    
            $registrationResult = $this->userService->registerUser($data);
    
            if ($registrationResult === 'success') {
                $this->registrationSuccessful = true;
                $this->flashMessage('Registration successful!', 'success');
                $this->redirect('this');
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
    }

    /**
     * Render Create 
     * Sets template variables for the registration status 
     * @return void 
     */
    public function renderCreate(): void
    {
        $this->template->registrationSuccessful = $this->registrationSuccessful;
    }
}
