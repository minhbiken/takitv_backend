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

Route::apiResource('homepage', 'Api\HomepageController')->only(['index']);
Route::apiResource('movies', 'Api\MovieController')->only(['index']);
Route::apiResource('tvshows', 'Api\TvshowController')->only(['index', 'show']);
Route::apiResource('episode', 'Api\EpisodeController')->only(['show']);