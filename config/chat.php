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

    'pagination' => true,
    'per_page' => env('CHAT_PER_PAGE', 25),

    /*
    |-------------------------------------
    | Users table configurations
    |-------------------------------------
    |
    | Specify the column(s) used to retrieve 
    | user names from the "users" table.
    |
    */

    'user_image_url' => true,
    'user_image_col' => 'avatar',
    'user_name_cols' => ['first_name', 'last_name'],

    /*
    |-------------------------------------
    | File service configurations
    |-------------------------------------
    */

    'file' => [
        'disk' => env('CHAT_FILE_DISK', 'public'),
        'upload_folder' => 'attachments',
        'default_prefix' => 'File',
    ],
];
