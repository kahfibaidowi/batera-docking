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

//AUTHENTICATION
$router->post("/auth/login", [
    'uses'=>"AuthController@login"
]);
$router->post("/auth/logout", [
    'uses'=>"AuthController@logout",
    'middleware'=>[
        'auth'
    ]
]);
$router->post("/auth/verify_login", [
    'uses'=>"AuthController@verify_login",
    'middleware'=>[
        'auth'
    ]
]);
$router->post("/auth/get_profile", [
    'uses'=>"AuthController@get_profile",
    'middleware'=>[
        'auth'
    ]
]);
$router->post("/auth/update_profile", [
    'uses'=>"AuthController@update_profile",
    'middleware'=>[
        'auth'
    ]
]);
$router->post("/auth/gets_token", [
    'uses'=>"AuthController@gets_token",
    'middleware'=>[
        'auth'
    ]
]);
$router->post("/auth/delete_token", [
    'uses'=>"AuthController@delete_token",
    'middleware'=>[
        'auth'
    ]
]);
$router->post("/auth/delete_token_expired", [
    'uses'=>"AuthController@delete_token_expired",
    'middleware'=>[
        'auth'
    ]
]);

//USERS LOGIN
$router->post("/user_login/gets", [
    'uses'  =>"UserLoginController@gets",
    'middleware'=>[
        'auth', 
        'role:admin'
    ]
]);
$router->post("/user_login/delete", [
    'uses'  =>"UserLoginController@delete",
    'middleware'=>[
        'auth',
        'role:admin'
    ]
]);
$router->post("/user_login/delete_expired", [
    'uses'  =>"UserLoginController@delete_expired",
    'middleware'=>[
        'auth',
        'role:admin'
    ]
]);

//USERS
$router->post("/user/gets", [
    'uses'  =>"UserController@gets",
    'middleware'=>[
        'auth',
        'role:admin'
    ]
]);
$router->post("/user/get", [
    'uses'  =>"UserController@get",
    'middleware'=>[
        'auth',
        'role:admin'
    ]
]);
$router->post("/user/delete", [
    'uses'  =>"UserController@delete",
    'middleware'=>[
        'auth',
        'role:admin'
    ]
]);
$router->post("/user/add", [
    'uses'  =>"UserController@add",
    'middleware'=>[
        'auth',
        'role:admin'
    ]
]);
$router->post("/user/update", [
    'uses'  =>"UserController@update",
    'middleware'=>[
        'auth',
        'role:admin'
    ]
]);

//FILE
$router->post("/file/upload", [
    'uses'=>"FileController@upload",
    'middleware'=>[
        'auth'
    ]
]);
$router->get("/file/show/{file}", [
    'uses'=>"FileController@show"
]);

//PROYEK
$router->post("/proyek/add", [
    'uses'=>"ProyekController@add",
    'middleware'=>[
        'auth',
        'role:admin,shipowner,shipmanager'
    ]
]);
// $router->post("/proyek/gets", [
//     'uses'=>"ProyekController@gets",
//     'middleware'=>[
//         'auth',
//         'role:admin,shipowner'
//     ]
// ]);

//TENDER
$router->post("/tender/add", [
    'uses'=>"TenderController@add",
    'middleware'=>[
        'auth',
        'role:admin,shipyard,shipmanager'
    ]
]); 
$router->post("/tender/select_yard", [
    'uses'=>"TenderController@select_yard",
    'middleware'=>[
        'auth',
        'role:admin,shipmanager,shipowner'
    ]
]);
$router->post("/tender/cancel_select_yard", [
    'uses'=>"TenderController@cancel_select_yard",
    'middleware'=>[
        'auth',
        'role:admin,shipmanager,shipowner'
    ]
]);

//REPORT
$router->post("/report/update_progress", [
    'uses'=>"ReportController@update_progress",
    'middleware'=>[
        'auth',
        'role:admin,shipyard,shipmanager'
    ]
]);
$router->post("/report/apply_progress", [
    'uses'=>"ReportController@apply_progress",
    'middleware'=>[
        'auth',
        'role:admin,shipowner,shipmanager'
    ]
]);
$router->post("/report/reject_progress", [
    'uses'=>"ReportController@reject_progress",
    'middleware'=>[
        'auth',
        'role:admin,shipyard,shipmanager'
    ]
]);

//MAIL
$router->post("/mail/send", [
    'uses'=>"MailController@send"
]);