<?php

use App\Geo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GeoTableSeeder extends Seeder
{
    const COLUMN_INDEX_NAME = 2;
    const COLUMN_INDEX_GEOMETRY = 1;
    const DATABASE_FILE_PATH = 'seeds/geo.seed.csv';
    const HAS_HEADER_ROW = true;

    public function run()
    {
        $file_path = database_path(self::DATABASE_FILE_PATH);
        $file = fopen($file_path, 'r');
        if (self::HAS_HEADER_ROW) {
            fgetcsv($file);
        }

        while ($row = fgetcsv($file)) {
            Geo::create([
                'name' => $row[self::COLUMN_INDEX_NAME],
                'description' => Str::random(50),
                'type' => ['field', 'bed', 'mts'][rand(0, 2)],
                'geometry' => Geo::getPolygonFromWKT($row[self::COLUMN_INDEX_GEOMETRY])
            ]);
        }

        fclose($file);
    }


    /**
     * @param string $file_path
     * @param int $name_index
     * @param int $geometry_index
     * @param bool $skip_first_row
     * @throws Throwable
     */
    protected function seedFromFlatFile(
        string $file_path,
        int $name_index,
        int $geometry_index,
        bool $skip_first_row
    )
    {
        try {
            $file = fopen($file_path, 'r');
            if ($skip_first_row) {
                fgetcsv($file);
            }

            while ($row = fgetcsv($file)) {
                Geo::create([
                    'name' => $row[$name_index],
                    'description' => Str::random(50),
                    'type' => ['field', 'bed', 'mts'][rand(0,2)],
                    'geometry' => Geo::getPolygonFromWKT($row[$geometry_index])
                ]);
            }
        } finally {
            if ($file) {
                fclose($file);
            }
        }
    }
}
