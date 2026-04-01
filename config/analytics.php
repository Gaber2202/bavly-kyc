<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Report dashboard cache TTL (seconds)
    |--------------------------------------------------------------------------
    |
    | Short TTL keeps analytics fresh while reducing repeated heavy aggregates.
    | Set to 0 in .env (ANALYTICS_CACHE_TTL=0) to disable caching.
    |
    */

    'cache_ttl' => (int) env('ANALYTICS_CACHE_TTL', 60),

];
