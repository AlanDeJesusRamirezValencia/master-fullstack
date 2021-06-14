<?php

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

Route::get('/pruebas/{nombre?}', function ($nombre = null) {
    $texto = '<h1>Texto desde una ruta</h1> Nombre: '.$nombre;
    return view('pruebas', array(
        'texto' => $texto
    ));
});

Route::post('/api/user/register/', 'UserController@register');
Route::post('/api/user/login/', 'UserController@login');
Route::put('/api/user/update/', 'UserController@update');