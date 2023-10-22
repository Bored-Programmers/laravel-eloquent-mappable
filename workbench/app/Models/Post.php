<?php declare(strict_types=1);

namespace Workbench\App\Models;

use BoredProgrammers\LaravelEloquentMappable\Models\Traits\HasMappableColumns;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{

    use HasMappableColumns;

    protected $fillable = [
        'mapped_title',
        'content',
    ];

    public $mapAttributeToColumn = [
        'mapped_title' => 'title'
    ];
}
