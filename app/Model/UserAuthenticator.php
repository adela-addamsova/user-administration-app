<?php

declare(strict_types=1);


use Nette\Database\Explorer;
use Nette\Security\Passwords;
use Nette\Security\IIdentity;
use Nette\Security\AuthenticationException;
use Nette\Security\SimpleIdentity;

/** 
 * Class Authenticator 
 * Implements user authentication
 */
class Authenticator implements Nette\Security\Authenticator
{
    private Explorer $database;
    private Passwords $passwords;

    /** 
     * Constructor 
     * Initializes the Authenticator with database and password handling dependencies
     * @param Explorer $database
     * @param Passwords $passwords
     */

    public function __construct(Explorer $database, Passwords $passwords)
    {
        $this->database = $database;
        $this->passwords = $passwords;
    }

    /** 
     * Authenticate 
     *
     * Authenticates a user by verifying their username and password against the database records
     * @param string $username - User's username 
     * @param string $password - User's password
     * @return IIdentity - Returns a SimpleIdentity instance representing the authenticated user
     * @throws AuthenticationException - Throws exception if authentication fails due to invalid username or password
     */
    public function authenticate(string $login, string $password): IIdentity
    {
        $user = $this->database->table('users')->where('login', $login)->fetch();

        if($user && $user->deleted_at !== NULL) {
            throw new AuthenticationException('Deleted user');
        }

        if (!$user) {
            throw new AuthenticationException('Invalid login');
        }

        if (!$this->passwords->verify($password, $user->password)) {
            throw new AuthenticationException('Invalid password');
        }
        return new SimpleIdentity($user->id, null, $user->toArray());
    }
}
