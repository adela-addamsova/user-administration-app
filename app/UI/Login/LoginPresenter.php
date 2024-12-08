<?php

declare(strict_types=1);

namespace App\UI\Login;

use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;
use App\Model\userService;
use Nette\Security\AuthenticationException;

/**
 * Class LoginPresenter
 * Handles user login functionality - form creation, validation, and user authentication
 */
class LoginPresenter extends Presenter
{
    private userService $userService;

    /** 
     * Constructor 
     * Initializes the LoginPresenter with user service dependency
     * @param userService $userService - User service for managing user-related operations
     */
    public function __construct(userService $userService)
    {
        parent::__construct();
        $this->userService = $userService;
    }

    /**
     * Create Component Login Form 
     * Creates a login form with fields for username, password, and a remember me checkbox
     * Sets up a success callback for form submission
     * @return Form - Returns an instance of Nette\Application\UI\Form configured with login fields and validation
     */
    protected function createComponentLoginForm(): Form
    {
        $form = new Form;
        $form->addText('login', 'Username:')
            ->setRequired();
        $form->addPassword('password', 'Password:')
            ->setRequired();
        $form->addCheckbox('remember', ' Remember me');
        $form->addSubmit('send', 'Log In');

        $form->onSuccess[] = [$this, 'loginFormSucceeded'];

        return $form;
    }

    /**
     * Login Form Succeeded
     * Handles the successful submission of the login form. Attempts to log in the user using the userService and redirects to the dashboard on success
     * @param Form $form - The submitted form instance
     * @param \stdClass $values - The submitted form values
     * @return void 
     */
    public function loginFormSucceeded(Form $form, \stdClass $values): void
    {
        try {
            $this->userService->login($values->login, $values->password, $values->remember);
            $this->redirect('Dashboard:dashboard');
        } catch (AuthenticationException $e) {
            if ($e->getMessage() === 'invalid login') {
                $this->flashMessage('The username does not exist.', 'danger');
            } elseif ($e->getMessage() === 'invalid password') {
                $this->flashMessage('The password is incorrect.', 'danger');
            } else {
                $this->flashMessage('Invalid credentials. Please try again.', 'danger');
            }
        }
    }

    /**
     * Render Login 
     * Sets a template variable indicating whether the user is logged in
     * @return void 
     */
    public function renderLogin(): void
    {
        $this->template->isLoggedIn = $this->user->isLoggedIn();
    }
}
