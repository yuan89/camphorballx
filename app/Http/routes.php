<?php

$app->group(['prefix' => 'api/v1'], function($app)
{
    $app->post('car','CarController@createCar');
    $app->put('car/{id}','CarController@updateCar');
    $app->delete('car/{id}','CarController@deleteCar');
    $app->get('car','CarController@index');
});
