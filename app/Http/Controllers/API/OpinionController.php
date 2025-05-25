<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Opinions\Store;
use App\Http\Requests\Opinions\Update;
use App\Http\Resources\OpinionResource;
use App\Models\Opinion;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OpinionController extends Controller{

    public function __construct(private readonly ProductService $service) {}

    public function index(): JsonResponse {
        $opinions = Opinion::all();
        return response()->json($opinions);
    }

    public function store(Store $request): JsonResponse {
        $validated = $request->validated();

        $opinion = $this->service->createProductOpinion($validated);

        return response()->json(['data' => new OpinionResource($opinion)], 201);
    }

    public function show(Opinion $opinion): JsonResponse {

        return response()->json(['data' => new OpinionResource($opinion)], 200);
    }

    public function update(Update $request, Opinion $opinion): JsonResponse {

        $validated = $request->validated();

        if ($opinion->user_id !== auth()->user()->id) {
            return response()->json(['error' => 'Unauthorised to delete this opinion'], 403);
        }

        $opinion->update([
            'rating' => $validated['rating'],
            'content' => $validated['content'],
        ]);

        return response()->json(['data' => new OpinionResource($opinion)], 200);
    }

    public function destroy(Opinion $opinion) : JsonResponse {

        if ($opinion->user_id !== auth()->user()->id && auth()->user()->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorise to delete this opinion'], 403);
        }

        $opinion->delete();

        return response()->json(['message' => 'Opinion successfully deleted'], 200);
    }
}
