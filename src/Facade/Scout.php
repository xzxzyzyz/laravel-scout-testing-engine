<?php

namespace Xzxzyzyz\Laravel\ScoutTestingEngine\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Support\Collection|array|null getData($index = null, $key = null)
 * @method static array getDispatched()
 * @method static void update($models)
 * @method static void delete($models)
 * @method static string getIndexFromCollection($models)
 * @method static bool isDispatched($index, $key)
 * @method static bool isNotDispatched($index, $key)
 * @method static bool exists($index, $key)
 * @method static bool notExists($index, $key)
 * @method static void assertDispatched($index, $key, $times = 1)
 * @method static void assertNotDispatched($index, $key)
 * @method static void assertExists($index, $key)
 * @method static void assertNotExists($index, $key)
 *
 * @see \Ocapeer\LaravelScoutTestingEngine\Engine
 */
class Scout extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel.scout.testing.engine';
    }
}
