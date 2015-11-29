<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/**
 * Authentication Routes
 */
Route::group(['prefix' => 'auth'], function () {
    Route::post('login', 'AuthController@postLogin');
    Route::post('register', 'AuthController@postRegister');
    Route::get('logout', 'AuthController@getLogout');
    Route::get('id', 'AuthController@getId');
});

Route::group(['prefix' => 'password-recovery', 'middleware' => 'guest'], function() {
    Route::post('email', 'PasswordController@postEmail');
    Route::post('reset', 'PasswordController@postReset');
});

/**
 * Routes protected from unauthorized access by auth middleware
 */
Route::group(['middleware' => 'auth'], function() {
    /**
     * App-User Routes
     */
    Route::post('profile', 'UserController@postProfileInformation');
    Route::post('profile/picture', 'UserController@postProfilePicture');
    Route::group(['prefix' => 'account'], function() {
        Route::get('settings', 'UserController@getSettings');
        Route::post('settings', 'UserController@postSettings');
        Route::post('username', 'UserController@postUsername');
        Route::post('password', 'UserController@postPassword');
    });
    /**
     * User Profile Routes
     */
    Route::get('user/{id}', 'UserController@getProfile');
    /**
     * Battle List Routes
     */
    Route::group(['prefix' => 'battles'], function() {
        Route::get('trending', 'BattleController@getTrending');
        Route::get('open-voting', 'BattleController@getOpenVoting');
        Route::get('completed', 'BattleController@getCompleted');
        Route::get('open', 'BattleController@getOpen');
    });
    /**
     * Battle Routes
     */
    Route::get('battle/{id}', 'BattleController@getBattle');
    Route::post('battle/{id}/vote', 'BattleController@postVote');
    /**
     * Open Battle Routes
     */
    Route::get('open-battle/{id}', 'OpenBattleController@getBattle');
    Route::post('open-battle/{id}/round', 'OpenBattleController@postRound');
    /**
     * Battle Request Routes
     */
    Route::get('requests', 'BattleRequestController@getRequests');
    Route::post('request', 'BattleRequestController@postRequest');
    Route::post('request/{id}', 'BattleRequestController@postAnswer');
    Route::get('request/random', 'BattleRequestController@getRandomOpponent');
    /**
     * Search Routes
     */
    Route::post('search', 'SearchController@postSearch');
    /**
     * Data Access Routes
     */
    Route::get('picture/{id}', ['as' => 'data.picture', 'uses' => 'DataAccessController@getPicture']);
    Route::get('video/{id}', ['as' => 'data.video', 'uses' => 'DataAccessController@getVideo']);
    /*
     * Push Notification Routes
     */
    Route::post('device-token', 'PushNotificationController@postToken');
});
