<?php declare(strict_types=1);

namespace BoredProgrammers\LaravelEloquentMappable\Tests\Unit;

use BoredProgrammers\LaravelEloquentMappable\Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Workbench\App\Models\Post;

class EloquentTest extends TestCase
{

    public function test_create()
    {
        $post = Post::create([
            'mapped_title' => 'testing title',
            'content' => 'testing content',
        ]);

        $this->assertEquals('testing title', $post->mapped_title);
        $this->assertEquals('testing title', $post->title);
    }

    public function test_delete()
    {
        Post::create([
            'mapped_title' => 'testing title2',
            'content' => 'testing content2',
        ]);
        Post::create([
            'mapped_title' => 'testing title3',
            'content' => 'testing content3',
        ]);

        $this->assertEquals(2, Post::count());

        Post::whereMappedTitle('testing title2')->delete();

        $this->assertEquals(1, Post::count());
    }

    public function test_insert()
    {
        Post::query()->insert([
            'mapped_title' => 'testing title2',
            'content' => 'testing content2',
        ]);

        $firstPost = Post::first();

        $this->assertEquals('testing title2', $firstPost->mapped_title);
        $this->assertEquals('testing title2', $firstPost->title);
    }

    public function test_upsert()
    {
        Post::create([
            'mapped_title' => 'testing title2',
            'content' => 'testing content2',
        ]);

        $this->assertEquals('testing title2', Post::first()->mapped_title);

        Post::upsert([
            'mapped_title' => 'testing title2',
            'content' => 'testing content3',
        ], ['mapped_title'], ['mapped_title' => DB::raw('content')]);

        $post = Post::first();

        $this->assertEquals('testing content2', $post->mapped_title);
        $this->assertEquals('testing content2', $post->title);

        $this->assertEquals(1, Post::count());
    }

}
