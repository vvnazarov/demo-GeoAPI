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

    public const VALIDATION_RULES = [
        'name' => 'max:64',
        'description' => 'max:256',
        'type' => 'exists:geo_types,id',
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
            if ($model->getGeometryModification()['is_modified']) {
                $model->calculateArea();
            }
        });

        static::saved(function (self $model) {
            $geometryModification = $model->getGeometryModification();
            if (isset($geometryModification['previous']) && $geometryModification['is_modified']) {
                $model->saveHistory($geometryModification['previous_object']);
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
     * @return mixed[]
     */
    protected function getGeometryModification(): array
    {
        static $geometryModification = null;
        if (isset($geometryModification)) {
            return $geometryModification;
        }

        /** @var Polygon $previousGeometryObject */
        $previousGeometryObject = $this->getOriginal()['geometry'] ?? null;
        $previousGeometry = $previousGeometryObject ? $previousGeometryObject->toWKT() : null;
        $newGeometry = $this->getGeometryAsWKT();
        $geometryModification = [
            'is_modified' => $previousGeometry !== $newGeometry,
            'previous' => $previousGeometry,
            'previous_object' => $previousGeometryObject,
            'new' => $newGeometry,
        ];

        return $geometryModification;
    }

    /**
     * @throws GeoException
     */
    protected function calculateArea(): void
    {
        try {
            /** @todo
             * разобраться, почему не работает через binding
             * однако, теущий вариант также безопасен
             */
            $query = DB::selectOne(
                'SELECT ST_Area(ST_GeomFromText("' .
                $this->getGeometryAsWKT() .
                '")) AS area'
            );
            $this->area = $query->area;
        } catch (\Throwable $e) {
            throw new GeoException('cam\'t calculate geo\'s area');
        }
    }

    /**
     * @param Polygon $history
     * @throws GeoException
     */
    protected function saveHistory(Polygon $history): void
    {
        try {
            GeoHistory::create([
                'geo_id' => $this->id,
                'geometry' => $history,
            ]);
        } catch (\Throwable $e) {
            Log::error($e);
            throw new GeoException('cam\'t save geo\'s history');
        }
    }
}
