<?php

/*
|--------------------------------------------------------------------------
| Ably configuration file
|--------------------------------------------------------------------------
|
| You may use any of the ClientOptions here.
| See the complete list here: https://www.ably.io/documentation/rest/usage#client-options
|
| A key or any other valid means of authenticating is required.
*/

return [
    'key' => env('MIX_ABLY_KEY'),
];
