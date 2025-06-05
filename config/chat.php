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
    | user names from the "users" table.
    |
    */

    'user' => [
        'name_cols' => ['first_name', 'last_name'],
        'image_col' => 'avatar',
        'enable_image_url' => true,
    ],
  
    /*
    |-------------------------------------
    | Chat messages/activity configurations
    |-------------------------------------
    */

    'enable_activity_messages' =>  true,

    'enable_update_message_time' => true,
    'update_message_time_limit' => 60,
    
    'enable_delete_message_time' => true,
    'delete_message_time_limit' => 60,

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
