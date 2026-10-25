<?php

use App\Base\Constants\Auth\Role;

Route::prefix('subscriptions')->namespace('Subscription')->middleware('auth')->group(function () {
    Route::middleware(role_middleware(Role::mobileAppRoles()))->group(function () {
        Route::get('/plans', 'SubscriptionController@plans');
        Route::get('/mine', 'SubscriptionController@mySubscription');
        Route::post('/buy', 'SubscriptionController@buy');
    });
});
