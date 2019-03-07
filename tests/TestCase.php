<?php

namespace Tests\Xzxzyzyz\Laravel\ScoutTestingEngine;

use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function getScoutConfig()
    {
        return [
            'testing' => ['storage' => dirname(__DIR__).'/tests/.temp/file.json']
        ];
    }
}
