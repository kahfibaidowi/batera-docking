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

//PENGATURAN
$router->group(['prefix'=>"/pengaturan", 'middleware'=>"auth"], function()use($router){
    $router->get("/profile_perusahaan", ['uses'=>"PengaturanController@get_profile_perusahaan"]);
    $router->put("/profile_perusahaan", ['uses'=>"PengaturanController@update_profile_perusahaan"]);
});

//AUTHENTICATION
$router->group(['prefix'=>"/auth", 'middleware'=>"auth"], function()use($router){
    $router->get("/verify_login", ['uses'=>"AuthController@verify_login"]);
    $router->get("/profile", ['uses'=>"AuthController@get_profile"]);
    $router->put("/profile", ['uses'=>"AuthController@update_profile"]);
    $router->get("/token", ['uses'=>"AuthController@gets_token"]);
    $router->delete("/token/{id}", ['uses'=>"AuthController@delete_token_by_id"]);
    $router->delete("/token", ['uses'=>"AuthController@delete_token"]);
    $router->delete("/logout", ['uses'=>"AuthController@logout"]);
});
$router->post("/auth/login", ['uses'=>"AuthController@login"]);


//USERS LOGIN
$router->group(['prefix'=>"/user_login", 'middleware'=>"auth"], function()use($router){
    $router->get("/", ['uses'=>"UserLoginController@gets"]);
    $router->delete("/", ['uses'=>"UserLoginController@delete"]);
    $router->delete("/{id}", ['uses'=>"UserLoginController@delete_by_id"]);
});


//USERS
$router->group(['prefix'=>"/user", 'middleware'=>"auth"], function()use($router){
    $router->post("/", ['uses'=>"UserController@add"]);
    $router->get("/", ['uses'=>"UserController@gets"]);
    $router->get("/{id}", ['uses'=>"UserController@get_by_id"]);
    $router->put("/user/{id}", ['uses'=>"UserController@update_by_id"]);
    $router->delete("/{id}", ['uses'=>"UserController@delete_by_id"]);
});


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


//DASHBOARD KAPAL
$router->group(['prefix'=>"/home", 'middleware'=>"auth"], function()use($router){
    $router->post("/kapal", ['uses'=>"HomeController@add_vessel"]);
    $router->get("/kapal", ['uses'=>"HomeController@gets_vessel"]);
    $router->get("/kapal/{id}", ['uses'=>"HomeController@get_vessel"]);
    $router->delete("/kapal/{id}", ['uses'=>"HomeController@delete_vessel"]);
    $router->put("/kapal/{id}", ['uses'=>"HomeController@update_vessel"]);
});


//PROYEK
$router->group(['prefix'=>"/proyek", 'middleware'=>"auth"], function()use($router){
    $router->post("/", ['uses'=>"ProyekController@add_proyek"]);
    $router->put("/{id}", ['uses'=>"ProyekController@update_proyek"]);
    $router->delete("/{id}", ['uses'=>"ProyekController@delete_proyek"]);
    $router->get("/", ['uses'=>"ProyekController@gets_proyek"]);
    $router->get("/{id}", ['uses'=>"ProyekController@get_proyek"]);
    //--work area
    $router->put("/{id}/work_area", ['uses'=>"ProyekController@update_proyek_work_area"]);
});


//TENDER
$router->group(['prefix'=>"/tender", 'middleware'=>"auth"], function()use($router){
    $router->post("/", ['uses'=>"TenderController@add_tender"]);
    $router->get("/", ['uses'=>"TenderController@gets_tender"]);
    $router->get("/{id}", ['uses'=>"TenderController@get_tender"]);
    $router->put("/{id}", ['uses'=>"TenderController@update_tender"]);
    $router->delete("/{id}", ['uses'=>"TenderController@delete_tender"]);
    $router->get("/proyek/{id}", ['uses'=>"TenderController@gets_tender_proyek"]);
    $router->post("/{id}/select_tender", ['uses'=>"TenderController@select_tender"]);
    $router->delete("/{id}/unselect_tender", ['uses'=>"TenderController@unselect_tender"]);
    //--work area
    $router->put("/{id}/work_area", ['uses'=>"TenderController@update_tender_work_area"]);
});


//REPORT
$router->group(['prefix'=>"/report", 'middleware'=>"auth"], function()use($router){
    //--proyek summary
    $router->put("/proyek/{id}", ['uses'=>"ReportController@update_report"]);
    $router->get("/proyek", ['uses'=>"ReportController@gets_report"]);
    $router->get("/proyek/{id}", ['uses'=>"ReportController@get_report"]);
    //--proyek summary detail
    $router->post("/detail", ['uses'=>"ReportController@add_detail"]);
    $router->put("/detail/{id}", ['uses'=>"ReportController@update_detail"]);
    $router->delete("/detail/{id}", ['uses'=>"ReportController@delete_detail"]);
    $router->get("/detail/{id}", ['uses'=>"ReportController@get_detail"]);
    $router->get("/proyek/{id}/detail", ['uses'=>"ReportController@gets_report_detail"]);
    //--proyek summary work area
    $router->put("/proyek/{id}/work_area", ['uses'=>"ReportController@update_report_work_area"]);
    $router->put("/proyek/{id}/variant_work", ['uses'=>"ReportController@update_report_variant_work"]);
    //--proyek summary pic
    $router->get("/proyek/{id}/pic", ['uses'=>"ReportController@gets_report_pic"]);
});


//TRACKING
$router->get("/tracking", [
    'uses'=>"TrackingController@gets_proyek_summary",
    'middleware'=>[
        'auth'
    ]
]);