<?php

namespace Wink\ViewGenerator\Analyzers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ModelAnalyzer
{
    protected string $table;

    public function __construct(string $table = null)
    {
        $this->table = $table;
    }

    /**
     * Analyze the model and table structure.
     */
    public function analyze(): array
    {
        if (!$this->table) {
            throw new \InvalidArgumentException('Table name is required');
        }

        return [
            'table' => $this->table,
            'model_name' => $this->getModelName(),
            'columns' => $this->getColumns(),
            'relationships' => $this->getRelationships(),
            'indexes' => $this->getIndexes(),
            'primary_key' => $this->getPrimaryKey(),
            'timestamps' => $this->hasTimestamps(),
            'soft_deletes' => $this->hasSoftDeletes(),
        ];
    }

    /**
     * Get the model name from table name.
     */
    protected function getModelName(): string
    {
        return Str::studly(Str::singular($this->table));
    }

    /**
     * Get table columns with metadata.
     */
    protected function getColumns(): array
    {
        $columns = Schema::getColumnListing($this->table);
        $columnData = [];

        foreach ($columns as $column) {
            $columnData[$column] = [
                'name' => $column,
                'type' => Schema::getColumnType($this->table, $column),
                'nullable' => $this->isNullable($column),
                'default' => $this->getDefaultValue($column),
            ];
        }

        return $columnData;
    }

    /**
     * Detect relationships based on column naming conventions.
     */
    protected function getRelationships(): array
    {
        $relationships = [];
        $columns = Schema::getColumnListing($this->table);

        foreach ($columns as $column) {
            if (Str::endsWith($column, '_id')) {
                $relationName = Str::beforeLast($column, '_id');
                $relationships[] = [
                    'type' => 'belongsTo',
                    'name' => $relationName,
                    'foreign_key' => $column,
                    'related_table' => Str::plural($relationName),
                    'related_model' => Str::studly($relationName),
                ];
            }
        }

        return $relationships;
    }

    /**
     * Get table indexes.
     */
    protected function getIndexes(): array
    {
        // This would need to be implemented based on the database driver
        // For now, return empty array
        return [];
    }

    /**
     * Get the primary key column.
     */
    protected function getPrimaryKey(): string
    {
        $columns = Schema::getColumnListing($this->table);
        
        // Check for common primary key names
        if (in_array('id', $columns)) {
            return 'id';
        }

        // Return first column as fallback
        return $columns[0] ?? 'id';
    }

    /**
     * Check if table has timestamp columns.
     */
    protected function hasTimestamps(): bool
    {
        $columns = Schema::getColumnListing($this->table);
        return in_array('created_at', $columns) && in_array('updated_at', $columns);
    }

    /**
     * Check if table has soft delete column.
     */
    protected function hasSoftDeletes(): bool
    {
        $columns = Schema::getColumnListing($this->table);
        return in_array('deleted_at', $columns);
    }

    /**
     * Check if column is nullable.
     */
    protected function isNullable(string $column): bool
    {
        // This would need database-specific implementation
        // For now, assume non-nullable
        return false;
    }

    /**
     * Get column default value.
     */
    protected function getDefaultValue(string $column): mixed
    {
        // This would need database-specific implementation
        // For now, return null
        return null;
    }
}