<?php

namespace App\Http\Controllers\Api\Pages;

use App\Http\Controllers\Controller;

class EntrepriseController extends Controller
{
    public function entrepriseIndex()
    {
        return response()->json(['page' => 'SaaSLanding/entreprise/Index']);
    }
}
