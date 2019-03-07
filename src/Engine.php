<?php

namespace Xzxzyzyz\Laravel\ScoutTestingEngine;

use Illuminate\Filesystem\Filesystem;
use Laravel\Scout\Engines\NullEngine;
use PHPUnit\Framework\Assert as PHPUnit;

class Engine extends NullEngine
{
    /** @var \Illuminate\Filesystem\Filesystem */
    public $filesystem;

    /** @var array */
    public $config;

    /** @var string */
    public $tempFile;

    /** @var array */
    public $dispatched = [];

    /**
     * Engine constructor.
     *
     * @param \Illuminate\Filesystem\Filesystem $filesystem
     * @param array $config
     */
    public function __construct(Filesystem $filesystem, array $config)
    {
        $this->filesystem = $filesystem;
        $this->config = $config;

        if (isset($config['testing']['storage'])) {
            $this->tempFile = $config['testing']['storage'];
        }
        else {
            $this->tempFile = storage_path('app/laravel-scout-testing-engine/laravel-scout-testing-engine.json');
        }

        $this->filesystem->delete($this->tempFile);

        $directory = $this->filesystem->dirname($this->tempFile);

        if (! $this->filesystem->isDirectory($directory)) {
            $this->filesystem->makeDirectory($directory);
        } else {
            $this->filesystem->cleanDirectory($directory);
        }
    }

    /**
     * @return array
     */
    public function getDispatched()
    {
        return $this->dispatched;
    }

    /**
     * Get file contents
     *
     * @return \Illuminate\Support\Collection|array|null
     */
    public function getData($index = null, $key = null)
    {
        $tempData = null;
        if ($this->filesystem->exists($this->tempFile)) {
            $tempData = $this->filesystem->get($this->tempFile);
        }

        $data = collect(json_decode($tempData, true));

        if (is_null($index)) {
            return $data;
        }

        $dataOnlyIndex = $data->get($index, []);

        if (is_null($key)) {
            return $dataOnlyIndex;
        }

        return $dataOnlyIndex[$key] ?? null;
    }

    /**
     * Update the given model in the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection $models
     * @return void
     */
    public function update($models)
    {
        $key = $this->getKeyNameFromCollection($models);

        $models->each(function($model) use ($key) {
            /** @var \Illuminate\Database\Eloquent\Model|\Laravel\Scout\Searchable $model */
            $this->dispatched[$model->searchableAs()][$model->{$key}][] = $model->toSearchableArray();
        });

        $this->updateFile($models->keyBy($key));
    }

    /**
     * Remove the given model from the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection $models
     * @return void
     */
    public function delete($models)
    {
        $key = $this->getKeyNameFromCollection($models);

        $models->each(function($model) use ($key) {
            /** @var \Illuminate\Database\Eloquent\Model|\Laravel\Scout\Searchable $model */
            $this->dispatched[$model->searchableAs()][$model->{$key}][] = $model->toSearchableArray();
        });

        $this->deleteFile($models->keyBy($key));
    }


    /**
     * @param \Illuminate\Database\Eloquent\Collection $models
     * @return string
     */
    public function getIndexFromCollection($models)
    {
        return $models->first()->searchableAs();
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection $models
     * @return string
     */
    public function getKeyNameFromCollection($models)
    {
        return $models->first()->getKeyName();
    }

    /**
     * Update file with data
     *
     * @param \Illuminate\Database\Eloquent\Collection $models
     * @return boolean
     * @throws \Exception
     */
    private function updateFile($models)
    {
        $items = $models->map(function ($model) {
            /** @var \Illuminate\Database\Eloquent\Model|\Laravel\Scout\Searchable $model */
            return $model->toSearchableArray();
        });

        if ($items->isNotEmpty()) {
            $index = $this->getIndexFromCollection($models);

            $existsData = $this->getData();

            if (in_array($index, $existsData->keys()->all())) {
                $data = $existsData->map(function ($value, $key) use ($index, $items) {
                    if ($key === $index) {
                        return array_merge($value, $items->toArray());
                    }

                    return $value;
                });
            }
            else {
                $data = $existsData->prepend($items->toArray(), $index);
            }

            if ($this->filesystem->put($this->tempFile, $data->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) === false) {
                throw new \Exception("Error occurred while writing to '$this->tempFile'");
            }
        }

        return true;
    }

    /**
     * Update file with data
     *
     * @param \Illuminate\Database\Eloquent\Collection $models
     * @return boolean
     * @throws \Exception
     */
    private function deleteFile($models)
    {
        if ($models->isNotEmpty()) {
            $index = $this->getIndexFromCollection($models);

            $existsData = $this->getData();

            if (in_array($index, $existsData->keys()->all())) {
                $otherData = $existsData->except($index);

                $current = $existsData->get($index);

                $models->each(function ($model) use (&$current) {
                    /** @var \Illuminate\Database\Eloquent\Model|\Laravel\Scout\Searchable $model */
                    unset($current[$model->{$model->getKeyName()}]);
                });

                $data = $otherData->prepend($current, $index);

                if ($this->filesystem->put($this->tempFile, $data->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) === false) {
                    throw new \Exception("Error occurred while writing to '$this->tempFile'");
                }
            }
        }

        return true;
    }

    /**
     * @param string $index
     * @param string $key
     * @return bool
     */
    public function isDispatched($index, $key)
    {
        return isset($this->dispatched[$index][$key]) && ! empty($this->dispatched[$index][$key]);
    }

    /**
     * @param string $index
     * @param string $key
     * @return bool
     */
    public function countDispatched($index, $key)
    {
        return isset($this->dispatched[$index][$key])? count($this->dispatched[$index][$key]): 0;
    }

    /**
     * @param string $index
     * @param string $key
     * @return bool
     */
    public function isNotDispatched($index, $key)
    {
        return ! $this->isDispatched($index, $key);
    }

    /**
     * @param string $index
     * @param string $key
     * @return bool
     */
    public function exists($index, $key)
    {
        return ! empty($this->getData($index, $key));
    }

    /**
     * @param string $index
     * @param string $key
     * @return bool
     */
    public function notExists($index, $key)
    {
        return ! $this->exists($index, $key);
    }

    /**
     * @param string $index
     * @param string $key
     * @param int $times
     * @return void
     */
    public function assertDispatched($index, $key, $times = 1)
    {
        $count = $this->countDispatched($index, $key);

        PHPUnit::assertTrue(
            $count === $times && $this->isDispatched($index, $key),
            "The expected [{$index}:{$key}] event was dispatched {$count} times instead of {$times} times."
        );
    }

    /**
     * @param string $index
     * @param string $key
     * @return void
     */
    public function assertNotDispatched($index, $key)
    {
        PHPUnit::assertTrue(
            $this->isNotDispatched($index, $key),
            "The unexpected [{$index}:{$key}] model was dispatched."
        );
    }

    /**
     * @param string $index
     * @param string $key
     * @return void
     */
    public function assertExists($index, $key)
    {
        PHPUnit::assertTrue(
            $this->exists($index, $key),
            "The expected [{$index}:{$key}] model was not exists."
        );
    }

    /**
     * @param string $index
     * @param string $key
     * @return bool
     */
    public function assertNotExists($index, $key)
    {
        PHPUnit::assertTrue(
            $this->notExists($index, $key),
            "The unexpected [{$index}:{$key}] model was exists."
        );
    }
}
