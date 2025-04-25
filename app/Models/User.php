<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'login',
        'avatar',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    //Посты пользователя
    public function posts(): HasMany{
        return $this->hasMany(Post::class);
    }

    //Комментарии пользователя
    public function comments(): HasMany{
        return $this->hasMany(Comment::class);
    }

    //Подписки пользователя
    public function following():BelongsToMany{
        return $this->belongsToMany(User::class, 'subscriptions', 'follower_id', 'followed_id')
            ->withTimestamps();
    }

    //Подписчики пользователя
    public function followers():BelongsToMany{
        return $this->belongsToMany(User::class, 'subscriptions', 'followed_id', 'follower_id')
            ->withTimestamps();
    }
}
