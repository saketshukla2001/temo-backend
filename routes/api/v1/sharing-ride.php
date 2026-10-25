<?php

use App\Base\Constants\Auth\Role;

Route::prefix('sharing-rides')->namespace('SharingRide')->middleware('auth')->group(function () {
    Route::middleware(role_middleware(Role::mobileAppRoles()))->group(function () {
        Route::get('/', 'SharingRideController@index');
        Route::get('/mine', 'SharingRideController@myRides');
        Route::get('/locations', 'SharingRideController@locations');
        Route::get('/{sharingRide}', 'SharingRideController@show');
        Route::post('/create', 'SharingRideController@store');
        Route::post('/{sharingRide}/join', 'SharingRideController@join');
        Route::post('/bookings/{booking}/arrive', 'SharingRideController@arriveBooking');
        Route::post('/bookings/{booking}/verify-otp', 'SharingRideController@verifyOtp');
        Route::post('/bookings/{booking}/start', 'SharingRideController@startBooking');
        Route::post('/bookings/{booking}/drop', 'SharingRideController@dropBooking');
        Route::post('/bookings/{booking}/payment', 'SharingRideController@updateBookingPayment');
        Route::post('/bookings/{booking}/cash-collected', 'SharingRideController@markBookingCashCollected');
        Route::post('/bookings/{booking}/complete', 'SharingRideController@completeBooking');
        Route::post('/{sharingRide}/complete', 'SharingRideController@complete');
        Route::post('/{sharingRide}/cancel', 'SharingRideController@cancel');
    });
});
