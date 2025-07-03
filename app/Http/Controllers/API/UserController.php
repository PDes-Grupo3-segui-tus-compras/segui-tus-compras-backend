<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ListPurchaseResource;
use App\Http\Resources\ListProductResource;

/*  *
    *
    * title="Users",
    * description="Endpoints involving listing users"
    */
class UserController extends Controller {

    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="User list",
     *     description="User list",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User list",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@doe.com"),
     *                 @OA\Property(property="email_verified_at", type="string", format="date-time", example="2024-06-01T15:03:00Z"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-01T15:03:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-01T15:03:00Z"),
     *                 @OA\Property(property="user_type", type="string", example="user")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Error: Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorise to delete this opinion")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse {
        $users = User::all();
        return response()->json($users);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}/favourites",
     *     summary="List of Favourite products of user",
     *     description="List of Favourite products of user",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of Favourite products of user",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="catalog_product_id", type="string", example="MLA29815169"),
     *                 @OA\Property(property="id", type="string", example="12"),
     *                 @OA\Property(property="price", type="float", example="5000,50"),
     *                 @OA\Property(property="name", type="string", example="PlayStation 5"),
     *                 @OA\Property(property="image", type="string", example="https://example.com/image.jpg"),
     *                 @OA\Property(property="short_description", type="string", example="Consola de videojuegos de última generación")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Error: Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Not authorized to access this user data",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="nauthorise to access this user data")
     *         )
     *     )
     * )
     */
    public function favourites(Request $request, User $user): JsonResponse {

        if ($user->id !== auth()->user()->id && auth()->user()->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorise to access this user data'], 403);
        }
        $favourites = $user->favouriteProducts;

        return response()->json(ListProductResource::collection($favourites));
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}/purchases",
     *     summary="List of Purchased products of user",
     *     description="List of Purchased products of user",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of Purchased products of user",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="catalog_product_id", type="string", example="MLA29815169"),
     *                 @OA\Property(property="id", type="string", example="12"),
     *                 @OA\Property(property="name", type="string", example="PlayStation 5"),
     *                 @OA\Property(property="image", type="string", example="https://example.com/image.jpg"),
     *                 @OA\Property(property="short_description", type="string", example="Consola de videojuegos de última generación"),
     *                 @OA\Property(property="quantity", type="integer", example=1),
     *                 @OA\Property(property="price", type="number", format="float", example=599.99),
     *                 @OA\Property(property="purchase_date", type="string", format="date", example="2025-05-05")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Error: Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Not authorized to access this user data",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="nauthorise to access this user data")
     *         )
     *     )
     * )
     */
    public function purchases(Request $request, User $user): JsonResponse {
        if ($user->id !== auth()->user()->id && auth()->user()->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorise to access this user data'], 403);
        }

        $purchases = $user->purchases()->with('product')->get();

        return response()->json(ListPurchaseResource::collection($purchases));
    }
}
