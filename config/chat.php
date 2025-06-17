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

    'pagination' => false,
    'per_page' => env('CHAT_PER_PAGE', 25),

    /*
    |-------------------------------------
    | Users table configurations
    |-------------------------------------
    |
    | Specify the column(s) used to retrieve 
    | user data from the "users" table.
    |
    */

    'user' => [
        'name_cols' => ['first_name', 'last_name'],
        'image_col' => 'avatar',
        'enable_image_url' => true,
    ],

    /*
    |-------------------------------------
    | Chat messages configurations
    |-------------------------------------
    */

    'message' => [  
        'enable_activity' =>  true,
        'enable_update_time' => true,
        'update_time_limit' => 60, // mins
        'enable_delete_time' => true,
        'delete_time_limit' => 60,  // mins
    ],

    /*
    |-------------------------------------
    | Chat group configurations
    |-------------------------------------
    */

    'group' => [
        'min_users' =>  2,
        'max_users' =>  9,
    ],

    /*
    |-------------------------------------
    | File service configurations
    |-------------------------------------
    */

    'file' => [
        'disk' => env('CHAT_FILE_DISK', 'public'),
        'folder' => 'attachments',
        'prefix' => 'File',
        'max_size' => 10240, // 10MB
        'max_files' => 10
    ],
];
