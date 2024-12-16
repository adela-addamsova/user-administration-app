<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Security\Passwords;
use Nette\Security\IIdentity;
use Nette\Security\AuthenticationException;
use Nette\Security\SimpleIdentity;
use Nette\Security\Authenticator;
use App\Model\UserErrorMessages;

/** 
 * Class Authenticator 
 * Implements user authentication
 */
class UserAuthenticator implements Authenticator
{
    private Explorer $database;
    private Passwords $passwords;

    /** 
     * Constructor 
     * Initializes the Authenticator with database and password handling dependencies
     * @param Explorer $database
     * @param Passwords $passwords
     */

    public function __construct(Explorer $database, Passwords $passwords
    )
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

        if ($user && $user->deleted_at !== NULL) {
            throw new AuthenticationException(UserErrorMessages::DELETED_USER);
        }

        if (!$user) {
            throw new AuthenticationException(UserErrorMessages::INVALID_LOGIN);
        }

        if (!$this->passwords->verify($password, $user->password)) {
            throw new AuthenticationException(UserErrorMessages::INVALID_PASSWORD);
        }
        return new SimpleIdentity($user->id, null, $user->toArray());
    }
}
