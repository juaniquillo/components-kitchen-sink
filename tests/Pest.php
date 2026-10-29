<?php

declare(strict_types=1);

use App\Builder\Contracts\BuilderNode;
use App\Builder\Support\TreeMutator;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Builder Files
|--------------------------------------------------------------------------
|
| The builder writes a workspace file for every component a test opens, and
| the autosave writes it again on every change. The directory of the worker
| is emptied around every builder test, so a file left behind by one test is
| not work another one finds.
|
*/

uses()
    ->in('Feature/Builder')
    ->beforeEach(function () {
        Storage::disk('local')->deleteDirectory(builderTestDirectory());
    })
    ->afterEach(function () {
        Storage::disk('local')->deleteDirectory(builderTestDirectory());
    });

/*
|--------------------------------------------------------------------------
| Composition Files
|--------------------------------------------------------------------------
|
| The saved compositions are plain files under a configured directory of the
| "local" disk. Every test process gets a directory of its own, so parallel
| workers never see the files another one is juggling. Para-test hands every
| worker a TEST_TOKEN; on its own the worker identifies itself by its id.
*/

function builderTestDirectory(): string
{
    return 'builder-tests-'.(getenv('TEST_TOKEN') ?: getmypid());
}

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * A tree with a text segment in it, the way the builder stores text.
 *
 * @return array<string, mixed>
 */
function treeWithText(string $id, string $type, string $text): array
{
    $tree = BuilderNode::tree($id, $type);
    $tree['nodes'][$id]['children'] = ['t1'];
    $tree['nodes']['t1'] = BuilderNode::make('t1', BuilderNode::TYPE_TEXT, $text);

    return $tree;
}

/**
 * A tree with a scaffolded table under its root, 3 columns and 2 body rows.
 *
 * The ids the scaffold hands out are fixed, so tests can point at parts:
 * the table t1, the head t2, the head row t3, the body t4; the header cells
 * t5, t7 and t9 with their segments t6, t8 and t10; the body rows t11 and
 * t18 with the cells t12, t14, t16 and t19, t21, t23.
 *
 * @return array<string, mixed>
 */
function tableTree(): array
{
    $counter = 0;

    return TreeMutator::scaffoldTable(
        BuilderNode::tree('n1', 'div'),
        'n1',
        3,
        2,
        function (string $id) use (&$counter): string {
            $counter++;

            return 't'.$counter;
        },
    );
}

/**
 * A factory for the ids of a table operation, counting on from the ones the
 * scaffold used.
 *
 * @return callable(string): string
 */
function tableIdFactory(): callable
{
    $counter = 24;

    return function (string $id) use (&$counter): string {
        $counter++;

        return 't'.$counter;
    };
}
