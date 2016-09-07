<?php

namespace vojtabiberle\MediaStorage\Bridges\Nette\Router;

use Nette\Application\IRouter;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use vojtabiberle\MediaStorage\Files\IFile;

class Factory
{
    const DELIMETER = ':';

    /**
     * @param array $config
     * @return RouteList|array
     */
    public static function createRouter(array $config) {
        $router = new RouteList;

        /*$router[] = new Route(
            $config['adminMask'],
            [
                'module' => 'MediaStorage',
                'presenter' => 'Media',
                'action' => 'grid'
            ]
        );*/

        $router[] = new Route(
            $config['popupAction'],
            [
                'module' => 'MediaStorage',
                'presenter' => 'MediaPopup',
                'action' => 'gridPopup'
            ]
        );

        $router[] = new Route(
            $config['deleteAction'],
            [
                'module' => 'MediaStorage',
                'presenter' => 'Media',
                'action' => 'delete'
            ]
        );

        $router[] = new Route(
            $config['filesMask'],
            [
                'module' => 'MediaStorage',
                'presenter' => 'Files',
                'action' => 'default',
                'name' => [
                    Route::FILTER_IN => function ($name)
                    {
                        return self::decodeName($name);
                    },
                    Route::FILTER_OUT => function ($name)
                    {
                        return self::encodeName($name);
                    }
                ],
            ]
        );

        $router[] = new Route(
            $config['imagesMask'],
            [
                'module'     => 'MediaStorage',
                'presenter' => 'Images',
                'action' => 'default',
                /*'filter' => [
                    Route::FILTER_IN => function($filter)
                    {
                        $filter = str_replace('filter-', '', $filter);
                        return $filter;
                    },
                    Route::FILTER_OUT => function ($filter)
                    {
                        $filter = 'filter-'.$filter;
                        return $filter;
                    }
                ],
                'size' => [
                    Route::FILTER_IN => function ($size)
                    {
                        $size = str_replace('size-', '', $size);
                        $size = str_replace('%25', '%', $size);
                        return $size;
                    },
                    Route::FILTER_OUT => function ($size)
                    {
                        $size = 'size-'.$size;
                        $size = str_replace('%', '%25', $size);
                        return $size;
                    }
                ],*/
                'noimage' => [
                    Route::FILTER_IN => function ($noimage)
                    {
                        //$noimage = str_replace('noimage-', '', $noimage);
                        return self::decodeName($noimage);
                    },
                    Route::FILTER_OUT => function ($noimage)
                    {
                        //$noimage = 'noimage-'.$noimage;
                        return self::encodeName($noimage);
                    }
                ],
                'name'    => [
                    Route::FILTER_IN => function ($name)
                    {
                        return self::decodeName($name);
                    },
                    Route::FILTER_OUT => function ($name)
                    {
                        return self::encodeName($name);
                    }
                ],
            ],
            $config['flag']
        );

        $router[] = new Route(
            $config['iconsMask'],
            [
                'module' => 'MediaStorage',
                'presenter' => 'Icons',
                'action' => 'default',
                'name'    => [
                    Route::FILTER_IN => function ($type)
                    {
                        //$type = str_replace('.png', '', $type);
                        return $type;
                    },
                    Route::FILTER_OUT => function ($type)
                    {
                        /*if (strpos($type, '/')) {
                            list(, $type) = explode('/', $type);
                        }*/
                        //$type = str_replace('/', '-', $type);
                        //$type .= '.png';
                        return $type;
                    }
                ],
            ]
        );

        return $router;
    }

    /**
     * @param IRouter $router
     * @param IRouter $route
     */
    public static function prepend(IRouter &$router, IRouter $route) {
        $router[] = $route;

        $last = count($router) - 1;
        foreach ($router as $i => $r) {
            if ($i === $last) {
                break;
            }
            $router[$i + 1] = $r;
        }

        $router[0] = $route;
    }

    /**
     * @param string $name
     * @return string
     */
    public static function encodeName($name) {
        if (!$name) {
            return NULL;
        }

        if ($name instanceof IFile) {
            $name = $name->getName();
        }

        //$name = str_replace('/', self::DELIMETER, $name);

        /*$pos = strrpos($name, '.');

        if ($pos !== FALSE) {
            $name = substr_replace($name, self::DELIMETER, $pos, 1);
        }*/

        return $name;
    }

    /**
     * @param string $name
     * @return string
     */
    public static function decodeName($name) {
        if (!$name) {
            return NULL;
        }

        //$name = str_replace(self::DELIMETER, '/', $name);

        /*$pos = strrpos($name, '/');

        if ($pos !== FALSE) {
            $name = substr_replace($name, '.', $pos, 1);
        }*/

        return $name;
    }
}