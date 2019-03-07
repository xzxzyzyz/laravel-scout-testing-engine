<?php

namespace Tests\Xzxzyzyz\Laravel\ScoutTestingEngine\Facade;

use Tests\Xzxzyzyz\Laravel\ScoutTestingEngine\TestCase;
use Xzxzyzyz\Laravel\ScoutTestingEngine\Facade\Scout;

class ScoutTest extends TestCase
{
    public function test_should_return_testing_engine_alias()
    {
        $scout = new Scout;
        $reflection = new \ReflectionClass($scout);
        $method = $reflection->getMethod('getFacadeAccessor');
        $method->setAccessible(true);

        $this->assertEquals('laravel.scout.testing.engine', $method->invoke($scout));
    }
}
