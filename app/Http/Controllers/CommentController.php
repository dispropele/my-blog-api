<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * GET /api/posts/{post}/comments
     */
    public function index(Post $post)
    {
        $comments = $post->comments()
            ->with('user:id,avatar,login')
            ->latest()
            ->paginate(20);

        return CommentResource::collection($comments);
    }

    /**
     * Store a newly created resource in storage.
     *
     * POST /api/posts/{post}/comments
     */
    public function store(StoreCommentRequest $request, Post $post)
    {
        $comment = $post->comments()->create([
           'user_id' => $request->user()->id,
            'body' => $request->validated()['body'],
        ]);

        $comment->load('user:id,avatar,login');

        return response()->json(new CommentResource($comment), 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * DELETE /api/comments/{comment}
     */
    public function destroy(Comment $comment, Request $request)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->noContent();
    }
}
