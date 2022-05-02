<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
    use Nette\StaticClass;

    public static function createRouter(): RouteList
    {
        {
            $router = new RouteList;
            $router[] = $adminRoute = new RouteList('Admin');
            $adminRoute[] = new Route(
                'admin/<presenter>[/<action>][/<id>]', [

                                                     'presenter' => 'Admin',
                                                     'action' => 'default',
                                                     'id' => null,
                                                 ]
            );

            $router[] = $frontRoute = new RouteList('Front');
            // SK router
            $frontRoute[] = new Route('priklad', ['presenter' => 'Example', 'action' => 'default', 'locale' => 'sk']);
            $frontRoute[] = new Route('en/example', ['presenter' => 'Example', 'action' => 'default', 'locale' => 'en']);
            $frontRoute[] = new Route('[<locale=sk sk|en>/]<presenter>[/<action>][/<id>]', 'Default:default');
            return $router;
        }
    }
}
