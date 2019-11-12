<?php

namespace App\Services;

use App\Geo;
use Illuminate\Database\QueryException;
use App\Exceptions\GeoException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Log;

class GeoService
{
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

    /**
     * Get all geos
     * @return Collection
     */
    public function all(): Collection
    {
        return Geo::all('id', 'name', 'description', 'type', 'area');
    }

    /**
     * @param int $id
     * @return Geo
     * @throws \Exception
     */
    public function find(int $id): Geo
    {
        return Geo::findOrFail($id);
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     * @throws GeoException
     */
    public function store(Request $request)
    {
        $data = $this->validate($request, true);
        /** @var Geo $geo */
        $geo = Geo::create($data);
        return [
            'status' => env('APP_STATUS_OK_TEXT'),
            'result' => 'created',
            'id' => $geo->id,
        ];
    }

    /**
     * @param Request $request
     * @param int $id
     * @return array
     * @throws \Exception
     * @throws GeoException
     */
    public function update(Request $request, int $id)
    {
        $data = $this->validate($request, false);

        /** @var Geo $geo */
        $geo = Geo::findOrFail($id);
        $geo->fill($data);

        $messageOK = 'updated';
        $messageError = 'not ' . $messageOK;
        try {
            $success = DB::transaction(function () use ($geo) {
                return $geo->save();
            });
        } catch (GeoException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            if ($e instanceof QueryException) {
                $messageError .= ' (DB)';
            }
            throw new GeoException($messageError);
        }

        if ($success) {
            return [
                'status' => env('APP_STATUS_OK_TEXT'),
                'result' => $messageOK,
            ];
        } else {
            throw new GeoException($messageError . ' (can\'t save)');
        }
    }

    /**
     * @param int $id
     * @param bool $archive
     * @return array
     * @throws GeoException
     */
    public function destroy(int $id, bool $archive)
    {
        /** @var Geo $geo */
        $geo = Geo::findOrFail($id);

        if ($archive) {
            $messageOK = 'archived';
            $method = 'delete';
            $verb = 'archive';
        } else {
            $messageOK = 'deleted';
            $method = 'forceDelete';
            $verb = 'delete';
        }
        $messageError = 'not ' . $messageOK;

        try {
            if ($geo->$method()) {
                return [
                    'status' => env('APP_STATUS_OK_TEXT'),
                    'result' => $messageOK,
                ];
            } else {
                throw new GeoException($messageError . ' (can\'t ' . $verb . ')');
            }
        } catch (GeoException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            if ($e instanceof QueryException) {
                $messageError .= ' (DB)';
            }
            throw new GeoException($messageError);
        }
    }
}
