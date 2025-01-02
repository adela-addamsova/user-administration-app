<?php

declare(strict_types=1);

namespace App\UI\Login;

use App\Components\RequireUnloggedUser;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;
use App\Model\Interfaces\UserManagementInterface;
use Nette\Security\AuthenticationException;
use App\Model\Errors\UserErrorMessages;

/**
 * Class LoginPresenter
 * 
 * Handles the user login process, including form creation, validation, and authentication
 */
class LoginPresenter extends Presenter
{
    private UserManagementInterface $manageUser;

    /** 
     * Constructor
     * 
     * Initializes the LoginPresenter with the user management service dependency
     * @param UserManagementInterface $manageUser - Service responsible for user authentication and management
     */
    public function __construct(UserManagementInterface $manageUser)
    {
        parent::__construct();
        $this->manageUser = $manageUser;
    }

    /**
     * Startup
     * 
     * This method checks if a user is already logged in and redirects to the dashboard if authenticated.
     * Otherwise, it continues with the normal flow for unauthenticated users
     * @return void
     */

    use RequireUnloggedUser;
    public function startup()
    {
        parent::startup();

        $this->requireUserUnlogged();
    }

    /**
     * Create Component Login Form
     * 
     * Creates and returns a login form with fields for username, password, and a 'remember me' checkbox
     * The form will be validated on submission, and a success callback is set up to handle the login process
     * @return Form - A configured Nette form instance for user login
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
     * 
     * Processes the form submission upon successful login. Attempts to authenticate the user with the provided credentials.
     * On successful login, the user is redirected to the dashboard. If authentication fails, an appropriate error message is displayed.
     * @param \stdClass $formValues - The values submitted in the login form (username, password, and remember flag)
     * @return void
     */
    public function loginFormSucceeded(\stdClass $formValues): void
    {
        try {
            $this->manageUser->login($formValues->login, $formValues->password, $formValues->remember);
            $this->redirect('Dashboard:');
        } catch (AuthenticationException $e) {
            switch ($e->getMessage()) {

                case UserErrorMessages::DELETED_USER:
                    $this->flashMessage(UserErrorMessages::DELETED_USER, 'danger');
                    break;

                case UserErrorMessages::INVALID_LOGIN:
                    $this->flashMessage(UserErrorMessages::INVALID_LOGIN, 'danger');
                    break;

                case UserErrorMessages::INVALID_PASSWORD:
                    $this->flashMessage(UserErrorMessages::INVALID_PASSWORD, 'danger');
                    break;

                default:
                    $this->flashMessage('Invalid credentials. Please try again.', 'danger');
                    break;
            }
        }
    }
}
