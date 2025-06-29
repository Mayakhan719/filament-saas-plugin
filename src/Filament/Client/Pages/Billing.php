<?php

namespace Maya\FilamentSaasPlugin\Filament\Client\Pages;

use Maya\FilamentSaasPlugin\Events\CancelPlan;
use Maya\FilamentSaasPlugin\Facades\FilamentSubscriptions;
use Maya\FilamentSaasPlugin\Http\Middleware\VerifyBillableIsSubscribed;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Pages\Page;
use Filament\Pages\Concerns;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Event;
use Filament\Actions\Action;
use Laravelcm\Subscriptions\Models\Plan;

class Billing extends Page
{
    public $user;
    public $tenant;
    public $plans;
    public $currentSubscription;
    public $currentPanel;
    use Concerns\HasTopbar;
    use InteractsWithActions;
    protected static ?string $title = 'Billing';
    protected static string $view = 'filament-saas-plugin::filament.client.pages.billing';
    protected static string $layout = 'filament-saas-plugin::filament.client.layouts.billing';
    protected static string | array $withoutRouteMiddleware = VerifyBillableIsSubscribed::class;


    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
    protected function getLayoutData(): array
    {
        return [
            'hasTopbar' => $this->hasTopbar(),
        ];
    }

    public function hasLogo(): bool
    {
        return true;
    }
    public function mount()
    {
        $this->plans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $this->tenant = Filament::getTenant();
        $this->currentSubscription = $this->tenant->planSubscriptions()->first();
        $this->currentPanel = Filament::getCurrentPanel()->getId();

        if ($this->currentSubscription) {
            return null;
        }
    }

    public function subscribe(int $planId, bool $main = false)
    {
        if (!$planId) {
            return $this->handeNotificationWithRedirectToPanel(
                'Invalid Plan',
                'The selected plan is invalid or does not exist.',
                'danger',
            );
        }

        $plan = Plan::find($planId);

        if (!$plan) {
            return $this->handeNotificationWithRedirectToPanel(
                'Plan Not Found',
                'The selected plan was not found.',
                'danger',
            );
        }

        if ($this->currentSubscription) {
            if ($this->currentSubscription->plan_id === $plan->id) {
                if ($this->currentSubscription->active()) {
                    return $this->handeNotificationWithRedirectToPanel(
                        'Already Subscribed',
                        'You are already subscribed to this plan.',
                        'info'
                    );
                }

                if (!$main) {
                    return call_user_func(FilamentSubscriptions::getBeforeRenew(), [
                        "old" => $this->currentSubscription->plan,
                        "new" => $plan,
                        "subscription" => $this->currentSubscription,
                        "team_id" => Filament::getTenant()->id
                    ]);
                }
            }

            if (!$main) {
                return call_user_func(FilamentSubscriptions::getBeforeChange(), [
                    "old" => $this->currentSubscription->plan,
                    "new" => $plan,
                    "subscription" => $this->currentSubscription,
                    "team_id" => Filament::getTenant()->id
                ]);
            }
        }

        if (!$main) {
            return call_user_func(FilamentSubscriptions::getBeforeSubscription(), [
                "old" => null,
                "new" => $plan,
                "subscription" => null,
                "team_id" => Filament::getTenant()->id
            ]);
        }
    }


    public function changePlanAction(?Plan $plan = null): Action
    {
        $currentSubscription = $this->tenant?->planSubscriptions()->first();

        $isCurrentPlan = $plan
            && $currentSubscription
            && $currentSubscription->plan()->is($plan);

        $isCurrentPlanAndActive = $isCurrentPlan && $currentSubscription?->active();

        return Action::make('changePlanAction')
            ->requiresConfirmation()
            ->label(fn(): ?string => $this->textByPlan($plan))
            ->modalHeading(fn(array $arguments): ?string => $this->textByPlan(Plan::find($arguments['plan']['id'])))
            ->disabled(fn(): bool => $isCurrentPlanAndActive)
            ->color(fn(): string => match (true) {
                $isCurrentPlanAndActive => 'success',
                $isCurrentPlan && !$currentSubscription->active() => 'warning',
                default => 'primary',
            })
            ->icon(fn(): string => match (true) {
                $isCurrentPlanAndActive => 'heroicon-s-check-circle',
                $isCurrentPlan && $currentSubscription->canceled() => 'heroicon-s-arrow-path-rounded-square',
                $isCurrentPlan && $currentSubscription->ended() => 'heroicon-s-arrow-path-rounded-square',
                default => 'heroicon-s-arrows-right-left',
            })
            ->action(function (array $arguments) {
                $this->subscribe($arguments['plan']['id']);
            });
    }
    private function textByPlan(?Plan $plan = null): ?string
    {
        if (!$plan) {
            return null;
        }

        $subscription = $this->tenant->planSubscriptions()->first();

        if (!$subscription) {
            return 'Subscribe';
        }

        if ($subscription->plan()->is($plan)) {
            return match (true) {
                $subscription->active() => 'Current Subscription',
                $subscription->canceled() => 'Re-Subscribe',
                $subscription->ended() => 'Renew Subscription',
            };
        }

        return 'Change Subscription';
    }
    public function cancelPlanAction(): Action
    {
        return Action::make('cancelPlanAction')
            ->requiresConfirmation()
            ->label('Cancel Subscription')
            ->action(function () {
                $this->cancel();
            });
    }

    public function cancel()
    {
        $subscription = $this->tenant?->planSubscriptions()->first();

        if (!$subscription) {
            Notification::make()
                ->title('No active subscription found.')
                ->danger()
                ->send();
            return;
        }

        if ($subscription->canceled_at) {
            Notification::make()
                ->title('Subscription is already canceled.')
                ->warning()
                ->send();
            return;
        }

        $subscription->canceled_at = now();
        $subscription->ends_at = now()->addDays(30);
        $subscription->save();

        Event::dispatch(new CancelPlan([
            'old' => $subscription->plan,
            'new' => null,
            'subscription' => $subscription,
        ]));

        Notification::make()
            ->title('Subscription cancelled successfully.')
            ->success()
            ->send();
    }

    private function handeNotificationWithRedirectToPanel(
        string $title,
        string $body,
        string $status = 'info',
    ) {
        Notification::make()
            ->title($title)
            ->body($body)
            ->status($status)
            ->send();

        return redirect('/client');
    }
}
