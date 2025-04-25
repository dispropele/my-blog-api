<?php
namespace App\Http\Controllers;

use App\Http\Requests\Api\StorePostRequest;
use App\Http\Requests\Api\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * CRUD operations for posts
 */
class PostController extends Controller
{

    /**
     * Display a listing of the resource.
     * GET /api/posts
     *
     * Parameters:
     * - category (string) : slug
     * - subscribed (boolean) : filter by followed authors (1 or 0)
     * - date_from
     * - date_to
     * - sort_by (string) : 'created_at'(default) or 'title'
     * - sort_dir (string) : 'desc' or 'asc'
     * - page (int): page for pagination
     */
    public function index(Request $request)
    {
        //Построение запроса, оптимизируем запрос выбирая только некоторые поля
        $query = Post::with(['user:id,login,avatar', 'category:id,name,slug']);

        //Фильтр по категории
        if($categorySlug = $request->input('category')){
            $query->whereHas('category',
                fn($q)=>$q->where('slug', $categorySlug));
        }

        //Фильтр по подпискам
        if($request->boolean('subscribed') && Auth::check()){
            $user = Auth::user();
            $followingIds = $user->following()->pluck('users.id');

            //Проверка на наличие подписок
            if($followingIds->isNotEmpty()){
                $query->whereIn('user_id', $followingIds);
            } else {
                //Не возвращаем ничего
                $query->whereRaw('1 = 0');
            }
        }

        //Фильтр по дате 'от'
        if($dateFrom = $request->input('date_from')){
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        //Фильтр по дате 'до'
        if($dateTo = $request->input('date_to')){
            $query->whereDate('created_at', '<=', $dateTo);
        }

        //Разрешенные сортировки
        $allowedSorts = ['created_at', 'title'];
        $defaultSort = 'created_at';

        $sortBy = $request->input('sort_by', $defaultSort);
        //Проверка на разрешенные сортировки
        if(!in_array($sortBy, $allowedSorts)){
            $sortBy = $defaultSort;
        }
        //Направление сортировки
        $sortDirection = $request->input('sort_dir', 'desc');
        if(!in_array(strtolower($sortDirection), ['desc', 'asc'])){
            $sortDirection = 'desc';
        }

        //Сортировка по параметру
        $query->orderBy($sortBy, $sortDirection);

        //Пагинация
        $posts = $query->paginate(15)->withQueryString();

        return PostResource::collection($posts);
    }


    /**
     * Store a newly created resource in storage.
     * POST /api/posts
     *
     * Parameters:
     * - image (file)
     * - title (string)
     * - category_id (integer)
     * - body (text)
     *
     */
    public function store(StorePostRequest $request)
    {
        $validated = $request->validated();

        //Обработка фото
        if($request->hasFile('image') && $request->file('image')->isValid()){
            //Сохраняем по пути /storage/app/public/post_images/
            $imagePath = $request->file('image')->store('post_images', 'public');
            $validated['image'] = $imagePath;
        } else {
            //Удаляем фото, если были ошибки
            unset($validated['image']);
        }

        //Создаем пост
        $post = Post::create([
                'user_id' => $request->user()->id,
            ] + $validated);
        //Подгружаем связи для ответа
        $post->load('user:id,login,avatar', 'category:id,name,slug');

        return response(new PostResource($post));
    }

    /**
     * Display the specified resource.
     * GET /api/posts/{slug-id}
     */
    public function show(Post $post, string $slugWithId)
    {
        //Получаем id
        if(!preg_match('/-(\d+)$/', $slugWithId, $matches)){
            abort(404, 'Invalid post format.');
        }
        $id = $matches[1];
        $slug = substr($slugWithId, 0, -strlen($matches[0]));

        //Ищем пост по id
        $post = Post::with([
            'user:id,login,avatar',
            'category:id,name,slug',  //Получаем комментарии и подгружаем пользователей
            'comments' => fn($query) => $query->with('user:id,login,avatar')->latest()
        ])->findOrFail($id);

        return new PostResource($post);
    }

    /**
     * Update the specified resource in storage.
     * PUT/PATCH /api/posts/{post}
     *
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        $validated = $request->validated();
        $imagePath = $post->image;

        //Обновление изображения
        if($request->hasFile('image')){
            //Проверяем наличие фото в посте
            if($imagePath){
                //Удаляем при наличии
                Storage::disk('public')->delete($imagePath);
            }
            //Сохраняем новый путь
            $imagePath = $request->file('image')->store('post_images', 'public');
            $validated['image'] = $imagePath;
        } else {
            unset($validated['image']);
        }

        $post->update($validated);
        $post->load('user:id,login,avatar', 'category:id,name,slug');

        return new PostResource($post);
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /api/posts/{post}
     *
     */
    public function destroy(Post $post)
    {
        //Проверяем доступ
        $this->authorize('delete', $post);
        $image = $post->image;

        //Удаляем фото при наличии
        if($image){
            Storage::disk('public')->delete($image);
        }
        $post->delete();

        return response()->noContent();
    }
}
