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
$router->delete("/auth/token/{id}", [
    'uses'=>"AuthController@delete_token_by_id",
    'middleware'=>[
        'auth'
    ]
]);
$router->delete("/auth/token", [
    'uses'=>"AuthController@delete_token",
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
$router->delete("/user_login/{id}", [
    'uses'  =>"UserLoginController@delete_by_id",
    'middleware'=>[
        'auth',
        'role:admin'
    ]
]);
$router->delete("/user_login", [
    'uses'  =>"UserLoginController@delete",
    'middleware'=>[
        'auth',
        'role:admin'
    ]
]);

//USERS SHIPOWNER
$router->get("/user_shipowner", [
    'uses'=>"UserShipownerController@gets",
    'middleware'=>[
        'auth',
        'role:admin,provider'
    ]
]);
$router->get("/user_shipowner/{id}", [
    'uses'  =>"UserShipownerController@get",
    'middleware'=>[
        'auth',
        'role:admin,provider'
    ]
]);
$router->delete("/user_shipowner/{id}", [
    'uses'  =>"UserShipownerController@delete",
    'middleware'=>[
        'auth',
        'role:admin,provider'
    ]
]);
$router->post("/user_shipowner", [
    'uses'=>"UserShipownerController@add",
    'middleware'=>[
        'auth',
        'role:admin,provider'
    ]
]);
$router->put("/user_shipowner/{id}", [
    'uses'  =>"UserShipownerController@update",
    'middleware'=>[
        'auth',
        'role:admin,provider'
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
$router->get("/user/{id}", [
    'uses'  =>"UserController@get_by_id",
    'middleware'=>[
        'auth',
        'role:admin'
    ]
]);
$router->delete("/user/{id}", [
    'uses'  =>"UserController@delete_by_id",
    'middleware'=>[
        'auth',
        'role:admin'
    ]
]);
$router->put("/user/{id}", [
    'uses'  =>"UserController@update_by_id",
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

//DASHBOARD
$router->post("/home/kapal", [
    'uses'=>"HomeController@add_vessel",
    'middleware'=>[
        'auth'
    ]
]);
$router->get("/home/kapal", [
    'uses'=>"HomeController@gets_vessel",
    'middleware'=>[
        'auth'
    ]
]);
$router->get("/home/kapal/{id}", [
    'uses'=>"HomeController@get_vessel",
    'middleware'=>[
        'auth'
    ]
]);
$router->delete("/home/kapal/{id}", [
    'uses'=>"HomeController@delete_vessel",
    'middleware'=>[
        'auth'
    ]
]);
$router->put("/home/kapal/{id}", [
    'uses'=>"HomeController@update_vessel",
    'middleware'=>[
        'auth'
    ]
]);

//PROYEK
$router->post("/proyek", [
    'uses'=>"ProyekController@add_proyek",
    'middleware'=>[
        'auth'
    ]
]);
$router->put("/proyek/{id}", [
    'uses'=>"ProyekController@update_proyek",
    'middleware'=>[
        'auth'
    ]
]);
$router->put("/proyek/{id}/publish", [
    'uses'=>"ProyekController@publish_proyek",
    'middleware'=>[
        'auth'
    ]
]);
$router->delete("/proyek/{id}", [
    'uses'=>"ProyekController@delete_proyek",
    'middleware'=>[
        'auth'
    ]
]);
$router->get("/proyek", [
    'uses'=>"ProyekController@gets_proyek",
    'middleware'=>[
        'auth'
    ]
]);
$router->get("/proyek/{id}", [
    'uses'=>"ProyekController@get_proyek",
    'middleware'=>[
        'auth'
    ]
]);

//TENDER
$router->post("/tender", [
    'uses'=>"TenderController@add_tender",
    'middleware'=>[
        'auth'
    ]
]);
$router->get("/tender", [
    'uses'=>"TenderController@gets_tender",
    'middleware'=>[
        "auth"
    ]
]);
$router->put("/tender/{id}", [
    'uses'=>"TenderController@update_tender",
    'middleware'=>[
        'auth'
    ]
]);
$router->put("/tender/{id}/publish", [
    'uses'=>"TenderController@publish_tender",
    'middleware'=>[
        'auth'
    ]
]);
$router->delete("/tender/{id}", [
    'uses'=>"TenderController@delete_tender",
    'middleware'=>[
        'auth'
    ]
]);
$router->get("/tender/proyek/{id}", [
    'uses'=>"TenderController@gets_tender_proyek",
    'middleware'=>[
        'auth'
    ]
]);
$router->get("/tender/{id}", [
    'uses'=>"TenderController@get_tender",
    'middleware'=>[
        'auth'
    ]
]);
$router->post("/tender/{id}/select_tender", [
    'uses'=>"TenderController@select_tender",
    'middleware'=>[
        "auth"
    ]
]);
$router->delete("/tender/{id}/unselect_tender", [
    'uses'=>"TenderController@unselect_tender",
    'middleware'=>[
        "auth"
    ]
]);

//REPORT
//--proyek summary
$router->put("/report/proyek/{id}", [
    'uses'=>"ReportController@update_summary",
    'middleware'=>[
        "auth"
    ]
]);
$router->get("/report/proyek", [
    'uses'=>"ReportController@gets_summary",
    'middleware'=>[
        "auth"
    ]
]);
$router->get("/report/proyek/{id}", [
    'uses'=>"ReportController@get_summary",
    'middleware'=>[
        "auth"
    ]
]);
//--proyek summary detail
$router->post("/report/detail", [
    'uses'=>"ReportController@add_detail",
    'middleware'=>[
        "auth"
    ]
]);
$router->put("/report/detail/{id}", [
    'uses'=>"ReportController@update_detail",
    'middleware'=>[
        "auth"
    ]
]);
$router->delete("/report/detail/{id}", [
    'uses'=>"ReportController@delete_detail",
    'middleware'=>[
        "auth"
    ]
]);
$router->get("/report/proyek/{id}/detail", [
    'uses'=>"ReportController@gets_summary_detail",
    'middleware'=>[
        "auth"
    ]
]);
$router->get("/report/detail/{id}", [
    'uses'=>"ReportController@get_detail",
    'middleware'=>[
        "auth"
    ]
]);
//--proyek summary work area
$router->put("/report/proyek/{id}/work_area", [
    'uses'=>"ReportController@update_progress",
    'middleware'=>[
        "auth"
    ]
]);
//--proyek summary pic
$router->get("/report/proyek/{id}/pic", [
    'uses'=>"ReportController@gets_summary_pic",
    'middleware'=>[
        "auth"
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

// //PROYEK
// $router->post("/proyek", [
//     'uses'=>"ProyekController@add",
//     'middleware'=>[
//         'auth',
//         'role:admin,shipowner,shipmanager'
//     ]
// ]);
// $router->post("/proyek/add_all", [
//     'uses'=>"ProyekController@add_all",
//     'middleware'=>[
//         'auth',
//         'role:admin,shipowner,shipmanager'
//     ]
// ]);
// $router->put("/proyek/{id}", [
//     'uses'=>"ProyekController@update_by_id",
//     'middleware'=>[
//         'auth',
//         'role:admin,shipowner,shipmanager'
//     ]
// ]);
// $router->delete("/proyek/{id}", [
//     'uses'=>"ProyekController@delete_by_id",
//     'middleware'=>[
//         'auth',
//         'role:admin,shipowner,shipmanager'
//     ]
// ]);
// $router->get("/proyek/{id}/draft", [
//     'uses'=>"ProyekController@get_draft_by_id",
//     'middleware'=>[
//         'auth',
//         'role:admin,shipowner,shipmanager'
//     ]
// ]);
// $router->put("/proyek/{id}/publish", [
//     'uses'=>"ProyekController@publish_by_id",
//     'middleware'=>[
//         'auth',
//         'role:admin,shipowner,shipmanager'
//     ]
// ]);
// $router->get("/proyek/{id}/berjalan", [
//     'uses'=>"ProyekController@get_proyek_berjalan",
//     'middleware'=>[
//         'auth'
//     ]
// ]);
// $router->get("/proyek", [
//     'uses'=>"ProyekController@gets_proyek",
//     'middleware'=>[
//         'auth'
//     ]
// ]);
// $router->get("/proyek/user/shipyard", [
//     'uses'=>"ProyekController@gets_shipyard",
//     'middleware'=>[
//         'auth'
//     ]
// ]);
// $router->get("/proyek/pekerjaan", [
//     'uses'=>"ProyekController@gets_pekerjaan",
//     'middleware'=>[
//         'auth'
//     ]
// ]);
// $router->post("/proyek/pekerjaan", [
//     'uses'=>"ProyekController@add_pekerjaan",
//     'middleware'=>[
//         'auth',
//         'role:admin,shipmanager,shipowner'
//     ]
// ]);
// $router->put("/proyek/pekerjaan/{id}", [
//     'uses'=>"ProyekController@update_pekerjaan",
//     'middleware'=>[
//         'auth',
//         'role:admin,shipmanager,shipowner'
//     ]
// ]);
// $router->delete("/proyek/pekerjaan/{id}", [
//     'uses'=>"ProyekController@delete_pekerjaan",
//     'middleware'=>[
//         'auth',
//         'role:admin,shipmanager,shipowner'
//     ]
// ]);

// //TENDER
// $router->post("/tender/add_all", [
//     'uses'=>"TenderController@add_all",
//     'middleware'=>[
//         'auth',
//         'role:admin,shipyard,shipmanager'
//     ]
// ]); 
// $router->post("/tender/select_yard", [
//     'uses'=>"TenderController@select_yard",
//     'middleware'=>[
//         'auth',
//         'role:admin,shipmanager,shipowner'
//     ]
// ]);
// $router->delete("/tender/cancel_select_yard", [
//     'uses'=>"TenderController@cancel_select_yard",
//     'middleware'=>[
//         'auth',
//         'role:admin,shipmanager,shipowner'
//     ]
// ]);
// $router->get("/tender/proyek", [
//     'uses'=>"TenderController@gets_proyek",
//     'middleware'=>[
//         'auth'
//     ]
// ]);
// $router->get("/tender/proyek/{id}", [
//     'uses'=>"TenderController@gets_tender",
//     'middleware'=>[
//         'auth'
//     ]
// ]);
// $router->get("/tender/proyek/{id}/detail", [
//     'uses'=>"TenderController@gets_tender_detail",
//     'middleware'=>[
//         'auth'
//     ]
// ]);
// $router->get("/tender/proyek/{id}/template", [
//     'uses'=>"TenderController@get_template_proyek",
//     'middleware'=>[
//         'auth',
//         'role:admin,shipmanager,shipyard'
//     ]
// ]);

// //REPORT
// $router->post("/report/update_progress", [
//     'uses'=>"ReportController@update_progress",
//     'middleware'=>[
//         'auth',
//         'role:admin,shipyard,shipmanager'
//     ]
// ]);
// $router->put("/report/apply_progress", [
//     'uses'=>"ReportController@apply_progress",
//     'middleware'=>[
//         'auth',
//         'role:admin,shipowner,shipmanager'
//     ]
// ]);
// $router->put("/report/reject_progress", [
//     'uses'=>"ReportController@reject_progress",
//     'middleware'=>[
//         'auth',
//         'role:admin,shipowner,shipmanager'
//     ]
// ]);
// $router->get("/report/proyek", [
//     'uses'=>"ReportController@gets_proyek",
//     'middleware'=>[
//         'auth'
//     ]
// ]);
// $router->get("/report/proyek/{id}", [
//     'uses'=>"ReportController@get_proyek",
//     'middleware'=>[
//         'auth'
//     ]
// ]);
// $router->put("/report/proyek/{id}/update_status", [
//     'uses'=>"ReportController@update_status",
//     'middleware'=>[
//         'auth',
//         'role:admin,shipyard,shipmanager'
//     ]
// ]);

// //DASHBOARD/HOME
// $router->get("/dashboard", [
//     'uses'=>"HomeController@index",
//     'middleware'=>[
//         'auth'
//     ]
// ]);

// //TRACKING
// $router->get("/tracking", [
//     'uses'=>"TrackingController@index",
//     'middleware'=>[
//         'auth'
//     ]
// ]);

// //PENGATURAN
// $router->get("/pengaturan/profile_perusahaan", [
//     'uses'=>"PengaturanController@get_profile_perusahaan",
//     'middleware'=>[
//         'auth',
//         'role:admin'
//     ]
// ]);
// $router->put("/pengaturan/profile_perusahaan", [
//     'uses'=>"PengaturanController@update_profile_perusahaan",
//     'middleware'=>[
//         'auth',
//         'role:admin'
//     ]
// ]);

// // //MAIL
// // $router->post("/mail/send", [
// //     'uses'=>"MailController@send"
// // ]);