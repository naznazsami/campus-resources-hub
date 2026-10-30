<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\StudyResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    /**
     * Comments on a resource, oldest first, with each author eager loaded (no N+1).
     */
    public function index(StudyResource $resource): AnonymousResourceCollection
    {
        $comments = $resource->comments()
            ->with('user')
            ->oldest()
            ->orderBy('id')
            ->paginate(20);

        return CommentResource::collection($comments);
    }

    public function store(StoreCommentRequest $request, StudyResource $resource): JsonResponse
    {
        // Creating through the relationship fills in study_resource_id;
        // user_id always comes from the token, never from the request body.
        $comment = $resource->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);

        return (new CommentResource($comment->load('user')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Edit a comment. Authorship is checked in UpdateCommentRequest::authorize().
     */
    public function update(UpdateCommentRequest $request, Comment $comment): CommentResource
    {
        $comment->update(['body' => $request->validated('body')]);

        return new CommentResource($comment->load('user'));
    }

    public function destroy(Comment $comment): Response
    {
        Gate::authorize('delete', $comment);

        $comment->delete();

        return response()->noContent();
    }
}
