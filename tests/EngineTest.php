<?php

namespace Tests\Xzxzyzyz\Laravel\ScoutTestingEngine;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Xzxzyzyz\Laravel\ScoutTestingEngine\Engine;
use Tests\Xzxzyzyz\Laravel\ScoutTestingEngine\Fixtures\CatModel;
use Tests\Xzxzyzyz\Laravel\ScoutTestingEngine\Fixtures\DogModel;

class EngineTest extends TestCase
{
    public function test_should_always_clean_temporary_directory()
    {
        $filesystem = new Filesystem;
        $temporaryFilePath = $this->getScoutConfig()['testing']['storage'];

        $filesystem->put($temporaryFilePath, 'content-body');

        // Exists temporary file
        $this->assertTrue($filesystem->exists($temporaryFilePath));

        $engine = new Engine(new Filesystem, $this->getScoutConfig());

        // Not exists when fired engine class construct
        $this->assertFalse($filesystem->exists($temporaryFilePath));
    }

    public function test_should_have_auto_attache_temp_file_when_config_not_setting()
    {
        $engine = new Engine(new Filesystem, []);

        $this->assertContains('app/laravel-scout-testing-engine/laravel-scout-testing-engine.json', $engine->tempFile);
    }

    public function test_should_write_searchable_models()
    {
        $engine = new Engine(new Filesystem, $this->getScoutConfig());

        $emptyData = $engine->getData();
        $this->assertCount(0, $emptyData);

        $dogModels = new EloquentCollection([
            $dog1 = new DogModel(['id' => 1]),
            $dog2 = new DogModel(['id' => 2]),
            $dog3 = new DogModel(['id' => 3]),
        ]);

        $engine->update($dogModels);

        // getting index with records
        $allData1 = $engine->getData();
        $this->assertCount(1, $allData1);
        $this->assertArrayHasKey('dogs', $allData1);

        // getting current index records
        $dogData = $engine->getData('dogs');
        $this->assertCount(3, $dogData);

        $catModels = new EloquentCollection([
            $cat1 = new CatModel(['id' => 1]),
            $cat2 = new CatModel(['id' => 2]),
        ]);

        $engine->update($catModels);

        // getting index with records
        $allData2 = $engine->getData();
        $this->assertCount(2, $allData2);
        $this->assertArrayHasKey('dogs', $allData2);
        $this->assertArrayHasKey('cats', $allData2);

        // getting current index records
        $catData = $engine->getData('cats');
        $this->assertCount(2, $catData);

        // getting record at primary key parameter
        $recordData = $engine->getData('dogs', 1);
        $this->assertArrayHasKey('id', $recordData);

        // not searchable index
        $pigData = $engine->getData('pigs');
        $this->assertCount(0, $pigData);
    }

    public function test_should_delete_searchable_model()
    {
        $engine = new Engine(new Filesystem, $this->getScoutConfig());

        $dogModels = new EloquentCollection([
            $dog1 = new DogModel(['id' => 1]),
            $dog2 = new DogModel(['id' => 2]),
            $dog3 = new DogModel(['id' => 3]),
        ]);
        $engine->update($dogModels);

        $catModels = new EloquentCollection([
            $cat1 = new CatModel(['id' => 1]),
            $cat2 = new CatModel(['id' => 2]),
        ]);
        $engine->update($catModels);

        $engine->delete($dogModels->where('id', 1));

        // getting index with records
        $allData = $engine->getData();
        $this->assertCount(2, $allData);
        $this->assertArrayHasKey('dogs', $allData);
        $this->assertArrayHasKey('cats', $allData);

        // getting current index records
        $dogData = $engine->getData('dogs');
        $this->assertCount(2, $dogData);

        // getting record at primary key parameter
        $emptyData = $engine->getData('dogs', 1);
        $this->assertNull($emptyData);
    }

    public function test_should_dispathed_model()
    {
        $engine = new Engine(new Filesystem, $this->getScoutConfig());

        $dogModels = new EloquentCollection([
            $dog1 = new DogModel(['id' => 1]),
        ]);

        $engine->update($dogModels);

        $this->assertTrue($engine->isDispatched('dogs', 1));
        $this->assertFalse($engine->isDispatched('dogs', 2));
    }

    public function test_should_dispathed_count_model()
    {
        $engine = new Engine(new Filesystem, $this->getScoutConfig());

        $dogModels = new EloquentCollection([
            $dog1 = new DogModel(['id' => 1]),
        ]);

        $engine->update($dogModels);

        $this->assertSame(1, $engine->countDispatched('dogs', 1));
        $this->assertSame(0, $engine->countDispatched('dogs', 2));
    }

    public function test_should_not_dispathed_model()
    {
        $engine = new Engine(new Filesystem, $this->getScoutConfig());

        $dogModels = new EloquentCollection([
            $dog1 = new DogModel(['id' => 1]),
        ]);

        $engine->update($dogModels);

        $this->assertFalse($engine->isNotDispatched('dogs', 1));
        $this->assertTrue($engine->isNotDispatched('dogs', 2));
    }

    public function test_should_exists_searchable_model()
    {
        $engine = new Engine(new Filesystem, $this->getScoutConfig());

        $dogModels = new EloquentCollection([
            $dog1 = new DogModel(['id' => 1]),
        ]);

        $engine->update($dogModels);

        $this->assertTrue($engine->exists('dogs', 1));
        $this->assertFalse($engine->exists('dogs', 2));
    }

    public function test_should_not_exists_searchable_model()
    {
        $engine = new Engine(new Filesystem, $this->getScoutConfig());

        $dogModels = new EloquentCollection([
            $dog1 = new DogModel(['id' => 1]),
        ]);

        $engine->update($dogModels);

        $this->assertFalse($engine->notExists('dogs', 1));
        $this->assertTrue($engine->notExists('dogs', 2));
    }

    public function test_should_get_dispatched_value()
    {
        $engine = new Engine(new Filesystem, $this->getScoutConfig());

        $dogModels = new EloquentCollection([
            $dog1 = new DogModel(['id' => 1]),
            $dog2 = new DogModel(['id' => 2]),
        ]);

        $engine->update($dogModels);
        $engine->update($dogModels->where('id', 2));

        $this->assertCount(1, $engine->getDispatched()['dogs'][1]);
        $this->assertCount(2, $engine->getDispatched()['dogs'][2]);
    }
}
