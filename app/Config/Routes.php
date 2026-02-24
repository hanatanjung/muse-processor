<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setAutoRoute(false);

/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/
$routes->get('/', 'Home::index');

/*
|--------------------------------------------------------------------------
| Combine
|--------------------------------------------------------------------------
*/
$routes->group('combine', function ($routes) {
    $routes->get('/', 'Combine::index');
    $routes->post('upload', 'Combine::upload');
});

/*
|--------------------------------------------------------------------------
| PPT (Image → JSON processor)
|--------------------------------------------------------------------------
*/
$routes->group('ppt', function ($routes) {
    $routes->get('/', 'Ppt::index');
    $routes->post('upload', 'Ppt::upload');
    $routes->post('delete', 'Ppt::delete');
});