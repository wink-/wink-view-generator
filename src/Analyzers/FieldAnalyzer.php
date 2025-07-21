<?php

namespace Wink\ViewGenerator\Analyzers;

class FieldAnalyzer
{
    protected array $columns;

    public function __construct(array $columns = [])
    {
        $this->columns = $columns;
    }

    /**
     * Analyze fields for form generation.
     */
    public function analyzeForForms(array $options = []): array
    {
        $fields = [];

        foreach ($this->columns as $column) {
            // Skip system columns
            if ($this->isSystemColumn($column['name'])) {
                continue;
            }

            $fields[] = [
                'name' => $column['name'],
                'label' => $this->generateLabel($column['name']),
                'input_type' => $this->determineInputType($column),
                'required' => $this->isRequired($column),
                'validation' => $this->generateValidation($column),
            ];
        }

        return $fields;
    }

    /**
     * Analyze fields for table display.
     */
    public function analyzeForTables(array $options = []): array
    {
        $fields = [];

        foreach ($this->columns as $column) {
            // Skip certain columns from table display
            if ($this->shouldHideFromTable($column['name'])) {
                continue;
            }

            $fields[] = [
                'name' => $column['name'],
                'label' => $this->generateLabel($column['name']),
                'display_type' => $this->determineDisplayType($column),
                'sortable' => $this->isSortable($column),
                'filterable' => $this->isFilterable($column),
            ];
        }

        return $fields;
    }

    /**
     * Check if column is a system column.
     */
    protected function isSystemColumn(string $columnName): bool
    {
        return in_array($columnName, [
            'id', 'created_at', 'updated_at', 'deleted_at', 'password', 'remember_token'
        ]);
    }

    /**
     * Check if column should be hidden from table.
     */
    protected function shouldHideFromTable(string $columnName): bool
    {
        return in_array($columnName, [
            'password', 'remember_token', 'email_verified_at'
        ]);
    }

    /**
     * Generate human-readable label from column name.
     */
    protected function generateLabel(string $columnName): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $columnName));
    }

    /**
     * Determine input type for forms.
     */
    protected function determineInputType(array $column): string
    {
        $name = $column['name'];
        $type = $column['type'] ?? 'string';

        // Check by column name first
        if (str_contains($name, 'email')) return 'email';
        if (str_contains($name, 'password')) return 'password';
        if (str_contains($name, 'phone')) return 'tel';
        if (str_contains($name, 'url') || str_contains($name, 'website')) return 'url';
        if (str_contains($name, 'description') || str_contains($name, 'content')) return 'textarea';
        if (str_contains($name, 'image') || str_contains($name, 'avatar') || str_contains($name, 'photo')) return 'file';

        // Check by column type
        switch ($type) {
            case 'boolean':
                return 'checkbox';
            case 'date':
                return 'date';
            case 'datetime':
            case 'timestamp':
                return 'datetime-local';
            case 'time':
                return 'time';
            case 'integer':
            case 'bigint':
            case 'smallint':
                return 'number';
            case 'decimal':
            case 'float':
            case 'double':
                return 'number';
            case 'text':
            case 'longtext':
                return 'textarea';
            default:
                return 'text';
        }
    }

    /**
     * Determine display type for tables.
     */
    protected function determineDisplayType(array $column): string
    {
        $name = $column['name'];
        $type = $column['type'] ?? 'string';

        if (str_contains($name, 'status')) return 'badge';
        if (str_contains($name, 'active') || str_contains($name, 'enabled')) return 'boolean';
        if (str_contains($name, 'image') || str_contains($name, 'avatar') || str_contains($name, 'photo')) return 'image';
        if (str_contains($name, 'email')) return 'email';
        if (str_contains($name, 'url') || str_contains($name, 'website')) return 'link';

        switch ($type) {
            case 'boolean':
                return 'boolean';
            case 'date':
            case 'datetime':
            case 'timestamp':
                return 'date';
            case 'decimal':
            case 'float':
            case 'double':
                return 'currency';
            default:
                return 'text';
        }
    }

    /**
     * Check if field is required.
     */
    protected function isRequired(array $column): bool
    {
        return !($column['nullable'] ?? true) && !isset($column['default']);
    }

    /**
     * Check if column is sortable.
     */
    protected function isSortable(array $column): bool
    {
        $type = $column['type'] ?? 'string';
        return !in_array($type, ['text', 'longtext', 'json', 'binary']);
    }

    /**
     * Check if column is filterable.
     */
    protected function isFilterable(array $column): bool
    {
        $name = $column['name'];
        $type = $column['type'] ?? 'string';

        // Status, boolean, and enum-like fields are good for filtering
        if (str_contains($name, 'status') || str_contains($name, 'type') || str_contains($name, 'category')) {
            return true;
        }

        return in_array($type, ['boolean', 'enum']);
    }

    /**
     * Generate validation rules for field.
     */
    protected function generateValidation(array $column): array
    {
        $rules = [];

        if ($this->isRequired($column)) {
            $rules[] = 'required';
        }

        $type = $column['type'] ?? 'string';
        switch ($type) {
            case 'string':
                $rules[] = 'string';
                $rules[] = 'max:255';
                break;
            case 'integer':
            case 'bigint':
            case 'smallint':
                $rules[] = 'integer';
                break;
            case 'decimal':
            case 'float':
            case 'double':
                $rules[] = 'numeric';
                break;
            case 'boolean':
                $rules[] = 'boolean';
                break;
            case 'date':
                $rules[] = 'date';
                break;
            case 'datetime':
            case 'timestamp':
                $rules[] = 'date';
                break;
        }

        // Add specific validation for common field names
        $name = $column['name'];
        if (str_contains($name, 'email')) {
            $rules[] = 'email';
        }
        if (str_contains($name, 'url') || str_contains($name, 'website')) {
            $rules[] = 'url';
        }

        return $rules;
    }
}