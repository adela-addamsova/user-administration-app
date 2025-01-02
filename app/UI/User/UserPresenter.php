<?php

declare(strict_types=1);

namespace App\UI\User;

use App\Components\RequireLoggedUser;
use App\Components\RequireUnloggedUser;
use App\Components\RenderVariables;
use App\Model\Interfaces\UserValidationInterface;
use Nette\Application\UI\Presenter;
use Nette\Database\UniqueConstraintViolationException;
use App\Model\Errors\UserErrorMessages;
use App\Model\Factories\FormFactory;
use App\Model\Interfaces\UserManagementInterface;
use Nette\Application\UI\Form;
use App\Model\Interfaces\UserDataInterface;

class UserPresenter extends Presenter
{
    private UserManagementInterface $manageUser;
    private UserValidationInterface $userValidation;
    private UserDataInterface $userData;
    private FormFactory $formFactory;
    public bool $registrationSuccessful = false;

    /**
     * Constructor
     * 
     * Initializes the UserPresenter with user services and form factory dependency
     * @param UserManagementInterface $manageUser - Service for managing user-related operations (e.g., registration, editing)
     * @param UserValidationInterface $userValidation - Service for validating user data (e.g., login, email, password)
     * @param UserDataInterface $userData - Service for accessing user data (e.g., user details)
     * @param FormFactory $formFactory - Factory to create form components for user actions
     */
    public function __construct(UserDataInterface $userData, UserManagementInterface $manageUser, UserValidationInterface $userValidation, FormFactory $formFactory)
    {
        parent::__construct();
        $this->userData = $userData;
        $this->manageUser = $manageUser;
        $this->userValidation = $userValidation;
        $this->formFactory = $formFactory;
    }

    /**
     * Startup
     * 
     * Checks if the user is logged in and redirects to the appropriate page based on their login status
     * @return void
     */
    use RequireLoggedUser;
    use RequireUnloggedUser;

    public function startup(): void
    {
        parent::startup();

        if ($this->isLinkCurrent('User:create') || $this->isLinkCurrent('User:edit')) {
            $this->requireUserLogged();
        } elseif ($this->isLinkCurrent('User:signup')) {
            $this->requireUserUnlogged();
        }
    }

    /**
     * Create Component SignUp Form
     * 
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
     * 
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

            $registrationResult = $this->manageUser->register($data);

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
     * Create Component Edit Form
     * 
     * Creates a form for editing user's information
     * @return Form - Returns an instance of Nette\Application\UI\Form configured with user editing fields and validation
     */
    protected function createComponentEditForm(): bool|Form
    {
        
        $id = $this->getParameter('id');
        $user = $this->userData->getUsersData()->get($id);

        if (!$user) {
            $this->flashMessage(UserErrorMessages::INVALID_LOGIN, 'danger');
            $this->redirect('Dashboard:');
            return false;
        }

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
     * 
     * Handles the successful submission of the edit form. Validates the input, checks for existing logins and emails, hashes the password if provided, updates the user data, and displays an appropriate message based on the operation result
     * @param Form $form - The submitted form instance
     * @param \stdClass $values - The submitted form values
     * @return void
     */
    public function editFormSuccess(Form $form, $values): void
    {
        try {
            $id = $this->getParameter('id');
            $user = $this->userData->getUsersData()->get($id);

            if (!$user) {
                $form->addError(UserErrorMessages::INVALID_LOGIN);
                $this->redirect('Dashboard:');
                return;
            }

            $updateData = [];

            if ($this->userValidation->isLoginTaken($values->login, $user->id)) {
                $form->addError(UserErrorMessages::LOGIN_TAKEN);
                return;
            }

            if ($values->login !== $user->login) {
                $updateData['login'] = $values->login;
            }

            if ($this->userValidation->isEmailTaken($values->email, $user->id)) {
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
                $password = $this->userData->getPassword();
                if (!$this->userValidation->isPasswordValid($values->password)) {
                    $this->flashMessage(UserErrorMessages::WRONG_PASSWORD_FORMAT, 'danger');
                    return;
                }
                $hashedPassword = $password->hash($values->password);
                if ($hashedPassword !== $user->password) {
                    $updateData['password'] = $hashedPassword;
                }
            }

            if (!empty($updateData)) {
                $this->manageUser->update($id, $updateData);
                $this->flashMessage('User updated successfully!', 'success');
            } else {
                $this->flashMessage('No changes made.', 'info');
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
     * Render SignUp
     * Sets template variables for the registration status and login status
     * @return void
     */
    use RenderVariables;
    public function renderSignUp(): void
    {
        $this->injectTemplateVariables();
        $this->template->title = 'Sign Up';
        $this->template->form = $this['signUpForm'];
    }

    /**
     * Render Edit
     * Sets template variables for the registration status and form fields
     * @return void
     */
    public function renderEdit(): void
    {
        $this->injectTemplateVariables();
        $this->template->title = 'Edit User';
        $this->template->form = $this['editForm'];
    }

    /**
     * Render Create
     * Sets template variables for the registration status and form fields
     * @return void
     */
    public function renderCreate()
    {
        $this->injectTemplateVariables();
        $this->template->title = 'Create new user';
        $this->template->form = $this['signUpForm'];
    }
}
