<?php

namespace Tests\Xzxzyzyz\Laravel\ScoutTestingEngine\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class DogModel extends Model
{
    use Searchable;

    public $id = 1;

    public function __construct(array $attributes = [])
    {
        if (isset($attributes['id'])) {
            $this->id = $attributes['id'];
            unset($attributes['id']);
        }

        parent::__construct($attributes);
    }

    public function searchableAs()
    {
        return 'dogs';
    }

    public function getKey()
    {
        return $this->id;
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
        ];
    }
}
