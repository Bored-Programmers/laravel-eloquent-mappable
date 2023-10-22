<?php declare(strict_types=1);

namespace BoredProgrammers\LaravelEloquentMappable\Tests\Unit;

use BoredProgrammers\LaravelEloquentMappable\Tests\TestCase;
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

}
