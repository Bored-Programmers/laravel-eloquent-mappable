<?php declare(strict_types=1);

namespace BoredProgrammers\LaravelEloquentMappable;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar;

class MappableGrammar
{

    public function __construct(
        private array $attributeToColumn,
        private Grammar $originalGrammar,
    )
    {
    }

    public function compileInsert(Builder $query, array $values): string
    {
        return $this->originalGrammar->compileInsert($query, $this->mappedValues($values));
    }

    public function compileUpdate(Builder $query, array $values): string
    {
        $newValues = $values;

        foreach ($values as $column => $columnValue) {
            if ($mappedColumn = $this->attributeToColumn[$column] ?? null) {
                $newValues = array_key_rename($newValues, $column, $mappedColumn);
            }
        }

        return $this->originalGrammar->compileUpdate($query, $newValues);
    }

    public function compileUpsert(Builder $query, array $values, array $uniqueBy, array $update): string
    {
        $newUniqueBy = $uniqueBy;

        foreach ($uniqueBy as $key => $column) {
            if (is_scalar($column) && $mappedColumn = $this->attributeToColumn[$column] ?? null) {
                $newUniqueBy[$key] = $mappedColumn;
            }
        }

        $newUpdate = $update;

        foreach ($update as $key => $value) {
            if (is_scalar($value) && $mappedColumn = $this->attributeToColumn[$value] ?? null) {
                $newUpdate[$key] = $mappedColumn;
            }

            if (!is_numeric($key) && $mappedColumn = $this->attributeToColumn[$key] ?? null) {
                $newUpdate = array_key_rename($newUpdate, $key, $mappedColumn);
            }
        }

        return $this->originalGrammar->compileUpsert($query, $this->mappedValues($values), $newUniqueBy, $newUpdate);
    }

    private function mappedValues(array $values): array
    {
        if (!is_array(reset($values))) {
            $values = [$values];
        }

        $newValues = $values;

        foreach ($values as $index => $value) {
            foreach ($value as $column => $columnValue) {
                if ($mappedColumn = $this->attributeToColumn[$column] ?? null) {
                    $newValues[$index] = array_key_rename($value, $column, $mappedColumn);
                }
            }
        }

        return $newValues;
    }

    public function __call($method, $parameters)
    {
        return $this->originalGrammar->$method(...$parameters);
    }

}
