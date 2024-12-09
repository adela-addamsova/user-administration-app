<?php

namespace App\UI\EditUser;

use App\Model\UserService;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Security\Passwords;

/**
 * Class EditUserPresenter
 * Handles user editing functionality, including form creation, validation, and user data updates
 */
class EditUserPresenter extends Presenter
{
    private $userService;

    /**
     * Constructor
     * Initializes the EditUserPresenter with user service dependency
     * @param UserService $userService - User service for managing user-related operations
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Startup
     * Checks if the user is logged in and redirects to the login page if not authenticated
     * @return void
     */
    public function startup(): void
    {
        parent::startup();

        if (!$this->userService->checkLoginStatus()) {
            $this->flashMessage('You must be logged in to edit users.', 'warning');
            $this->redirect('Login:login');
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
            $this->flashMessage('Invalid user ID.', 'error');
            $this->redirect('Dashboard:dashboard');
            return;
        }

        $user = $this->userService->getUserById($id);
        if (!$user) {
            $this->flashMessage('User not found.', 'error');
            $this->redirect('Dashboard:dashboard');
            return;
        }

        $this['editForm']->setDefaults([
            'id' => $id,
            'login' => $user->login,
            'email' => $user->email,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
        ]);
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

        if (!$user) {
            $this->flashMessage('User not found.', 'error');
            $this->redirect('Dashboard:dashboard');
            return false;
        }

        $form = new Form;

        $form->addText('id', 'Id:')
            ->setRequired('This is the user ID.')
            ->setDefaultValue($id)
            ->setDisabled();

        $form->addText('login', 'Login:')
            ->setRequired('Please enter a username.')
            ->setDefaultValue($user->login);

        $form->addText('email', 'Email:')
            ->setRequired('Please enter an email address.')
            ->setDefaultValue($user->email);

        $form->addText('firstname', 'Firstname:')
            ->setRequired('Please enter a firstname.')
            ->setDefaultValue($user->firstname);

        $form->addText('lastname', 'Lastname:')
            ->setRequired('Please enter a lastname.')
            ->setDefaultValue($user->lastname);

        $form->addPassword('password', 'Password:')
            ->setNullable();

        $form->addSubmit('save', 'Save Changes');

        $form->onSuccess[] = [$this, 'editFormSucceeded'];

        return $form;
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
            $this->flashMessage('User not found.', 'danger');
            $this->redirect('Dashboard:dashboard');
            return;
        }

        $updateData = [];
        if ($user->login !== $values->login) {
            if ($this->userService->isLoginTaken($values->login)) {
                $this->flashMessage('Username is already taken!', 'danger');
                return;
            }
            $updateData['login'] = $values->login;
        }

        if ($user->email !== $values->email) {
            if ($this->userService->isEmailTaken($values->email)) {
                $this->flashMessage('Email is already taken!', 'danger');
                return;
            }
            $updateData['email'] = $values->email;
        }

        if ($user->firstname !== $values->firstname) {
            $updateData['firstname'] = $values->firstname;
        }

        if ($user->lastname !== $values->lastname) {
            $updateData['lastname'] = $values->lastname;
        }

        if (!empty($values->password)) {
            $password = new Passwords();

            $hashedPassword = $password->hash($values->password);

            $updateData['password']  = $hashedPassword;
        }

        if (!empty($updateData)) {
            $this->userService->updateUser($id, $updateData);
            $this->flashMessage('User updated successfully!', 'success');
        } else {
            $this->flashMessage('No changes made.', 'info');
        }
    }

    /**
     * Render Edit User
     * Sets a template variable indicating whether the user is logged in
     * @return void 
     */
    public function renderEditUser(): void
    {
        $this->template->isLoggedIn = $this->user->isLoggedIn();
    }
}
