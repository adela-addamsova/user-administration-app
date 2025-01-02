<?php

declare(strict_types=1);

namespace App\UI\Login;

use App\Components\RequireUnloggedUser;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;
use App\Model\Interfaces\ManageUserInterface;
use Nette\Security\AuthenticationException;
use App\Model\Errors\UserErrorMessages;

/**
 * Class LoginPresenter
 * Handles user login functionality - form creation, validation, and user authentication
 */
class LoginPresenter extends Presenter
{
    private ManageUserInterface $manageUser;

    /** 
     * Constructor 
     * Initializes the LoginPresenter with user service dependency
     * @param ManageUserInterface $manageUser - User service for managing user-related operations
     */
    public function __construct(ManageUserInterface $manageUser)
    {
        parent::__construct();
        $this->manageUser = $manageUser;
    }

    /**
     * Startup
     * Checks if the user is logged in and redirects to the dashboard page if is authenticated
     * @return void 
     */

   use RequireUnloggedUser;

   
    /**
     * Startup
     * Checks if the user is logged in and redirects to the login page if not authenticated
     * @return void 
     */
    public function startup()
    {
        parent::startup();

        $this->requireUserUnlogged();
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
     * Handles the successful submission of the login form. Attempts to log in the user using the manageUser and redirects to the dashboard on success
     * @param \stdClass $formValues - The submitted form values
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
