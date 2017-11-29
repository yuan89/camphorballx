<?php

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

$app->get('/', function () use ($app) {
    return $app->version();
});

//elasticsearch接口
$app->group(['prefix' => 'elasticsearch'], function ($app) {
    $app->post('create', ['middleware' => ['cors'], 'uses' => 'ElasticsearchController@create']);
    $app->options('create', ['middleware' => ['cors'], 'uses' => 'ElasticsearchController@create']);

    $app->post('update', ['middleware' => ['cors'], 'uses' => 'ElasticsearchController@update']);
    $app->options('update', ['middleware' => ['cors'], 'uses' => 'ElasticsearchController@update']);

    $app->post('lists', ['middleware' => ['cors'], 'uses' => 'ElasticsearchController@lists']);
    $app->options('lists', ['middleware' => ['cors'], 'uses' => 'ElasticsearchController@lists']);

    $app->post('remain', ['middleware' => ['cors'], 'uses' => 'ElasticsearchController@remain']);
    $app->options('remain', ['middleware' => ['cors'], 'uses' => 'ElasticsearchController@remain']);

    $app->post('active', ['middleware' => ['cors'], 'uses' => 'ElasticsearchController@active']);
    $app->options('active', ['middleware' => ['cors'], 'uses' => 'ElasticsearchController@active']);

    $app->post('versionRemain', ['middleware' => ['cors'], 'uses' => 'ElasticsearchController@versionRemain']);
    $app->options('versionRemain', ['middleware' => ['cors'], 'uses' => 'ElasticsearchController@versionRemain']);

    $app->post('newMac', ['middleware' => ['cors'], 'uses' => 'ElasticsearchController@newMac']);
    $app->options('newMac', ['middleware' => ['cors'], 'uses' => 'ElasticsearchController@newMac']);

    //$app->get('cat/indices', 'ElasticsearchController@catIndices');
    $app->get('cat/indices', [
        'middleware' => ['cors'],
        'uses' => 'ElasticsearchController@catIndices'
    ]);

    $app->options('mapping', ['middleware' => ['cors'], 'uses' => 'ElasticsearchController@mapping']);
    $app->post('mapping', ['middleware' => ['cors'], 'uses' => 'ElasticsearchController@mapping']);


    $app->get('test', 'ElasticsearchController@test');
});

$app->group(['prefix' => 'elasticsearchAdvance'], function ($app) {
    $app->post('create', ['middleware' => ['cors'], 'uses' => 'elasticsearchAdvanceController@create']);
    $app->options('create', ['middleware' => ['cors'], 'uses' => 'elasticsearchAdvanceController@create']);
});



//视图面板
$app->group(['prefix' => 'panel'], function ($app) {
    $app->post('visualize/create', ['middleware' => ['cors'], 'uses' => 'panelController@visualizeCreate']);
    $app->options('visualize/create', ['middleware' => ['cors'], 'uses' => 'panelController@visualizeCreate']);

    $app->post('visualize/update', ['middleware' => ['cors'], 'uses' => 'panelController@visualizeUpdate']);
    $app->options('visualize/update', ['middleware' => ['cors'], 'uses' => 'panelController@visualizeUpdate']);

    $app->options('visualize/delete', ['middleware' => ['cors'], 'uses' => 'panelController@visualizeDelete']);
    $app->post('visualize/delete', ['middleware' => ['cors'], 'uses' => 'panelController@visualizeDelete']);


    $app->post('visualize/list', ['middleware' => ['cors'], 'uses' => 'panelController@visualizeList']);
    $app->options('visualize/list', ['middleware' => ['cors'], 'uses' => 'panelController@visualizeList']);

    $app->post('dashboard/create', ['middleware' => ['cors'], 'uses' => 'panelController@dashboardCreate']);
    $app->options('dashboard/create', ['middleware' => ['cors'], 'uses' => 'panelController@dashboardCreate']);

    $app->post('dashboard/update', ['middleware' => ['cors'], 'uses' => 'panelController@dashboardUpdate']);
    $app->options('dashboard/update', ['middleware' => ['cors'], 'uses' => 'panelController@dashboardUpdate']);

    $app->post('dashboard/delete', ['middleware' => ['cors'], 'uses' => 'panelController@dashboardDelete']);
    $app->options('dashboard/delete', ['middleware' => ['cors'], 'uses' => 'panelController@dashboardDelete']);

    $app->post('dashboard/list', ['middleware' => ['cors'], 'uses' => 'panelController@dashboardList']);
    $app->options('dashboard/list', ['middleware' => ['cors'], 'uses' => 'panelController@dashboardList']);

    $app->get('dashboard/{id}', ['middleware' => ['cors'], 'uses' => 'panelController@getDashboard']);

    $app->post('indices/create', ['middleware' => ['cors'], 'uses' => 'panelController@indicesCreate']);
    $app->options('indices/create', ['middleware' => ['cors'], 'uses' => 'panelController@indicesCreate']);

    $app->post('indices/update', ['middleware' => ['cors'], 'uses' => 'panelController@indicesUpdate']);
    $app->options('indices/update', ['middleware' => ['cors'], 'uses' => 'panelController@indicesUpdate']);

    $app->post('indices/rupdate', ['middleware' => ['cors'], 'uses' => 'panelController@indicesRUpdate']);
    $app->options('indices/rupdate', ['middleware' => ['cors'], 'uses' => 'panelController@indicesRUpdate']);

    $app->post('indices/delete', ['middleware' => ['cors'], 'uses' =>'panelController@indicesDelete']);
    $app->options('indices/delete', ['middleware' => ['cors'], 'uses' =>'panelController@indicesDelete']);

    $app->post('indices/list', ['middleware' => ['cors'], 'uses' => 'panelController@indicesList']);
    $app->options('indices/list', ['middleware' => ['cors'], 'uses' => 'panelController@indicesList']);

    $app->get('indices/totallist', ['middleware' => ['cors'], 'uses' => 'panelController@indicesTotalList']);

    $app->post('product/create', ['middleware' => ['cors'], 'uses' => 'panelController@productCreate']);
    $app->options('product/create', ['middleware' => ['cors'], 'uses' => 'panelController@productCreate']);

    $app->post('product/update', ['middleware' => ['cors'], 'uses' => 'panelController@productUpdate']);
    $app->options('product/update', ['middleware' => ['cors'], 'uses' => 'panelController@productUpdate']);

    $app->post('product/delete', ['middleware' => ['cors'], 'uses' => 'panelController@productDelete']);
    $app->options('product/delete', ['middleware' => ['cors'], 'uses' => 'panelController@productDelete']);

    $app->get('product/totallist', ['middleware' => ['cors'], 'uses' => 'panelController@productTotalList']);

    $app->post('product/list', ['middleware' => ['cors'], 'uses' => 'panelController@productList']);
    $app->options('product/list', ['middleware' => ['cors'], 'uses' => 'panelController@productList']);

    $app->post('product/productRole', ['middleware' => ['cors'], 'uses' => 'panelController@productAttachRole']);
    $app->options('product/productRole', ['middleware' => ['cors'], 'uses' => 'panelController@productAttachRole']);

    $app->post('product/productIndex', ['middleware' => ['cors'], 'uses' => 'panelController@productAttachIndex']);
    $app->options('product/productIndex', ['middleware' => ['cors'], 'uses' => 'panelController@productAttachIndex']);

    $app->post('product/productVisualize', ['middleware' => ['cors'], 'uses' => 'panelController@productAttachVisualize']);
    $app->options('product/productVisualize', ['middleware' => ['cors'], 'uses' => 'panelController@productAttachVisualize']);

    $app->post('product/productDashboard', ['middleware' => ['cors'], 'uses' => 'panelController@productAttachDashboard']);
    $app->options('product/productDashboard', ['middleware' => ['cors'], 'uses' => 'panelController@productAttachDashboard']);

    $app->post('product/productUser', ['middleware' => ['cors'], 'uses' => 'panelController@productAttachUser']);
    $app->options('product/productUser', ['middleware' => ['cors'], 'uses' => 'panelController@productAttachUser']);

    $app->post('indices/getbyproduct', ['middleware' => ['cors'], 'uses' => 'panelController@getIndicesByProduct']);
    $app->options('indices/getbyproduct', ['middleware' => ['cors'], 'uses' => 'panelController@getIndicesByProduct']);

    $app->post('visualize/getbyproduct', ['middleware' => ['cors'], 'uses' => 'panelController@getVisualizeByProduct']);
    $app->options('visualize/getbyproduct', ['middleware' => ['cors'], 'uses' => 'panelController@getVisualizeByProduct']);

    $app->post('dashboard/getbyproduct', ['middleware' => ['cors'], 'uses' => 'panelController@getDashboardByProduct']);
    $app->options('dashboard/getbyproduct', ['middleware' => ['cors'], 'uses' => 'panelController@getDashboardByProduct']);

});


//用户权限
$app->group(['prefix' => 'rbac'], function ($app) {
    $app->post('role/create', ['middleware' => ['cors'], 'uses' =>'RbacController@roleCreate']);
    $app->options('role/create', ['middleware' => ['cors'], 'uses' => 'RbacController@roleCreate']);

    $app->post('permission/create', ['middleware' => ['cors'], 'uses' => 'RbacController@permissionCreate']);
    $app->options('permission/create', ['middleware' => ['cors'], 'uses' => 'RbacController@permissionCreate']);

    $app->post('role/attachPermission', ['middleware' => ['cors'], 'uses' => 'RbacController@attachPermission']);
    $app->options('role/attachPermission', ['middleware' => ['cors'], 'uses' => 'RbacController@attachPermission']);

    $app->post('user/attachRole', ['middleware' => ['cors'], 'uses' => 'RbacController@attachRole']);
    $app->options('user/attachRole', ['middleware' => ['cors'], 'uses' => 'RbacController@attachRole']);

    $app->get('roles/list', ['middleware' => ['cors'], 'uses' => 'RbacController@rolesList']);

    $app->get('permissions/list', ['middleware' => ['cors'], 'uses' => 'RbacController@permissionsList']);

    $app->post('user/roles', ['middleware' => ['cors'], 'uses' => 'RbacController@userRoles']);
    $app->options('user/roles', ['middleware' => ['cors'], 'uses' => 'RbacController@userRoles']);

    $app->post('role/permissions', ['middleware' => ['cors'], 'uses' => 'RbacController@rolePermissions']);
    $app->options('role/permissions', ['middleware' => ['cors'], 'uses' => 'RbacController@rolePermissions']);

    $app->post('user/hasPermission', ['middleware' => ['cors'], 'uses' => 'RbacController@userHasPermission']);
    $app->options('user/hasPermission', ['middleware' => ['cors'], 'uses' => 'RbacController@userHasPermission']);

    $app->get('user/list', ['middleware' => ['cors'], 'uses' => 'RbacController@userList']);

    $app->options('user/delete', ['middleware' => ['cors'], 'uses' => 'RbacController@userDelete']);
    $app->post('user/delete', ['middleware' => ['cors'], 'uses' => 'RbacController@userDelete']);

    $app->options('role/delete', ['middleware' => ['cors'], 'uses' => 'RbacController@roleDelete']);
    $app->post('role/delete', ['middleware' => ['cors'], 'uses' => 'RbacController@roleDelete']);

    $app->options('permission/delete', ['middleware' => ['cors'], 'uses' => 'RbacController@permissionDelete']);
    $app->post('permission/delete', ['middleware' => ['cors'], 'uses' => 'RbacController@permissionDelete']);

    $app->options('role/update/{id}', ['middleware' => ['cors'], 'uses' => 'RbacController@roleUpdate']);
    $app->post('role/update/{id}', ['middleware' => ['cors'], 'uses' => 'RbacController@roleUpdate']);

    $app->options('permission/update/', ['middleware' => ['cors'], 'uses' => 'RbacController@permissionUpdate']);
    $app->post('permission/update/', ['middleware' => ['cors'], 'uses' => 'RbacController@permissionUpdate']);

    $app->options('user/detachRoles', ['middleware' => ['cors'], 'uses' => 'RbacController@userDetachRoles']);
    $app->post('user/detachRoles', ['middleware' => ['cors'], 'uses' => 'RbacController@userDetachRoles']);

    $app->options('permission/detachPermissions', ['middleware' => ['cors'], 'uses' => 'RbacController@userDetachPermissions']);
    $app->post('permission/detachPermissions', ['middleware' => ['cors'], 'uses' => 'RbacController@userDetachPermissions']);

    $app->post('user/permissions', ['middleware' => ['cors'], 'uses' => 'RbacController@userPermissions']);
    $app->options('user/permissions', ['middleware' => ['cors'], 'uses' => 'RbacController@userPermissions']);

});

//登录注册
$app->group(['prefix' => 'user'], function ($app) {
    $app->post('login', ['middleware' => 'cors', 'uses' => 'UserController@login']);
    $app->options('login', ['middleware' => 'cors', 'uses' => 'UserController@login']);

    $app->post('register', ['middleware' => 'cors', 'uses' => 'UserController@register']);
    $app->options('register', ['middleware' => 'cors', 'uses' => 'UserController@register']);

    $app->post('update', ['middleware' => 'cors', 'uses' => 'UserController@update']);
    $app->options('update', ['middleware' => 'cors', 'uses' => 'UserController@update']);

    $app->get('info', [
        'middleware' => ['auth', 'cors'],
        'uses' => 'UserController@info'
    ]);

    $app->post('getproduct', ['middleware' => 'cors', 'uses' => 'UserController@getProduct']);
    $app->options('getproduct', ['middleware' => 'cors', 'uses' => 'UserController@getProduct']);

    //用户关联产品
    $app->post('userProduct', ['middleware' => 'cors', 'uses' => 'UserController@userProduct']);
    $app->options('userProduct', ['middleware' => 'cors', 'uses' => 'UserController@userProduct']);


});



//Excel相关
$app->group(['prefix' => 'excel'], function ($app) {
    $app->get('export/{id}', ['middleware' => ['cors'], 'uses' => 'ExcelController@export']);
    $app->get('gogo', ['middleware' => ['cors'], 'uses' => 'ExcelController@gogo']);
    $app->post('generate', ['middleware' => ['cors'], 'uses' => 'ExcelController@generate']);
});

