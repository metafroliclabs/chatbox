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

#### 1. Chat type:

You can switch between standard (private/group chat) and universal (global chat):

```php
'type' => 'standard',
```

Also in universal type, you can enable/disable some modules:

```php
'features' => [
    'reactions' => true,
    'views' => false
],
```

#### 2. Pagination:

Enable or disable pagination:

```php
'pagination' => true,
```

#### 3. Activity Messages

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

#### 4. User Model Configuration

Define how user information (name and avatar) is retrieved:

```php
'user' => [
    'name_cols' => ['first_name', 'last_name'], // Columns to build full name
    'image_col' => 'avatar',                    // Column for profile picture
    'enable_image_url' => true,                 // If true, image will be URL
]
```

#### 5. Rate Limiting

You can control how many chats a user can create and how many messages they can send per minute. These limits help prevent spam and abuse.

```php
'rate_limits' => [
    'chat_creation_per_minute' => 20, // Max 20 chats per user per minute
    'messages_per_minute' => 40      // Max 40 messages per user per minute
],
```

- Limits are enforced per authenticated user (or IP if unauthenticated).
- You can adjust these values to suit your application's needs.

## 📡 Events in Laravel Chat

Laravel Chat dispatches events to help you hook into the system and extend functionality such as notifications, logging, analytics, and more.

### 🔥 Available Events

#### `Metafroliclabs\LaravelChat\Events\MessageSent`

Dispatched when a message is successfully sent in a chat.

#### Event Data:

```php
public MessageSent(Chat $chat, array $messages, User $sender, array $receiver)
```

### ⚙️ How to Use

#### Step 1: Create a Listener

```bash
php artisan make:listener HandleMessageSent
```

#### Step 2: Handle the Event

```php
namespace App\Listeners;

use Metafroliclabs\LaravelChat\Events\MessageSent;

class HandleMessageSent
{
    public function handle(MessageSent $event)
    {
        $chat = $event->chat;
        $messages = $event->messages;
        $sender = $event->sender;
        $receiver = $event->receiver;

        // Example: Send push notifications or log activity
    }
}
```

### 🔧 Register the Listener

In your `app/Providers/EventServiceProvider.php`:

```php
protected $listen = [
    Metafroliclabs\LaravelChat\Events\MessageSent::class => [
        App\Listeners\HandleMessageSent::class,
    ],
];
```

Then run:

```bash
php artisan event:cache
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

#### ⚡ Rate Limiting

Some endpoints are rate limited to prevent abuse:

- **Chat Creation** (`/create`, `/create/group`): Limited to `chat_creation_per_minute` (default: 20) per user per minute.
- **Message Sending** (`/{id}/messages`, `/{id}/messages/forward`): Limited to `messages_per_minute` (default: 40) per user per minute.

If a user exceeds these limits, a `429 Too Many Requests` response will be returned by the API.

## 📄 License

This project is licensed under the MIT License.
