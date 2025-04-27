<?php

namespace App\Listeners;

use App\Events\PostCreated;
use App\Notifications\NewPostNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class NotifyFollowersAboutNewPost
{
    use InteractsWithQueue; //Трейт для очередей

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PostCreated $event): void
    {
        $author = $event->post->user;

        $author->followers()->chunkById(200, function($followers) use ($event) {
            Notification::send($followers, new NewPostNotification($event->post));
        });
    }
}
