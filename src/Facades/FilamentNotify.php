<?php

namespace Maya\FilamentSaasPlugin\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void success(string $title, ?string $body = null, bool $sendDatabase = false, ?\Illuminate\Database\Eloquent\Model $recipient = null, array $actions = [])
 * @method static void error(string $title, ?string $body = null, bool $sendDatabase = false, ?\Illuminate\Database\Eloquent\Model $recipient = null, array $actions = [])
 * @method static void warning(string $title, ?string $body = null, bool $sendDatabase = false, ?\Illuminate\Database\Eloquent\Model $recipient = null, array $actions = [])
 * @method static void info(string $title, ?string $body = null, bool $sendDatabase = false, ?\Illuminate\Database\Eloquent\Model $recipient = null, array $actions = [])
 * @method static void custom(string $title, ?string $body = null, ?string $icon = null, ?string $color = null, bool $sendDatabase = false, ?\Illuminate\Database\Eloquent\Model $recipient = null, array $actions = [])
 * @method static void persistent(string $title, ?string $body = null, string $type = 'info', bool $sendDatabase = false, ?\Illuminate\Database\Eloquent\Model $recipient = null, array $actions = [])
 */
class FilamentNotify extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'filament-notify';
    }
}