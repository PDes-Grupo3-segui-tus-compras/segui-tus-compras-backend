<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class RandomController extends Controller {

    public function responseToLifeTheUniverseAndEverything(): JsonResponse {
        return response()->json("42");
    }

}
