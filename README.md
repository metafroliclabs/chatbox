<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Laravel Chat Package

A powerful and customizable chat system built for Laravel applications. This package supports private and group chats, media sharing, chat settings, user roles, activity messages, and more.

## 🚀 Features

- Private & Group Chat
- Message types: message, activity
- User roles: admin, user
- Group settings (permissions control)
- Message read/unread tracking
- Activity messages: group creation, joins, leaves, settings changes
- Media upload support
- Configurable user info: name, avatar
- Extendable & clean architecture (Service-based)

## 📦 Installation

```bash
composer require metafroliclabs/laravel-chat
```

```bash
php artisan chat:install
```

Or, manually publish configuration file:

```bash
php artisan vendor:publish --tag=chat-config
```

Run migrations:

```bash
php artisan migrate
```

Make sure storage is linked:

```bash
php artisan storage:link
```

## ⚙️ Configuration

Customize settings in `config/chat.php`:

1.  #### Pagination:

Enable or disable pagination:

```php
'pagination' => true,
```

2.  #### Activity Messages

Automatically generated for:

- Group creation
- User added/removed
- Group settings updated
- User left the chat

You can disable all activity messages globally:

```php
'message' => [
    'enable_activity' =>  false,
]
```

3.  #### User Model Configuration

Define how user information (name and avatar) is retrieved:

```php
'user' => [
    'name_cols' => ['first_name', 'last_name'], // Columns to build full name
    'image_col' => 'avatar',                    // Column for profile picture
    'enable_image_url' => true,                 // If true, image will be URL
]
```

## 🧠 Usage

#### 📚 API Endpoints

All routes are prefixed by the config value `chat.prefix` (default: chat) and use the `chat.middleware` middleware group.

_Middleware:_ `auth:sanctum` is required.

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
| GET    | `/{id}`         | Chat detail                     |

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
| POST   | `/{id}/messages/forward`      | Forward messages           |
| POST   | `/{id}/messages/{mid}/update` | Update a message           |
| POST   | `/{id}/messages/{mid}/delete` | Delete a message           |

#### 💖 Reactions & Views

| Method | Endpoint                     | Description                    |
| ------ | ---------------------------- | ------------------------------ |
| GET    | `/{id}/messages/{mid}/likes` | Get users who liked a message  |
| POST   | `/{id}/messages/{mid}/likes` | Like/unlike a message          |
| GET    | `/{id}/messages/{mid}/views` | Get users who viewed a message |
| POST   | `/{id}/messages/{mid}/views` | Mark message as viewed         |

## 📄 License

This project is licensed under the MIT License.
