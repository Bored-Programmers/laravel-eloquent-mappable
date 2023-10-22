<?php declare(strict_types=1);

namespace BoredProgrammers\LaravelEloquentMappable\Models\Traits;

use BoredProgrammers\LaravelEloquentMappable\Models\Scopes\MappedColumnsScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;

/**
 * @property array $mapAttributeToColumn
 */
trait HasMappableColumns
{

    use Macroable {
        __call as macroCall;
    }

    protected static function bootHasMappableColumns(): void
    {
        static::addGlobalScope(new MappedColumnsScope());

        foreach ((new static)->mapAttributeToColumn ?? [] as $attribute => $column) {
            static::macro('get' . Str::studly($attribute) . 'Attribute', function () use ($column) {
                return $this->$column;
            });

            static::macro('set' . Str::studly($attribute) . 'Attribute', function ($value) use ($column) {
                $this->$column = $value;
            });
        }
    }

    public function newInstance($attributes = [], $exists = false): Model
    {
        /** @var Model $instance */
        $instance = parent::newInstance(exists: $exists);

        foreach ($instance->mapAttributeToColumn ?? [] as $attribute => $column) {
            $instance->hidden[] = $column;
            $instance->appends[] = $attribute;

            if (in_array($attribute, $instance->guarded)) {
                $instance->guarded[] = $column;
            }

            if (in_array($attribute, $instance->fillable)) {
                $instance->fillable[] = $column;
            }
        }

        $instance->fill((array)$attributes);

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
