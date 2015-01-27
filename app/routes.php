<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/','HomeController@index');


//USERS

Route::get('/users/create','UsersController@create');
Route::get('/users/login/{username}/{password}','UsersController@login');
Route::get('/users/auth/status','UsersController@status');
Route::get('/users/logout','UsersController@logout');
Route::get('/users/ip','UsersController@ip');


//COMPANY
Route::get('/companies/dashboard','CompaniesController@dashboard');
Route::get('/company','CompaniesController@company');
Route::get('/companies','CompaniesController@companies');
Route::post('/companies/{id}/drivers','CompaniesController@drivers');

//DRIVERS

Route::post('/drivers','DriversController@index');
Route::get('/drivers/find/availablibility','DriversController@availablibility');
Route::get('/drivers/trip/find','DriversController@driver_trip_find');

Route::get('/oauth2callback','DriversController@oauth2callback');
Route::get('/oauth2callback/oauth2callback','DriversController@oauth2callback');


Route::post('/drivers/getdata','DriversController@get');
Route::get('/drivers/dashboard','DriversController@dashboard');
Route::get('/drivers/times','DriversController@times');
Route::get('/drivers/times/dashboard/{id}','DriversController@timesdashboard');
Route::post('/drivers/timesheet/{id}','DriversController@timesheet');
Route::get('/drivers/timesheet/save/{id}','DriversController@timesheet_save');


//CUSTOMERS
Route::get('/customers/dashboard','CustomersController@dashboard');
Route::get('/customers/{id}/dashboard','CustomersController@customer_dashboard');
Route::post('/customers','CustomersController@index');