<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Post Model
 * 
 * Example model showing best practices for integration with
 * Wink View Generator generated views.
 */
class Post extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'content',
        'status',
        'slug',
        'excerpt',
        'featured_image',
        'published_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * Boot the model and set up event listeners.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug when creating
        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
                
                // Ensure uniqueness
                $baseSlug = $post->slug;
                $counter = 1;
                while (static::where('slug', $post->slug)->exists()) {
                    $post->slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
            }
            
            // Auto-generate excerpt if not provided
            if (empty($post->excerpt)) {
                $post->excerpt = Str::limit(strip_tags($post->content), 150);
            }
        });

        // Update slug if title changes and slug wasn't manually set
        static::updating(function ($post) {
            if ($post->isDirty('title') && 
                ($post->getOriginal('slug') === Str::slug($post->getOriginal('title')))) {
                $post->slug = Str::slug($post->title);
            }
            
            // Update excerpt if content changes and excerpt wasn't manually set
            if ($post->isDirty('content') && empty($post->excerpt)) {
                $post->excerpt = Str::limit(strip_tags($post->content), 150);
            }
        });
    }

    /**
     * Get the status badge class for UI display.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'published' => 'success',
            'draft' => 'warning',
            'archived' => 'secondary',
            default => 'light',
        };
    }

    /**
     * Get the status icon for UI display.
     */
    public function getStatusIconAttribute(): string
    {
        return match ($this->status) {
            'published' => 'bi-check-circle',
            'draft' => 'bi-clock',
            'archived' => 'bi-archive',
            default => 'bi-circle',
        };
    }

    /**
     * Get formatted published date for display.
     */
    public function getFormattedPublishedAtAttribute(): string
    {
        return $this->published_at?->format('M d, Y') ?? 'Not published';
    }

    /**
     * Get formatted created date for display.
     */
    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at->format('M d, Y g:i A');
    }

    /**
     * Get the post URL (useful for frontend links).
     */
    public function getUrlAttribute(): string
    {
        return route('posts.show', $this);
    }

    /**
     * Get the edit URL.
     */
    public function getEditUrlAttribute(): string
    {
        return route('posts.edit', $this);
    }

    /**
     * Get word count of content.
     */
    public function getWordCountAttribute(): int
    {
        return str_word_count(strip_tags($this->content));
    }

    /**
     * Get reading time estimate (assuming 200 words per minute).
     */
    public function getReadingTimeAttribute(): string
    {
        $minutes = max(1, round($this->word_count / 200));
        return $minutes . ' min read';
    }

    /**
     * Check if post is published and visible.
     */
    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published' && 
               $this->published_at && 
               $this->published_at <= now();
    }

    /**
     * Get a short excerpt for table display.
     */
    public function getShortExcerptAttribute(): string
    {
        return Str::limit($this->excerpt ?: strip_tags($this->content), 80);
    }

    /**
     * Scope to get only published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    /**
     * Scope to get draft posts.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope to get archived posts.
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Scope to search posts by title, content, or excerpt.
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('content', 'like', "%{$term}%")
              ->orWhere('excerpt', 'like', "%{$term}%");
        });
    }

    /**
     * Scope to get posts by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get recent posts.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get the route key for the model (use slug instead of ID).
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Resolve route binding using slug or ID.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('slug', $value)
                   ->orWhere('id', $value)
                   ->first() ?? abort(404);
    }

    /**
     * Get all available statuses for forms and filters.
     */
    public static function getStatuses(): array
    {
        return [
            'draft' => 'Draft',
            'published' => 'Published',
            'archived' => 'Archived',
        ];
    }

    /**
     * Get status options for select dropdowns.
     */
    public static function getStatusOptions(): array
    {
        return collect(self::getStatuses())
            ->map(fn($label, $value) => ['value' => $value, 'label' => $label])
            ->values()
            ->toArray();
    }

    /**
     * Convert to array for API responses.
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'status' => $this->status,
            'status_badge' => $this->status_badge,
            'featured_image' => $this->featured_image,
            'word_count' => $this->word_count,
            'reading_time' => $this->reading_time,
            'is_published' => $this->is_published,
            'url' => $this->url,
            'edit_url' => $this->edit_url,
            'published_at' => $this->published_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }

    /**
     * Get validation rules for forms.
     */
    public static function getValidationRules($id = null): array
    {
        $slugRule = $id ? "unique:posts,slug,{$id}" : 'unique:posts,slug';
        
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
            'status' => 'required|in:' . implode(',', array_keys(self::getStatuses())),
            'slug' => "nullable|string|max:255|{$slugRule}",
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|string|max:255',
            'published_at' => 'nullable|date',
        ];
    }

    /**
     * Get validation messages for forms.
     */
    public static function getValidationMessages(): array
    {
        return [
            'title.required' => 'The post title is required.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'content.required' => 'The post content is required.',
            'content.min' => 'The content must be at least 10 characters.',
            'status.required' => 'Please select a status for the post.',
            'status.in' => 'The selected status is invalid.',
            'slug.unique' => 'This slug is already taken.',
            'excerpt.max' => 'The excerpt may not be greater than 500 characters.',
            'published_at.date' => 'Please enter a valid publication date.',
        ];
    }
}