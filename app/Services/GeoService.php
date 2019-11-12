<?php

namespace App\Services;

use App\Geo;
use Illuminate\Http\Request;
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


    /**
     * @param Request $request
     * @param bool $creation
     * @return array
     * @throws \Exception
     */
    public function validate(Request $request, bool $creation = false): array
    {
        $validationRules = Geo::VALIDATION_RULES;
        if ($creation) {
            $validationRules = array_map(function ($value) {
                return 'required|' . $value;
            }, $validationRules);
        }

        $validatedData = $request->validate($validationRules);
        if (isset($request->geometry)) {
            $validatedData['geometry'] = Geo::getPolygonFromWKT($request->geometry);
        }

        return $validatedData;
    }

}
