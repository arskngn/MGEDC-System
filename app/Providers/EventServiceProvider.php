<?php

namespace App\Providers;

use App\Events\SaleCreated;
use App\Events\SaleReturnCreated;
use App\Events\SalePaymentReceived;
use App\Events\PurchaseCreated;
use App\Events\PurchaseReturnCreated;
use App\Events\PurchasePaymentRecorded;
use App\Events\AdjustmentCreated;
use App\Events\TransferCreated;
use App\Events\StaffCreated;
use App\Events\ModelChanged;
use App\Listeners\SendSaleNotification;
use App\Listeners\CheckLowStock;
use App\Listeners\SendStaffWelcomeNotification;
use App\Listeners\UpdateUserLastSeen;
use App\Listeners\LogModelActivity;
use App\Listeners\PasswordResetListener;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        PasswordReset::class => [
            PasswordResetListener::class,
        ],
        Login::class => [
            UpdateUserLastSeen::class,
        ],
        StaffCreated::class => [
            SendStaffWelcomeNotification::class,
        ],
        ModelChanged::class => [
            LogModelActivity::class,
        ],
        SaleCreated::class => [
            SendSaleNotification::class,
            CheckLowStock::class,
        ],
        SaleReturnCreated::class => [
            CheckLowStock::class,
        ],
        SalePaymentReceived::class => [
            // Add listeners for payments if needed (e.g., UpdateAccounting)
        ],
        PurchaseCreated::class => [
            CheckLowStock::class,
        ],
        PurchaseReturnCreated::class => [
            CheckLowStock::class,
        ],
        PurchasePaymentRecorded::class => [
            // Add listeners for payments if needed
        ],
        AdjustmentCreated::class => [
            CheckLowStock::class,
        ],
        TransferCreated::class => [
            CheckLowStock::class,
        ],
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
