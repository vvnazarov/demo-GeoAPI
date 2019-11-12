<?php

namespace App\Http\Controllers;

use App\Geo;
use App\Services\GeoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Exceptions\GeoException;

class GeoController extends Controller
{
    /** @var GeoService */
    protected $service;

    /**
     * GeoController constructor.
     * @param GeoService $service
     */
    public function __construct(GeoService $service)
    {
        $this->service = $service;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function index()
    {
        return $this->service->all();
    }

    /**
     * @param int $id
     * @return Geo
     * @throws \Exception
     */
    public function show(int $id)
    {
        return $this->service->find($id);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(Request $request)
    {
        return response()->json([
            $this->service->store($request)
        ]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @throws GeoException
     */
    public function update(Request $request, int $id)
    {
        return response()->json([
            $this->service->update($request, $id)
        ]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @throws GeoException
     */
    public function destroy(Request $request, int $id)
    {
        return response()->json([
            $this->service->destroy($id, $request->archive === 'true')
        ]);
    }
}
