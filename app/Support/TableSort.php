<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

class TableSort
{
    public static function apply(Builder $query, array $allowed, string $default = 'created_at', string $defaultDir = 'desc'): Builder
    {
        $sort = request('sort', $default);
        $dir = request('dir', $defaultDir) === 'asc' ? 'asc' : 'desc';

        if (in_array($sort, $allowed, true)) {
            $query->orderBy($sort, $dir);
        } else {
            $query->orderBy($default, $defaultDir);
        }

        return $query;
    }
}
