<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Nnjeim\World\Models\City;
use Nnjeim\World\Models\Country;

class LocationController extends Controller
{
    public function countries(): JsonResponse
    {
        $countries = Country::select('id', 'iso2', 'name', 'emoji')
            ->orderBy('name')
            ->get();

        return response()->json($countries);
    }

    public function cities(Country $country): JsonResponse
    {
        $cities = City::where('country_id', $country->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($cities);
    }
}
