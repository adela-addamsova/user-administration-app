<?php

declare(strict_types=1);

namespace App\UI\User;

use App\Model\UserService;
use Nette\Application\UI\Presenter;
use Nette\Database\UniqueConstraintViolationException;
use App\Model\UserErrorMessages;
use App\Components\FormFactory;
use Nette\Application\UI\Form;

class UserPresenter extends Presenter
{
    private UserService $userService;
    private FormFactory $formFactory;
    public bool $registrationSuccessful = false;

    /**
     * Constructor
     * Initializes the UserPresenter with user service dependency
     * @param UserService $userService - User service for managing user-related operations
     */
    public function __construct(UserService $userService, FormFactory $formFactory)
    {
        parent::__construct();
        $this->userService = $userService;
        $this->formFactory = $formFactory;
    }

    /**
     * Startup
     * Checks if the user is logged in and redirects to the dashboard page if is authenticated
     * @return void 
     */
    public function startup()
    {
        parent::startup();       

        if (!$this->user->loggedIn && $this->isLinkCurrent('User:create')) {
            $this->flashMessage(UserErrorMessages::NOT_LOGGED_IN, 'warning');
            $this->redirect('Login:login');
        } elseif ($this->user->loggedIn && $this->isLinkCurrent('User:signup')) {
            $this->flashMessage(UserErrorMessages::LOGGED_IN, 'warning');
            $this->redirect('Dashboard:');
        }
    }

    /**
     * Create Component SignUp Form 
     * Creates a sign-up form with fields for login, firstname, lastname, email, password, and password confirmation
     * Adds validation rules and sets up a success callback
     * @return Form - Returns an instance of Nette\Application\UI\Form configured with user registration fields and validation
     */

     protected function createComponentSignUpForm()
     {
        $isEditMode = false;
        
        $signUpForm = $this->formFactory->createComponentForm( $isEditMode);

        
        $signUpForm->onSuccess[] = [$this, 'signUpFormSuccess'];

        return $signUpForm;
     }

     public function signUpFormSuccess(Form $form, $values): void
    {
        try {
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
        } catch(UniqueConstraintViolationException $e) {
            if (strpos($e->getMessage(), 'email') !== false) {
                $form->addError(UserErrorMessages::EMAIL_TAKEN);
            } elseif (strpos($e->getMessage(), 'login') !== false) {
                $form->addError(UserErrorMessages::LOGIN_TAKEN);
            } 
        }
    }

    /**
     * Action Edit User
     * Handles the request to edit a user's information. Checks if the user ID is valid, fetches the user data, and sets the default values in the edit form. 
     * @param int|string $id - The ID of the user to edit
     * @return void 
     */
    public function actionEditUser($id): void
    {
        if (!$id) {
            $this->flashMessage(UserErrorMessages::INVALID_LOGIN, 'error');
            $this->redirect('Dashboard:');
            return;
        }

        $user = $this->userService->getUserById($id);
        if (!$user) {
            $this->flashMessage(UserErrorMessages::INVALID_LOGIN, 'error');
            $this->redirect('Dashboard:');
            return;
        }
    }

    /**
     * Create Component Edit Form
     * Creates a form for editing user's information
     * @return Form - Returns an instance of Nette\Application\UI\Form configured with user editing fields and validation
     */
    protected function createComponentEditForm(): bool|Form
    {
        $isEditMode = $this->getParameter('id') ? true : false;
        $id = $this->getParameter('id');
        $user = $this->userService->getUserById($id);

        $editForm = $this->formFactory->createComponentForm($isEditMode);
        $editForm->addText('id', 'Id:')
            ->setRequired('This is the user ID.')
            ->setDefaultValue($id)
            ->setDisabled();

        $editForm->addSubmit('save', 'Save Changes');

        $editForm->setDefaults([
            'id' => $id,
            'login' => $user->login,
            'email' => $user->email,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
        ]);

        $editForm->onSuccess[] = [$this, 'editFormSuccess'];

        return $editForm;
    }
    
    /**
     * Edit Form Succeeded
     * Handles the successful submission of the edit form. Validates the input, checks for existing logins and emails, hashes the password if provided, updates the user data, and displays an appropriate message based on the operation result
     * @param Form $form - The submitted form instance
     * @param \stdClass $values - The submitted form values
     * @return void 
     */
    public function editFormSucceeded(Form $form, $values): void
    {
        $id = $this->getParameter('id');
        $user = $this->userService->getUserById($id);
        if (!$user) {
            $form->addError(UserErrorMessages::INVALID_LOGIN);
            $this->redirect('Dashboard:');
            return;
        }

        $updateData = [];

        if ($this->userService->isLoginTaken($values->login)) {
            $this->flashMessage(UserErrorMessages::LOGIN_TAKEN, 'danger');
            return;
        }
        $updateData['login'] = $values->login;


        if ($this->userService->isEmailTaken($values->email)) {
            $this->flashMessage(UserErrorMessages::EMAIL_TAKEN, 'danger');
            return;
        }
        
        $updateData['email'] = $values->email;
        $updateData['firstname'] = $values->firstname;
        $updateData['lastname'] = $values->lastname;
        
       
        if (!empty($values->password)) {
            $password = $this->userService->getPassword();

            if (!$this->userService->isPasswordValid($values->password)) {
                $this->flashMessage(UserErrorMessages::WRONG_PASSWORD_FORMAT, 'danger');
                return;
            }

            $hashedPassword = $password->hash($values->password);

            $updateData['password'] = $hashedPassword;
        }

        
        if (!empty($updateData)) {
            $this->userService->updateUser($id, $updateData);
            $this->flashMessage('User updated successfully!', 'success');
            bdump($user);
        } else {
            $this->flashMessage('No changes made.', 'info');
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
