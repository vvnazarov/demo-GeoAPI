<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Grimzy\LaravelMysqlSpatial\Types\Polygon;
use Illuminate\Support\Facades\DB;
use App\Exceptions\GeoException;

class Geo extends Model
{
    use SpatialTrait, SoftDeletes;

    protected $guarded = [];

    protected $spatialFields = [
        'geometry'
    ];

    public function type()
    {
        return $this->belongsTo(GeoType::class);
    }

    public function histories()
    {
        return $this->hasMany(GeoHistory::class);
    }

    public static function boot()
    {
        parent::boot();

        static::saving(function (self $model) {
            /** @todo оптимизировать */
            try {
                $query = DB::selectOne(
                    'SELECT ST_Area(ST_GeomFromText("' .
                    $model->getGeometryAsWKT() .
                    '")) AS area'
                );
                $model->area = $query->area;
            } catch (\Exception $e) {
                throw new GeoException('cam\'t calculate geo\'s area');
            }
        });

        static::saved(function (self $model) {
            $previousGeometry = $model->getOriginal()['geometry'] ?? null;
            if (
                isset($previousGeometry) &&
                ($previousGeometry->toWKT() !== $model->getGeometryAsWKT())
            ) {
                GeoHistory::create([
                    'geo_id'   => $model->id,
                    'geometry' => $previousGeometry,
                ]);
            }
        });
    }

    /**
     * @param string $geometry
     * @return Polygon
     * @throws \Exception
     */
    public static function getPolygonFromWKT(string $geometry): Polygon
    {
        try {
            return Polygon::fromWKT($geometry);
        } catch (\Throwable $e) {
            throw new GeoException('illegal WKT');
        }
    }

    /**
     * @return string
     */
    public function getGeometryAsWKT(): string
    {
        return $this->geometry->toWKT();
    }

    /**
     * @param bool $creation
     * @return array
     */
    public static function validationRules(bool $creation = false): array
    {
        $validationRules = [
            'name' => 'max:64',
            'description' => 'max:256',
            'type' => 'exists:geo_types,id',
        ];

        if ($creation) {
            $validationRules = array_map(function ($value) {
                return 'required|' . $value;
            }, $validationRules);

        }
        return $validationRules;
    }
}
