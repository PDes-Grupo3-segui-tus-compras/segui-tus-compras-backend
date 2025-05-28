<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
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
}
