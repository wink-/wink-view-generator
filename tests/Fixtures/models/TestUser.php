<?php

namespace Wink\ViewGenerator\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestUser extends Model
{
    use HasFactory;

    protected $table = 'test_users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'birth_date',
        'status',
        'bio',
        'is_admin',
        'salary',
        'preferences',
        'avatar',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_admin' => 'boolean',
        'salary' => 'decimal:2',
        'preferences' => 'array',
        'email_verified_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the posts for the user.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(TestPost::class, 'user_id');
    }

    /**
     * Get the validation rules for this model.
     */
    public static function getValidationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:test_users',
            'password' => 'required|string|min:8|confirmed',
            'birth_date' => 'nullable|date|before:today',
            'status' => 'required|in:active,inactive,suspended',
            'bio' => 'nullable|string|max:1000',
            'is_admin' => 'boolean',
            'salary' => 'nullable|numeric|min:0|max:999999.99',
            'preferences' => 'nullable|array',
            'avatar' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get the field types for form generation.
     */
    public static function getFieldTypes(): array
    {
        return [
            'name' => 'text',
            'email' => 'email',
            'password' => 'password',
            'birth_date' => 'date',
            'status' => 'select',
            'bio' => 'textarea',
            'is_admin' => 'checkbox',
            'salary' => 'number',
            'preferences' => 'textarea',
            'avatar' => 'file',
        ];
    }

    /**
     * Get select options for enum fields.
     */
    public static function getSelectOptions(): array
    {
        return [
            'status' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
                'suspended' => 'Suspended',
            ],
        ];
    }
}