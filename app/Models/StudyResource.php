<?php

namespace App\Models;

use App\Enums\ResourceType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tells IDEs about the withDetails() scope, which Laravel calls for us.
 *
 * @method static Builder<static> withDetails(User $user)
 */
#[Fillable(['title', 'description', 'type', 'file_path', 'original_name', 'mime_type', 'size'])]
class StudyResource extends Model
{
    /** @use HasFactory<\Database\Factories\StudyResourceFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ResourceType::class,
        ];
    }

    /**
     * Everything a resource response shows, loaded up front so the number of
     * queries stays the same however many resources are listed (no N+1).
     * Used as StudyResource::withDetails($user).
     */
    #[Scope]
    protected function withDetails(Builder $query, User $user): void
    {
        $query->with(['user', 'tags'])
            ->withSum('votes as score', 'value')
            // The unique index allows one vote per user, so this sum is just "my vote".
            ->withSum(['votes as my_vote' => fn ($query) => $query->where('user_id', $user->id)], 'value');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
