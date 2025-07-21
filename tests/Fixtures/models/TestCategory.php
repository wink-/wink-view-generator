<?php

namespace Wink\ViewGenerator\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TestCategory extends Model
{
    use HasFactory;

    protected $table = 'test_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'is_active',
        'sort_order',
        'parent_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the parent category.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Get the posts for the category.
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(TestPost::class, 'test_post_category', 'category_id', 'post_id');
    }

    /**
     * Get the validation rules for this model.
     */
    public static function getValidationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:test_categories',
            'description' => 'nullable|string|max:1000',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'parent_id' => 'nullable|exists:test_categories,id',
        ];
    }

    /**
     * Get the field types for form generation.
     */
    public static function getFieldTypes(): array
    {
        return [
            'name' => 'text',
            'slug' => 'text',
            'description' => 'textarea',
            'color' => 'color',
            'icon' => 'text',
            'is_active' => 'checkbox',
            'sort_order' => 'number',
            'parent_id' => 'select',
        ];
    }

    /**
     * Get select options for enum fields.
     */
    public static function getSelectOptions(): array
    {
        return [];
    }
}