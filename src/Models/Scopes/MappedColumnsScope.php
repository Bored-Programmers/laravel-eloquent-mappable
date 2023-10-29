<?php declare(strict_types=1);

namespace BoredProgrammers\LaravelEloquentMappable\Models\Scopes;

use BoredProgrammers\LaravelEloquentMappable\MappableGrammar;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;

class MappedColumnsScope implements Scope
{

    public function apply(EloquentBuilder $builder, Model $model): void
    {
        if (!$attributeToColumn = $model->mapAttributeToColumn ?? null) {
            return;
        }

        $modelTable = $model->getTable();
        $attributeToColumnWithTable = collect($attributeToColumn)
            ->mapWithKeys(fn($value, $key) => [$modelTable . '.' . $key => $modelTable . '.' . $value])
            ->merge($attributeToColumn)
            ->toArray();

        $builder->beforeQuery(function (QueryBuilder $builder) use ($attributeToColumnWithTable) {
            $builder->grammar = new MappableGrammar($attributeToColumnWithTable, $builder->grammar);

            $builder->columns = $this->mapColumns($builder->columns, $attributeToColumnWithTable);
            $builder->wheres = $this->mapColumns($builder->wheres, $attributeToColumnWithTable);
            $builder->joins = $this->mapJoins($builder->joins, $attributeToColumnWithTable);
        });
    }

    private function mapColumns($array, $mappedColToDbColWithTable)
    {
        if (!$array) {
            return $array;
        }

        foreach ($array as $key => $column) {
            if (is_array($column)) {
                if ($column['type'] === 'Exists') {
                    $column['query']->applyBeforeQueryCallbacks();

                } elseif ($column['type'] === 'Column') {
                    if ($newColumnName = $mappedColToDbColWithTable[$column['first']] ?? null) {
                        $array[$key]['first'] = $newColumnName;
                    }

                    if ($newColumnName = $mappedColToDbColWithTable[$column['second']] ?? null) {
                        $array[$key]['second'] = $newColumnName;
                    }

                } elseif (isset($column['column'])) {
                    if ($newColumnName = $mappedColToDbColWithTable[$column['column']] ?? null) {
                        $array[$key]['column'] = $newColumnName;
                    }
                }

                // fixme: some unhandled cases?

                continue;
            }

            if ($column === '*' || str_contains($column, ' as ')) { // todo: add support for column aliases
                continue;
            }

            if ($newColumnName = $mappedColToDbColWithTable[$column] ?? null) {
                $array[$key] = $newColumnName;
            }
        }

        return $array;
    }

    private function mapJoins($joins, $mappedColToDbColWithTable)
    {
        if (!$joins) {
            return $joins;
        }

        /** @var JoinClause $joinClause */
        foreach ($joins as $joinClause) {
            foreach ($joinClause->wheres as $whereKey => $whereData) {
                if ($whereData['type'] === 'Column') {
                    if ($dbCol = $mappedColToDbColWithTable[$whereData['first']] ?? null) {
                        $joinClause->wheres[$whereKey]['first'] = $dbCol;
                    }

                    if ($dbCol = $mappedColToDbColWithTable[$whereData['second']] ?? null) {
                        $joinClause->wheres[$whereKey]['second'] = $dbCol;
                    }
                }
            }

            if ($joinClause->joins) {
                $joinClause->joins = $this->mapJoins($joinClause->joins, $mappedColToDbColWithTable);
            }
        }

        return $joins;
    }

}
