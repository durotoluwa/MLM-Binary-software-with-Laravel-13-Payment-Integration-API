<?php

return [

    'session_key' => 'impersonated_by',

    'take_redirect_to' => '/user/dashboard',

    'leave_redirect_to' => '/superadmin/dashboard',

    'middleware' => [
        'take'   => [],
        'leave'  => [],
    ],

];
