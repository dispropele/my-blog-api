<?php

namespace App\Providers;

use App\Events\PostCreated;
use App\Listeners\NotifyFollowersAboutNewPost;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{

    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        PostCreated::class => [
            NotifyFollowersAboutNewPost::class
        ]
    ];

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents() : bool{
        return false;
    }
}
