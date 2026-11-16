<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Card-to-Card Payment Settings
    |--------------------------------------------------------------------------
    */

    'card_to_card' => [
        'destination_card' => env('CARD_TO_CARD_DESTINATION', '6037-9975-1234-5678'),
        'account_name' => env('CARD_TO_CARD_ACCOUNT_NAME', 'آسمان‌پوش'),
        'bank_name' => env('CARD_TO_CARD_BANK_NAME', 'بانک ملت'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Receipt Upload Settings
    |--------------------------------------------------------------------------
    */

    'receipts' => [
        'disk' => env('RECEIPT_DISK', 'public'),
        'max_kb' => (int) env('RECEIPT_MAX_KB', 4096),
    ],

];
