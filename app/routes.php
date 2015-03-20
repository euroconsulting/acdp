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


//DOCUMENTS
Route::get('/documents','DocumentsController@index');
Route::get('/documents/dashboard','DocumentsController@dashboard');
Route::get('/documents/{Origin}/{id}','DocumentsController@get');
Route::get('/documents/{Origin}/{id}/pdf','DocumentsController@getpdf');


//MANGO API
Route::get('/mango','MangoPayController@index');
Route::get('/mango/companies','MangoPayController@companies');
Route::get('/mango/company/edit/{id}','MangoPayController@company_edit');
Route::get('/mangopay/company/wallets/edit','MangoPayController@edit_wallet');
Route::get('/mangopay/company/wallets/{id}','MangoPayController@company_wallets');
Route::get('/mangopay/wallets/delete/{id}','MangoPayController@delete_wallet');
Route::get('/mangopay/wallets/inactive/{company}/{id}','MangoPayController@disable_wallet');
Route::get('/mangopay/bookings/pay/{id}','MangoPayController@pay_booking');

Route::get('/mangopay/validatecard/{id}','MangoPayController@validate_card');

Route::get('/mangopay/bank/account/edit/{id}','MangoPayController@bank_account_edit');
Route::get('/mangopay/bankaccounts/{type}/{id}','MangoPayController@bank_accounts');
Route::get('/mangopay/bankaccounts/inactive/{company}/{id}','MangoPayController@disable_bank_account');


Route::get('/','HomeController@index');
Route::get('/admin','HomeController@admin');
Route::get('/profile','HomeController@admin');
Route::get('/myprofile/dashboard','HomeController@myprofile');

//USERS
Route::post('/users','UsersController@index');
Route::get('/users/register','UsersController@register');
Route::get('/users/dashboard','UsersController@dashboard');
Route::get('/users/{id}/edit','UsersController@edit');
Route::post('/users/save','UsersController@save');
Route::get('/users/login','UsersController@login');
Route::get('/users/auth/status','UsersController@status');
Route::get('/users/logout','UsersController@logout');
Route::get('/users/ip','UsersController@ip');

Route::get('/logout','UsersController@logout_redirect');



//COMPANY
Route::post('/companies/get','CompaniesController@index');
Route::get('/companies/dashboard','CompaniesController@dashboard');
Route::get('/company','CompaniesController@company');

Route::get('/companies/{id}/edit','CompaniesController@edit');
Route::post('/companies/{id}/drivers','CompaniesController@drivers');
Route::post('/companies/save','CompaniesController@save');
Route::get('/companies/mango/{id}','CompaniesController@mango_fetch');
//DRIVERS

Route::post('/drivers','DriversController@index');
//CE MOETHOD ES REPOSNABLE POUR TROUBVER LES CHAUFFER DISPOINIBLES AVEC LES VOITURES, SONT UN PARAMETRE RETOUER TARIF, 
//ÇA TROUVE LES CHAUFFER DISPONIBLE PUIS CES TARIFS
Route::get('/drivers/find/availablibility','DriversController@trip_find_availablibility');
Route::get('/drivers/find','DriversController@trip_find_availablibility');
Route::get('/driver/logout','UsersController@driver_logout');

Route::post('/drivers/getdata','DriversController@get');
Route::get('/drivers/dashboard','DriversController@dashboard');
Route::get('/drivers/times','DriversController@times');
Route::get('/drivers/times/dashboard/{id}','DriversController@timesdashboard');
Route::post('/drivers/timesheet/{id}','DriversController@timesheet');
Route::get('/drivers/timesheet/save/{id}','DriversController@timesheet_save');
Route::get('/drivers/cars','DriversController@cars');
Route::post('/drivers/bookings/{id}/{interval}/{type}','BookingsController@bookings_by_interval');
Route::get('/compute/','DriversController@compute_distance');
//DRIVERS FRONTEND
Route::get('/drivers/front','DriversController@front');
Route::get('/driver/bookings/','BookingsController@driver_bookings');

//GOOGLE API TESTES
Route::get('/oauth2callback','DriversController@oauth2callback');
Route::get('/oauth2callback/oauth2callback','DriversController@oauth2callback');


//BOOKINGS
Route::get('/bookings','BookingsController@index');
Route::get('/bookings/book','BookingsController@book');

//CUSTOMERS
Route::get('/customers/dashboard','CustomersController@dashboard');
Route::get('/customers/{id}/dashboard','CustomersController@customer_dashboard');
Route::post('/customers','CustomersController@index');
Route::post('/customers/bookings/{id}/{interval}/{type}','BookingsController@bookings_by_interval');


//CUSTOMER FRONTEND
Route::get('/customer/front','CustomersController@front');


//CARS
Route::get('/cars/dashboard/','CarsController@dashboard');
Route::get('/cars/{id}/','CarsController@car');
Route::post('/cars','CarsController@index');
Route::post('/cars/save','CarsController@save');
Route::get('/cars/{id}/edit','CarsController@edit');



//OFFERS
Route::get('/offers/dashboard/','OffersController@dashboard');
Route::get('/offers/{id}/edit','OffersController@edit');
Route::get('/offers/{id}/','OffersController@car');
Route::post('/offers','OffersController@index');
Route::post('offers/timesheet/{id}','OffersController@timesheet');
Route::post('offers/save','OffersController@save');

//AMENITIES
Route::get('/amenities/dashboard/','AmenitiesController@dashboard');
Route::get('/amenities/{id}/','AmenitiesController@car');
Route::post('/amenities','AmenitiesController@index');


// Display all SQL executed in Eloquent
//Event::listen('illuminate.query', function($query)
//{
//    var_dump($query);
//});

