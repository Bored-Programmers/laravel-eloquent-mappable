<?php declare(strict_types=1);

namespace BoredProgrammers\LaravelEloquentMappable\Models\Scopes;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;

class MappedColumnsScope implements Scope
{

    public function apply(EloquentBuilder $builder, Model $model): void
    {
        $builder->beforeQuery(function (QueryBuilder $builder) use ($model) {
            if (!$attributeToColumn = $model->mapAttributeToColumn ?? null) {
                return;
            }

            $modelTable = $model->getTable();
            $attributeToColumnWithTable = collect($attributeToColumn)
                ->mapWithKeys(fn($value, $key) => [$modelTable . '.' . $key => $modelTable . '.' . $value])
                ->merge($attributeToColumn);

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
            $columnName = is_array($column) ? $column['column'] : $column;

            if ($columnName === '*' || str_contains($columnName, ' as ')) { // todo: add support for column aliases
                continue;
            }

            if ($newColumnName = $mappedColToDbColWithTable[$columnName] ?? null) {
                if (is_array($column)) {
                    $array[$key]['column'] = $newColumnName;
                } else {
                    $array[$key] = $newColumnName;
                }
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
