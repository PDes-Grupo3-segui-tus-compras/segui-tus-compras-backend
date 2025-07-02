<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\MercadoLibreService;

/*  *
    *
    * title="Mercado Libre",
    * description="Endpoints para interactuar con la API de Mercado Libre"
    */
class MercadoLibreController extends Controller
{
    protected $mercadoLibreService;

    public function __construct(MercadoLibreService $mercadoLibreService)
    {
        $this->mercadoLibreService = $mercadoLibreService;
    }

    /**
     * @OA\Get(
     *     path="/api/search-products",
     *     summary="Buscar productos en Mercado Libre",
     *     description="Busca productos activos en Mercado Libre por texto de búsqueda",
     *     tags={"Mercado Libre"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Texto a buscar (por ejemplo: 'Samsung')",
     *         required=true,
     *         @OA\Schema(type="string", example="Samsung")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de productos encontrados",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="string", example="MLA12345678"),
     *                 @OA\Property(property="name", type="string", example="Samsung Galaxy S21"),
     *                 @OA\Property(property="image", type="string", example="https://http2.mlstatic.com/D_NQ_NP_2X_123-MLA123456789_0123-F.jpg")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validación fallida",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="object", example={"q": {"El campo q es obligatorio."}})
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
     *         response=421,
     *         description="Error al obtener los productos desde Mercado Libre",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unable to fetch product details from Mercado Libre."),
     *             @OA\Property(property="message", type="string", example="Access token expired")
     *         )
     *     )
     * )
     */
    public function searchProducts(Request $request) {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try{
            $query = $request->input('q', 'Samsung');
            $products = $this->mercadoLibreService->searchProducts($query);
            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to fetch product details from Mercado Libre.',
                'message' => $e->getMessage(),
            ], 421);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/products/get-product",
     *     summary="Obtener información de un producto de Mercado Libre",
     *     description="Devuelve información detallada de un producto en Mercado Libre por su ID",
     *     tags={"Mercado Libre"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="product_id",
     *         in="query",
     *         description="ID del producto en Mercado Libre",
     *         required=true,
     *         @OA\Schema(type="string", example="MLA12345678")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Información detallada del producto",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", example="MLA12345678"),
     *             @OA\Property(property="name", type="string", example="Samsung Galaxy S21"),
     *             @OA\Property(property="price", type="number", format="float", example=59999.99),
     *             @OA\Property(property="available_quantity", type="integer", example=10),
     *             @OA\Property(property="sold_quantity", type="integer", example=250),
     *             @OA\Property(property="thumbnail", type="string", example="https://example.com/img.jpg"),
     *             @OA\Property(property="short_description", type="string", example="Un smartphone potente y moderno")
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
     *         response=421,
     *         description="Error al obtener el producto desde Mercado Libre",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unable to fetch product details from Mercado Libre."),
     *             @OA\Property(property="message", type="string", example="Access token expired")
     *         )
     *     )
     * )
     */
    public function getProductInformation(Request $request){

        try{
            $product_id = $request->input('product_id');
            $product = $this->mercadoLibreService->getProductInformation($product_id);
            return response()->json($product);
        }catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to fetch product details from Mercado Libre.',
                'message' => $e->getMessage(),
            ], 421);
        }
    }
}
