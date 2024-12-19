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
     * Initializes the UserPresenter with user service and form factory dependency
     * @param UserService $userService - User service for managing user-related operations
     * @param FormFactory $formFactory
     */
    public function __construct(UserService $userService, FormFactory $formFactory)
    {
        parent::__construct();
        $this->userService = $userService;
        $this->formFactory = $formFactory;
    }

    /**
     * Startup
     * Checks if the user is logged in and redirects to the appropriate page based on their login status
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
        $signUpForm = $this->formFactory->createComponentForm();

        $signUpForm->addSubmit('send', 'Create User');
        $signUpForm->onSuccess[] = [$this, 'signUpFormSuccess'];

        return $signUpForm;
    }

    /**
     * Signup Form Success
     * Handles the successful submission of the sign-up form
     * @param Form $form - The submitted form instance 
     * @param \stdClass $values - The submitted form values
     * @return void
     */
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
        } catch (UniqueConstraintViolationException $e) {
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
        $user = $this->userService->getUserById($id);
        if (!$id) {
            $this->flashMessage(UserErrorMessages::INVALID_LOGIN, 'error');
            $this->redirect('Dashboard:');
            return;
        }

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
        $id = $this->getParameter('id');
        $user = $this->userService->getUserById($id);

        $editForm = $this->formFactory->createComponentForm();

        $editForm->setDefaults([
            'id' => $id,
            'login' => $user->login,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'email' => $user->email,
        ]);

        $editForm->addSubmit('send', 'Edit User');


        $editForm->onSuccess[] =  [$this, 'editFormSuccess'];

        $editForm->setAction($this->link('User:edit', ['id' => $id]));

        return $editForm;
    }

    /**
     * Edit Form Succeeded
     * Handles the successful submission of the edit form. Validates the input, checks for existing logins and emails, hashes the password if provided, updates the user data, and displays an appropriate message based on the operation result
     * @param Form $form - The submitted form instance
     * @param \stdClass $values - The submitted form values
     * @return void 
     */
    public function editFormSuccess(Form $form, $values): void
    {
        $id = $this->getParameter('id');
        $user = $this->userService->getUserById($id);
        
        if (!$user) {
            $form->addError(UserErrorMessages::INVALID_LOGIN);
            $this->redirect('Dashboard:');
            return;
        }

        $updateData = [];

        if ($this->userService->isLoginTaken($values->login, $user->id)) {
            $this->flashMessage(UserErrorMessages::LOGIN_TAKEN, 'danger');
            return;
        }

        if ($values->login !== $user->login) {
            $updateData['login'] = $values->login;
        }

        if ($this->userService->isEmailTaken($values->email, $user->id)) {
            $this->flashMessage(UserErrorMessages::EMAIL_TAKEN, 'danger');
            return;
        }

        if ($values->email !== $user->email) {
            $updateData['email'] = $values->email;
        }

        if ($values->firstname !== $user->firstname) {
            $updateData['firstname'] = $values->firstname;
        }

        if ($values->lastname !== $user->lastname) {
            $updateData['lastname'] = $values->lastname;
        }

        if (!empty($values->password)) {
            $password = $this->userService->getPassword();
            if (!$this->userService->isPasswordValid($values->password)) {
                $this->flashMessage(UserErrorMessages::WRONG_PASSWORD_FORMAT, 'danger');
                return;
            }
            $hashedPassword = $password->hash($values->password);
            if ($hashedPassword !== $user->password) {
                $updateData['password'] = $hashedPassword;
            }
        }

        if (!empty($updateData)) {
            $this->userService->updateUser($id, $updateData);
            $this->flashMessage('User updated successfully!', 'success');
        } else {
            $this->flashMessage('No changes made.', 'info');
        }
        
        // $this->redirect('this', ['id' => $id]);
    }

    /** 
     * Render SignUp
     * Sets template variables for the registration status and login status
     * @return void
     */
    public function renderSignUp(): void
    {
        $this->template->registrationSuccessful = $this->registrationSuccessful;
        $this->template->isCreateActive = $this->isLinkCurrent('User:create');
        $this->template->isSignupActive = $this->isLinkCurrent('User:signup');
        $this->template->isEditActive = $this->isLinkCurrent('User:edit');
        $this->template->form = $this['signUpForm'];
    }

    /**
     * Render Edit
     * Sets template variables for the registration status and form fields
     * @return void 
     */
    public function renderEdit(): void
    {
        $this->template->registrationSuccessful = $this->registrationSuccessful;
        $this->template->isCreateActive = $this->isLinkCurrent('User:create');
        $this->template->isSignupActive = $this->isLinkCurrent('User:signup');
        $this->template->isEditActive = $this->isLinkCurrent('User:edit');
        $this->template->form = $this['editForm'];
    }

    /**
     * Render Create 
     * Sets template variables for the registration status and form fields
     * @return void
     */
    public function renderCreate()
    {
        $this->template->registrationSuccessful = $this->registrationSuccessful;
        $this->template->isCreateActive = $this->isLinkCurrent('User:create');
        $this->template->isSignupActive = $this->isLinkCurrent('User:signup');
        $this->template->isEditActive = $this->isLinkCurrent('User:edit');
        $this->template->form = $this['signUpForm'];
    }
}
