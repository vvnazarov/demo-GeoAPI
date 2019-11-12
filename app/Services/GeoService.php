<?php

namespace App\Services;

use App\Geo;
use Illuminate\Support\Collection;

class GeoService
{
    /**
     * Get all geos
     * @return Collection
     */
    public function all(): Collection
    {
        return Geo::all('id', 'name', 'description', 'type', 'area');
    }
}
