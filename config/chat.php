<?php

return [

    /*
    |-------------------------------------
    | Chat configurations
    |-------------------------------------
    |
    | Chat type: 'standard' (private/group chat) 
    | or 'universal' (global chat). You can toggle
    | optional features for universal chat.
    |
    */

    'type' => 'standard',
    'features' => [
        'reactions' => true,
        'views' => false,
    ],

    /*
    |-------------------------------------
    | Routes configurations
    |-------------------------------------
    */

    'prefix' => 'chat',
    'middleware' => ['auth:sanctum'],
    'rate_limits' => [
        'chat_creation_per_minute' => 20,
        'messages_per_minute' => 40
    ],

    /*
    |-------------------------------------
    | Pagination configurations
    |-------------------------------------
    */

    'pagination' => false,
    'per_page' => 25,

    /*
    |-------------------------------------
    | Chat users configurations
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
        'filters' => []
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
        'disk' => 'public',
        'folder' => 'attachments',
        'prefix' => 'File',
        'max_size' => 10240, // 10MB
        'max_files' => 10
    ],
];
