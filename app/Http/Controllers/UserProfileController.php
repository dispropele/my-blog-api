<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Http\Resources\UserProfileResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{

    /**
     * @param Request $request
     * @param User $user
     * @return UserProfileResource
     *
     * Просмотр профиля пользователя
     * GET /api/profiles/{user}
     * - user это id
     */
    public function show(Request $request, User $user)
    {
        $user->loadCount(['followers', 'following']);

        $posts = $user->posts()
            ->with(['user:id,login,avatar', 'category:id,name,slug'])
            ->latest()
            ->paginate(10);

        //Статус подписки текущего пользователя
        $isFollowing = false;
        $viewer = Auth::guard('sanctum')->user();

        if($viewer)
        {
            //Проверка на свой профиль
            if($viewer->id !== $user->id){
                $isFollowing = $viewer->following()->where('users.id', $user->id)->exists();
                //Смотрим есть ли подписка или нет по id просматриваемого пользователя
            }
        }

        return (new UserProfileResource($user))
            ->additional([
                //Добавляем к ответу посты с пагинацией
               'posts' => PostResource::collection($posts),
                //Добавляем мета-инфу о подписке
                'meta' => [
                    'is_following' => $isFollowing
                ]
            ]);

    }


}
