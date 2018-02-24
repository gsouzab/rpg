<?php

use Illuminate\Http\Request;

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

Route::prefix('game')->group(function () {
    Route::get('/', 'GameController@play');

    Route::get('/startRound', 'GameController@startRound');

    Route::get('/attackPlayer/attacker/{attackerName}/defender/{defenderName}', 'GameController@attackPlayer');

    Route::get('/restart', 'GameController@restart');
});