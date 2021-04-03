<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'user'], function () use ($router) {
    $router->get('login', 'AuthenticationController@login');
    $router->get('logout', 'AuthenticationController@logout');
    $router->get('{username}', 'UserController@show');

    $router->post('createWithArray', 'UserController@storeMultiple');
    $router->post('createWithList', 'UserController@storeMultiple');
    $router->post('/', 'UserController@store');

    $router->put('{username}', 'UserController@update');

    $router->delete('{username}', 'UserController@destroy');
});

$router->group(['prefix' => 'pet'], function () use ($router) {
    $router->get('findByStatus', 'PetController@findByStatus');
    $router->get('{petId}', 'PetController@show');

    $router->post('/', 'PetController@store');
    $router->post('{petId}/uploadImage', 'PetController@uploadImage');

    $router->put('/', 'PetController@update');

    $router->delete('{petId}', 'PetController@destroy');
});

$router->group(['prefix' => 'store'], function () use ($router) {
    $router->group(['prefix' => 'order'], function () use ($router) {
        $router->get('{orderId}', 'OrderController@show');

        $router->post('/', 'OrderController@store');

        $router->delete('{orderId}', 'OrderController@destroy');
    });
});
