<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Grimzy\LaravelMysqlSpatial\Types\Polygon;
use Illuminate\Support\Facades\DB;
use App\Exceptions\GeoException;
use Log;

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
            if (!$model->getGeometryChanges()['changed']) {
                return;
            }

            try {
                /** @todo разобраться, почему не работает через binding */
                $query = DB::selectOne(
                    'SELECT ST_Area(ST_GeomFromText("' .
                    $model->getGeometryAsWKT() .
                    '")) AS area'
                );
                $model->area = $query->area;
            } catch (\Throwable $e) {
                throw new GeoException('cam\'t calculate geo\'s area');
            }
        });

        static::saved(function (self $model) {
            $geometryChanges = $model->getGeometryChanges();
            if (isset($geometryChanges['previous']) && $geometryChanges['changed']) {
                try {
                    GeoHistory::create([
                        'geo_id'   => $model->id,
                        'geometry' => $geometryChanges['previous_object'],
                    ]);
                } catch (\Throwable $e) {
                    Log::error($e);
                    throw new GeoException('cam\'t save geo\'s history');
                }
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
     * @return array
     */
    public function getGeometryChanges(): array
    {
        static $geometryChanges = null;

        if (isset($geometryChanges)) {
            return $geometryChanges;
        }

        /** @var Polygon $previousGeometryObject */
        $previousGeometryObject = $this->getOriginal()['geometry'] ?? null;
        $previousGeometry = $previousGeometryObject ? $previousGeometryObject->toWKT() : null;
        $newGeometry = $this->getGeometryAsWKT();
        $geometryChanges = [
            'changed'  => $previousGeometry !== $newGeometry,
            'previous' => $previousGeometry,
            'previous_object' => $previousGeometryObject,
            'new'      => $newGeometry,
        ];
        return $geometryChanges;

    }

    /**
     * @param bool $creation
     * @return array
     */
    public static function getValidationRules(bool $creation = false): array
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
