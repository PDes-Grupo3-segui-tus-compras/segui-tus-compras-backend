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

/**
  * 
  * title="Opinions",
  * description="Endpoints relacionados a compras y favoritos de productos"
  */
class OpinionController extends Controller{

    public function __construct(private readonly ProductService $service) {}

    public function index(): JsonResponse {
        $opinions = Opinion::all();
        return response()->json($opinions);
    }
    
    /**
     * @OA\Post(
     *     path="/api/opinions",
     *     summary="Create an opinión",
     *     tags={"Opinions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"catalog_product_id", "name", "image", "price", "content", "rating"},
     *             @OA\Property(property="catalog_product_id", type="string", example="MLA29815169"),
     *             @OA\Property(property="name", type="string", example="Luke Skywalker Star Wars Kenner Star Wars"),
     *             @OA\Property(property="image", type="string", example="https://example.com/image.jpg"),
     *             @OA\Property(property="short_description", type="string", nullable=true, example="Muñeco starwars Test"),
     *             @OA\Property(property="price", type="number", format="float", example=599.99),
     *             @OA\Property(property="content", type="string", example="Really Good Product"),
     *             @OA\Property(property="rating", type="integer", minimum=1, maximum=5, example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Opinion created",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Opinion created"),
     *             @OA\Property(property="purchase", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="catalog_product_id", type="string", example="MLA29815169"),
     *                 @OA\Property(property="user_id", type="integer", example=2),
     *                 @OA\Property(property="rating", type="integer", minimum=1, maximum=5, example=5),
     *                 @OA\Property(property="content", type="string", example="Really Good Product"),
     *                 @OA\Property(property="user_name", type="string", example="John Doe"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-01T15:03:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-01T15:03:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="Error", type="string", example="Validation Error.")
     *         )
     *     )
     * )
     */
    public function store(Store $request): JsonResponse {
        $validated = $request->validated();

        $opinion = $this->service->createProductOpinion($validated);

        return response()->json(['data' => new OpinionResource($opinion)], 201);
    }

    public function show(Opinion $opinion): JsonResponse {

        return response()->json(['data' => new OpinionResource($opinion)], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/opinions/{id}",
     *     summary="Update an opinión",
     *     tags={"Opinions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content", "rating"},
     *             @OA\Property(property="content", type="string", example="Actualización del comentario"),
     *             @OA\Property(property="rating", type="integer", minimum=1, maximum=5, example=4)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Opinion updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Opinion updated"),
     *             @OA\Property(property="purchase", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="product_id", type="integer", example=5),
     *                 @OA\Property(property="user_id", type="integer", example=2),
     *                 @OA\Property(property="rating", type="integer", minimum=1, maximum=5, example=5),
     *                 @OA\Property(property="content", type="string", example="Updated opinion content"),
     *                 @OA\Property(property="user_name", type="string", example="John Doe"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-01T15:03:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-01T15:03:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorised",
     *         @OA\JsonContent(
     *             @OA\Property(property="Error", type="string", example="Unauthorised to change this opinion.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="Error", type="string", example="Validation Error.")
     *         )
     *     )
     * )
     */
    public function update(Update $request, Opinion $opinion): JsonResponse {

        $validated = $request->validated();

        if ($opinion->user_id !== auth()->user()->id) {
            return response()->json(['error' => 'Unauthorised to change this opinion'], 403);
        }

        $opinion->update([
            'rating' => $validated['rating'],
            'content' => $validated['content'],
        ]);

        return response()->json(['data' => new OpinionResource($opinion)], 200);
    }


    /**
     * @OA\Delete(
     *     path="/api/opinions/{id}",
     *     summary="Delete an opinion",
     *     description="Allows to delete an opinion if the user is the owner or if the user is an admin.",
     *     tags={"Opinions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the opinion to delete",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Opinion successfully deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Opinion successfully deleted")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Not authorized to delete this opinion",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorise to delete this opinion")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid authentication token",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized.")
     *         )
     *     )
     * )
     */
    public function destroy(Opinion $opinion) : JsonResponse {

        if ($opinion->user_id !== auth()->user()->id && auth()->user()->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorise to delete this opinion'], 403);
        }

        $opinion->delete();

        return response()->json(['message' => 'Opinion successfully deleted'], 200);
    }
}
