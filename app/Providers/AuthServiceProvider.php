<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Policies\CommentPolicy;
use App\Policies\PostPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;


class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Post::class => PostPolicy::class,
        Comment::class => CommentPolicy::class
    ];


    public function boot(): void{
        $this->registerPolicies();
    }

}
