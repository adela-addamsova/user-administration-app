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
		
        $router[] = new Route('/', 'Login:login');
        $router[] = new Route('/<presenter>/<action>');
        
        return $router;
    }
}

