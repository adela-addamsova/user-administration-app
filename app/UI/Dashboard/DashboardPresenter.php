<?php

declare(strict_types=1);

namespace App\UI\Dashboard;

use Ublaboo\DataGrid\DataGrid;
use Nette\Application\UI\Presenter;
use App\Model\UserService;

/**
 * Class DashboardPresenter
 * Handles dashboard-related operations - displaying users
 */
class DashboardPresenter extends Presenter
{
    private UserService $userService;

    /** 
     * Constructor
     * Initializes the DashboardPresenter with user service dependency
     * @param UserService $userService - User service for managing user-related operations
     */
    public function __construct(UserService $userService)
    {
        parent::__construct();
        $this->userService = $userService;
    }

    /**
     * Startup
     * Checks if the user is logged in and redirects to the login page if not authenticated
     * @return void 
     */
    public function startup()
    {
        parent::startup();

        if (!$this->userService->checkLoginStatus()) {
            $this->flashMessage('You must be logged in to access this section.', 'warning');
            $this->redirect('Login:login');
        }
    }

    /**
     * Action Logout
     * Logs out the current user and redirects to the login page
     * @return void 
     */
    public function actionLogout()
    {
        $this->userService->logoutUser();
        $this->flashMessage('Logout successful', 'success');
        $this->redirect('Login:login');
    }

    /**
     * Create Component Simple Grid
     * Creates a DataGrid component for displaying users data
     * @return DataGrid - Returns an instance of Ublaboo\DataGrid\DataGrid
     */
    protected function createComponentSimpleGrid(): DataGrid
    {
        $grid = new DataGrid($this, 'simpleGrid');

        $grid->setDataSource($this->userService->getUsersData());

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

        $grid->addFilterText('id', 'Search by Login')
            ->addAttribute('placeholder', 'Search by id');
        $grid->addFilterText('login', 'Search by Login')
            ->addAttribute('placeholder', 'Search by username');
        $grid->addFilterText('firstname', 'Search by Login')
            ->addAttribute('placeholder', 'Search by firstname');
        $grid->addFilterText('lastname', 'Search by Login')
            ->addAttribute('placeholder', 'Search by lastname');
        $grid->addFilterText('email', 'Search by Login')
            ->addAttribute('placeholder', 'Search by email');

        $grid->addAction('edit', 'Edit User', 'editUser!')
            ->setIcon('edit')
            ->setTitle('Edit')
            ->setClass('btn btn-xs btn-primary');


        return $grid;
    }

    /**
     * Handle Edit User
     * Handles the edit action for a user and redirects to the EditUser presenter
     * @param int|string $id - ID of the user to edit
     * @return void
     */
    public function handleEditUser($id)
    {
        $this->redirect('EditUser:editUser', ['id' => $id]);
    }
}
