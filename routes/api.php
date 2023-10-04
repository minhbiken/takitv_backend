<?php

use App\Http\Middleware\IfModifiedSince;
use App\Http\Middleware\LastModified;
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

Route::apiResource('homepage', 'Api\HomepageController')->only(['index'])->middleware([IfModifiedSince::class, LastModified::class]);
Route::apiResource('movies', 'Api\MovieController')->only(['index', 'show'])->middleware([IfModifiedSince::class, LastModified::class]);
Route::apiResource('tvshows', 'Api\TvshowController')->only(['index', 'show'])->middleware([IfModifiedSince::class, LastModified::class]);
Route::apiResource('episode', 'Api\EpisodeController')->only(['show'])->middleware([IfModifiedSince::class, LastModified::class]);
Route::get('search', 'App\Http\Controllers\Api\HomepageController@search')->middleware([IfModifiedSince::class, LastModified::class]);
Route::get('tvShowHomepage', 'App\Http\Controllers\Api\HomepageController@tvShowHomepage')->middleware([IfModifiedSince::class, LastModified::class]);

Route::get('clearCache', 'App\Http\Controllers\Api\HomepageController@clearCache');
Route::get('clearCacheByKey/{key}', 'App\Http\Controllers\Api\HomepageController@clearCacheByKey');
Route::get('clearCacheHomePage', 'App\Http\Controllers\Api\HomepageController@clearCacheHomePage');
Route::get('clearCacheTvShowHomePage', 'App\Http\Controllers\Api\HomepageController@clearCacheTvShowHomePage');

Route::get('timeCheck', 'App\Http\Controllers\Api\HomepageController@timeCheck');
Route::get('makeCacheFirst', 'App\Http\Controllers\Api\HomepageController@makeCacheFirst');

Route::get('putGmtTime', 'App\Http\Controllers\Api\HomepageController@putGmtTime');
Route::get('getGmtTime', 'App\Http\Controllers\Api\HomepageController@getGmtTime');
Route::get('getMovieTMDBId', 'App\Http\Controllers\Api\HomepageController@getMovieTMDBId')->name('movie.tmdb');
Route::get('getMovieLimit', 'App\Http\Controllers\Api\HomepageController@getMovieLimit');
Route::get('getTvshowTMDBId', 'App\Http\Controllers\Api\HomepageController@getTvshowTMDBId')->name('tvshow.tmdb');
Route::get('getTvshowLimit', 'App\Http\Controllers\Api\HomepageController@getTvshowLimit');
Route::get('insertPerson', 'App\Http\Controllers\Api\HomepageController@insertPerson');
Route::get('autoImportPerson', 'App\Http\Controllers\Api\HomepageController@autoImportPerson');