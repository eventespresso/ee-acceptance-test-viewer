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
    return view('viewer');
});
Route::get('build/{build_id}', 'ArtifactsController@locateBuild');
Route::get('build/{$build_id}/job/{$job_id}', 'ArtifactsController@locateJob');
