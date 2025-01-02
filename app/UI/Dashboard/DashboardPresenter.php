<?php

declare(strict_types=1);

namespace App\UI\Dashboard;

use App\Components\RequireLoggedUser;
use App\Model\Interfaces\UserManagementInterface;
use App\Model\Interfaces\UserDataInterface;
use Nette\Application\UI\Presenter;
use Ublaboo\DataGrid\DataGrid;

/**
 * Class DashboardPresenter
 * 
 * Manages the dashboard view, user data display, and user operations like logout, edit, and delete
 */
class DashboardPresenter extends Presenter
{
    private UserManagementInterface $manageUser;
    private UserDataInterface $userData;

    /** 
     * Constructor
     * 
     * Initializes the DashboardPresenter with user management and user data services
     * @param UserManagementInterface $manageUser - Service for managing user-related operations
     * @param UserDataInterface $userData - Service for fetching user data to be displayed in the dashboard
     */
    public function __construct(UserManagementInterface $manageUser, UserDataInterface $userData)
    {
        parent::__construct();
        $this->manageUser = $manageUser;
        $this->userData = $userData;
    }

    /**
     * Startup
     * 
     * Checks if the user is logged in and redirects to the login page if the user is not authenticated
     * @return void
     */
    use RequireLoggedUser;
    public function startup(): void
    {
        parent::startup();

        $this->requireUserLogged();
    }

    /**
     * Action Logout
     * 
     * Logs out the current user and redirects to the login page with a success message
     * @return void
     */
    public function actionLogout()
    {
        $this->manageUser->logout();
        $this->flashMessage('Logout successful!', 'success');
        $this->redirect('Login:');
    }

    /**
     * Create Component Simple Grid
     * 
     * Creates a DataGrid component for displaying users' data
     * @return DataGrid - Returns an instance of Ublaboo\DataGrid\DataGrid
     */
    protected function createComponentSimpleGrid(): DataGrid
    {
        $grid = new DataGrid($this, 'simpleGrid');

        $grid->setCustomPaginatorTemplate(__DIR__ . '/grid/data_grid.paginator.latte');

        $grid->setDataSource($this->userData->getUsersData());

        $grid->addColumnText('id', 'Id')
            ->setSortable();
        $grid->addColumnText('login', 'Username')
            ->setSortable();
        $grid->addColumnText('email', 'Email')
            ->setSortable();
        $grid->addColumnText('firstname', 'Firstname')
            ->setSortable();
        $grid->addColumnText('lastname', 'Lastname')
            ->setSortable();

        $grid->addFilterText('id', 'Search id')
            ->addAttribute('placeholder', ' Search by id');
        $grid->addFilterText('login', 'Search login')
            ->addAttribute('placeholder', ' Search by login');
        $grid->addFilterText('firstname', 'Search firstname')
            ->addAttribute('placeholder', ' Search by firstname');
        $grid->addFilterText('lastname', 'Search lastname')
            ->addAttribute('placeholder', ' Search by lastname');
        $grid->addFilterText('email', 'Search email')
            ->addAttribute('placeholder', ' Search by email');

        $grid->addAction('edit', 'Edit', 'editUser!')
            ->setTitle('Edit')
            ->setClass('btn btn-xs btn-primary edit');

        $grid->addAction('delete', 'Delete', 'deleteUser!')
            ->setTitle('Delete')
            ->setClass('btn btn-xs btn-primary delete')
            ->setRenderer(function ($user) {
                return '<a href="' . $this->link('deleteUser!', $user->id) . '" class="btn btn-xs btn-primary delete" 
                data-id="' . $user->id . '" 
                data-name="' . $user->login . '" 
                data-message="Do you really want to delete user ' . $user->login . ' with id ' . $user->id . '?">Delete</a>';
            });

        return $grid;
    }

    /**
     * Handle Edit User
     * 
     * Handles the edit action for a user and redirects to the EditUser presenter
     * @param int $id - ID of the user to edit
     * @return void
     */
    public function handleEditUser(int $id)
    {
        $this->redirect('User:edit', ['id' => $id]);
    }

    /**
     * Handle Delete User
     * 
     * Handles the request to delete a user and provides feedback via flash message and redirects
     * @param int $id - The ID of the user to be deleted
     * @return void
     */
    public function handleDeleteUser(int $id): void
    {
        $this->manageUser->delete($id);

        if ($this->isAjax()) {
            $this->sendJson([
                'redirect' => $this->link('Dashboard:'),
                'flashMessage' => "User with id $id has been deleted."
            ]);
        } else {
            $this->redirect('Dashboard:');
        }
    }
}
