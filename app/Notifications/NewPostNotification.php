<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPostNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Post $post; //храним сам пост
    public User $recipient; //получатель

    /**
     * Create a new notification instance.
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Указываем что нужно хранить в бд и отправить через вещание
        return ['database', 'broadcast'];
    }

    /**
     * Get the broadcast representation of the notification.
     * Данные которые будут отправлены через pusher
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => class_basename($this),
            'read_at' => null,
            'created_at' => now()->toIso8601String(),
            'created_at_diff' => now()->diffForHumans(),

            'post_id' => $this->post->id,
            'post_title' => $this->post->title,
            'author_id' => $this->post->user->id,
            'author_name' => $this->post->user->login,
            'message' => $this->post->user->name . ' published a new post: "' . $this->post->title . '"',
        ]);
    }

    public function broadcastOn() : array{
        return [new PrivateChannel('App.Models.User'.$this->recipient->id)];
    }
}
