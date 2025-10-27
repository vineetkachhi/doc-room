<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes...
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
// For testing
Route::get('logout', 'Auth\LoginController@logout')->name('logout');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

// Registration Routes...
Route::get('/register', 'Auth\RegisterController@index')->name('register');
Route::post('/register', 'Auth\RegisterController@create');

Route::get('/create/group', 'GroupController@index')->name('create.group');
Route::post('/create/group', 'GroupController@create');

// Password Reset Routes...
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');

Route::get('/home/{groupId}', 'HomeController@index')->name('home')->middleware(['auth.groupuser', 'cache.policy']);

Route::get('/super', 'HomeController@super')->name('super.dashboard')->middleware('auth.superuser');
Route::get('/admin', 'HomeController@admin')->name('admin.dashboard')->middleware('auth.adminuser');
Route::get('/edit-user/{userId}', 'UserController@index')->name('admin.edit.user')->middleware('auth.adminuser');
Route::post('/edit-user/{userId}', 'UserController@edit')->middleware('auth.adminuser');
Route::get('/edit-group/{groupId}', 'GroupController@editIndex')->name('admin.edit.group')->middleware('auth.adminuser');
Route::post('/edit-group/{groupId}', 'GroupController@edit')->middleware('auth.adminuser');

