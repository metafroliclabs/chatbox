<?php

namespace Metafroliclabs\LaravelChat\Policies;

class ChatAccessPolicy
{
    /**
     * Base permissions for each chat type.
     *
     * @return array<string, array<string>>
     */
    protected static function basePermissions(): array
    {
        return [
            'standard' => ['*'], // allow everything
            'universal' => [
                'ChatController@index',
                'ChatController@unread_list',
                'ChatController@unread_count',
                'ChatController@delete',
                'ChatController@mute',
                'ChatController@show',
                'ChatMessageController@send_message',
                // 'ChatMessageController@forward_messages',
                'ChatMessageController@index',
                'ChatMessageController@update_message',
                'ChatMessageController@delete_message',
            ]
        ];
    }

    /**
     * Dynamically append permissions based on feature flags in config.
     *
     * @return array<string, array<string>>
     */
    protected static function dynamicPermissions(): array
    {
        $permissions = static::basePermissions();

        if (config('chat.features.reactions')) {
            $permissions['universal'][] = 'ChatMessageController@get_message_likes';
            $permissions['universal'][] = 'ChatMessageController@like_message';
        }

        if (config('chat.features.views')) {
            $permissions['universal'][] = 'ChatMessageController@get_message_views';
            $permissions['universal'][] = 'ChatMessageController@view_message';
        }

        return $permissions;
    }

    /**
     * Determine if the current controller @ method call is allowed under current chat mode.
     *
     * @param string $controller
     * @param string $method
     * @return bool
     */
    public static function isAllowed(string $controller, string $method): bool
    {
        $mode = config('chat.type', 'standard');
        $key = "{$controller}@{$method}";

        $allowed = static::dynamicPermissions()[$mode] ?? [];

        return in_array('*', $allowed) || in_array($key, $allowed);
    }

    /**
     * Determine if a given feature is enabled based on chat type and config.
     *
     * @param string $feature
     * @return bool
     */
    public static function isFeatureEnabled(string $feature): bool
    {
        $type = config('chat.type', 'standard');

        // In standard mode, all features are allowed by default
        if ($type === 'standard') {
            return true;
        }

        // In universal mode, check if the feature is explicitly enabled
        return config("chat.features.$feature", false);
    }
}
