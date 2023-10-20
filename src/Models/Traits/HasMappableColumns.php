<?php declare(strict_types=1);

namespace BoredProgrammers\LaravelEloquentMappable\Models\Traits;

use BoredProgrammers\LaravelEloquentMappable\Models\Scopes\MappedColumnsScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;

/**
 * @property array $columns
 */
trait HasMappableColumns
{

    use Macroable {
        __call as macroCall;
    }

    protected static function bootHasMappableColumns(): void
    {
        static::addGlobalScope(new MappedColumnsScope());

        foreach ((new static)->columns ?? [] as $mappedCol => $dbCol) {
            static::macro('get' . Str::studly($mappedCol) . 'Attribute', function () use ($dbCol) {
                return $this->$dbCol;
            });

            static::macro('set' . Str::studly($mappedCol) . 'Attribute', function ($value) use ($dbCol) {
                $this->$dbCol = $value;
            });
        }
    }

    public function newInstance($attributes = [], $exists = false): Model
    {
        /** @var Model $instance */
        $instance = parent::newInstance($attributes, $exists);

        foreach ($instance->columns ?? [] as $mappedCol => $dbCol) {
            $instance->hidden[] = $dbCol;
            $instance->appends[] = $mappedCol;

            if (in_array($mappedCol, $instance->guarded)) {
                $instance->guarded[] = $dbCol;
            }

            if (in_array($mappedCol, $instance->fillable)) {
                $instance->fillable[] = $dbCol;
            }
        }

        return $instance;
    }

    public function hasGetMutator($key): bool
    {
        return parent::hasGetMutator($key) || static::hasMacro('get' . Str::studly($key) . 'Attribute');
    }

    public function hasSetMutator($key): bool
    {
        return parent::hasSetMutator($key) || static::hasMacro('set' . Str::studly($key) . 'Attribute');
    }

    public function __call($method, $parameters)
    {
        if (Str::startsWith($method, ['set', 'get']) && self::hasMacro($method)) {
            return self::macroCall($method, $parameters);
        }

        return parent::__call($method, $parameters);
    }

    public static function __callStatic($method, $parameters)
    {
        if (Str::startsWith($method, ['set', 'get']) && self::hasMacro($method)) {
            return self::macroCall($method, $parameters);
        }

        return parent::__callStatic($method, $parameters);
    }

}
