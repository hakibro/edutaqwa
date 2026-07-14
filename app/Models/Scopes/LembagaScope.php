<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Schema;

class LembagaScope implements Scope
{
    private static array $tableColumns = [];

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (!auth()->check()) {
            return;
        }

        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return;
        }

        $table = $model->getTable();

        if (!isset(self::$tableColumns[$table])) {
            self::$tableColumns[$table] = Schema::getColumnListing($table);
        }

        $columns = self::$tableColumns[$table];

        if ($user->isAdminYayasan() && in_array('yayasan_id', $columns)) {
            $builder->where($table . '.yayasan_id', $user->yayasan_id);
            return;
        }

        if ($user->lembaga_id && in_array('lembaga_id', $columns)) {
            $builder->where($table . '.lembaga_id', $user->lembaga_id);
        }
    }
}
