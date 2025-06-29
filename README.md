# Filament SaaS Plugin
A Laravel + Filament plugin to add SaaS multi-tenant features like plans, subscriptions, payments, and more. This package includes both admin and client panel logic, designed to streamline multi-tenant SaaS application development using Filament.

Supports **PSR-4 Autoloading**, **PSR-12 Coding Standards**, and is designed to work within Laravel's service container and event systems.

---

## 📦 Installation

### 1. Laravel Subscriptions Library

Install Laravel Subscriptions:  
GitHub: [laravelcm/laravel-subscriptions](https://github.com/laravelcm/laravel-subscriptions)

```bash
composer require laravelcm/laravel-subscriptions
php artisan vendor:publish --provider="Laravelcm\Subscriptions\SubscriptionServiceProvider"
php artisan migrate
```
### 2. Spatie Media Library
Used for managing media uploads (e.g., plan images, user uploads)
GitHub: spatie/laravel-medialibrary

```bash
composer require spatie/laravel-medialibrary
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
php artisan migrate
```
### 3. Filament SaaS Plugin
```bash
composer require maya/filament-saas-plugin
php artisan vendor:publish --provider="Maya\FilamentSaasPlugin\FilamentSaasPluginServiceProvider"
```
### 🧩 Panel Setup
Admin Panel Provider
Add the following to your Admin PanelProvider:

```php
->plugin(new FilamentSaasAdminPlugin)
```
Includes:
✅ Subscription Resource

✅ Payment Resource (with gateway relation manager)

✅ Plan Resource (with features relation manager)

⚠️ Do not include ->login() or ->register() in the super admin panel.

Client Panel Provider
Add the following to your Client PanelProvider:

```php
->plugin(new FilamentSaasClientPlugin)
```
Includes:

✅ Billing & Payment pages

✅ Livewire components for subscription and payment

✅ Middleware for subscription checks

✅ Custom single-page login

👤 The client panel is meant to be the default panel.

### 💳 Payment Integration
Inside App\Providers\AppServiceProvider → boot() method, define the following:

```php
FilamentSubscriptions::beforeSubscription(function ($data) {
    $this->PaymentPage($data, SubscribePlan::class);
});
FilamentSubscriptions::beforeRenew(function ($data) {
    $this->PaymentPage($data, RenewPlan::class);
});
FilamentSubscriptions::beforeChange(function ($data) {
    $this->PaymentPage($data, ChangePlan::class);
});
```
And define the PaymentPage() method:

```php
private function PaymentPage($data, $event)
{
    return redirect()->to(
        FilamentPayments::pay(
            data: PaymentRequest::make(Plan::class)
                ->model_id($data['new']->id)
                ->team_id($data['team_id'])
                ->event($event)
                ->currency('USD')
                ->amount($data['new']->price)
                ->details('Subscription Payment')
                ->success_url(url('/client'))
                ->cancel_url(url('/client'))
                ->customer(
                    PaymentCustomer::make('John Doe')
                        ->email('john@gmail.com')
                        ->mobile('+201207860084')
                )
                ->billing_info(
                    PaymentBillingInfo::make('123 Main St')
                        ->area('Downtown')
                        ->city('Cairo')
                        ->state('Cairo')
                        ->postcode('12345')
                        ->country('EG')
                )
                ->shipping_info(
                    PaymentShippingInfo::make('123 Main St')
                        ->area('Downtown')
                        ->city('Cairo')
                        ->state('Cairo')
                        ->postcode('12345')
                        ->country('EG')
                )
        )
    );
}
```
### 🔔 After Payment Callback
After a successful payment, you may:

Send database or broadcast notifications

Notify all super admins

Example:

```php
$super_admins = User::where('is_super', true)->get();
```
### 👤 User Model Setup
In your User.php model:

```php
use Maya\FilamentSaasPlugin\Contracts\SaasUser;
use Maya\FilamentSaasPlugin\Traits\HasSaasFeatures;

class User extends Authenticatable implements SaasUser
{
    use HasSaasFeatures;

    protected $fillable = [
        'is_super',
        'latest_team_id',
    ];
}
```
### 🧑‍🤝‍🧑 Team Model Setup
Create your Team.php model and extend from the base model:

```php
use Maya\FilamentSaasPlugin\Models\Team as BaseTeam;

class Team extends BaseTeam
{
    // Add additional relations if needed
}
```
To support multi-tenancy, configure the necessary relations in this model.
📖 See Filament Multi-Tenancy Docs

✅ Features Summary
✔ Admin & Client panel plugins

✔ Payment integration hooks

✔ Subscription lifecycle events

✔ Filament Resources for Plans, Features, Subscriptions

✔ Media uploads (via Spatie)

✔ Laravel Subscriptions Support

✔ Multi-tenancy ready

✔ Super admin control

📚 Resources
Laravel: https://laravel.com

FilamentPHP: https://filamentphp.com

Laravel Subscriptions: https://github.com/laravelcm/laravel-subscriptions

Spatie Media Library: https://github.com/spatie/laravel-medialibrary

📄 License
The MIT License (MIT). Please see the LICENSE file for more information.

```vbnet
Let me know if you'd like the corresponding `composer.json`, a `CONTRIBUTING.md`, or GitHub Actions CI/CD workflow example to go along with it.
```
