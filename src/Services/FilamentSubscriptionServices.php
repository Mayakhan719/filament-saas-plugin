<?php

namespace Maya\FilamentSaasPlugin\Services;

use Maya\FilamentSaasPlugin\Facades\FilamentNotify;
use Maya\FilamentSaasPlugin\Models\Team;
use Maya\FilamentSaasPlugin\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Maya\FilamentSaasPlugin\Services\Contracts\Subscriber;

class FilamentSubscriptionServices
{
    public static array $authorTypes = [];

    public \Closure $beforeSubscription;
    public \Closure $beforeRenew;
    public \Closure $beforeCanceling;
    public \Closure $beforeChange;

    public \Closure $afterSubscription;
    public \Closure $afterRenew;
    public \Closure $afterCanceling;
    public \Closure $afterChange;

    private string $currentPanel;

    public function __construct()
    {
        $this->currentPanel = Filament::getCurrentPanel()->getId();
        $this->beforeSubscription = fn(array $data) => null;
        $this->beforeRenew = fn(array $data) => null;
        $this->beforeCanceling = fn(array $data) => null;
        $this->beforeChange = fn(array $data) => null;
        $this->afterSubscription = fn (array $data) => null;
        $this->afterRenew = fn (array $data) => null;
        $this->afterCanceling = fn (array $data) => null;
        $this->afterChange = fn (array $data) => null;
    }

    public function getAfterRenew(): \Closure
    {
        return $this->afterRenew;
    }

    public function getAfterSubscription(): \Closure
    {
        return $this->afterSubscription;
    }

    public function getAfterCanceling(): \Closure
    {
        return $this->afterCanceling;
    }

    public function getAfterChange(): \Closure
    {
        return $this->afterChange;
    }

    public function getBeforeRenew(): \Closure
    {
        return $this->beforeRenew;
    }

    public function getBeforeSubscription(): \Closure
    {
        return $this->beforeSubscription;
    }

    public function getBeforeCanceling(): \Closure
    {
        return $this->beforeCanceling;
    }

    public function getBeforeChange(): \Closure
    {
        return $this->beforeChange;
    }

    public static function register(Subscriber|array $author)
    {
        if (is_array($author)) {
            foreach ($author as $type) {
                self::register($type);
            }
            return;
        }
        self::$authorTypes[] = $author;
    }

    public function afterSubscription(\Closure $afterSubscription): void
    {
        $this->afterSubscription = $afterSubscription;
    }

    public function afterRenew(\Closure $afterRenew): void
    {
        $this->afterRenew = $afterRenew;
    }

    public function afterCanceling(\Closure $afterCanceling): void
    {
        $this->afterCanceling = $afterCanceling;
    }

    public function afterChange(\Closure $afterChange): void
    {
        $this->afterChange = $afterChange;
    }

    public function beforeSubscription(\Closure $beforeSubscription): void
    {
        $this->beforeSubscription = $beforeSubscription;
    }

    public function beforeRenew(\Closure $beforeRenew): void
    {
        $this->beforeRenew = $beforeRenew;
    }

    public function beforeCanceling(\Closure $beforeCanceling): void
    {
        $this->beforeCanceling = $beforeCanceling;
    }

    public function beforeChange(\Closure $beforeChange): void
    {
        $this->beforeChange = $beforeChange;
    }

    public static function getOptions(): Collection
    {
        return collect(self::$authorTypes);
    }
}
