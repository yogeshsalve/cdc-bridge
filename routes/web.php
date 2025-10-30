<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });


use App\Http\Controllers\ConnectorController;

Route::get('/connectors-view', [ConnectorController::class, 'view']);


// Route::get('/cdc/connect', function () {
//     return view('cdc.connect');
// });

// Route::get('/cdc/live', function () {
//     return view('cdc.live');
// });

Route::view('/', 'cdc.connect');            // Main connection page
Route::view('/live-dashboard', 'cdc.live'); // Live + History page
Route::view('/connect', 'cdc.connect');
Route::view('/live', 'cdc.live');
Route::post('/disconnect', function() {
    try {
        Http::post('http://127.0.0.1:8001/disconnect');
    } catch (Exception $e) {}
    return redirect('/connect');
});
