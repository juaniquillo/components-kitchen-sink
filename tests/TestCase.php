<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * The saved compositions are plain files under a configured directory
     * of the "local" disk. Every test process gets a directory of its own,
     * so parallel workers never see the files another one is juggling.
     * Para-test hands every worker a TEST_TOKEN; on its own the worker
     * identifies itself by its id.
     */
    protected function setUp(): void
    {
        parent::setUp();

        config()->set(
            'builder.directory',
            'builder-tests-'.(getenv('TEST_TOKEN') ?: getmypid()),
        );
    }
}
