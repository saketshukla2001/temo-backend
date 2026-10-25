<?php

namespace App\Base\Constants\Masters;

class PushEnums
{

    // for driver
    const REQUEST_CREATED ='request_created';
    const REQUEST_CANCELLED_BY_USER ='request_cancelled_by_user';
    const GENERAL_NOTIFICATION ='general_notification';
    const DRIVER_ACCOUNT_APPROVED = 'driver_account_approved';
    const DRIVER_ACCOUNT_DECLINED = 'driver_account_declined';
    // For User
    const TRIP_ACCEPTED_BY_DRIVER = 'trip_accepted';
    const DRIVER_ARRIVED = 'driver_arrived';
    const DRIVER_STARTED_TO_PICKUP = 'driver_started_to_pickup';
    const DRIVER_STARTED_THE_TRIP = 'driver_started_the_trip';
    const DRIVER_END_THE_TRIP = 'driver_end_the_trip';
    const REQUEST_CANCELLED_BY_DRIVER ='request_cancelled_by_driver';
    const NO_DRIVER_FOUND ='no_driver_found';

    const COMPLAINT_TAKEN = 'complaint_taken';
    const COMPLAINT_SOLVED = 'complaint_solved';
    const NEW_MESSAGE = 'new_message';
    const DROP_CHANGED = 'drop_changed';

    const AMOUNT_CREDITED = 'amount_credited';
    const CARD_TO_WALLET_TRANSACTION_FAILED = 'card_to_wallet_transaction_failed';

    // Sharing ride
    const SHARING_RIDE_JOINED = 'sharing_ride_joined';
    const SHARING_RIDE_ARRIVED = 'sharing_ride_arrived';
    const SHARING_RIDE_OTP_VERIFIED = 'sharing_ride_otp_verified';
    const SHARING_RIDE_STARTED = 'sharing_ride_started';
    const SHARING_RIDE_DROPPED = 'sharing_ride_dropped';
    const SHARING_RIDE_PAYMENT_UPDATED = 'sharing_ride_payment_updated';
    const SHARING_RIDE_CASH_COLLECTED = 'sharing_ride_cash_collected';
    const SHARING_RIDE_BOOKING_COMPLETED = 'sharing_ride_booking_completed';
    const SHARING_RIDE_COMPLETED = 'sharing_ride_completed';
    const SHARING_RIDE_CANCELLED_BY_CREATOR = 'sharing_ride_cancelled_by_creator';
    const SHARING_RIDE_CANCELLED_BY_JOINER = 'sharing_ride_cancelled_by_joiner';
}
