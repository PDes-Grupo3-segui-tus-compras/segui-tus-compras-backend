<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\MercadoLibreService;

class MercadoLibreController extends Controller
{
    protected $mercadoLibreService;

    public function __construct(MercadoLibreService $mercadoLibreService)
    {
        $this->mercadoLibreService = $mercadoLibreService;
    }

    public function searchProducts(Request $request)
    {   
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
        } catch (RequestException $e) {
            return response()->json([
                'error' => 'Unable to fetch product details from Mercado Libre.',
                'message' => $e->getMessage(),
            ], 421);
        };
        
    }
    
    public function getProductInformation(Request $request){
        try{
            $product_id = $request->input('product_id');
            $product = $this->mercadoLibreService->getProductInformation($product_id);
            return response()->json($product);
        }catch (RequestException $e) {
            return response()->json([
                'error' => 'Unable to fetch product details from Mercado Libre.',
                'message' => $e->getMessage(),
            ], 421);
        };
    }
}
