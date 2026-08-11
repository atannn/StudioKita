<?php

return [
    'midtrans_bypass_enabled' => (bool) env('PAYMENT_MIDTRANS_BYPASS_ENABLED', false),
    'midtrans_bypass_allowed_envs' => env('PAYMENT_MIDTRANS_BYPASS_ALLOWED_ENVS', 'local,testing'),
];

