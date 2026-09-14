<?php

return [
    'vapid' => [
        'subject' => env('VAPID_SUBJECT', 'mailto:admin@piticoin.id'),
        'public_key' => env('VAPID_PUBLIC_KEY', ''),
        'private_key' => env('VAPID_PRIVATE_KEY', ''),
    ],
    'options' => [
        'TTL' => (int) env('WEBPUSH_TTL', 86400),
        'urgency' => env('WEBPUSH_URGENCY', 'normal'),
        'batch_size' => (int) env('WEBPUSH_BATCH_SIZE', 200),
    ],
];
