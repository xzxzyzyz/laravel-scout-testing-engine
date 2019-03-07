<?php

namespace Tests\Xzxzyzyz\Laravel\ScoutTestingEngine\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class CatModel extends Model
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
        return 'cats';
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
