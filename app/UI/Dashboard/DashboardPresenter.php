<?php

declare(strict_types=1);

namespace App\UI\Dashboard;

use App\Model\UserService;
use Nette\Application\UI\Presenter;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Column\Action\Confirmation\CallbackConfirmation;
use App\Model\UserErrorMessages;

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

        if (!$this->userService->getUser()->loggedIn) {
            $this->flashMessage(UserErrorMessages::NOT_LOGGED_IN, 'warning');
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
     * Creates a DataGrid component for displaying users' data
     * @return DataGrid - Returns an instance of Ublaboo\DataGrid\DataGrid
     */
    protected function createComponentSimpleGrid(): DataGrid
    {
        $grid = new DataGrid($this, 'simpleGrid');

        $grid->setCustomPaginatorTemplate(__DIR__ . '/grid/data_grid.paginator.latte');

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

        $grid->addFilterText('id', 'Search id')
            ->addAttribute('placeholder', ' Search by id');
        $grid->addFilterText('login', 'Search login')
            ->addAttribute('placeholder', ' Search by username');
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
            ->setConfirmation(
                new CallbackConfirmation(
                    function ($user):string {
                        $confirmMessage = "Do you really want to delete user with id $user->id?";
                        return $confirmMessage;
                    }
                )
            );

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

    /**
     * Handle Delete User
     * Handles the delete action for a user and redirects back to dashboard
     * @param int|string $id - ID of the user to delete
     * @return void
     */
    public function handleDeleteUser($id): void
    {
        $this->userService->deleteUser($id);

        $this->flashMessage("User with id $id has been deleted.", 'success');

        if ($this->isAjax()) {
            $this['actionsGrid']->reload();
        } else {
            $this->redirect('this');
        }
    }
}
