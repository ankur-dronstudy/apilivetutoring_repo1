<?php

return [
    'driver' => env('FCM_PROTOCOL', 'http'),
    'log_enabled' => false,

    'http' => [
        'server_key' => env('FCM_SERVER_KEY', 'AAAARynqK1s:APA91bGAv_LDnN_LHc0RJN8EPul5Sz4KODu9e2b7mekU5Hsuc2u1skF0iFZwrQ56hG0frCPbKqxFbtdSkZNv6quCEoScClwqQBUuLP6vauFhphCEYD6yslxR4Oz08_57zdjdeFmVavg'),
        'sender_id' => env('FCM_SENDER_ID', '305645890395'),
        'server_send_url' => 'https://fcm.googleapis.com/fcm/send',
        'server_group_url' => 'https://android.googleapis.com/gcm/notification',
        'timeout' => 30.0, // in second
    ],
];
