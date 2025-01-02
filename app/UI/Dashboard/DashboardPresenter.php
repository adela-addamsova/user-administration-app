<?php

declare(strict_types=1);

namespace App\UI\Dashboard;

use App\Components\RequireLoggedUser;
use App\Model\Interfaces\ManageUserInterface;
use App\Model\Interfaces\UserDataInterface;
use Nette\Application\UI\Presenter;
use Ublaboo\DataGrid\DataGrid;

/**
 * Class DashboardPresenter
 * Handles dashboard-related operations - displaying users
 */
class DashboardPresenter extends Presenter
{
    private ManageUserInterface $manageUser;
    private UserDataInterface $userData;

    /** 
     * Constructor
     * Initializes the DashboardPresenter with user service dependency
     * @param ManageUserInterface $manageUser - User service for managing user-related operations
     */
    public function __construct(ManageUserInterface $manageUser, UserDataInterface $userData)
    {
        parent::__construct();
        $this->manageUser = $manageUser;
        $this->userData = $userData;
    }

    use RequireLoggedUser;
    /**
     * Startup
     * Checks if the user is logged in and redirects to the login page if not authenticated
     * @return void 
     */
    public function startup()
    {
        parent::startup();

        $this->requireUserLogged();
    }

    /**
     * Action Logout
     * Logs out the current user and redirects to the login page
     * @return void 
     */
    public function actionLogout()
    {
        $this->manageUser->logoutUser();
        $this->flashMessage('Logout successful!', 'success');
        $this->redirect('Login:');
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
     * Handles the edit action for a user and redirects to the EditUser presenter
     * @param int|string $id - ID of the user to edit
     * @return void
     */
    public function handleEditUser($id)
    {
        $this->redirect('User:edit', ['id' => $id]);
    }

    /**
     * Handle Delete User
     * Handles the delete action for a user and redirects back to dashboard
     * @param int|string $id - ID of the user to delete
     * @return void
     */
    public function handleDeleteUser($id): void
    {
        $this->manageUser->deleteUser($id);

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
