<?php

namespace App\Models;

use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'slug', 'type', 'description', 'poster_url', 'duration_minutes', 'age_limit', 'published_at'])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->published_at?->isPast() ?? false;
    }

    public function isScheduled(): bool
    {
        return $this->published_at?->isFuture() ?? false;
    }

    public function publicationStatus(): string
    {
        if ($this->isPublished()) {
            return 'published';
        }

        return $this->isScheduled() ? 'scheduled' : 'draft';
    }

    public function performances(): HasMany
    {
        return $this->hasMany(Performance::class);
    }
}
