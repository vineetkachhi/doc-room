<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('add', 'APIController@add')->middleware('auth.groupuser');
Route::post('move', 'APIController@move')->middleware('auth.groupuser');
Route::post('update-doctor', 'APIController@updateDoctor')->middleware('auth.groupuser');
Route::post('sort-doctors', 'APIController@sortDoctor')->middleware('auth.groupuser');

Route::post('sort', 'APIController@sort')->middleware('auth.groupuser');
Route::post('remove', 'APIController@remove')->middleware('auth.groupuser');
Route::post('setWidth', 'APIController@setWidth')->middleware('auth.groupuser');
Route::post('refresh', 'APIController@refreshAll')->middleware('auth.groupuser');
