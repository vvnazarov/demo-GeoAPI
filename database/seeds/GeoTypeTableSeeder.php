<?php

use Illuminate\Database\Seeder;
use App\GeoType;

class GeoTypeTableSeeder extends Seeder
{
    public function run()
    {
        GeoType::create([
            'id'   => 'field',
            'name' => 'поле',
        ]);
        GeoType::create([
            'id'   => 'bed',
            'name' => 'грядка',
        ]);
        GeoType::create([
            'id'   => 'mts',
            'name' => 'МТС',
        ]);
    }
}
