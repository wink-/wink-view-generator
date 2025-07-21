<?php

namespace Wink\ViewGenerator\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TestPost extends Model
{
    use HasFactory;

    protected $table = 'test_posts';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'status',
        'featured',
        'views_count',
        'published_at',
        'meta_title',
        'meta_description',
        'tags',
        'featured_image',
        'user_id',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'views_count' => 'integer',
        'published_at' => 'datetime',
        'tags' => 'array',
    ];

    /**
     * Get the user that owns the post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(TestUser::class, 'user_id');
    }

    /**
     * Get the categories for the post.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(TestCategory::class, 'test_post_category', 'post_id', 'category_id');
    }

    /**
     * Get the validation rules for this model.
     */
    public static function getValidationRules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:test_posts',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published,archived',
            'featured' => 'boolean',
            'views_count' => 'integer|min:0',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'tags' => 'nullable|array',
            'featured_image' => 'nullable|string|max:255',
            'user_id' => 'required|exists:test_users,id',
        ];
    }

    /**
     * Get the field types for form generation.
     */
    public static function getFieldTypes(): array
    {
        return [
            'title' => 'text',
            'slug' => 'text',
            'content' => 'textarea',
            'excerpt' => 'textarea',
            'status' => 'select',
            'featured' => 'checkbox',
            'views_count' => 'number',
            'published_at' => 'datetime',
            'meta_title' => 'text',
            'meta_description' => 'textarea',
            'tags' => 'textarea',
            'featured_image' => 'file',
            'user_id' => 'select',
        ];
    }

    /**
     * Get select options for enum fields.
     */
    public static function getSelectOptions(): array
    {
        return [
            'status' => [
                'draft' => 'Draft',
                'published' => 'Published',
                'archived' => 'Archived',
            ],
        ];
    }
}