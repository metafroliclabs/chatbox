<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Laravel Chat Package

A powerful and customizable chat system built for Laravel applications. This package supports private and group chats, media sharing, chat settings, user roles, activity messages, and more.

## 🚀 Features

- Private & Group Chat  
- Message types: message, activity  
- User roles: (admin/user)  
- Group settings (permissions control)  
- Message read/unread tracking  
- Activity messages: (joins, leaves, settings changes)  
- Media upload support  
- Configurable user info (name, avatar)  
- Extendable & clean architecture (Service-based)

## 📦 Installation

```bash
composer require metafroliclabs/laravel-chat
```

Publish configuration and migrations:

```bash
php artisan vendor:publish --tag=chat-config
php artisan migrate
```

## ⚙️ Configuration

Customize settings in `config/chat.php`:

```php
return [
    'pagination' => true,
    'per_page' => 25,

    'enable_activity_messages' => true,

    'user' => [
        'name_cols' => ['first_name', 'last_name'],
        'image_col' => 'avatar',
        'enable_image_url' => true,
    ],
];
```

## 🧠 Usage

#### 📚 API Endpoints
All routes are prefixed by the config value `chat.prefix` (default: chat) and use the `chat.middleware` middleware group.

#### 🔍 Chat List & Info

| Method | Endpoint        | Description                |
| ------ | --------------- | -------------------------- |
| GET    | `/all/list`     | Get all chats for the user |
| GET    | `/unread/list`  | Get all unread chats       |
| GET    | `/unread/count` | Get unread chat count      |

#### 🛠️ Chat Management

| Method | Endpoint        | Description                     |
| ------ | --------------- | ------------------------------- |
| POST   | `/create`       | Create private chat             |
| POST   | `/create/group` | Create group chat               |
| POST   | `/{id}/update`  | Update chat name/image/settings |
| POST   | `/{id}/delete`  | Delete a chat                   |
| POST   | `/{id}/leave`   | Leave group chat                |
| POST   | `/{id}/mute`    | Mute/unmute chat                |

#### 👥 User Management

| Method | Endpoint                  | Description                       |
| ------ | ------------------------- | --------------------------------- |
| GET    | `/{id}/users`             | Get all users in the chat         |
| POST   | `/{id}/users/add`         | Add users to a group              |
| POST   | `/{id}/users/remove`      | Remove users                      |
| POST   | `/{id}/users/{uid}/admin` | Promote/demote user to/from admin |

#### 💬 Messaging

| Method | Endpoint                      | Description                |
| ------ | ----------------------------- | -------------------------- |
| GET    | `/{id}/messages`              | Get all messages in a chat |
| POST   | `/{id}/messages`              | Send a new message         |
| POST   | `/{id}/messages/{mid}/update` | Update a message           |
| POST   | `/{id}/messages/{mid}/delete` | Delete a message           |

#### 💖 Reactions & Views

| Method | Endpoint                     | Description                    |
| ------ | ---------------------------- | ------------------------------ |
| GET    | `/{id}/messages/{mid}/likes` | Get users who liked a message  |
| POST   | `/{id}/messages/{mid}/likes` | Like/unlike a message          |
| GET    | `/{id}/messages/{mid}/views` | Get users who viewed a message |
| POST   | `/{id}/messages/{mid}/views` | Mark message as viewed         |

## 🔔 Activity Messages

Automatically generated for:

- Group creation
- User added/removed
- Group settings updated
- User left the chat

Disable globally in `config/chat.php`:

```php
'enable_activity_messages' => false,
```

## 📁 File Uploads

Supports media upload (image, video, file).

Make sure storage is linked:

```php
php artisan storage:link
```

## 📄 License

This project is licensed under the MIT License.
