<?php
namespace Maya\FilamentSaasPlugin\Services;

use Maya\FilamentSaasPlugin\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class FilamentNotificationService
{
    public function notify(
        string $title,
        ?string $body = null,
        string $type = 'info',
        bool $toDatabase = false,
        bool $toToast = true,
        ?User $to = null,
        bool $persistent = false,
        ?string $icon = null,
        ?string $color = null,
        array $actions = []
    ): void {
        $user = $to ?? Auth::user();

        if (!$user) {
            return;
        }

        $notification = Notification::make()
            ->title($title)
            ->{self::mapType($type)}();

        if ($body) {
            $notification->body($body);
        }

        if ($persistent) {
            $notification->persistent();
        }

        if ($icon) {
            $notification->icon($icon);
        }

        if ($color) {
            $notification->color($color);
        }

        if (!empty($actions)) {
            $notification->actions($actions);
        }

        if ($toToast && $user->id === Auth::id()) {
            $notification->send();
        }

        if ($toDatabase) {
            $notification->sendToDatabase($user);
        }
    }

    protected static function mapType(string $type): string
    {
        return [
            'success' => 'success',
            'info' => 'info',
            'warning' => 'warning',
            'danger' => 'danger',
            'error' => 'danger',
        ][$type] ?? 'info';
    }
}
