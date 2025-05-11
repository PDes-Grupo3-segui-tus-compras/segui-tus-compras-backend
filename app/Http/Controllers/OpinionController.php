<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Opinion;
use App\Services\ProductService;
use App\Http\Resources\OpinionResource;
use App\Http\Requests\Opinions\Store;
use App\Http\Requests\Opinions\Update;

class OpinionController extends Controller{
    
    public function __construct(private ProductService $service) {}

    public function index(){
        $opinions = Opinion::all();
        return response()->json($opinions);
    }
    
    public function store(Store $request){
        $validated = $request->validated();
        /*
        $productId = $validated['product_id'];
        $userId = auth()->user()->id;
        if (Opinion::where('user_id', $userId )->where('product_id', $productId)
        ->exists()){
            return response()->json(['error' => 'A User can only give only one opinion per product, try to update your old one'], 422);
        } 
        */
        $opinion = $this->service->createProductOpinion($validated);
        
        return response()->json(new OpinionResource($opinion), 201);
    }

    public function show(Opinion $opinion){

        return response()->json(new OpinionResource($opinion), 200);
    }

    public function update(Update $request, Opinion $opinion){
        
        $validated = $request->validated();
        
        if ($opinion->user_id !== auth()->user()->id) {
            return response()->json(['error' => 'Unauthorise to delete this opinion'], 403);
        }

        $opinion->update([
            'rating' => $validated['rating'],
            'content' => $validated['content'],
        ]);

        return response()->json(new OpinionResource($opinion), 200);
    }

    public function destroy(Request $request, Opinion $opinion){

        if ($opinion->user_id !== auth()->user()->id or auth()->user()->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorise to delete this opinion'], 403);
        }
        
        $opinion->delete();

        return response()->json(['message' => 'Opinión eliminada correctamente'], 200);
    }
}
