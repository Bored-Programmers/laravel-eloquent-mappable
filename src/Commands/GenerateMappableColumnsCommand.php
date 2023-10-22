<?php declare(strict_types=1);

namespace BoredProgrammers\LaravelEloquentMappable\Commands;

use Barryvdh\Reflection\DocBlock;
use Barryvdh\Reflection\DocBlock\Tag;
use Barryvdh\Reflection\DocBlock\Tag\MethodTag;
use Barryvdh\Reflection\DocBlock\Tag\PropertyTag;
use Composer\ClassMapGenerator\ClassMapGenerator;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use ReflectionClass;

class GenerateMappableColumnsCommand extends Command
{

    protected $signature = 'mappable-columns:generate';

    protected $description = 'Generate php doc for mappable columns';

    public function handle(): void
    {
        $classMap = ClassMapGenerator::createMap(app_path('Models'));

        foreach ($classMap as $model => $path) {
            $reflection = new ReflectionClass($model);

            if (!$reflection->isSubclassOf(Model::class)) {
                continue;
            }

            if (!$reflection->isInstantiable()) {
                continue;
            }

            if (!$reflection->hasProperty('columns')) {
                continue;
            }

            echo 'Generating for ' . $model . "\n";

            $mappedColumns = $reflection->newInstance()->columns;
            $docBlock = new DocBlock($reflection);

            $existingProperties = [];
            $existingMethods = [];

            foreach ($docBlock->getTags() as $tag) {
                if ($tag instanceof PropertyTag) {
                    $existingProperties[] = $tag->getVariableName();
                } elseif ($tag instanceof MethodTag) {
                    $existingMethods[] = $tag->getMethodName();
                }
            }

            foreach ($mappedColumns as $mappedColumn => $dbColumn) {
                if (in_array('$' . $mappedColumn, $existingProperties)) {
                    continue;
                }

                $methodName = 'where' . Str::studly($mappedColumn);

                if (in_array($methodName, $existingMethods)) {
                    continue;
                }

                $tagLine = "@property mixed \$$mappedColumn mapped for db column '$dbColumn'";
                $docBlock->appendTag(Tag::createInstance($tagLine, $docBlock));

                $tagLine = "@method static \\Illuminate\\Database\\Eloquent\\Builder $methodName(\$value)";
                $docBlock->appendTag(Tag::createInstance($tagLine, $docBlock));
            }

            $doc = (new DocBlock\Serializer())->getDocComment($docBlock);
            $contents = file_get_contents($path);

            if ($originalDoc = $reflection->getDocComment()) {
                $contents = str_replace($originalDoc, $doc, $contents);
            } else {
                $classname = $reflection->getShortName();
                $pos = strpos($contents, "final class {$classname}") ?: strpos($contents, "class {$classname}");
                if ($pos !== false) {
                    $contents = substr_replace($contents, $doc . "\n", $pos, 0);
                }
            }

            file_put_contents($path, $contents);
        }
    }

}
