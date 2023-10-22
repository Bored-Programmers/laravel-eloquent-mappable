<?php declare(strict_types=1);

namespace BoredProgrammers\LaravelEloquentMappable\Tests\Unit;

use BoredProgrammers\LaravelEloquentMappable\Tests\TestCase;
use Illuminate\Database\Query\JoinClause;
use Workbench\App\Models\Post;

class QueryBuilderTest extends TestCase
{

    public function test_where()
    {
        $this->assertEquals(
            "select * from \"posts\" where \"title\" = 'asd'",
            Post::whereMappedTitle('asd')->toRawSql()
        );

        $this->assertEquals(
            "select * from \"posts\" where \"title\" = 'asd'",
            Post::where('mapped_title', 'asd')
                ->toRawSql()
        );

        $this->assertEquals(
            "select * from \"posts\" where \"title\" != 'asd'",
            Post::where('mapped_title', '!=', 'asd')
                ->toRawSql()
        );
    }

    public function test_or_where()
    {
        $this->assertEquals(
            "select * from \"posts\" where \"title\" = 'asd' or \"title\" like '%testing%'",
            Post::where('mapped_title', 'asd')
                ->orWhere('mapped_title', 'like', '%testing%')
                ->toRawSql()
        );
    }

    public function test_where_not_null()
    {
        $this->assertEquals(
            "select * from \"posts\" where \"title\" is not null",
            Post::whereNotNull('mapped_title')
                ->toRawSql()
        );
    }

    public function test_join()
    {
        $this->assertEquals(
            "select * from \"posts\""
            . " inner join \"user_accounts\" on \"user_accounts\".\"id\" = \"posts\".\"title\""
            . " where \"title\" is not null",
            Post::whereNotNull('mapped_title')
                ->join('user_accounts', 'user_accounts.id', '=', 'posts.mapped_title')
                ->toRawSql()
        );

        $this->assertEquals(
            "select * from \"posts\" inner join (\"user_accounts\" as \"ua1\""
            . " inner join \"user_accounts\" as \"ua2\" on \"ua2\".\"id\" = \"posts\".\"title\")"
            . " where \"title\" is not null",
            Post::whereNotNull('mapped_title')
                ->join('user_accounts as ua1', function (JoinClause $join) {
                    $join->join('user_accounts as ua2', 'ua2.id', '=', 'posts.mapped_title');
                })
                ->toRawSql()
        );
    }

}
