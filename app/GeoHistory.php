<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;

class GeoHistory extends Model
{
    use SpatialTrait;

    protected $guarded = [];

    protected $spatialFields = [
        'geometry'
    ];
}
