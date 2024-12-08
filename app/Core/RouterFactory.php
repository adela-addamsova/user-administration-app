<?php

declare(strict_types=1);

namespace App\Core;

use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;

class RouterFactory
{
    public static function createRouter(): RouteList
    {
        $router = new RouteList;
        $router[] = new Route('/home', 'Home:default');
		$router[] = new Route('/', 'Login:login');
        $router[] = new Route('/sign-up', 'SignUp:signup');
        $router[] = new Route('/dashboard', 'Dashboard:dashboard');
		$router[] = new Route('/dashboard/logout', 'Dashboard:logout');
        $router[] = new Route('/dashboard/createUser', 'Signup:create');
        $router[] = new Route('/dashboard/editUser/<id>', 'EditUser:editUser');
        return $router;
    }
}

