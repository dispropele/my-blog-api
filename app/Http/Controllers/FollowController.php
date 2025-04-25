<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{


    /**
     * @param User $user пользователь на которого подписывается
     *
     * Подписка на пользователя
     * POST /api/users/{user}/follow
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(User $user, Request $request)
    {
        $follower = $request->user();

        //Проверка на самого себя
        if($follower->id === $user->id){
            return response()->json([
                'success' => false,
                'message' => 'Вы не можете подписаться на самого себя!'
            ], 400);
        }

        //Проверка на подписку
        if($follower->following()->where('users.id', $user->id)->exists()){
            return response()->json([
                'success' => false,
                'message' => 'Вы уже подписаны на этого пользователя'
            ], 409);
        }

        //Подписываем пользователя
        // attach() для связи многие-ко-многим
        $follower->following()->attach($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Комментарий добавлен'
        ], 201);
    }

    /**
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     *
     * Отписка от пользователя
     * DELETE /api/users/{user}/follow
     */
    public function destroy(Request $request, User $user)
    {
        $follower = $request->user();

        if($follower->id === $user->id){
            return response()->json([
                'success' => false,
                'message' => 'Вы не можете отписаться от самого себя!'
            ], 400);
        }

        //Проверка на подписку
        if(!$follower->following()->where('user.id', $user->id)->exists()){
            return response()->json([
                'success' => false,
                'message' => 'Вы не подписаны на этого пользователя'
            ], 409);
        }

        //Удаляем подписку
        $follower->following()->detach($user->id);

        return response()->noContent();
    }

    /**
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     *
     * GET /api/users/{user}/following
     */
    public function followingIndex(Request $request, User $user)
    {
        //Получаем список подписок пользователя
        $following = $user->following()
            ->withCount(['followers','posts'])
            ->paginate(20);

        return UserResource::collection($following);
    }

    /**
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     *
     * GET /api/users/{user}/followers
     */
    public function followerIndex(Request $request, User $user)
    {
        //Получаем список подписчиков пользователя
        $followers = $user->followers()
            ->withCount(['followers','posts'])
            ->paginate(20);

        return UserResource::collection($followers);
    }

}
