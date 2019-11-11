<?php

namespace App\Http\Controllers;

use App\Geo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Exceptions\GeoException;
use Log;

class GeoController extends Controller
{
    /**
     * @return Geo[]
     */
    public function index()
    {
        return Geo::all('id', 'name', 'description', 'type', 'area');
    }

    /**
     * @param Request $request
     * @param int $id
     * @return mixed
     */
    public function show(Request $request, int $id)
    {
        return Geo::findOrFail($id);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(Request $request)
    {
        $data = $request->validate(Geo::getValidationRules(true));
        $data['geometry'] = Geo::getPolygonFromWKT($request->geometry);

        /** @var Geo $geo */
        $geo = Geo::create($data);
        return response()->json([
            'status' => env('APP_STATUS_OK_TEXT'),
            'result' => 'created',
            'id' => $geo->id,
        ]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws GeoException
     */
    public function update(Request $request, int $id)
    {
        $data = $request->validate(Geo::getValidationRules());
        if (isset($request->geometry)) {
            $data['geometry'] = Geo::getPolygonFromWKT($request->geometry);
        }

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
            return response()->json([
                'status' => env('APP_STATUS_OK_TEXT'),
                'result' => $messageOK,
            ]);
        } else {
            throw new GeoException($messageError . ' (can\'t save)');
        }
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws GeoException
     */
    public function destroy(Request $request, int $id)
    {
        /** @var Geo $geo */
        $geo = Geo::findOrFail($id);

        if ($request->archive === 'true') {
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
                return response()->json([
                    'status' => env('APP_STATUS_OK_TEXT'),
                    'result' => $messageOK,
                ]);
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
