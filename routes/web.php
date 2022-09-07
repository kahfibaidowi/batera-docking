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
$router->delete("/auth/logout", [
    'uses'=>"AuthController@logout",
    'middleware'=>[
        'auth'
    ]
]);
$router->get("/auth/verify_login", [
    'uses'=>"AuthController@verify_login",
    'middleware'=>[
        'auth'
    ]
]);
$router->get("/auth/profile", [
    'uses'=>"AuthController@get_profile",
    'middleware'=>[
        'auth'
    ]
]);
$router->put("/auth/profile", [
    'uses'=>"AuthController@update_profile",
    'middleware'=>[
        'auth'
    ]
]);
$router->get("/auth/token", [
    'uses'=>"AuthController@gets_token",
    'middleware'=>[
        'auth'
    ]
]);
$router->delete("/auth/token/id/{id}", [
    'uses'=>"AuthController@delete_token",
    'middleware'=>[
        'auth'
    ]
]);
$router->delete("/auth/token/expired", [
    'uses'=>"AuthController@delete_token_expired",
    'middleware'=>[
        'auth'
    ]
]);

//USERS LOGIN
$router->get("/user_login", [
    'uses'  =>"UserLoginController@gets",
    'middleware'=>[
        'auth', 
        'role:admin'
    ]
]);
$router->delete("/user_login/id/{id}", [
    'uses'  =>"UserLoginController@delete",
    'middleware'=>[
        'auth',
        'role:admin'
    ]
]);
$router->delete("/user_login/expired", [
    'uses'  =>"UserLoginController@delete_expired",
    'middleware'=>[
        'auth',
        'role:admin'
    ]
]);

//USERS
$router->get("/user", [
    'uses'  =>"UserController@gets",
    'middleware'=>[
        'auth',
        'role:admin'
    ]
]);
$router->post("/user", [
    'uses'  =>"UserController@add",
    'middleware'=>[
        'auth',
        'role:admin'
    ]
]);
$router->get("/user/id/{id}", [
    'uses'  =>"UserController@get",
    'middleware'=>[
        'auth',
        'role:admin'
    ]
]);
$router->delete("/user/id/{id}", [
    'uses'  =>"UserController@delete",
    'middleware'=>[
        'auth',
        'role:admin'
    ]
]);
$router->put("/user/id/{id}", [
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
$router->post("/proyek/add_all", [
    'uses'=>"ProyekController@add_all",
    'middleware'=>[
        'auth',
        'role:admin,shipowner,shipmanager'
    ]
]);
$router->get("/proyek/persiapan", [
    'uses'=>"ProyekController@gets_proyek_persiapan",
    'middleware'=>[
        'auth',
        'role:admin,shipowner,shipmanager'
    ]
]);
$router->get("/proyek/berjalan", [
    'uses'=>"ProyekController@gets_proyek_berjalan",
    'middleware'=>[
        'auth'
    ]
]);
$router->get("/proyek/berjalan/id/{id}", [
    'uses'=>"ProyekController@get_proyek_berjalan",
    'middleware'=>[
        'auth'
    ]
]);
$router->get("/proyek/shipyard", [
    'uses'=>"ProyekController@gets_shipyard",
    'middleware'=>[
        'auth'
    ]
]);
$router->get("/proyek/pekerjaan/mendekati_deadline", [
    'uses'=>"ProyekController@gets_pekerjaan_mendekati_deadline",
    'middleware'=>[
        'auth'
    ]
]);

//TENDER
$router->post("/tender/add_all", [
    'uses'=>"TenderController@add_all",
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
$router->delete("/tender/cancel_select_yard", [
    'uses'=>"TenderController@cancel_select_yard",
    'middleware'=>[
        'auth',
        'role:admin,shipmanager,shipowner'
    ]
]);
$router->get("/tender/proyek", [
    'uses'=>"TenderController@gets_proyek",
    'middleware'=>[
        'auth'
    ]
]);
$router->get("/tender/proyek/id/{id}", [
    'uses'=>"TenderController@gets_tender",
    'middleware'=>[
        'auth'
    ]
]);
$router->get("/tender/proyek/id/{id}/detail", [
    'uses'=>"TenderController@gets_tender_detail",
    'middleware'=>[
        'auth'
    ]
]);
$router->get("/tender/proyek/id/{id}/template", [
    'uses'=>"TenderController@get_template_proyek",
    'middleware'=>[
        'auth',
        'role:admin,shipmanager,shipyard'
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
$router->put("/report/apply_progress", [
    'uses'=>"ReportController@apply_progress",
    'middleware'=>[
        'auth',
        'role:admin,shipowner,shipmanager'
    ]
]);
$router->put("/report/reject_progress", [
    'uses'=>"ReportController@reject_progress",
    'middleware'=>[
        'auth',
        'role:admin,shipowner,shipmanager'
    ]
]);
$router->get("/report/proyek", [
    'uses'=>"ReportController@gets_proyek",
    'middleware'=>[
        'auth'
    ]
]);
$router->get("/report/proyek/id/{id}", [
    'uses'=>"ReportController@get_proyek",
    'middleware'=>[
        'auth'
    ]
]);
$router->put("/report/proyek/id/{id}/update_status", [
    'uses'=>"ReportController@update_status",
    'middleware'=>[
        'auth',
        'role:admin,shipyard,shipmanager'
    ]
]);

//DASHBOARD/HOME
$router->get("/dashboard", [
    'uses'=>"HomeController@index",
    'middleware'=>[
        'auth'
    ]
]);

//TRACKING
$router->get("/tracking", [
    'uses'=>"TrackingController@index",
    'middleware'=>[
        'auth'
    ]
]);

//PENGATURAN
$router->get("/pengaturan/profile_perusahaan", [
    'uses'=>"PengaturanController@get_profile_perusahaan",
    'middleware'=>[
        'auth',
        'role:admin'
    ]
]);
$router->put("/pengaturan/profile_perusahaan", [
    'uses'=>"PengaturanController@update_profile_perusahaan",
    'middleware'=>[
        'auth',
        'role:admin'
    ]
]);

// //MAIL
// $router->post("/mail/send", [
//     'uses'=>"MailController@send"
// ]);