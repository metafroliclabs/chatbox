<?php

return [

    /*
    |-------------------------------------
    | Routes configurations
    |-------------------------------------
    */

    'prefix' => env('CHAT_URL_PREFIX', 'api/chat'),
    'middleware' => ['api', 'auth:sanctum'],

    /*
    |-------------------------------------
    | Pagination configurations
    |-------------------------------------
    */

    'paginate_records' => env('CHAT_PAGINATE_RECORDS', 25),

    /*
    |-------------------------------------
    | Users table configurations
    |-------------------------------------
    |
    | Specify the column(s) used to retrieve 
    | user names from the "users" table.
    |
    */

    'user_name_cols' => ['first_name', 'last_name'],
];
