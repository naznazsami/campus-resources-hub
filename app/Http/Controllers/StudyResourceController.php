<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexStudyResourceRequest;
use App\Http\Requests\StoreStudyResourceRequest;
use App\Http\Requests\UpdateStudyResourceRequest;
use App\Http\Resources\StudyResourceResource;
use App\Models\StudyResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudyResourceController extends Controller
{
    /**
     * Paginated list with optional ?type=, ?tag=, ?search= and ?sort=newest|top.
     */
    public function index(IndexStudyResourceRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();

        $resources = StudyResource::withDetails($request->user())
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($filters['tag'] ?? null, fn ($query, $tag) => $query->whereHas(
                'tags', fn ($query) => $query->where('name', Str::lower($tag))
            ))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('title', 'like', "%{$search}%"))
            // The project's one raw SQL exception: SUM() of no votes is NULL, which
            // sorts below every number, so treat it as 0. Fixed text, no user input.
            ->when(($filters['sort'] ?? 'newest') === 'top', fn ($query) => $query->orderByRaw('COALESCE(score, 0) DESC'))
            ->latest()
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return StudyResourceResource::collection($resources);
    }

    /**
     * Upload a new resource (multipart/form-data).
     */
    public function store(StoreStudyResourceRequest $request): JsonResponse
    {
        $file = $request->file('file');

        // Laravel picks the stored name (40 random characters plus an extension
        // worked out from the content) and saves it on the private "local" disk.
        $path = $file->store('resources', 'local');
        abort_if($path === false, 500, 'The file could not be stored.');

        $resource = DB::transaction(function () use ($request, $file, $path) {
            $resource = $request->user()->resources()->create([
                'title' => $request->validated('title'),
                'description' => $request->validated('description'),
                'type' => $request->validated('type'),
                'file_path' => $path,
                // The client's filename is only a label for downloads; it never touches the disk.
                'original_name' => Str::substr($file->getClientOriginalName(), 0, 255),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);

            $this->syncTags($resource, $request->validated('tags') ?? []);

            return $resource;
        });

        return (new StudyResourceResource(StudyResource::withDetails($request->user())->findOrFail($resource->id)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, StudyResource $resource): StudyResourceResource
    {
        return new StudyResourceResource(StudyResource::withDetails($request->user())->findOrFail($resource->id));
    }

    /**
     * Edit title, description, type and tags (JSON). Ownership is checked in
     * UpdateStudyResourceRequest::authorize().
     */
    public function update(UpdateStudyResourceRequest $request, StudyResource $resource): StudyResourceResource
    {
        DB::transaction(function () use ($request, $resource) {
            $resource->update($request->safe()->except('tags'));

            // Only touch tags if the client sent them; an empty list removes them all.
            if ($request->has('tags')) {
                $this->syncTags($resource, $request->validated('tags'));
            }
        });

        return new StudyResourceResource(StudyResource::withDetails($request->user())->findOrFail($resource->id));
    }

    public function destroy(StudyResource $resource): Response
    {
        Gate::authorize('delete', $resource);

        // Votes, comments and tag links are removed by the cascading foreign keys.
        // The row goes first: if deleting the file then fails, we are left with a
        // harmless stray file rather than a record pointing at nothing.
        $resource->delete();
        Storage::disk('local')->delete($resource->file_path);

        return response()->noContent();
    }

    public function download(StudyResource $resource): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($resource->file_path), 404, 'File not found.');

        // Sent as an "attachment", so the browser saves the file instead of opening it.
        return Storage::disk('local')->download($resource->file_path, $resource->original_name);
    }

    /**
     * Find or create a Tag for each name, then make those exactly the
     * resource's tags (sync adds new links and removes old ones).
     *
     * @param  array<int, string>  $names
     */
    private function syncTags(StudyResource $resource, array $names): void
    {
        $tagIds = collect($names)->map(fn (string $name) => Tag::firstOrCreate(['name' => $name])->id);

        $resource->tags()->sync($tagIds);
    }
}
